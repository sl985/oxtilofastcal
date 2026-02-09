<?php
/**
 * Rate Limiter for Oxtilofastcal.
 *
 * @package Oxtilofastcal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Rate Limiter class.
 *
 * Provides basic rate limiting functionality to protect against
 * abuse (booking spam, brute force hash enumeration, DoS).
 *
 * @since 0.8.0
 */
final class Oxtilofastcal_Rate_Limiter {

	/**
	 * Single instance.
	 *
	 * @var self|null
	 */
	private static ?self $instance = null;

	/**
	 * Detected WAF/CDN provider.
	 *
	 * @var string|false
	 */
	private $detected_provider = null;

	/**
	 * Get instance.
	 *
	 * @return self
	 */
	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		// Empty - initialization happens on first use.
	}

	/**
	 * Check rate limit for the current request.
	 *
	 * @param string $context Context identifier (e.g., 'ajax', 'api', 'booking', 'manage').
	 * @return bool True if request is allowed, false if rate limited.
	 */
	public function check( string $context = 'general' ): bool {
		// Check if rate limiting is enabled.
		$security = $this->get_security_settings();
		
		if ( empty( $security['rate_limit_enabled'] ) ) {
			return true;
		}

		$limit    = isset( $security['rate_limit_requests'] ) ? absint( $security['rate_limit_requests'] ) : 30;
		$window   = isset( $security['rate_limit_window'] ) ? absint( $security['rate_limit_window'] ) : 60;

		if ( $limit <= 0 || $window <= 0 ) {
			return true;
		}

		$ip  = $this->get_client_ip();
		$key = 'oxtilofastcal_rl_' . md5( $ip . '_' . $context );

		$count = (int) get_transient( $key );

		if ( $count >= $limit ) {
			return false;
		}

		set_transient( $key, $count + 1, $window );

		return true;
	}

	/**
	 * Send rate limit exceeded response for AJAX requests.
	 *
	 * @return void
	 */
	public function send_ajax_error(): void {
		wp_send_json_error(
			array( 'message' => __( 'Too many requests. Please try again later.', 'oxtilofastcal' ) ),
			429
		);
	}

	/**
	 * Send rate limit exceeded response for REST API requests.
	 *
	 * @return WP_Error
	 */
	public function get_rest_error(): WP_Error {
		return new WP_Error(
			'rate_limit_exceeded',
			__( 'Too many requests. Please try again later.', 'oxtilofastcal' ),
			array( 'status' => 429 )
		);
	}

	/**
	 * Send rate limit exceeded response for regular requests (wp_die).
	 *
	 * @return void
	 */
	public function send_die_error(): void {
		wp_die(
			esc_html__( 'Too many requests. Please try again later.', 'oxtilofastcal' ),
			esc_html__( 'Rate Limit Exceeded', 'oxtilofastcal' ),
			array( 'response' => 429 )
		);
	}

	/**
	 * Get the client IP address.
	 *
	 * Takes into account WAF/CDN proxy headers based on configuration.
	 *
	 * @return string Client IP address.
	 */
	public function get_client_ip(): string {
		$security = $this->get_security_settings();
		$source   = $security['ip_source'] ?? 'auto';

		// Manual sources (explicit header trust).
		if ( 'cf_connecting_ip' === $source && isset( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
			return $this->sanitize_ip( $_SERVER['HTTP_CF_CONNECTING_IP'] );
		}

		if ( 'x_forwarded_for' === $source && isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			return $this->extract_first_ip( $_SERVER['HTTP_X_FORWARDED_FOR'] );
		}

		if ( 'x_real_ip' === $source && isset( $_SERVER['HTTP_X_REAL_IP'] ) ) {
			return $this->sanitize_ip( $_SERVER['HTTP_X_REAL_IP'] );
		}

		if ( 'sucuri' === $source && isset( $_SERVER['HTTP_X_SUCURI_CLIENTIP'] ) ) {
			return $this->sanitize_ip( $_SERVER['HTTP_X_SUCURI_CLIENTIP'] );
		}

		if ( 'cloudfront' === $source && isset( $_SERVER['HTTP_CLOUDFRONT_VIEWER_ADDRESS'] ) ) {
			// CloudFront format can be "IP:PORT".
			$cf_ip = $_SERVER['HTTP_CLOUDFRONT_VIEWER_ADDRESS'];
			$pos   = strrpos( $cf_ip, ':' );
			if ( false !== $pos ) {
				$cf_ip = substr( $cf_ip, 0, $pos );
			}
			return $this->sanitize_ip( $cf_ip );
		}

		if ( 'fastly' === $source && isset( $_SERVER['HTTP_FASTLY_CLIENT_IP'] ) ) {
			return $this->sanitize_ip( $_SERVER['HTTP_FASTLY_CLIENT_IP'] );
		}

		// Auto detection mode.
		if ( 'auto' === $source ) {
			$provider = $this->detect_waf_provider();

			if ( 'Cloudflare' === $provider && isset( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
				return $this->sanitize_ip( $_SERVER['HTTP_CF_CONNECTING_IP'] );
			}

			if ( 'Sucuri' === $provider && isset( $_SERVER['HTTP_X_SUCURI_CLIENTIP'] ) ) {
				return $this->sanitize_ip( $_SERVER['HTTP_X_SUCURI_CLIENTIP'] );
			}

			if ( 'CloudFront' === $provider && isset( $_SERVER['HTTP_CLOUDFRONT_VIEWER_ADDRESS'] ) ) {
				$cf_ip = $_SERVER['HTTP_CLOUDFRONT_VIEWER_ADDRESS'];
				$pos   = strrpos( $cf_ip, ':' );
				if ( false !== $pos ) {
					$cf_ip = substr( $cf_ip, 0, $pos );
				}
				return $this->sanitize_ip( $cf_ip );
			}

			if ( 'Fastly' === $provider && isset( $_SERVER['HTTP_FASTLY_CLIENT_IP'] ) ) {
				return $this->sanitize_ip( $_SERVER['HTTP_FASTLY_CLIENT_IP'] );
			}
		}

		// Fallback to REMOTE_ADDR.
		if ( 'remote_addr' === $source || 'auto' === $source ) {
			return $this->sanitize_ip( $_SERVER['REMOTE_ADDR'] ?? '' );
		}

		return $this->sanitize_ip( $_SERVER['REMOTE_ADDR'] ?? '' );
	}

	/**
	 * Detect WAF/CDN provider from request headers.
	 *
	 * @return string|false Provider name or false if none detected.
	 */
	public function detect_waf_provider() {
		if ( null !== $this->detected_provider ) {
			return $this->detected_provider;
		}

		// Cloudflare detection.
		if ( isset( $_SERVER['HTTP_CF_CONNECTING_IP'] ) || isset( $_SERVER['HTTP_CF_RAY'] ) ) {
			$this->detected_provider = 'Cloudflare';
			return $this->detected_provider;
		}

		// Sucuri detection.
		if ( isset( $_SERVER['HTTP_X_SUCURI_CLIENTIP'] ) ) {
			$this->detected_provider = 'Sucuri';
			return $this->detected_provider;
		}

		// AWS CloudFront detection.
		if ( isset( $_SERVER['HTTP_CLOUDFRONT_VIEWER_ADDRESS'] ) ) {
			$this->detected_provider = 'CloudFront';
			return $this->detected_provider;
		}

		// Fastly detection.
		if ( isset( $_SERVER['HTTP_FASTLY_CLIENT_IP'] ) ) {
			$this->detected_provider = 'Fastly';
			return $this->detected_provider;
		}

		// Generic proxy (less precise).
		if ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$this->detected_provider = 'Generic Proxy/CDN';
			return $this->detected_provider;
		}

		$this->detected_provider = false;
		return $this->detected_provider;
	}

	/**
	 * Get WAF/CDN provider information with plugin recommendation.
	 *
	 * @return array{provider: string|false, plugin: string|false, plugin_url: string|false, warning: string|false}
	 */
	public function get_waf_info(): array {
		$provider = $this->detect_waf_provider();

		$info = array(
			'provider'   => $provider,
			'plugin'     => false,
			'plugin_url' => false,
			'warning'    => false,
		);

		switch ( $provider ) {
			case 'Cloudflare':
				$info['plugin']     = 'Cloudflare';
				$info['plugin_url'] = 'https://wordpress.org/plugins/cloudflare/';
				// Check if Cloudflare plugin is active.
				if ( ! is_plugin_active( 'cloudflare/cloudflare.php' ) && ! is_plugin_active( 'cloudflare-flexible-ssl/cloudflare-flexible-ssl.php' ) ) {
					$info['warning'] = sprintf(
						/* translators: %s: plugin URL */
						__( 'Cloudflare detected. For accurate IP detection, consider installing the <a href="%s" target="_blank">Cloudflare plugin</a>.', 'oxtilofastcal' ),
						$info['plugin_url']
					);
				}
				break;

			case 'Sucuri':
				$info['plugin']     = 'Sucuri Security';
				$info['plugin_url'] = 'https://wordpress.org/plugins/sucuri-scanner/';
				if ( ! is_plugin_active( 'sucuri-scanner/sucuri.php' ) ) {
					$info['warning'] = sprintf(
						/* translators: %s: plugin URL */
						__( 'Sucuri detected. For accurate IP detection, consider installing the <a href="%s" target="_blank">Sucuri Security plugin</a>.', 'oxtilofastcal' ),
						$info['plugin_url']
					);
				}
				break;

			case 'CloudFront':
				$info['warning'] = __( 'AWS CloudFront detected. IP detection should work automatically with HTTP_CLOUDFRONT_VIEWER_ADDRESS header.', 'oxtilofastcal' );
				break;

			case 'Fastly':
				$info['warning'] = __( 'Fastly detected. IP detection should work automatically with HTTP_FASTLY_CLIENT_IP header.', 'oxtilofastcal' );
				break;

			case 'Generic Proxy/CDN':
				$info['warning'] = __( 'A proxy or CDN is detected (X-Forwarded-For header present). If rate limiting is not working correctly, please configure the IP source manually below.', 'oxtilofastcal' );
				break;
		}

		return $info;
	}

	/**
	 * Get security settings.
	 *
	 * @return array
	 */
	private function get_security_settings(): array {
		$security = get_option( 'oxtilofastcal_security', array() );
		return is_array( $security ) ? $security : array();
	}

	/**
	 * Sanitize and validate IP address.
	 *
	 * @param string $ip IP address to sanitize.
	 * @return string Sanitized IP or empty string if invalid.
	 */
	private function sanitize_ip( $ip ): string {
		$ip = trim( (string) $ip );

		// Validate IPv4 or IPv6.
		if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6 ) ) {
			return $ip;
		}

		return '';
	}

	/**
	 * Extract the first (leftmost) IP from X-Forwarded-For header.
	 *
	 * @param string $header X-Forwarded-For header value.
	 * @return string First valid IP address.
	 */
	private function extract_first_ip( $header ): string {
		$ips = explode( ',', (string) $header );
		foreach ( $ips as $ip ) {
			$clean_ip = $this->sanitize_ip( $ip );
			if ( '' !== $clean_ip ) {
				return $clean_ip;
			}
		}
		return '';
	}
}
