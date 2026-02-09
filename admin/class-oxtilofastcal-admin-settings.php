<?php
/**
 * Admin settings registration for Oxtilofastcal.
 *
 * @package Oxtilofastcal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Settings class.
 *
 * @since 0.5.0
 */
final class Oxtilofastcal_Admin_Settings {

	/**
	 * Register all settings.
	 */
	public static function register(): void {
		register_setting( 'oxtilofastcal_general_group', 'oxtilofastcal_general', array(
			'type'              => 'array',
			'sanitize_callback' => array( __CLASS__, 'sanitize_general' ),
			'default'           => array(),
		) );

		register_setting( 'oxtilofastcal_services_group', 'oxtilofastcal_services_json', array(
			'type'              => 'string',
			'sanitize_callback' => array( __CLASS__, 'sanitize_services_json' ),
			'default'           => '[]',
		) );

		register_setting( 'oxtilofastcal_working_hours_group', 'oxtilofastcal_working_hours', array(
			'type'              => 'array',
			'sanitize_callback' => array( __CLASS__, 'sanitize_working_hours' ),
			'default'           => array(),
		) );

		register_setting( 'oxtilofastcal_ics_group', 'oxtilofastcal_ics_feeds', array(
			'type'              => 'array',
			'sanitize_callback' => array( __CLASS__, 'sanitize_ics_feeds' ),
			'default'           => array(),
		) );

		register_setting( 'oxtilofastcal_email_templates_group', 'oxtilofastcal_email_templates', array(
			'type'              => 'array',
			'sanitize_callback' => array( __CLASS__, 'sanitize_email_templates' ),
			'default'           => array(),
		) );

		register_setting( 'oxtilofastcal_security_group', 'oxtilofastcal_security', array(
			'type'              => 'array',
			'sanitize_callback' => array( __CLASS__, 'sanitize_security' ),
			'default'           => array(),
		) );
	}

	/**
	 * Sanitize general settings.
	 *
	 * @param mixed $value Input value.
	 * @return array
	 */
	public static function sanitize_general( $value ): array {
		$value   = is_array( $value ) ? $value : array();
		$current = get_option( 'oxtilofastcal_general', array() );
		$current = is_array( $current ) ? $current : array();

		// Calendar feed token (read-only, for ICS feeds).
		$token = $current['calendar_feed_token'] ?? '';
		if ( '' === $token || ! preg_match( '/^[a-zA-Z0-9]+$/', (string) $token ) ) {
			$token = oxtilofastcal_generate_alnum_token( 32 );
		}

		// API token (read/write, for REST API - separate for security).
		$api_token = $current['api_token'] ?? '';
		if ( '' === $api_token || ! preg_match( '/^[a-zA-Z0-9]+$/', (string) $api_token ) ) {
			$api_token = oxtilofastcal_generate_alnum_token( 48 );
		}

		return array(
			'admin_notification_email' => isset( $value['admin_notification_email'] ) ? sanitize_email( $value['admin_notification_email'] ) : '',
			'admin_name'               => isset( $value['admin_name'] ) ? sanitize_text_field( $value['admin_name'] ) : '',
			'min_lead_time'            => isset( $value['min_lead_time'] ) ? absint( $value['min_lead_time'] ) : 60,
			'max_days_future'          => isset( $value['max_days_future'] ) ? absint( $value['max_days_future'] ) : 30,
			'google_meet_link'         => isset( $value['google_meet_link'] ) ? esc_url_raw( $value['google_meet_link'] ) : '',
			'calendar_feed_token'      => $token,
			'api_token'                => $api_token,
			'time_format_display'      => isset( $value['time_format_display'] ) && '12h' === $value['time_format_display'] ? '12h' : '24h',
			'time_slot_interval'       => isset( $value['time_slot_interval'] ) && in_array( (int) $value['time_slot_interval'], array( 15, 30, 60 ), true ) ? (int) $value['time_slot_interval'] : 30,
			'include_manage_link'      => ! empty( $value['include_manage_link'] ) ? 1 : 0,
		);
	}

