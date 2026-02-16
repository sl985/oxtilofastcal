<?php
/**
 * AJAX handlers for Oxtilofastcal.
 *
 * @package Oxtilofastcal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AJAX class.
 *
 * @since 0.5.0
 */
final class Oxtilofastcal_Ajax {

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
		// Get slots AJAX.
		add_action( 'wp_ajax_oxtilofastcal_get_slots', array( $this, 'get_slots' ) );
		add_action( 'wp_ajax_nopriv_oxtilofastcal_get_slots', array( $this, 'get_slots' ) );

		// Booking submission.
		add_action( 'admin_post_oxtilofastcal_submit_booking', array( $this, 'handle_booking_submit' ) );
		add_action( 'admin_post_nopriv_oxtilofastcal_submit_booking', array( $this, 'handle_booking_submit' ) );
	}

	/**
	 * AJAX: Get available slots.
	 */
	public function get_slots(): void {
		// Rate limiting check.
		$rate_limiter = Oxtilofastcal_Rate_Limiter::instance();
		if ( ! $rate_limiter->check( 'ajax_slots' ) ) {
			$rate_limiter->send_ajax_error();
		}

		check_ajax_referer( 'oxtilofastcal_get_slots', 'nonce' );

		$service_id = isset( $_POST['service_id'] ) ? absint( $_POST['service_id'] ) : 0;
		$date       = isset( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : '';
		$booking_id = isset( $_POST['booking_id'] ) ? absint( $_POST['booking_id'] ) : 0;

		if ( '' === $date || ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Invalid request.', 'oxtilo-fast-cal' ) ), 400 );
		}

		// Validate max days in future.
		$general  = get_option( 'oxtilofastcal_general', array() );
		$max_days = isset( $general['max_days_future'] ) ? absint( $general['max_days_future'] ) : 30;
		if ( $max_days > 0 ) {
			$today = new DateTimeImmutable( 'now', wp_timezone() );
			$max_date = $today->add( new DateInterval( 'P' . $max_days . 'D' ) );
			$requested_date = new DateTimeImmutable( $date, wp_timezone() );
			
			if ( $requested_date > $max_date ) {
				wp_send_json_error( array( 'message' => esc_html__( 'Date is too far in the future.', 'oxtilo-fast-cal' ) ), 400 );
			}
		}

		$slots = Oxtilofastcal_Availability::get_available_slots( $date, $service_id, 0, $booking_id );

		$out = array();
		foreach ( $slots as $slot ) {
			$start = $slot['start'] ?? '';
			$end   = $slot['end'] ?? '';
			if ( '' === $start || '' === $end ) {
				continue;
			}
			$out[] = array(
				'start' => $start,
				'end'   => $end,
				'label' => oxtilofastcal_format_slot_label( $start, $end ),
			);
		}

		wp_send_json_success( array( 'slots' => $out ) );
	}

	/**
	 * Handle booking form submission.
	 */
	public function handle_booking_submit(): void {
		// Rate limiting check.
		$rate_limiter = Oxtilofastcal_Rate_Limiter::instance();
		if ( ! $rate_limiter->check( 'booking_submit' ) ) {
			$rate_limiter->send_die_error();
		}

		$security = get_option( 'oxtilofastcal_security', array() );
		
		if ( ! empty( $security['antibot_enabled'] ) ) {
			// 1. Honeypot check.
			if ( ! empty( $_POST['oxtilofastcal_website'] ) ) {
				wp_die( esc_html__( 'Bot detected', 'oxtilo-fast-cal' ), esc_html__( 'Error', 'oxtilo-fast-cal' ), array( 'response' => 403 ) );
			}

			// 2. Nonce check.
			if ( ! check_ajax_referer( 'oxtilofastcal_booking_action', 'oxtilofastcal_security', false ) ) {
				wp_die( esc_html__( 'Security check failed', 'oxtilo-fast-cal' ), esc_html__( 'Error', 'oxtilo-fast-cal' ), array( 'response' => 403 ) );
			}

			// 3. JS & Time Trap check.
			$valid_token = isset( $_POST['oxtilofastcal_valid'] ) ? sanitize_text_field( wp_unslash( $_POST['oxtilofastcal_valid'] ) ) : '';

			if ( empty( $valid_token ) ) {
				wp_die( esc_html__( 'Bot detected (No JS)', 'oxtilo-fast-cal' ), esc_html__( 'Error', 'oxtilo-fast-cal' ), array( 'response' => 403 ) );
			}

			if ( 0 !== strpos( $valid_token, 'human_verified_' ) ) {
				wp_die( esc_html__( 'Bot detected (Invalid Token)', 'oxtilo-fast-cal' ), esc_html__( 'Error', 'oxtilo-fast-cal' ), array( 'response' => 403 ) );
			}

			$duration = (int) str_replace( 'human_verified_', '', $valid_token );

			if ( $duration < 3000 ) {
				wp_die( esc_html__( 'Bot detected (Too Fast)', 'oxtilo-fast-cal' ), esc_html__( 'Error', 'oxtilo-fast-cal' ), array( 'response' => 403 ) );
			}
		} else {
			// Fallback: standard nonce check only.
			if ( ! isset( $_POST['oxtilofastcal_booking_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['oxtilofastcal_booking_nonce'] ) ), 'oxtilofastcal_submit_booking' ) ) {
				wp_die( esc_html__( 'Access Denied', 'oxtilo-fast-cal' ), esc_html__( 'Access Denied', 'oxtilo-fast-cal' ), array( 'response' => 403 ) );
			}
		}

		$service_id = isset( $_POST['service_id'] ) ? absint( $_POST['service_id'] ) : -1;
		$date       = isset( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : '';
		$slot_start = isset( $_POST['slot_start'] ) ? sanitize_text_field( wp_unslash( $_POST['slot_start'] ) ) : '';
		$name       = isset( $_POST['client_name'] ) ? sanitize_text_field( wp_unslash( $_POST['client_name'] ) ) : '';
		$email      = isset( $_POST['client_email'] ) ? sanitize_email( wp_unslash( $_POST['client_email'] ) ) : '';
		$message    = isset( $_POST['client_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['client_message'] ) ) : '';
		$return_url = isset( $_POST['return_url'] ) ? esc_url_raw( wp_unslash( $_POST['return_url'] ) ) : home_url( '/' );

		$services = oxtilofastcal_get_services();
		
		if ( ! isset( $services[ $service_id ] ) ) {
			wp_safe_redirect( add_query_arg( 'oxtilofastcal_error', 'invalid_service', $return_url ) );
			exit;
		}

		if ( '' === $date || ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
			wp_safe_redirect( add_query_arg( 'oxtilofastcal_error', 'invalid_date', $return_url ) );
			exit;
		}

		if ( '' === $slot_start ) {
			wp_safe_redirect( add_query_arg( 'oxtilofastcal_error', 'no_time', $return_url ) );
			exit;
		}

		if ( '' === $name || '' === $email || ! is_email( $email ) ) {
			wp_safe_redirect( add_query_arg( 'oxtilofastcal_error', 'invalid_details', $return_url ) );
			exit;
		}

		// Check initial availability (non-blocking pre-check for user feedback).
		$slots   = Oxtilofastcal_Availability::get_available_slots( $date, $service_id );
		$matched = null;

		foreach ( $slots as $slot ) {
			if ( isset( $slot['start'] ) && $slot_start === (string) $slot['start'] ) {
				$matched = $slot;
				break;
			}
		}

		if ( ! $matched ) {
			wp_safe_redirect( add_query_arg( 'oxtilofastcal_error', 'unavailable', $return_url ) );
			exit;
		}

		$tz = wp_timezone();
		try {
			$start_dt = new DateTimeImmutable( (string) $matched['start'], $tz );
			$end_dt   = new DateTimeImmutable( (string) $matched['end'], $tz );
		} catch ( Exception $e ) {
			wp_safe_redirect( add_query_arg( 'oxtilofastcal_error', 'invalid_time', $return_url ) );
			exit;
		}

		$service      = $services[ $service_id ];
		$type         = $service['type'] ?? 'online';
		$meeting_type = ( 'in_person' === $type ) ? 'in_person' : 'online';

		// Use atomic insert with transaction to prevent race conditions (TOCTOU).
		$result = Oxtilofastcal_Database::insert_booking_atomic( array(
			'service_id'   => $service_id,
			'client_name'  => $name,
			'client_email' => $email,
			'start_time'   => $start_dt->format( 'Y-m-d H:i:s' ),
			'end_time'     => $end_dt->format( 'Y-m-d H:i:s' ),
			'status'       => 'confirmed',
			'meeting_type' => $meeting_type,
		) );

		if ( ! $result['success'] ) {
			$error_code = 'conflict' === $result['error'] ? 'unavailable' : 'db';
			wp_safe_redirect( add_query_arg( 'oxtilofastcal_error', $error_code, $return_url ) );
			exit;
		}

		$booking_id = $result['booking_id'];

		Oxtilofastcal_Notifications::send( $booking_id, array( 'client_message' => $message ) );

		wp_safe_redirect( add_query_arg( 'oxtilofastcal_success', '1', $return_url ) );
		exit;
	}
}
