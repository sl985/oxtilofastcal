<?php
/**
 * Booking Manager for Oxtilofastcal.
 *
 * @package Oxtilofastcal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles booking management (edit/cancel) by clients via secure hash.
 *
 * @since 0.6.0
 */
class Oxtilofastcal_Manager {

	/**
	 * Instance.
	 *
	 * @var Oxtilofastcal_Manager|null
	 */
	private static $instance = null;

	/**
	 * Get instance.
	 *
	 * @return Oxtilofastcal_Manager
	 */
	public static function instance(): Oxtilofastcal_Manager {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_action( 'init', array( $this, 'add_rewrite_rules' ) );
		add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
		add_action( 'template_redirect', array( $this, 'handle_management_logic' ) );
	}

	/**
	 * Add rewrite rules.
	 */
	public function add_rewrite_rules(): void {
		add_rewrite_rule(
			'^booking-manage/([a-zA-Z0-9]+)/?$',
			'index.php?oxtilofastcal_manage=$matches[1]',
			'top'
		);
	}

	/**
	 * Add query vars.
	 *
	 * @param array $vars Query vars.
	 * @return array
	 */
	public function add_query_vars( array $vars ): array {
		$vars[] = 'oxtilofastcal_manage';
		return $vars;
	}

	/**
	 * Handle management logic.
	 */
	public function handle_management_logic(): void {
		$hash = get_query_var( 'oxtilofastcal_manage' );
		if ( empty( $hash ) ) {
			return;
		}

		// Rate limiting check (before database query to prevent hash enumeration).
		$rate_limiter = Oxtilofastcal_Rate_Limiter::instance();
		if ( ! $rate_limiter->check( 'booking_manage' ) ) {
			$rate_limiter->send_die_error();
		}

		$booking = Oxtilofastcal_Database::get_booking_by_hash( $hash );
		if ( ! $booking ) {
			wp_die( esc_html__( 'Invalid booking link.', 'oxtilo-fast-cal' ), 404 );
		}

		// Handle POST actions.
		if ( isset( $_POST['oxtilofastcal_action'] ) && check_admin_referer( 'oxtilofastcal_manage_' . $booking['id'] ) ) {
			$this->process_action( $booking );
		}

		// Enqueue styles for the frontend.
		wp_enqueue_style( 'oxtilofastcal-frontend', OXTILOFASTCAL_PLUGIN_URL . 'assets/oxtilofastcal-frontend.css', array(), OXTILOFASTCAL_VERSION );
		wp_enqueue_script( 'oxtilofastcal-frontend', OXTILOFASTCAL_PLUGIN_URL . 'assets/oxtilofastcal-frontend.js', array( 'jquery' ), OXTILOFASTCAL_VERSION, true );
		wp_localize_script( 'oxtilofastcal-frontend', 'oxtilofastcalFrontend', array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'oxtilofastcal_get_slots' ),
			'booking_id' => $booking['id'],
			'i18n'    => array(
				'selectService' => __( 'Please select a service.', 'oxtilo-fast-cal' ),
				'selectDate'    => __( 'Please choose a date.', 'oxtilo-fast-cal' ),
				'loading'       => __( 'Loading available times…', 'oxtilo-fast-cal' ),
				'noSlots'       => __( 'No available time slots for this date.', 'oxtilo-fast-cal' ),
				'chooseTime'    => __( 'Choose a time slot', 'oxtilo-fast-cal' ),
			),
		) );

		// Load template.
		// We use a custom template loader similar to how themes work.
		// If we can hook into template_include, that's great for keeping theme.
		// But since we are inside template_redirect, we are early enough to hijack specific logic or force a template.
		
		// Let's force load our template wrapping with current theme header/footer if possible.
		// Actually, the best way in 'template_redirect' is to just include the file if we want to bypass normal WP hierarchy,
		// OR let WP continue and use 'template_include' filter.
		// But since we are here:
		
		global $wp_query;
		$wp_query->is_404 = false;
		$wp_query->is_page = true;
		status_header( 200 );

		// We can't easily wrap with theme header/footer without being inside the loop or a page template.
		// So we will try to use 'template_include'.
		add_filter( 'template_include', function() use ( $booking ) {
			// Make variables available to template.
			set_query_var( 'oxtilofastcal_booking', $booking );
			return OXTILOFASTCAL_PLUGIN_DIR . 'templates/manage-booking.php';
		} );
	}

	/**
	 * Process POST actions.
	 *
	 * @param array $booking Booking data.
	 */
	private function process_action( array $booking ): void {
		$action = sanitize_key( wp_unslash( $_POST['oxtilofastcal_action'] ) );

		if ( 'cancel_booking' === $action ) {
			// Cancel booking.
			Oxtilofastcal_Database::update_booking( $booking['id'], array( 'status' => 'cancelled' ) );
			// Send notification.
			Oxtilofastcal_Notifications::send_update( $booking['id'] );

			// Redirect.
			wp_safe_redirect( add_query_arg( 'updated', 'cancelled', remove_query_arg( 'oxtilofastcal_action' ) ) );
			exit;
		}

		if ( 'reschedule_booking' === $action ) {
			$service_id = isset( $_POST['service_id'] ) ? absint( $_POST['service_id'] ) : 0;
			$date       = sanitize_text_field( wp_unslash( $_POST['date'] ?? '' ) );
			$start_time = sanitize_text_field( wp_unslash( $_POST['slot_start'] ?? '' ) );

			if ( ! $date || ! $start_time ) {
				// Error
				wp_safe_redirect( add_query_arg( 'error', 'missing_details' ) );
				exit;
			}

			// Validate slot logic (simplified here, ideally reuse validation logic).
			// We should verify if the slot is free.
			// Reusing availability class:
			// Note: We need to calculate end time.
			$services = oxtilofastcal_get_services();
			$service  = $services[ $service_id ] ?? null;
			if ( ! $service ) {
				wp_safe_redirect( add_query_arg( 'error', 'invalid_service' ) );
				exit;
			}

			$duration = absint( $service['duration'] );
			$tz = wp_timezone();
			try {
				// Slot start is already a full datetime string from AJAX (Y-m-d H:i:s).
				$start_dt = new DateTimeImmutable( $start_time, $tz );
				
				// Verify if the slot date matches the selected date.
				if ( $start_dt->format( 'Y-m-d' ) !== $date ) {
					throw new Exception( 'Date mismatch' );
				}

				$end_dt = $start_dt->add( new DateInterval( 'PT' . $duration . 'M' ) );
			} catch ( Exception $e ) {
				wp_safe_redirect( add_query_arg( 'error', 'invalid_date' ) );
				exit;
			}

			// Check availability before updating.
			// We must exclude the current booking from the busy list.
			$busy = Oxtilofastcal_Availability::get_busy_intervals_for_date( $start_dt, (int) $booking['id'] );

			if ( Oxtilofastcal_Availability::interval_overlaps_any( $start_dt, $end_dt, $busy ) ) {
				wp_safe_redirect( add_query_arg( 'error', 'unavailable' ) );
				exit;
			}

			// Update booking.
			Oxtilofastcal_Database::update_booking( $booking['id'], array(
				'service_id' => $service_id,
				'start_time' => $start_dt->format( 'Y-m-d H:i:s' ),
				'end_time'   => $end_dt->format( 'Y-m-d H:i:s' ),
				'status'     => 'confirmed', // Reactivate if it was cancelled
			) );

			Oxtilofastcal_Notifications::send_update( $booking['id'] );

			wp_safe_redirect( add_query_arg( 'updated', 'rescheduled' ) );
			exit;
		}
	}
}