	/**
	 * Sanitize services JSON.
	 *
	 * @param mixed $value Input value.
	 * @return string
	 */
	public static function sanitize_services_json( $value ): string {
		$json    = is_string( $value ) ? $value : wp_json_encode( $value );
		$decoded = json_decode( $json, true );

		if ( ! is_array( $decoded ) ) {
			return '[]';
		}

		$out = array();
		foreach ( $decoded as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}

			$name     = isset( $row['name'] ) ? sanitize_text_field( $row['name'] ) : '';
			$duration = isset( $row['duration'] ) ? absint( $row['duration'] ) : 0;
			$type     = isset( $row['type'] ) ? sanitize_text_field( $row['type'] ) : 'online';

			if ( '' === $name ) {
				continue;
			}
			if ( $duration <= 0 ) {
				$duration = 30;
			}
			$type = in_array( $type, array( 'online', 'in_person' ), true ) ? $type : 'online';

			$out[] = array( 'name' => $name, 'duration' => $duration, 'type' => $type );
		}

		return (string) wp_json_encode( $out );
	}

	/**
	 * Sanitize working hours.
	 *
	 * @param mixed $value Input value.
	 * @return array
	 */
	public static function sanitize_working_hours( $value ): array {
		$value = is_array( $value ) ? $value : array();
		$days  = array( 'mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun' );
		$out   = array();

		foreach ( $days as $day ) {
			$start   = isset( $value[ $day ]['start'] ) ? sanitize_text_field( $value[ $day ]['start'] ) : '';
			$end     = isset( $value[ $day ]['end'] ) ? sanitize_text_field( $value[ $day ]['end'] ) : '';
			$day_off = ! empty( $value[ $day ]['day_off'] ) ? 1 : 0;

			$out[ $day ] = array(
				'start'   => oxtilofastcal_sanitize_time_hhmm( $start ),
				'end'     => oxtilofastcal_sanitize_time_hhmm( $end ),
				'day_off' => $day_off,
			);
		}

		return $out;
	}

	/**
	 * Sanitize ICS feeds.
	 *
	 * @param mixed $value Input value.
	 * @return array
	 */
	public static function sanitize_ics_feeds( $value ): array {
		$value = is_array( $value ) ? $value : array();

		$freq = isset( $value['update_frequency'] ) ? absint( $value['update_frequency'] ) : 60;
		if ( $freq < 5 ) {
			$freq = 5; // Minimum 5 minutes.
		}

		return array(
			'icloud'           => isset( $value['icloud'] ) ? esc_url_raw( $value['icloud'] ) : '',
			'proton'           => isset( $value['proton'] ) ? esc_url_raw( $value['proton'] ) : '',
			'holidays'         => isset( $value['holidays'] ) ? esc_url_raw( $value['holidays'] ) : '',
			'update_frequency' => $freq,
		);
	}

	/**
	 * Sanitize email templates.
	 *
	 * @param mixed $value Input value.
	 * @return array
	 */
	public static function sanitize_email_templates( $value ): array {
		$value = is_array( $value ) ? $value : array();

		// Allowed HTML tags for email content.
		$allowed_html = array(
			'a'      => array( 'href' => true, 'title' => true, 'style' => true, 'class' => true ),
			'br'     => array(),
			'p'      => array( 'style' => true, 'class' => true ),
			'strong' => array( 'style' => true, 'class' => true ),
			'b'      => array( 'style' => true, 'class' => true ),
			'em'     => array( 'style' => true, 'class' => true ),
			'i'      => array( 'style' => true, 'class' => true ),
			'u'      => array( 'style' => true, 'class' => true ),
			'span'   => array( 'style' => true, 'class' => true ),
			'div'    => array( 'style' => true, 'class' => true ),
			'h1'     => array( 'style' => true, 'class' => true ),
			'h2'     => array( 'style' => true, 'class' => true ),
			'h3'     => array( 'style' => true, 'class' => true ),
			'h4'     => array( 'style' => true, 'class' => true ),
			'ul'     => array( 'style' => true, 'class' => true ),
			'ol'     => array( 'style' => true, 'class' => true ),
			'li'     => array( 'style' => true, 'class' => true ),
			'table'  => array( 'style' => true, 'class' => true, 'border' => true, 'cellpadding' => true, 'cellspacing' => true, 'width' => true ),
			'tr'     => array( 'style' => true, 'class' => true ),
			'td'     => array( 'style' => true, 'class' => true, 'colspan' => true, 'rowspan' => true, 'width' => true, 'align' => true, 'valign' => true ),
			'th'     => array( 'style' => true, 'class' => true, 'colspan' => true, 'rowspan' => true, 'width' => true, 'align' => true, 'valign' => true ),
			'thead'  => array( 'style' => true, 'class' => true ),
			'tbody'  => array( 'style' => true, 'class' => true ),
			'img'    => array( 'src' => true, 'alt' => true, 'style' => true, 'class' => true, 'width' => true, 'height' => true ),
			'hr'     => array( 'style' => true, 'class' => true ),
		);

		return array(
			// Admin email templates.
			'admin_subject' => isset( $value['admin_subject'] ) ? sanitize_text_field( $value['admin_subject'] ) : '',
			'admin_body'    => isset( $value['admin_body'] ) ? wp_kses( $value['admin_body'], $allowed_html ) : '',
			// Client email templates.
			'client_subject' => isset( $value['client_subject'] ) ? sanitize_text_field( $value['client_subject'] ) : '',
			'client_body'    => isset( $value['client_body'] ) ? wp_kses( $value['client_body'], $allowed_html ) : '',
			// Update templates.
			'update_subject' => isset( $value['update_subject'] ) ? sanitize_text_field( $value['update_subject'] ) : '',
			'update_body'    => isset( $value['update_body'] ) ? wp_kses( $value['update_body'], $allowed_html ) : '',
			// Cancel templates.
			'cancel_subject' => isset( $value['cancel_subject'] ) ? sanitize_text_field( $value['cancel_subject'] ) : '',
			'cancel_body'    => isset( $value['cancel_body'] ) ? wp_kses( $value['cancel_body'], $allowed_html ) : '',
		);
	}

	/**
	 * Sanitize security settings.
	 *
	 * @param mixed $value Input value.
	 * @return array
	 */
	public static function sanitize_security( $value ): array {
		$value = is_array( $value ) ? $value : array();

		// Rate limit enabled.
		$enabled = ! empty( $value['rate_limit_enabled'] ) ? 1 : 0;

		// Rate limit requests (per window).
		$requests = isset( $value['rate_limit_requests'] ) ? absint( $value['rate_limit_requests'] ) : 30;
		if ( $requests < 5 ) {
			$requests = 5; // Minimum 5 requests.
		}
		if ( $requests > 1000 ) {
			$requests = 1000; // Maximum 1000 requests.
		}

		// Rate limit window (seconds).
		$window = isset( $value['rate_limit_window'] ) ? absint( $value['rate_limit_window'] ) : 60;
		if ( $window < 10 ) {
			$window = 10; // Minimum 10 seconds.
		}
		if ( $window > 3600 ) {
			$window = 3600; // Maximum 1 hour.
		}

		// IP source.
		$allowed_sources = array(
			'auto',
			'remote_addr',
			'cf_connecting_ip',
			'x_forwarded_for',
			'x_real_ip',
			'sucuri',
			'cloudfront',
			'fastly',
		);
		$source = isset( $value['ip_source'] ) ? sanitize_key( $value['ip_source'] ) : 'auto';
		if ( ! in_array( $source, $allowed_sources, true ) ) {
			$source = 'auto';
		}

		// Anti-bot enabled.
		$antibot_enabled = ! empty( $value['antibot_enabled'] ) ? 1 : 0;

		return array(
			'antibot_enabled'     => $antibot_enabled,
			'rate_limit_enabled'  => $enabled,
			'rate_limit_requests' => $requests,
			'rate_limit_window'   => $window,
			'ip_source'           => $source,
		);
	}
}
