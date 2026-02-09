<?php
/**
 * Availability calculations for Oxtilofastcal.
 *
 * @package Oxtilofastcal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Availability class.
 *
 * @since 0.5.0
 */
final class Oxtilofastcal_Availability {

	/**
	 * Get available slots for a date and service.
	 *
	 * @param string $date            Y-m-d format.
	 * @param int    $service_id      Service index.
	 * @param int    $custom_duration Optional custom duration in minutes (overrides service duration).
	 * @return array<int,array{start:string,end:string}>
	 */
	public static function get_available_slots( string $date, int $service_id, int $custom_duration = 0, int $exclude_booking_id = 0 ): array {
		$services = oxtilofastcal_get_services();
		if ( ! isset( $services[ $service_id ] ) ) {
			// When no service found but custom_duration provided, still proceed.
			if ( $custom_duration <= 0 ) {
				return array();
			}
			$service = array( 'duration' => $custom_duration );
		} else {
			$service = $services[ $service_id ];
		}

		// Use custom_duration if provided and positive, otherwise use service duration.
		if ( $custom_duration > 0 ) {
			$duration = $custom_duration;
		} else {
			$duration = isset( $service['duration'] ) ? absint( $service['duration'] ) : 30;
			if ( $duration <= 0 ) {
				$duration = 30;
			}
		}

		$tz = wp_timezone();

		try {
			$day = new DateTimeImmutable( $date . ' 00:00:00', $tz );
		} catch ( Exception $e ) {
			return array();
		}

		$working = self::get_working_hours_for_date( $day );
		if ( empty( $working ) ) {
			return array();
		}

		$work_start = $working['start'];
		$work_end   = $working['end'];
		$busy       = self::get_busy_intervals_for_date( $day, $exclude_booking_id );

		// Get general settings (including time slot interval and min lead time).
		$general = get_option( 'oxtilofastcal_general', array() );
		$general = is_array( $general ) ? $general : array();

		// Get time slot interval from settings (15/30/60 minutes) - this determines start time alignment.
		$slot_interval = isset( $general['time_slot_interval'] ) ? absint( $general['time_slot_interval'] ) : 30;
		if ( ! in_array( $slot_interval, array( 15, 30, 60 ), true ) ) {
			$slot_interval = 30;
		}
		$step_minutes = (int) apply_filters( 'oxtilofastcal_slot_step_minutes', $slot_interval, $service_id, $service );
		$step_minutes = max( 5, $step_minutes );

		$slots  = array();
		$cursor = $work_start;

		$now          = new DateTimeImmutable( 'now', $tz );
		$default_lead = isset( $general['min_lead_time'] ) ? absint( $general['min_lead_time'] ) : 60;
		
		$min_lead_minutes = (int) apply_filters( 'oxtilofastcal_min_lead_minutes', $default_lead, $service_id, $service );
		$min_start        = $now->add( new DateInterval( 'PT' . max( 0, $min_lead_minutes ) . 'M' ) );

		while ( true ) {
			$slot_start = $cursor;
			$slot_end   = $slot_start->add( new DateInterval( 'PT' . $duration . 'M' ) );

			if ( $slot_end > $work_end ) {
				break;
			}

			if ( $slot_start >= $min_start && ! self::interval_overlaps_any( $slot_start, $slot_end, $busy ) ) {
				$slots[] = array(
					'start' => $slot_start->format( 'Y-m-d H:i:s' ),
					'end'   => $slot_end->format( 'Y-m-d H:i:s' ),
				);
			}

			$cursor = $cursor->add( new DateInterval( 'PT' . $step_minutes . 'M' ) );
		}

		return $slots;
	}

	/**
	 * Get working hours for a specific date.
	 *
	 * @param DateTimeImmutable $day The day.
	 * @return array{start:DateTimeImmutable,end:DateTimeImmutable}|array{}
	 */
	public static function get_working_hours_for_date( DateTimeImmutable $day ): array {
		$hours = get_option( 'oxtilofastcal_working_hours', array() );
		$hours = is_array( $hours ) ? $hours : array();

		$map = array( 1 => 'mon', 2 => 'tue', 3 => 'wed', 4 => 'thu', 5 => 'fri', 6 => 'sat', 7 => 'sun' );

		$weekday_num = (int) $day->format( 'N' );
		$key         = $map[ $weekday_num ] ?? 'mon';

		$row = isset( $hours[ $key ] ) && is_array( $hours[ $key ] ) ? $hours[ $key ] : array();
		$row = wp_parse_args( $row, array( 'start' => '', 'end' => '', 'day_off' => 0 ) );

		if ( ! empty( $row['day_off'] ) ) {
			return array();
		}

		$start = (string) ( $row['start'] ?? '' );
		$end   = (string) ( $row['end'] ?? '' );

		if ( ! preg_match( '/^(?:[01]\d|2[0-3]):[0-5]\d$/', $start ) || ! preg_match( '/^(?:[01]\d|2[0-3]):[0-5]\d$/', $end ) ) {
			return array();
		}

		$tz = wp_timezone();
		try {
			$work_start = new DateTimeImmutable( $day->format( 'Y-m-d' ) . ' ' . $start . ':00', $tz );
			$work_end   = new DateTimeImmutable( $day->format( 'Y-m-d' ) . ' ' . $end . ':00', $tz );
		} catch ( Exception $e ) {
			return array();
		}

		if ( $work_end <= $work_start ) {
			return array();
		}

		return array( 'start' => $work_start, 'end' => $work_end );
	}

