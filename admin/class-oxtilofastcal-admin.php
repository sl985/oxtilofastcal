<?php
/**
 * Admin functionality for Oxtilofastcal.
 *
 * @package Oxtilofastcal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin class.
 *
 * @since 0.5.0
 */
final class Oxtilofastcal_Admin {

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
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_init', array( Oxtilofastcal_Admin_Settings::class, 'register' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'wp_ajax_oxtilofastcal_generate_calendar_token', array( $this, 'ajax_generate_token' ) );
		add_action( 'wp_ajax_oxtilofastcal_generate_api_token', array( $this, 'ajax_generate_api_token' ) );
		add_action( 'wp_ajax_oxtilofastcal_test_ics_feed', array( $this, 'ajax_test_ics_feed' ) );
		add_action( 'wp_ajax_oxtilofastcal_diagnostics', array( $this, 'ajax_diagnostics' ) );

		// Form handlers.
		add_action( 'admin_post_oxtilofastcal_save_booking', array( $this, 'handle_save_booking' ) );
		add_action( 'admin_post_oxtilofastcal_delete_booking', array( $this, 'handle_delete_booking' ) );
		add_action( 'admin_post_oxtilofastcal_create_booking', array( $this, 'handle_create_booking' ) );
	}

	/**
	 * Register admin menu.
	 */
	public function register_menu(): void {
		add_menu_page(
			esc_html__( 'Oxtilo Fast Cal', 'oxtilo-fast-cal' ),
			esc_html__( 'Oxtilo Fast Cal', 'oxtilo-fast-cal' ),
			'manage_options',
			'oxtilo-fast-cal',
			array( $this, 'render_settings_page' ),
			'dashicons-calendar-alt',
			56
		);

		// 1. Settings (Default).
		add_submenu_page(
			'oxtilo-fast-cal',
			esc_html__( 'Settings', 'oxtilo-fast-cal' ),
			esc_html__( 'Settings', 'oxtilo-fast-cal' ),
			'manage_options',
			'oxtilo-fast-cal',
			array( $this, 'render_settings_page' )
		);

		// 2. Email Templates.
		add_submenu_page(
			'oxtilo-fast-cal',
			esc_html__( 'Email Templates', 'oxtilo-fast-cal' ),
			esc_html__( 'Email Templates', 'oxtilo-fast-cal' ),
			'manage_options',
			'oxtilofastcal-email-templates',
			array( $this, 'render_email_templates_page' )
		);

		// 3. Security.
		add_submenu_page(
			'oxtilo-fast-cal',
			esc_html__( 'Security', 'oxtilo-fast-cal' ),
			esc_html__( 'Security', 'oxtilo-fast-cal' ),
			'manage_options',
			'oxtilofastcal-security',
			array( $this, 'render_security_page' )
		);

		// 4. Diagnostics.
		add_submenu_page(
			'oxtilo-fast-cal',
			esc_html__( 'Diagnostics', 'oxtilo-fast-cal' ),
			esc_html__( 'Diagnostics', 'oxtilo-fast-cal' ),
			'manage_options',
			'oxtilofastcal-diagnostics',
			array( $this, 'render_diagnostics_page' )
		);

		// 5. Bookings.
		add_submenu_page(
			'oxtilo-fast-cal',
			esc_html__( 'Bookings', 'oxtilo-fast-cal' ),
			esc_html__( 'Bookings', 'oxtilo-fast-cal' ),
			'manage_options',
			'oxtilofastcal-bookings',
			array( $this, 'render_bookings_page' )
		);
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @param string $hook Current admin page.
	 */
	public function enqueue_assets( string $hook ): void {
		// Enqueue on all plugin pages.
		if ( strpos( $hook, 'page_oxtilofastcal' ) === false && 'toplevel_page_oxtilofastcal' !== $hook ) {
			return;
		}

		wp_enqueue_style( 'oxtilofastcal-admin', OXTILOFASTCAL_PLUGIN_URL . 'assets/oxtilofastcal-admin.css', array(), OXTILOFASTCAL_VERSION );
		wp_enqueue_script( 'oxtilofastcal-admin', OXTILOFASTCAL_PLUGIN_URL . 'assets/oxtilofastcal-admin.js', array( 'jquery' ), OXTILOFASTCAL_VERSION, true );

		$general  = get_option( 'oxtilofastcal_general', array() );
		$general  = is_array( $general ) ? $general : array();
		$token    = isset( $general['calendar_feed_token'] ) ? (string) $general['calendar_feed_token'] : '';
		$feed_url = ( $token && preg_match( '/^[a-zA-Z0-9]+$/', $token ) ) ? home_url( '/oxtilofastcal-feed/' . $token . '/' ) : '';

		wp_localize_script( 'oxtilofastcal-admin', 'oxtilofastcalAdmin', array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'oxtilofastcal_generate_calendar_token' ),
			'testNonce' => wp_create_nonce( 'oxtilofastcal_test_ics_feed' ),
			'diagNonce' => wp_create_nonce( 'oxtilofastcal_diagnostics' ),
			'i18n'    => array(
				'generating' => __( 'Generating…', 'oxtilo-fast-cal' ),
				'generated'  => __( 'New token generated.', 'oxtilo-fast-cal' ),
				'error'      => __( 'Could not generate token.', 'oxtilo-fast-cal' ),
				'feedUrl'    => __( 'Feed URL:', 'oxtilo-fast-cal' ),
				'loading'    => __( 'Loading…', 'oxtilo-fast-cal' ),
			),
			'feedUrl' => $feed_url,
		) );

		// Bookings page: enqueue bookings JS and inline badge CSS.
		if ( strpos( $hook, 'oxtilofastcal-bookings' ) !== false ) {
			wp_enqueue_script(
				'oxtilofastcal-admin-bookings',
				OXTILOFASTCAL_PLUGIN_URL . 'assets/oxtilofastcal-admin-bookings.js',
				array( 'jquery' ),
				OXTILOFASTCAL_VERSION,
				true
			);

			wp_localize_script( 'oxtilofastcal-admin-bookings', 'oxtilofastcalBookings', array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'oxtilofastcal_get_slots' ),
				'i18n'    => array(
					'selectDateFirst' => __( 'Please select a date first.', 'oxtilo-fast-cal' ),
					'loading'         => __( 'Loading...', 'oxtilo-fast-cal' ),
					'noSlots'         => __( 'No available slots for this date. You can still enter custom times.', 'oxtilo-fast-cal' ),
					'errorSlots'      => __( 'Error loading slots. You can still enter custom times.', 'oxtilo-fast-cal' ),
					'enterStartFirst' => __( 'Please enter a start time first.', 'oxtilo-fast-cal' ),
				),
			) );

			$bookings_css = '.oxtilofastcal-badge {'
				. 'display: inline-block;'
				. 'padding: 3px 8px;'
				. 'border-radius: 3px;'
				. 'font-size: 12px;'
				. 'font-weight: 500;'
				. '}'
				. '.oxtilofastcal-badge--success {'
				. 'background: #e7f7ed;'
				. 'color: #107c10;'
				. '}'
				. '.oxtilofastcal-badge--error {'
				. 'background: #fde7e9;'
				. 'color: #d63638;'
				. '}'
				. '.required {'
				. 'color: #d63638;'
				. '}';

			wp_add_inline_style( 'oxtilofastcal-admin', $bookings_css );
		}
	}

	/**
	 * AJAX: Generate new calendar token.
	 */
	public function ajax_generate_token(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Access Denied', 'oxtilo-fast-cal' ) ), 403 );
		}

		check_ajax_referer( 'oxtilofastcal_generate_calendar_token', 'nonce' );

		$general = get_option( 'oxtilofastcal_general', array() );
		$general = is_array( $general ) ? $general : array();

		$general['calendar_feed_token'] = oxtilofastcal_generate_alnum_token( 32 );
		update_option( 'oxtilofastcal_general', $general, false );

		$token    = (string) $general['calendar_feed_token'];
		$feed_url = home_url( '/oxtilofastcal-feed/' . $token . '/' );

		wp_send_json_success( array( 'token' => $token, 'feedUrl' => $feed_url ) );
	}

	/**
	 * AJAX: Generate new API token.
	 */
	public function ajax_generate_api_token(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Access Denied', 'oxtilo-fast-cal' ) ), 403 );
		}

		check_ajax_referer( 'oxtilofastcal_generate_calendar_token', 'nonce' );

		$general = get_option( 'oxtilofastcal_general', array() );
		$general = is_array( $general ) ? $general : array();

		$general['api_token'] = oxtilofastcal_generate_alnum_token( 48 );
		update_option( 'oxtilofastcal_general', $general, false );

		$token = (string) $general['api_token'];

		wp_send_json_success( array( 'token' => $token ) );
	}

	/**
	 * AJAX: Test ICS feed.
	 */
	public function ajax_test_ics_feed(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Access Denied', 'oxtilo-fast-cal' ) ), 403 );
		}

		check_ajax_referer( 'oxtilofastcal_test_ics_feed', 'nonce' );

		$url = isset( $_POST['url'] ) ? esc_url_raw( wp_unslash( $_POST['url'] ) ) : '';
		if ( empty( $url ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Empty URL', 'oxtilo-fast-cal' ) ) );
		}

		$events = Oxtilofastcal_Availability::fetch_and_parse_ics_events( $url, true );
		$count  = count( $events );

		if ( 0 === $count ) {
			wp_send_json_error( array( 'message' => esc_html__( 'No events found or error fetching feed.', 'oxtilo-fast-cal' ) ) );
		}

		/* translators: %d: number of events */
		$msg = sprintf( esc_html__( 'Success! Found %d events.', 'oxtilo-fast-cal' ), $count );
		wp_send_json_success( array( 'message' => $msg ) );
	}

	/**
	 * AJAX: Get diagnostics data.
	 */
	public function ajax_diagnostics(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Access Denied', 'oxtilo-fast-cal' ) ), 403 );
		}

		check_ajax_referer( 'oxtilofastcal_diagnostics', 'nonce' );

		$date = isset( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : '';
		if ( '' === $date || ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
			$date = wp_date( 'Y-m-d', strtotime( '+1 day' ) );
		}

		$tz = wp_timezone();

		try {
			$day = new DateTimeImmutable( $date . ' 00:00:00', $tz );
		} catch ( Exception $e ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Invalid date.', 'oxtilo-fast-cal' ) ) );
		}

		$feeds = get_option( 'oxtilofastcal_ics_feeds', array() );
		$feeds = is_array( $feeds ) ? $feeds : array();

		$sources = array(
			'icloud'   => __( 'ICS Calendar 1 URL', 'oxtilo-fast-cal' ),
			'proton'   => __( 'ICS Calendar 2 URL', 'oxtilo-fast-cal' ),
			'holidays' => __( 'Holidays ICS URL', 'oxtilo-fast-cal' ),
		);

		$day_start = $day->setTime( 0, 0, 0 );
		$day_end   = $day_start->add( new DateInterval( 'P1D' ) );

		$result = array(
			'date'          => $date,
			'wp_timezone'   => $tz->getName(),
			'current_time'  => wp_date( 'Y-m-d H:i:s T' ),
			'sources'       => array(),
			'busy_db'       => array(),
			'available'     => array(),
		);

		// Get busy intervals from database.
		$db_busy = Oxtilofastcal_Database::get_busy_intervals( $day_start, $day_end );
		foreach ( $db_busy as $b ) {
			$result['busy_db'][] = array(
				'start' => $b['start']->format( 'Y-m-d H:i:s T' ),
				'end'   => $b['end']->format( 'Y-m-d H:i:s T' ),
			);
		}

		// Get events from each ICS source.
		foreach ( $sources as $key => $label ) {
			$url = isset( $feeds[ $key ] ) ? trim( (string) $feeds[ $key ] ) : '';
			$source_data = array(
				'name'   => $label,
				'url'    => $url ? substr( $url, 0, 50 ) . '...' : '(empty)',
				'events' => array(),
			);

			if ( '' !== $url ) {
				$events = Oxtilofastcal_Availability::fetch_and_parse_ics_events( $url, true );
				foreach ( $events as $ev ) {
					$start = $ev['start'] instanceof DateTimeImmutable ? $ev['start']->setTimezone( $tz ) : null;
					$end   = $ev['end'] instanceof DateTimeImmutable ? $ev['end']->setTimezone( $tz ) : null;

					if ( $start && $end && $start < $day_end && $end > $day_start ) {
						$source_data['events'][] = array(
							'summary'    => $ev['summary'] ?? '(no title)',
							'start'      => $start->format( 'Y-m-d H:i:s T' ),
							'end'        => $end->format( 'Y-m-d H:i:s T' ),
							'start_orig' => $ev['start']->format( 'Y-m-d H:i:s T' ),
							'end_orig'   => $ev['end']->format( 'Y-m-d H:i:s T' ),
						);
					}
				}
			}

			$result['sources'][] = $source_data;
		}

		// Get available slots for service 0.
		$slots = Oxtilofastcal_Availability::get_available_slots( $date, 0 );
		foreach ( $slots as $slot ) {
			$result['available'][] = array(
				'start' => $slot['start'],
				'end'   => $slot['end'],
			);
		}

		wp_send_json_success( $result );
	}

	/**
	 * Render settings page.
	 */
	public function render_settings_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Access Denied', 'oxtilo-fast-cal' ) );
		}
		include OXTILOFASTCAL_PLUGIN_DIR . 'admin/views/settings-main.php';
	}

	/**
	 * Render email templates page.
	 */
	public function render_email_templates_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Access Denied', 'oxtilo-fast-cal' ) );
		}
		include OXTILOFASTCAL_PLUGIN_DIR . 'admin/views/settings-email.php';
	}

	/**
	 * Render security page.
	 */
	public function render_security_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Access Denied', 'oxtilo-fast-cal' ) );
		}
		include OXTILOFASTCAL_PLUGIN_DIR . 'admin/views/settings-security.php';
	}

	/**
	 * Render diagnostics page.
	 */
	public function render_diagnostics_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Access Denied', 'oxtilo-fast-cal' ) );
		}
		include OXTILOFASTCAL_PLUGIN_DIR . 'admin/views/settings-diagnostics.php';
	}

	/**
	 * Render bookings page.
	 */
	public function render_bookings_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Access Denied', 'oxtilo-fast-cal' ) );
		}
		include OXTILOFASTCAL_PLUGIN_DIR . 'admin/views/bookings-page.php';
	}

	/**
	 * Handle saving a booking.
	 */
	public function handle_save_booking(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Access Denied', 'oxtilo-fast-cal' ) );
		}

		check_admin_referer( 'oxtilofastcal_edit_booking', 'oxtilofastcal_nonce' );

		$booking_id = isset( $_POST['booking_id'] ) ? absint( $_POST['booking_id'] ) : 0;
		if ( $booking_id <= 0 ) {
			wp_die( esc_html__( 'Invalid ID', 'oxtilo-fast-cal' ) );
		}

		// Validate and parse date/time values.
		$tz = wp_timezone();
		try {
			$start_dt = new DateTimeImmutable( sanitize_text_field( wp_unslash( $_POST['start_time'] ?? '' ) ), $tz );
			$end_dt   = new DateTimeImmutable( sanitize_text_field( wp_unslash( $_POST['end_time'] ?? '' ) ), $tz );
		} catch ( Exception $e ) {
			wp_die( esc_html__( 'Invalid date/time format.', 'oxtilo-fast-cal' ) );
		}

		if ( $end_dt <= $start_dt ) {
			wp_die( esc_html__( 'End time must be after start time.', 'oxtilo-fast-cal' ) );
		}

		$data = array(
			'client_name'  => sanitize_text_field( wp_unslash( $_POST['client_name'] ?? '' ) ),
			'client_email' => sanitize_email( wp_unslash( $_POST['client_email'] ?? '' ) ),
			'service_name' => sanitize_text_field( wp_unslash( $_POST['service_name'] ?? '' ) ),
			'start_time'   => $start_dt->format( 'Y-m-d H:i:s' ),
			'end_time'     => $end_dt->format( 'Y-m-d H:i:s' ),
			'status'       => sanitize_key( wp_unslash( $_POST['status'] ?? 'confirmed' ) ),
		);

		Oxtilofastcal_Database::update_booking( $booking_id, $data );

		// Send notification.
		Oxtilofastcal_Notifications::send_update( $booking_id );

		wp_safe_redirect( add_query_arg( array( 'page' => 'oxtilofastcal-bookings', 'msg' => 'updated' ), admin_url( 'admin.php' ) ) );
		exit;
	}

	/**
	 * Handle deleting a booking.
	 */
	public function handle_delete_booking(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Access Denied', 'oxtilo-fast-cal' ) );
		}

		check_admin_referer( 'oxtilofastcal_delete_booking' );

		$booking_id = isset( $_GET['booking_id'] ) ? absint( wp_unslash( $_GET['booking_id'] ) ) : 0;
		if ( $booking_id > 0 ) {
			// Get booking before delete to send notification.
			$booking = Oxtilofastcal_Database::get_booking( $booking_id );
			if ( $booking ) {
				// We can mark it as cancelled then send email, then delete. Or just send "Cancellation" email.
				// But send_update relies on the DB record.
				// So update status to cancelled first.
				Oxtilofastcal_Database::update_booking( $booking_id, array( 'status' => 'cancelled' ) );
				Oxtilofastcal_Notifications::send_update( $booking_id );
				
				// Now delete.
				Oxtilofastcal_Database::delete_booking( $booking_id );
			}
		}

		wp_safe_redirect( add_query_arg( array( 'page' => 'oxtilofastcal-bookings', 'msg' => 'deleted' ), admin_url( 'admin.php' ) ) );
		exit;
	}

	/**
	 * Handle creating a new booking from admin panel.
	 */
	public function handle_create_booking(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Access Denied', 'oxtilo-fast-cal' ) );
		}

		check_admin_referer( 'oxtilofastcal_create_booking', 'oxtilofastcal_nonce' );

		$redirect_url = admin_url( 'admin.php?page=oxtilofastcal-bookings&action=add' );

		// Get and sanitize form data.
		$client_name    = isset( $_POST['client_name'] ) ? sanitize_text_field( wp_unslash( $_POST['client_name'] ) ) : '';
		$client_email   = isset( $_POST['client_email'] ) ? sanitize_email( wp_unslash( $_POST['client_email'] ) ) : '';
		$service_name   = isset( $_POST['service_name'] ) ? sanitize_text_field( wp_unslash( $_POST['service_name'] ) ) : '';
		$service_template = isset( $_POST['service_template'] ) ? absint( $_POST['service_template'] ) : -1;
		$booking_date   = isset( $_POST['booking_date'] ) ? sanitize_text_field( wp_unslash( $_POST['booking_date'] ) ) : '';
		$start_time     = isset( $_POST['start_time'] ) ? sanitize_text_field( wp_unslash( $_POST['start_time'] ) ) : '';
		$end_time       = isset( $_POST['end_time'] ) ? sanitize_text_field( wp_unslash( $_POST['end_time'] ) ) : '';
		$client_message = isset( $_POST['client_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['client_message'] ) ) : '';
		$send_notification = isset( $_POST['send_notification'] ) && '1' === $_POST['send_notification'];

		// Validation.
		if ( '' === $client_name || '' === $service_name || '' === $booking_date || '' === $start_time || '' === $end_time ) {
			wp_safe_redirect( add_query_arg( 'error', 'missing_data', $redirect_url ) );
			exit;
		}

		if ( '' === $client_email || ! is_email( $client_email ) ) {
			wp_safe_redirect( add_query_arg( 'error', 'invalid_email', $redirect_url ) );
			exit;
		}

		// Validate date format.
		if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $booking_date ) ) {
			wp_safe_redirect( add_query_arg( 'error', 'invalid_dates', $redirect_url ) );
			exit;
		}

		// Parse and validate times.
		$tz = wp_timezone();
		try {
			$start_dt = new DateTimeImmutable( $booking_date . ' ' . $start_time . ':00', $tz );
			$end_dt   = new DateTimeImmutable( $booking_date . ' ' . $end_time . ':00', $tz );
		} catch ( Exception $e ) {
			wp_safe_redirect( add_query_arg( 'error', 'invalid_dates', $redirect_url ) );
			exit;
		}

		// End time must be after start time.
		if ( $end_dt <= $start_dt ) {
			wp_safe_redirect( add_query_arg( 'error', 'invalid_dates', $redirect_url ) );
			exit;
		}

		// Get service type for meeting_type from template if selected.
		$services = oxtilofastcal_get_services();
		$service  = ( $service_template >= 0 && isset( $services[ $service_template ] ) ) ? $services[ $service_template ] : array();
		$type     = $service['type'] ?? 'online';
		$meeting_type = ( 'in_person' === $type ) ? 'in_person' : 'online';

		// Insert booking.
		$booking_id = Oxtilofastcal_Database::insert_booking( array(
			'service_id'     => $service_template >= 0 ? $service_template : 0,
			'service_name'   => $service_name,
			'client_name'    => $client_name,
			'client_email'   => $client_email,
			'client_message' => $client_message,
			'start_time'     => $start_dt->format( 'Y-m-d H:i:s' ),
			'end_time'       => $end_dt->format( 'Y-m-d H:i:s' ),
			'status'         => 'confirmed',
			'meeting_type'   => $meeting_type,
		) );

		if ( false === $booking_id ) {
			wp_safe_redirect( add_query_arg( 'error', 'db_error', $redirect_url ) );
			exit;
		}

		// Send notification emails if requested.
		if ( $send_notification ) {
			Oxtilofastcal_Notifications::send( $booking_id, array( 'client_message' => $client_message ) );
		}

		wp_safe_redirect( add_query_arg( array( 'page' => 'oxtilofastcal-bookings', 'msg' => 'created' ), admin_url( 'admin.php' ) ) );
		exit;
	}
}
