<?php
/**
 * ICS Feed endpoint for Oxtilofastcal.
 *
 * @package Oxtilofastcal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ICS Feed class.
 *
 * @since 0.5.0
 */
final class Oxtilofastcal_ICS_Feed {

	/**
	 * Single instance.
	 *
	 * @var self|null
	 */
	private static ?self $instance = null;

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
		add_action( 'init', array( $this, 'register_rewrite_rules' ) );
		add_filter( 'query_vars', array( $this, 'register_query_vars' ) );
		add_action( 'template_redirect', array( $this, 'maybe_output_feed' ) );
	}

	/**
	 * Register rewrite rules.
	 */
	public function register_rewrite_rules(): void {
		Oxtilofastcal_Database::add_rewrite_rules();
	}

	/**
	 * Register query vars.
	 *
	 * @param array $vars Query vars.
	 * @return array
	 */
	public function register_query_vars( array $vars ): array {
		$vars[] = 'oxtilofastcal_feed_token';
		return $vars;
	}

	/**
	 * Output ICS feed if token matches.
	 */
	public function maybe_output_feed(): void {
		$token = get_query_var( 'oxtilofastcal_feed_token' );
		if ( empty( $token ) ) {
			return;
		}

		// Rate limiting check (before token validation to prevent DoS).
		$rate_limiter = Oxtilofastcal_Rate_Limiter::instance();
		if ( ! $rate_limiter->check( 'ics_feed' ) ) {
			$rate_limiter->send_die_error();
		}

		$token = sanitize_text_field( (string) $token );

		$general = get_option( 'oxtilofastcal_general', array() );
		$general = is_array( $general ) ? $general : array();
		$saved   = isset( $general['calendar_feed_token'] ) ? (string) $general['calendar_feed_token'] : '';

		if ( '' === $saved || ! hash_equals( $saved, $token ) ) {
			wp_die(
				esc_html__( 'Access Denied', 'oxtilo-fast-cal' ),
				esc_html__( 'Access Denied', 'oxtilo-fast-cal' ),
				array( 'response' => 403 )
			);
		}

		$ics = $this->generate_feed();

		nocache_headers();
		status_header( 200 );
		header( 'Content-Type: text/calendar; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="oxtilofastcal-bookings.ics"' );
		header( 'X-Content-Type-Options: nosniff' );

		echo $ics; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit;
	}

	/**
	 * Generate ICS feed from confirmed bookings.
	 *
	 * @return string
	 */
	private function generate_feed(): string {
		$rows = Oxtilofastcal_Database::get_confirmed_bookings();
		
		$general = get_option( 'oxtilofastcal_general', array() );
		$general = is_array( $general ) ? $general : array();

		$site_tz = wp_timezone();
		$utc_tz  = new DateTimeZone( 'UTC' );
		$now_utc = new DateTimeImmutable( 'now', $utc_tz );

		$lines   = array();
		$lines[] = 'BEGIN:VCALENDAR';
		$lines[] = 'VERSION:2.0';
		$lines[] = 'CALSCALE:GREGORIAN';
		$lines[] = 'METHOD:PUBLISH';
		$lines[] = 'PRODID:-//Oxtilofastcal//Oxtilofastcal ' . OXTILOFASTCAL_VERSION . '//EN';

		foreach ( $rows as $row ) {
			$id         = absint( $row['id'] ?? 0 );
			$start_time = (string) ( $row['start_time'] ?? '' );
			$end_time   = (string) ( $row['end_time'] ?? '' );
			$name       = (string) ( $row['client_name'] ?? '' );

			if ( $id <= 0 || '' === $start_time || '' === $end_time ) {
				continue;
			}

			try {
				$start_local = new DateTimeImmutable( $start_time, $site_tz );
				$end_local   = new DateTimeImmutable( $end_time, $site_tz );
			} catch ( Exception $e ) {
				continue;
			}

			if ( $end_local <= $start_local ) {
				continue;
			}

			$start_utc = $start_local->setTimezone( $utc_tz );
			$end_utc   = $end_local->setTimezone( $utc_tz );

			$uid_host = wp_parse_url( home_url(), PHP_URL_HOST ) ?: 'localhost';
			$uid      = 'oxtilofastcal-' . $id . '@' . $uid_host;

			$name  = sanitize_text_field( $name );
			$email = isset( $row['client_email'] ) ? sanitize_email( $row['client_email'] ) : '';
			
			// Get stored service_name and check if it matches a predefined template
			$stored_service_name = isset( $row['service_name'] ) ? sanitize_text_field( $row['service_name'] ) : '';
			
			// Check if stored service_name matches any predefined service template
			$services = oxtilofastcal_get_services();
			$is_predefined_service = false;
			
			if ( ! empty( $stored_service_name ) ) {
				foreach ( $services as $svc ) {
					if ( ! empty( $svc['name'] ) && $stored_service_name === $svc['name'] ) {
						$is_predefined_service = true;
						break;
					}
				}
			} else {
				// No custom service_name stored - this is a predefined service
				$is_predefined_service = true;
			}
			
			// Build summary: use client name for predefined services, service_name for custom
			if ( $is_predefined_service ) {
				// Predefined service: use client name as title
				$summary = ( '' !== $name ) ? $name . ' #' . $id : __( 'Booking', 'oxtilo-fast-cal' ) . ' #' . $id;
			} else {
				// Custom service name: use service_name as title
				$summary = $stored_service_name . ' #' . $id;
			}

			$booking_hash = (string) ( $row['booking_hash'] ?? '' );
			$meeting_type = (string) ( $row['meeting_type'] ?? 'online' );
			$client_message = isset( $row['client_message'] ) ? sanitize_textarea_field( $row['client_message'] ) : '';
			$edit_link    = home_url( '/booking-manage/' . $booking_hash . '/' );
			
			$description_lines = array();

			if ( '' !== $email ) {
				$description_lines[] = __( 'Email:', 'oxtilo-fast-cal' ) . ' ' . $email;
			}
			
			if ( ! empty( $client_message ) ) {
				$description_lines[] = __( 'Message:', 'oxtilo-fast-cal' ) . ' ' . $client_message;
			}
			
			if ( 'online' === $meeting_type ) {
				$meet_link = isset( $general['google_meet_link'] ) ? (string) $general['google_meet_link'] : '';
				if ( ! empty( $meet_link ) ) {
					$description_lines[] = __( 'Join Meeting:', 'oxtilo-fast-cal' ) . ' ' . $meet_link;
				} else {
					$description_lines[] = __( 'Meeting Type: Online', 'oxtilo-fast-cal' );
				}
			} else {
				$description_lines[] = __( 'Meeting Type: In-person', 'oxtilo-fast-cal' );
			}

			if ( ! empty( $general['include_manage_link'] ) ) {
				$description_lines[] = '';
				$description_lines[] = __( 'Manage Booking:', 'oxtilo-fast-cal' ) . ' ' . $edit_link;
			}

			$description = implode( "\n", $description_lines );

			$lines[] = 'BEGIN:VEVENT';
			$lines[] = 'UID:' . oxtilofastcal_ics_escape_text( $uid );
			$lines[] = 'DTSTAMP:' . $now_utc->format( 'Ymd\THis\Z' );
			$lines[] = 'DTSTART:' . $start_utc->format( 'Ymd\THis\Z' );
			$lines[] = 'DTEND:' . $end_utc->format( 'Ymd\THis\Z' );
			$lines[] = 'SUMMARY:' . oxtilofastcal_ics_escape_text( $summary );
			$lines[] = 'DESCRIPTION:' . oxtilofastcal_ics_escape_text( $description );
			$lines[] = 'END:VEVENT';
		}

		$lines[] = 'END:VCALENDAR';

		return implode( "\r\n", $lines ) . "\r\n";
	}
}