	/**
	 * Get busy intervals for a date.
	 *
	 * @param DateTimeImmutable $day The day.
	 * @return array
	 */
	public static function get_busy_intervals_for_date( DateTimeImmutable $day, int $exclude_booking_id = 0 ): array {
		$tz        = wp_timezone();
		$day_start = $day->setTime( 0, 0, 0 );
		$day_end   = $day_start->add( new DateInterval( 'P1D' ) );

		$busy = Oxtilofastcal_Database::get_busy_intervals( $day_start, $day_end );

		if ( $exclude_booking_id > 0 ) {
			$busy = array_filter( $busy, function( $b ) use ( $exclude_booking_id ) {
				return ! isset( $b['booking_id'] ) || (int) $b['booking_id'] !== $exclude_booking_id;
			} );
		}

		$feeds = get_option( 'oxtilofastcal_ics_feeds', array() );
		$feeds = is_array( $feeds ) ? $feeds : array();

		$urls = array_filter( array(
			$feeds['icloud'] ?? '',
			$feeds['proton'] ?? '',
			$feeds['holidays'] ?? '',
		), function( $u ) { return is_string( $u ) && '' !== trim( $u ); } );

		foreach ( $urls as $url ) {
			$events = self::fetch_and_parse_ics_events( $url );
			foreach ( $events as $ev ) {
				$start = $ev['start'] instanceof DateTimeImmutable ? $ev['start']->setTimezone( $tz ) : null;
				$end   = $ev['end'] instanceof DateTimeImmutable ? $ev['end']->setTimezone( $tz ) : null;

				if ( $start && $end && $start < $day_end && $end > $day_start && $end > $start ) {
					$busy[] = array( 'start' => $start, 'end' => $end, 'source' => 'ics' );
				}
			}
		}

		return $busy;
	}

	/**
	 * Fetch and parse ICS events with caching.
	 *
	 * @param string $url ICS URL.
	 * @return array
	 */
	public static function fetch_and_parse_ics_events( string $url, bool $force_refresh = false ): array {
		$url = esc_url_raw( $url );
		if ( '' === $url ) {
			return array();
		}

		$key    = 'oxtilofastcal_ics_' . md5( $url );
		$cached = get_transient( $key );
		
		if ( ! $force_refresh && is_array( $cached ) ) {
			$events = array();
			foreach ( $cached as $row ) {
				if ( ! is_array( $row ) || empty( $row['start'] ) || empty( $row['end'] ) ) {
					continue;
				}
				try {
					$events[] = array(
						'start'   => new DateTimeImmutable( (string) $row['start'] ),
						'end'     => new DateTimeImmutable( (string) $row['end'] ),
						'summary' => $row['summary'] ?? '',
					);
				} catch ( Exception $e ) {
					continue;
				}
			}
			return $events;
		}

		$response = wp_remote_get( $url, array( 'timeout' => 15, 'redirection' => 3, 'user-agent' => 'Oxtilofastcal/' . OXTILOFASTCAL_VERSION ) );

		if ( is_wp_error( $response ) ) {
			set_transient( $key, array(), 5 * MINUTE_IN_SECONDS );
			return array();
		}

		$code = (int) wp_remote_retrieve_response_code( $response );
		$body = (string) wp_remote_retrieve_body( $response );

		if ( $code < 200 || $code >= 300 || '' === trim( $body ) ) {
			set_transient( $key, array(), 5 * MINUTE_IN_SECONDS );
			return array();
		}

		$events   = Oxtilofastcal_ICS_Parser::parse( $body );
		$to_store = array();
		
		foreach ( $events as $ev ) {
			$to_store[] = array(
				'start'   => $ev['start']->format( DATE_ATOM ),
				'end'     => $ev['end']->format( DATE_ATOM ),
				'summary' => (string) $ev['summary'],
			);
		}

		$feeds = get_option( 'oxtilofastcal_ics_feeds', array() );
		$freq  = isset( $feeds['update_frequency'] ) ? absint( $feeds['update_frequency'] ) : 60;
		if ( $freq < 5 ) {
			$freq = 5;
		}
		
		set_transient( $key, $to_store, $freq * MINUTE_IN_SECONDS );
		return $events;
	}

	/**
	 * Check if interval overlaps any busy interval.
	 *
	 * @param DateTimeImmutable $start Start.
	 * @param DateTimeImmutable $end   End.
	 * @param array             $busy  Busy intervals.
	 * @return bool
	 */
	public static function interval_overlaps_any( DateTimeImmutable $start, DateTimeImmutable $end, array $busy ): bool {
		foreach ( $busy as $b ) {
			$bs = $b['start'] ?? null;
			$be = $b['end'] ?? null;
			if ( $bs instanceof DateTimeImmutable && $be instanceof DateTimeImmutable && $start < $be && $end > $bs ) {
				return true;
			}
		}
		return false;
	}
}
