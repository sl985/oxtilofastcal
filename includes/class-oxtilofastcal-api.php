<?php
/**
 * REST API for Oxtilofastcal.
 *
 * @package Oxtilofastcal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API class.
 *
 * @since 0.6.0
 */
final class Oxtilofastcal_API {

	/**
	 * Single instance of the class.
	 *
	 * @var Oxtilofastcal_API|null
	 */
	private static ?self $instance = null;

	/**
	 * API namespace.
	 *
	 * @var string
	 */
	private const NAMESPACE = 'oxtilofastcal/v1';

	/**
	 * Get the singleton instance.
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
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register REST API routes.
	 */
	public function register_routes(): void {
		// GET /wp-json/oxtilofastcal/v1/slots
		register_rest_route(
			self::NAMESPACE,
			'/slots',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_slots' ),
				'permission_callback' => array( $this, 'verify_token' ),
				'args'                => array(
					'date'       => array(
						'required'          => true,
						'type'              => 'string',
						'validate_callback' => array( $this, 'validate_date' ),
					),
					'service_id' => array(
						'required' => false,
						'type'     => 'integer',
						'default'  => 0,
					),
					'duration'   => array(
						'required' => false,
						'type'     => 'integer',
						'default'  => 0,
					),
				),
			)
		);

		// POST /wp-json/oxtilofastcal/v1/create
		register_rest_route(
			self::NAMESPACE,
			'/create',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'create_booking' ),
				'permission_callback' => array( $this, 'verify_token' ),
				'args'                => array(
					'client_name'    => array(
						'required' => true,
						'type'     => 'string',
					),
					'client_email'   => array(
						'required'          => true,
						'type'              => 'string',
						'validate_callback' => array( $this, 'validate_email' ),
					),
					'date'           => array(
						'required'          => true,
						'type'              => 'string',
						'validate_callback' => array( $this, 'validate_date' ),
					),
					'time'           => array(
						'required'          => true,
						'type'              => 'string',
						'validate_callback' => array( $this, 'validate_time' ),
					),
					'duration'       => array(
						'required' => false,
						'type'     => 'integer',
						'default'  => 60,
					),
					'service_name'   => array(
						'required' => false,
						'type'     => 'string',
						'default'  => 'Rezerwacja API',
					),
					'client_message' => array(
						'required' => false,
						'type'     => 'string',
						'default'  => '',
					),
				),
			)
		);
	}

	/**
	 * Verify API token from request header.
	 *
	 * @param WP_REST_Request $request REST request.
	 * @return bool|WP_Error
	 */
	public function verify_token( WP_REST_Request $request ) {
		// Rate limiting check (before token verification to defend against DoS).
		$rate_limiter = Oxtilofastcal_Rate_Limiter::instance();
		if ( ! $rate_limiter->check( 'rest_api' ) ) {
			return $rate_limiter->get_rest_error();
		}

		$token_header = $request->get_header( 'X-Oxtilofastcal-Token' );

		$general     = get_option( 'oxtilofastcal_general', array() );
		$general     = is_array( $general ) ? $general : array();
		$saved_token = $general['api_token'] ?? '';

		if ( empty( $saved_token ) || empty( $token_header ) || ! hash_equals( $saved_token, $token_header ) ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'Invalid or missing API token.', 'oxtilofastcal' ),
				array( 'status' => 403 )
			);
		}

		return true;
	}

	/**
	 * Validate date format (YYYY-MM-DD).
	 *
	 * @param string $value Date value.
	 * @return bool
	 */
	public function validate_date( string $value ): bool {
		if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ) ) {
			return false;
		}
		$dt = DateTime::createFromFormat( 'Y-m-d', $value );
		return $dt && $dt->format( 'Y-m-d' ) === $value;
	}

	/**
	 * Validate time format (HH:MM).
	 *
	 * @param string $value Time value.
	 * @return bool
	 */
	public function validate_time( string $value ): bool {
		return (bool) preg_match( '/^(?:[01]\d|2[0-3]):[0-5]\d$/', $value );
	}

	/**
	 * Validate email format.
	 *
	 * @param string $value Email value.
	 * @return bool
	 */
	public function validate_email( string $value ): bool {
		return is_email( $value ) !== false;
	}

	/**
	 * GET /slots endpoint handler.
	 *
	 * @param WP_REST_Request $request REST request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_slots( WP_REST_Request $request ) {
		$date       = sanitize_text_field( $request->get_param( 'date' ) );
		$service_id = absint( $request->get_param( 'service_id' ) );
		$duration   = absint( $request->get_param( 'duration' ) );

		// Validate max days in future.
		$general  = get_option( 'oxtilofastcal_general', array() );
		$max_days = isset( $general['max_days_future'] ) ? absint( $general['max_days_future'] ) : 30;

		if ( $max_days > 0 ) {
			$today          = new DateTimeImmutable( 'now', wp_timezone() );
			$max_date       = $today->add( new DateInterval( 'P' . $max_days . 'D' ) );
			$requested_date = new DateTimeImmutable( $date, wp_timezone() );

			if ( $requested_date > $max_date ) {
				return new WP_Error(
					'date_too_far',
					__( 'Date is too far in the future.', 'oxtilofastcal' ),
					array( 'status' => 400 )
				);
			}
		}

		$slots = Oxtilofastcal_Availability::get_available_slots( $date, $service_id, $duration );

		// Format response.
		$formatted_slots = array();
		$time_format     = (string) get_option( 'time_format', 'H:i' );

		foreach ( $slots as $slot ) {
			try {
				$start = new DateTimeImmutable( $slot['start'] );
				$end   = new DateTimeImmutable( $slot['end'] );

				$formatted_slots[] = array(
					'start' => $slot['start'],
					'end'   => $slot['end'],
					'label' => $start->format( $time_format ) . ' - ' . $end->format( $time_format ),
				);
			} catch ( Exception $e ) {
				continue;
			}
		}

		return new WP_REST_Response(
			array(
				'success' => true,
				'date'    => $date,
				'slots'   => $formatted_slots,
			),
			200
		);
	}

	/**
	 * POST /create endpoint handler.
	 *
	 * @param WP_REST_Request $request REST request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function create_booking( WP_REST_Request $request ) {
		$client_name    = sanitize_text_field( $request->get_param( 'client_name' ) );
		$client_email   = sanitize_email( $request->get_param( 'client_email' ) );
		$date           = sanitize_text_field( $request->get_param( 'date' ) );
		$time           = sanitize_text_field( $request->get_param( 'time' ) );
		$duration       = absint( $request->get_param( 'duration' ) );
		$service_name   = sanitize_text_field( $request->get_param( 'service_name' ) );
		$client_message = sanitize_textarea_field( $request->get_param( 'client_message' ) );

		// Clamp duration: min 5 minutes, max 480 minutes (8 hours).
		if ( $duration <= 0 ) {
			$duration = 60;
		}
		$duration = max( 5, min( $duration, 480 ) );

		$tz = wp_timezone();

		// Calculate start and end times.
		try {
			$start_dt = new DateTimeImmutable( $date . ' ' . $time . ':00', $tz );
			$end_dt   = $start_dt->add( new DateInterval( 'PT' . $duration . 'M' ) );
		} catch ( Exception $e ) {
			return new WP_Error(
				'invalid_datetime',
				__( 'Invalid date or time format.', 'oxtilofastcal' ),
				array( 'status' => 400 )
			);
		}

		// Pre-check for conflicts (non-blocking for user feedback, includes external calendars).
		$busy_intervals = Oxtilofastcal_Availability::get_busy_intervals_for_date( $start_dt );

		if ( Oxtilofastcal_Availability::interval_overlaps_any( $start_dt, $end_dt, $busy_intervals ) ) {
			return new WP_Error(
				'conflict',
				__( 'Termin jest już zajęty.', 'oxtilofastcal' ),
				array( 'status' => 409 )
			);
		}

		// Use atomic insert with transaction to prevent race conditions (TOCTOU).
		$result = Oxtilofastcal_Database::insert_booking_atomic(
			array(
				'service_id'     => 0,
				'service_name'   => $service_name,
				'client_name'    => $client_name,
				'client_email'   => $client_email,
				'client_message' => $client_message,
				'start_time'     => $start_dt->format( 'Y-m-d H:i:s' ),
				'end_time'       => $end_dt->format( 'Y-m-d H:i:s' ),
				'status'         => 'confirmed',
				'meeting_type'   => 'online',
			)
		);

		if ( ! $result['success'] ) {
			if ( 'conflict' === $result['error'] ) {
				return new WP_Error(
					'conflict',
					__( 'Termin jest już zajęty.', 'oxtilofastcal' ),
					array( 'status' => 409 )
				);
			}
			return new WP_Error(
				'insert_failed',
				__( 'Failed to create booking.', 'oxtilofastcal' ),
				array( 'status' => 500 )
			);
		}

		$booking_id = $result['booking_id'];

		// Send notifications.
		Oxtilofastcal_Notifications::send( $booking_id, array( 'client_message' => $client_message ) );

		return new WP_REST_Response(
			array(
				'success'    => true,
				'booking_id' => $booking_id,
				'message'    => __( 'Rezerwacja utworzona pomyślnie.', 'oxtilofastcal' ),
			),
			201
		);
	}
}
