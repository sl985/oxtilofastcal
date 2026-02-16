<?php
/**
 * Database operations for Oxtilofastcal.
 *
 * @package Oxtilofastcal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database class for Oxtilofastcal.
 *
 * @since 0.5.0
 */
final class Oxtilofastcal_Database {

	/**
	 * Get the bookings table name.
	 *
	 * @return string
	 */
	public static function get_table_name(): string {
		global $wpdb;
		return $wpdb->prefix . 'oxtilofastcal_bookings';
	}

	/**
	 * Plugin activation handler.
	 */
	public static function activate(): void {
		self::create_tables();
		self::migrate_tables();
		self::ensure_defaults();
		self::add_rewrite_rules();
		flush_rewrite_rules();
		Oxtilofastcal_Cron::schedule_events();
	}

	/**
	 * Create database tables.
	 */
	private static function create_tables(): void {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table_name      = self::get_table_name();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table_name} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			service_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			service_name VARCHAR(200) NOT NULL DEFAULT '',
			client_name VARCHAR(200) NOT NULL DEFAULT '',
			client_email VARCHAR(200) NOT NULL DEFAULT '',
			client_message TEXT,
			start_time DATETIME NOT NULL,
			end_time DATETIME NOT NULL,
			status VARCHAR(20) NOT NULL DEFAULT 'confirmed',
			meeting_type VARCHAR(20) NOT NULL DEFAULT 'offline',
			booking_hash VARCHAR(64) NOT NULL DEFAULT '',
			sequence INT UNSIGNED NOT NULL DEFAULT 0,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY service_id (service_id),
			KEY start_time (start_time),
			KEY end_time (end_time),
			KEY status (status),
			KEY meeting_type (meeting_type),
			KEY booking_hash (booking_hash)
		) {$charset_collate};";

		dbDelta( $sql );
	}

	/**
	 * Migrate existing tables to add new columns.
	 */
	private static function migrate_tables(): void {
		global $wpdb;

		$table_name = self::get_table_name();

		// Check if service_name column exists.
		$column_exists = $wpdb->get_results(
			$wpdb->prepare(
				"SHOW COLUMNS FROM {$table_name} LIKE %s",
				'service_name'
			)
		);

		if ( empty( $column_exists ) ) {
			$wpdb->query( "ALTER TABLE {$table_name} ADD COLUMN service_name VARCHAR(200) NOT NULL DEFAULT '' AFTER service_id" );
		}

		// Check if client_message column exists.
		$client_msg_exists = $wpdb->get_results(
			$wpdb->prepare(
				"SHOW COLUMNS FROM {$table_name} LIKE %s",
				'client_message'
			)
		);

		if ( empty( $client_msg_exists ) ) {
			$wpdb->query( "ALTER TABLE {$table_name} ADD COLUMN client_message TEXT AFTER client_email" );
		}
	}

	/**
	 * Add rewrite rules for ICS feed.
	 */
	public static function add_rewrite_rules(): void {
		add_rewrite_rule(
			'^oxtilofastcal-feed/([a-zA-Z0-9]+)/?$',
			'index.php?oxtilofastcal_feed_token=$matches[1]',
			'top'
		);
	}

	/**
	 * Ensure default options exist.
	 */
	private static function ensure_defaults(): void {
		// General defaults.
		$general = get_option( 'oxtilofastcal_general', array() );
		$general = is_array( $general ) ? $general : array();

		if ( empty( $general['calendar_feed_token'] ) || ! preg_match( '/^[a-zA-Z0-9]+$/', (string) $general['calendar_feed_token'] ) ) {
			$general['calendar_feed_token'] = oxtilofastcal_generate_alnum_token( 32 );
		}

		if ( empty( $general['admin_notification_email'] ) ) {
			$general['admin_notification_email'] = (string) get_option( 'admin_email' );
		}

		if ( ! isset( $general['google_meet_link'] ) ) {
			$general['google_meet_link'] = '';
		}

		update_option( 'oxtilofastcal_general', $general, false );

		// Services default.
		$services_json = (string) get_option( 'oxtilofastcal_services_json', '' );
		if ( '' === trim( $services_json ) ) {
			$services = array(
				array(
					'name'     => __( 'Consultation (Online)', 'oxtilo-fast-cal' ),
					'duration' => 30,
					'type'     => 'online',
				),
				array(
					'name'     => __( 'Consultation (In-person)', 'oxtilo-fast-cal' ),
					'duration' => 60,
					'type'     => 'in_person',
				),
			);
			update_option( 'oxtilofastcal_services_json', wp_json_encode( $services ), false );
		}

		// Working hours default.
		$hours = get_option( 'oxtilofastcal_working_hours', array() );
		if ( ! is_array( $hours ) || empty( $hours ) ) {
			$hours = array(
				'mon' => array( 'start' => '09:00', 'end' => '17:00', 'day_off' => 0 ),
				'tue' => array( 'start' => '09:00', 'end' => '17:00', 'day_off' => 0 ),
				'wed' => array( 'start' => '09:00', 'end' => '17:00', 'day_off' => 0 ),
				'thu' => array( 'start' => '09:00', 'end' => '17:00', 'day_off' => 0 ),
				'fri' => array( 'start' => '09:00', 'end' => '17:00', 'day_off' => 0 ),
				'sat' => array( 'start' => '', 'end' => '', 'day_off' => 1 ),
				'sun' => array( 'start' => '', 'end' => '', 'day_off' => 1 ),
			);
			update_option( 'oxtilofastcal_working_hours', $hours, false );
		}

		// ICS feeds default.
		$feeds = get_option( 'oxtilofastcal_ics_feeds', array() );
		if ( ! is_array( $feeds ) ) {
			$feeds = array();
		}
		$feeds = wp_parse_args(
			$feeds,
			array(
				'icloud'   => '',
				'proton'   => '',
				'holidays' => '',
			)
		);
		update_option( 'oxtilofastcal_ics_feeds', $feeds, false );
	}

	/**
	 * Get a booking by ID.
	 *
	 * @param int $booking_id Booking ID.
	 * @return array|null Booking data or null if not found.
	 */
	public static function get_booking( int $booking_id ): ?array {
		global $wpdb;

		$table = self::get_table_name();

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE id = %d LIMIT 1",
				$booking_id
			),
			ARRAY_A
		);

		return is_array( $row ) ? $row : null;
	}

	/**
	 * Get confirmed bookings.
	 *
	 * @return array
	 */
	public static function get_confirmed_bookings(): array {
		global $wpdb;

		$table = self::get_table_name();

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, service_id, service_name, start_time, end_time, client_name, client_email, client_message, meeting_type, booking_hash, sequence
				FROM {$table}
				WHERE status = %s
				ORDER BY start_time ASC",
				'confirmed'
			),
			ARRAY_A
		);

		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * Get a booking by hash.
	 *
	 * @param string $hash Booking hash.
	 * @return array|null Booking data or null if not found.
	 */
	public static function get_booking_by_hash( string $hash ): ?array {
		global $wpdb;

		$table = self::get_table_name();
		$hash  = preg_replace( '/[^a-zA-Z0-9]/', '', $hash );

		if ( empty( $hash ) ) {
			return null;
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE booking_hash = %s LIMIT 1",
				$hash
			),
			ARRAY_A
		);

		return is_array( $row ) ? $row : null;
	}

	/**
	 * Get busy intervals for a specific date.
	 *
	 * @param DateTimeImmutable $day_start Start of the day.
	 * @param DateTimeImmutable $day_end   End of the day.
	 * @return array
	 */
	public static function get_busy_intervals( DateTimeImmutable $day_start, DateTimeImmutable $day_end ): array {
		global $wpdb;

		$table = self::get_table_name();
		$tz    = wp_timezone();
		$busy  = array();

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, start_time, end_time
				FROM {$table}
				WHERE status = %s
				AND start_time < %s
				AND end_time > %s",
				'confirmed',
				$day_end->format( 'Y-m-d H:i:s' ),
				$day_start->format( 'Y-m-d H:i:s' )
			),
			ARRAY_A
		);

		if ( is_array( $rows ) ) {
			foreach ( $rows as $r ) {
				try {
					$s = new DateTimeImmutable( (string) $r['start_time'], $tz );
					$e = new DateTimeImmutable( (string) $r['end_time'], $tz );
				} catch ( Exception $ex ) {
					continue;
				}

				if ( $e > $s ) {
					$busy[] = array(
						'booking_id' => isset( $r['id'] ) ? (int) $r['id'] : 0,
						'start'      => $s,
						'end'        => $e,
						'source'     => 'db',
					);
				}
			}
		}

		return $busy;
	}

	/**
	 * Insert a new booking atomically with conflict checking.
	 *
	 * This method prevents race conditions (TOCTOU) by checking for conflicts
	 * and inserting the booking within a single database transaction using
	 * row-level locking (SELECT ... FOR UPDATE).
	 *
	 * @param array $data Booking data with 'start_time' and 'end_time' in 'Y-m-d H:i:s' format.
	 * @return array{success: bool, booking_id: int|null, error: string|null}
	 */
	public static function insert_booking_atomic( array $data ): array {
		global $wpdb;

		$table = self::get_table_name();

		// Validate required time fields.
		if ( empty( $data['start_time'] ) || empty( $data['end_time'] ) ) {
			return array(
				'success'    => false,
				'booking_id' => null,
				'error'      => 'missing_times',
			);
		}

		$start_time = sanitize_text_field( $data['start_time'] );
		$end_time   = sanitize_text_field( $data['end_time'] );

		// Start transaction.
		$wpdb->query( 'START TRANSACTION' );

		// Check for conflicts with row-level locking.
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$conflict = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table}
				WHERE status = 'confirmed'
				AND start_time < %s
				AND end_time > %s
				FOR UPDATE",
				$end_time,
				$start_time
			)
		);

		if ( (int) $conflict > 0 ) {
			$wpdb->query( 'ROLLBACK' );
			return array(
				'success'    => false,
				'booking_id' => null,
				'error'      => 'conflict',
			);
		}

		// Use the regular insert method (it will use the same transaction).
		$booking_id = self::insert_booking( $data );

		if ( false === $booking_id ) {
			$wpdb->query( 'ROLLBACK' );
			return array(
				'success'    => false,
				'booking_id' => null,
				'error'      => 'insert_failed',
			);
		}

		$wpdb->query( 'COMMIT' );

		return array(
			'success'    => true,
			'booking_id' => $booking_id,
			'error'      => null,
		);
	}

	/**
	 * Insert a new booking.
	 *
	 * @param array $data Booking data.
	 * @return int|false Inserted ID or false on failure.
	 */
	public static function insert_booking( array $data ) {
		global $wpdb;

		$table = self::get_table_name();

		$defaults = array(
			'service_id'     => 0,
			'service_name'   => '',
			'client_name'    => '',
			'client_email'   => '',
			'client_message' => '',
			'start_time'     => '',
			'end_time'       => '',
			'status'         => 'confirmed',
			'meeting_type'   => 'online',
		);

		$data = wp_parse_args( $data, $defaults );

		// Sanitize data.
		$data['service_id']     = absint( $data['service_id'] );
		$data['service_name']   = sanitize_text_field( $data['service_name'] );
		$data['client_name']    = sanitize_text_field( $data['client_name'] );
		$data['client_email']   = sanitize_email( $data['client_email'] );
		$data['client_message'] = sanitize_textarea_field( $data['client_message'] );
		$data['status']         = sanitize_key( $data['status'] );
		$data['meeting_type']   = sanitize_key( $data['meeting_type'] );

		if ( empty( $data['booking_hash'] ) ) {
			$data['booking_hash'] = oxtilofastcal_generate_alnum_token( 32 );
		}

		$inserted = $wpdb->insert(
			$table,
			array(
				'service_id'     => $data['service_id'],
				'service_name'   => $data['service_name'],
				'client_name'    => $data['client_name'],
				'client_email'   => $data['client_email'],
				'client_message' => $data['client_message'],
				'start_time'     => $data['start_time'],
				'end_time'       => $data['end_time'],
				'status'         => $data['status'],
				'meeting_type'   => $data['meeting_type'],
				'booking_hash'   => $data['booking_hash'],
			),
			array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		if ( false === $inserted ) {
			return false;
		}

		return (int) $wpdb->insert_id;
	}

	/**
	 * Update an existing booking.
	 *
	 * @param int   $id   Booking ID.
	 * @param array $data Data to update.
	 * @return int|false Number of rows affected or false on error.
	 */
	public static function update_booking( int $id, array $data ) {
		global $wpdb;

		$table = self::get_table_name();

		// Define allowed fields and their format types.
		$allowed_fields = array(
			'service_id'   => '%d',
			'service_name' => '%s',
			'client_name'  => '%s',
			'client_email' => '%s',
			'start_time'   => '%s',
			'end_time'     => '%s',
			'status'       => '%s',
			'meeting_type' => '%s',
		);

		// Filter data to only allowed fields and build formats in the same order.
		$update_data = array();
		$formats     = array();

		foreach ( $data as $key => $value ) {
			if ( isset( $allowed_fields[ $key ] ) ) {
				$update_data[ $key ] = $value;
				$formats[]           = $allowed_fields[ $key ];
			}
		}

		if ( empty( $update_data ) ) {
			return 0;
		}

		// Increment sequence number.
		$update_data['sequence'] = (int) $wpdb->get_var( $wpdb->prepare( "SELECT sequence FROM {$table} WHERE id = %d", $id ) ) + 1;
		$formats[] = '%d';

		return $wpdb->update(
			$table,
			$update_data,
			array( 'id' => $id ),
			$formats,
			array( '%d' )
		);
	}

	/**
	 * Delete a booking.
	 *
	 * @param int $id Booking ID.
	 * @return int|false Number of rows deleted or false on error.
	 */
	public static function delete_booking( int $id ) {
		global $wpdb;

		$table = self::get_table_name();

		return $wpdb->delete(
			$table,
			array( 'id' => $id ),
			array( '%d' )
		);
	}

	/**
	 * Get all bookings with pagination and sorting.
	 *
	 * @param int    $limit   Number of items per page.
	 * @param int    $offset  Offset.
	 * @param string $orderby Column to sort by.
	 * @param string $order   ASC or DESC.
	 * @return array
	 */
	public static function get_all_bookings( int $limit = 20, int $offset = 0, string $orderby = 'id', string $order = 'DESC' ): array {
		global $wpdb;

		$table = self::get_table_name();
		
		$allowed_orderby = array( 'id', 'start_time', 'end_time', 'created_at', 'status', 'client_name' );
		if ( ! in_array( $orderby, $allowed_orderby, true ) ) {
			$orderby = 'id';
		}
		
		$order = strtoupper( $order ) === 'ASC' ? 'ASC' : 'DESC';

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d",
				$limit,
				$offset
			),
			ARRAY_A
		);

		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * Count total bookings.
	 *
	 * @return int
	 */
	public static function count_bookings(): int {
		global $wpdb;
		$table = self::get_table_name();
		return (int) $wpdb->get_var( "SELECT COUNT(id) FROM {$table}" );
	}

	/**
	 * Delete old bookings (cleanup).
	 *
	 * @param int $days_old How many days old bookings to delete.
	 * @return int Number of deleted rows.
	 */
	public static function delete_old_bookings( int $days_old = 730 ): int {
		global $wpdb;

		$table  = self::get_table_name();
		$cutoff = gmdate( 'Y-m-d H:i:s', time() - ( $days_old * DAY_IN_SECONDS ) );

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$deleted = $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$table} WHERE end_time < %s",
				$cutoff
			)
		);

		return is_int( $deleted ) ? $deleted : 0;
	}
}
