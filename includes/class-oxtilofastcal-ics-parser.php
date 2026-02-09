<?php
/**
 * ICS Parser for Oxtilofastcal.
 *
 * @package Oxtilofastcal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ICS Parser class.
 *
 * Parses ICS calendar files and extracts events.
 *
 * @since 0.5.0
 */
final class Oxtilofastcal_ICS_Parser {

	/**
	 * Parse ICS string and return events as intervals.
	 *
	 * @param string $ics_raw Raw ICS content.
	 * @return array<int,array{start:DateTimeImmutable,end:DateTimeImmutable,summary:string}>
	 */
	public static function parse( string $ics_raw ): array {
		$ics_raw = (string) $ics_raw;
		if ( '' === trim( $ics_raw ) ) {
			return array();
		}

		// Normalize line endings.
		$ics_raw = str_replace( array( "\r\n", "\r" ), "\n", $ics_raw );

		// Unfold lines (RFC 5545: lines can be folded with CRLF + space/tab).
		$lines    = explode( "\n", $ics_raw );
		$unfolded = array();

		foreach ( $lines as $line ) {
			$line = rtrim( $line, "\n" );
			if ( '' === $line ) {
				continue;
			}

			if ( preg_match( '/^[ \t]/', $line ) && ! empty( $unfolded ) ) {
				$unfolded[ count( $unfolded ) - 1 ] .= ltrim( $line );
			} else {
				$unfolded[] = $line;
			}
		}

		$events   = array();
		$in_event = false;
		$cur      = self::get_empty_event();

		foreach ( $unfolded as $line ) {
			$line = trim( $line );

			if ( 'BEGIN:VEVENT' === $line ) {
				$in_event = true;
				$cur      = self::get_empty_event();
				continue;
			}

			if ( 'END:VEVENT' === $line ) {
				$in_event = false;

				$start = $cur['dtstart'];
				$end   = $cur['dtend'];

				if ( $start instanceof DateTimeImmutable ) {
					if ( ! ( $end instanceof DateTimeImmutable ) && is_string( $cur['duration'] ) && '' !== $cur['duration'] ) {
						$end = self::apply_duration( $start, $cur['duration'] );
					}

					if ( $end instanceof DateTimeImmutable && $end > $start ) {
						$events[] = array(
							'start'   => $start,
							'end'     => $end,
							'summary' => (string) $cur['summary'],
						);
					}
				}

				continue;
			}

			if ( ! $in_event ) {
				continue;
			}

			// Split KEY(;PARAMS)*:VALUE.
			$pos = strpos( $line, ':' );
			if ( false === $pos ) {
				continue;
			}

			$left  = substr( $line, 0, $pos );
			$value = substr( $line, $pos + 1 );

			$left_parts = explode( ';', $left );
			$key        = strtoupper( (string) array_shift( $left_parts ) );

			$params = array();
			foreach ( $left_parts as $p ) {
				$eq = strpos( $p, '=' );
				if ( false === $eq ) {
					continue;
				}
				$pk            = strtoupper( substr( $p, 0, $eq ) );
				$pv            = substr( $p, $eq + 1 );
				$params[ $pk ] = $pv;
			}

			switch ( $key ) {
				case 'DTSTART':
					$cur['dtstart'] = self::parse_dt( $value, $params );
					break;
				case 'DTEND':
					$cur['dtend'] = self::parse_dt( $value, $params );
					break;
				case 'DURATION':
					$cur['duration'] = trim( (string) $value );
					break;
				case 'SUMMARY':
					$cur['summary'] = self::unescape_text( (string) $value );
					break;
			}
		}

		return $events;
	}

	/**
	 * Get empty event structure.
	 *
	 * @return array
	 */
	private static function get_empty_event(): array {
		return array(
			'dtstart'  => null,
			'dtend'    => null,
			'duration' => null,
			'summary'  => '',
		);
	}

	/**
	 * Parse a date-time value from ICS.
	 *
	 * @param string $value  The date-time value.
	 * @param array  $params Associated parameters.
	 * @return DateTimeImmutable|null
	 */
	private static function parse_dt( string $value, array $params ): ?DateTimeImmutable {
		$value = trim( $value );
		if ( '' === $value ) {
			return null;
		}

		$tz = wp_timezone();

		if ( isset( $params['TZID'] ) && is_string( $params['TZID'] ) && '' !== $params['TZID'] ) {
			try {
				$tz = new DateTimeZone( $params['TZID'] );
			} catch ( Exception $e ) {
				$tz = wp_timezone();
			}
		}

		// DATE only (all-day): YYYYMMDD.
		if ( preg_match( '/^\d{8}$/', $value ) ) {
			try {
				$start = DateTimeImmutable::createFromFormat( 'Ymd', $value, $tz );
				if ( $start instanceof DateTimeImmutable ) {
					return $start->setTime( 0, 0, 0 );
				}
			} catch ( Exception $e ) {
				return null;
			}
		}

		$is_utc = false;
		if ( oxtilofastcal_str_ends_with( $value, 'Z' ) ) {
			$is_utc = true;
			$value  = substr( $value, 0, -1 );
		}

		// Date-time: YYYYMMDDTHHMMSS or YYYYMMDDTHHMM.
		$formats = array( 'Ymd\THis', 'Ymd\THi' );
		foreach ( $formats as $fmt ) {
			$dt = DateTimeImmutable::createFromFormat( $fmt, $value, $is_utc ? new DateTimeZone( 'UTC' ) : $tz );
			if ( $dt instanceof DateTimeImmutable ) {
				return $dt;
			}
		}

		return null;
	}

	/**
	 * Apply a duration to a start date.
	 *
	 * @param DateTimeImmutable $start    Start date.
	 * @param string            $duration ISO 8601 duration like PT30M.
	 * @return DateTimeImmutable|null
	 */
	private static function apply_duration( DateTimeImmutable $start, string $duration ): ?DateTimeImmutable {
		try {
			$interval = new DateInterval( $duration );
			return $start->add( $interval );
		} catch ( Exception $e ) {
			return null;
		}
	}

	/**
	 * Unescape ICS text.
	 *
	 * @param string $text Escaped text.
	 * @return string
	 */
	private static function unescape_text( string $text ): string {
		$text = str_replace( '\n', "\n", $text );
		$text = str_replace( '\,', ',', $text );
		$text = str_replace( '\;', ';', $text );
		$text = str_replace( '\\\\', '\\', $text );
		return $text;
	}
}
