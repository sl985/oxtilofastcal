<?php
/**
 * Notifications for Oxtilofastcal.
 *
 * @package Oxtilofastcal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Notifications class.
 *
 * @since 0.5.0
 */
final class Oxtilofastcal_Notifications {

	/**
	 * Send admin + client notifications for a booking.
	 *
	 * @param int $booking_id Booking ID.
	 * @return bool
	 */
	public static function send( int $booking_id, array $extras = array() ): bool {
		$booking_id = absint( $booking_id );
		if ( $booking_id <= 0 ) {
			return false;
		}

		$row = Oxtilofastcal_Database::get_booking( $booking_id );
		if ( ! $row ) {
			return false;
		}

		$services   = oxtilofastcal_get_services();
		$service_id = isset( $row['service_id'] ) ? absint( $row['service_id'] ) : 0;
		$service    = $services[ $service_id ] ?? array(
			'name'     => __( 'Service', 'oxtilofastcal' ),
			'duration' => 0,
			'type'     => 'online',
		);

		$tz = wp_timezone();
		try {
			$start = new DateTimeImmutable( (string) $row['start_time'], $tz );
			$end   = new DateTimeImmutable( (string) $row['end_time'], $tz );
		} catch ( Exception $e ) {
			return false;
		}

		$general     = get_option( 'oxtilofastcal_general', array() );
		$general     = is_array( $general ) ? $general : array();
		$admin_email = sanitize_email( $general['admin_notification_email'] ?? '' );
		
		if ( '' === $admin_email || ! is_email( $admin_email ) ) {
			$admin_email = (string) get_option( 'admin_email' );
		}

		$client_email = sanitize_email( $row['client_email'] ?? '' );
		$client_name  = sanitize_text_field( $row['client_name'] ?? '' );
		// Prefer stored service_name, fallback to service lookup by ID
		$service_name = ! empty( $row['service_name'] ) ? sanitize_text_field( $row['service_name'] ) : sanitize_text_field( $service['name'] ?? __( 'Service', 'oxtilofastcal' ) );
		$service_type = (string) ( $service['type'] ?? 'online' );
		$client_message = sanitize_textarea_field( $extras['client_message'] ?? '' );

		$date_fmt = (string) get_option( 'date_format', 'Y-m-d' );
		$time_fmt = (string) get_option( 'time_format', 'H:i' );

		$when_line = sprintf(
			/* translators: 1: Date, 2: Start time, 3: End time */
			__( 'When: %1$s (%2$s - %3$s)', 'oxtilofastcal' ),
			$start->format( $date_fmt ),
			$start->format( $time_fmt ),
			$end->format( $time_fmt )
		);

		// Prepare common variables.
		$meet_link = esc_url_raw( $general['google_meet_link'] ?? '' );
		$site_name = get_bloginfo( 'name' );
		$edit_link = home_url( '/booking-manage/' . ( $row['booking_hash'] ?? '' ) . '/' );

		// Create ICS attachments.
		$ics_extras = array(
			'meet_link'       => ( 'online' === $service['type'] ) ? $meet_link : '',
			'edit_link'       => $edit_link,
			'method'          => 'PUBLISH',
			'client_message'  => $client_message,
		);

		// Admin ICS
		$ics_extras['target'] = 'admin';
		$ics_string_admin     = self::generate_booking_ics( $row, $service, $ics_extras );
		$ics_file_admin       = self::create_ics_file( $booking_id, $ics_string_admin );
		$admin_attachments    = $ics_file_admin ? array( $ics_file_admin ) : array();

		// Client ICS
		$ics_extras['target'] = 'client';
		$ics_string_client    = self::generate_booking_ics( $row, $service, $ics_extras );
		$ics_file_client      = self::create_ics_file( $booking_id, $ics_string_client );
		$client_attachments   = $ics_file_client ? array( $ics_file_client ) : array();

		$replacements = array(
			'{booking_id}'         => (string) $booking_id,
			'{service_name}'       => $service_name,
			'{client_name}'        => $client_name,
			'{client_email}'       => $client_email,
			'{booking_date}'       => $start->format( $date_fmt ),
			'{booking_time_start}' => $start->format( $time_fmt ),
			'{booking_time_end}'   => $end->format( $time_fmt ),
			'{meet_link}'          => $meet_link,
			'{site_name}'          => $site_name,
			'{edit_link}'          => $edit_link,
			'{client_message}'     => $client_message,
		);

		// Send admin email.
		self::send_admin_email( $admin_email, $replacements, $admin_attachments, $when_line );

		// Send client email.
		if ( $client_email && is_email( $client_email ) ) {
			self::send_client_email( $client_email, $replacements, $service_type, $client_attachments, $when_line, $meet_link );
		}

		// Cleanup ICS files.
		if ( $ics_file_admin && file_exists( $ics_file_admin ) ) {
			wp_delete_file( $ics_file_admin );
		}
		if ( $ics_file_client && file_exists( $ics_file_client ) ) {
			wp_delete_file( $ics_file_client );
		}

		return true;
	}

	/**
	 * Send admin notification.
	 */
	private static function send_admin_email( string $email, array $replacements, array $attachments, string $default_when_line ): void {
		$templates = get_option( 'oxtilofastcal_email_templates', array() );
		$subject   = $templates['admin_subject'] ?? '';
		$body      = $templates['admin_body'] ?? '';

		if ( ! empty( $subject ) && ! empty( $body ) ) {
			// Use custom template.
			$subject = self::parse_template( $subject, $replacements );
			$body    = self::parse_template( $body, $replacements );
			$headers = array( 'Content-Type: text/html; charset=UTF-8' );
		} else {
			// Default template (Text).
			/* translators: %d: Booking ID */
			$subject = sprintf( __( 'New booking confirmed (#%d)', 'oxtilofastcal' ), $replacements['{booking_id}'] );
			
			$body = implode( "\n", array(
				/* translators: %1$s: Booking ID */
				sprintf( __( 'A new booking has been confirmed (ID: %1$s).', 'oxtilofastcal' ), $replacements['{booking_id}'] ),
				'',
				/* translators: %s: Service name */
				sprintf( __( 'Service: %s', 'oxtilofastcal' ), $replacements['{service_name}'] ),
				$default_when_line,
				/* translators: %s: Meeting link */
				( ! empty( $replacements['{meet_link}'] ) ? sprintf( __( 'Join Meeting: %s', 'oxtilofastcal' ), $replacements['{meet_link}'] ) : '' ),
				/* translators: %s: Client name */
				sprintf( __( 'Client: %s', 'oxtilofastcal' ), $replacements['{client_name}'] ),
				/* translators: %s: Client email */
				sprintf( __( 'Email: %s', 'oxtilofastcal' ), $replacements['{client_email}'] ),
				/* translators: %s: Client message */
				( ! empty( $replacements['{client_message}'] ) ? sprintf( __( 'Message: %s', 'oxtilofastcal' ), $replacements['{client_message}'] ) : '' ),
				'',
				/* translators: %s: Edit link */
				sprintf( __( 'Manage Booking: %s', 'oxtilofastcal' ), $replacements['{edit_link}'] ),
				'',
				__( 'ICS file attached.', 'oxtilofastcal' ),
			) );
			$headers = array( 'Content-Type: text/plain; charset=UTF-8' );
		}

		wp_mail( $email, $subject, $body, $headers, $attachments );
	}

	/**
	 * Send client notification.
	 */
	private static function send_client_email( string $email, array $replacements, string $service_type, array $attachments, string $default_when_line, string $meet_link ): void {
		$templates = get_option( 'oxtilofastcal_email_templates', array() );
		$subject   = $templates['client_subject'] ?? '';
		$body      = $templates['client_body'] ?? '';

		if ( ! empty( $subject ) && ! empty( $body ) ) {
			// Use custom template.
			$subject = self::parse_template( $subject, $replacements );
			$body    = self::parse_template( $body, $replacements );
			$headers = array( 'Content-Type: text/html; charset=UTF-8' );
		} else {
			// Default template (Text).
			/* translators: %s: Service name */
			$subject = sprintf( __( 'Booking confirmed: %s', 'oxtilofastcal' ), $replacements['{service_name}'] );

			$body_lines = array(
				/* translators: %s: Client name */
				sprintf( __( 'Hello %s,', 'oxtilofastcal' ), $replacements['{client_name}'] ?: __( 'there', 'oxtilofastcal' ) ),
				'',
				__( 'Your booking is confirmed.', 'oxtilofastcal' ),
				'',
				/* translators: %s: Service name */
				sprintf( __( 'Service: %s', 'oxtilofastcal' ), $replacements['{service_name}'] ),
				$default_when_line,
			);

			if ( 'online' === $service_type ) {
				$body_lines[] = '';
				$body_lines[] = __( 'Online meeting details:', 'oxtilofastcal' );
				$body_lines[] = $meet_link ? ( __( 'Join link:', 'oxtilofastcal' ) . ' ' . $meet_link ) : __( 'Join link: (to be provided)', 'oxtilofastcal' );
			} else {
				$body_lines[] = '';
				$body_lines[] = __( 'In-person meeting details:', 'oxtilofastcal' );
				$body_lines[] = __( 'Address: (to be provided)', 'oxtilofastcal' );
			}

			if ( ! empty( $replacements['{client_message}'] ) ) {
				$body_lines[] = '';
				/* translators: %s: Client message */
				$body_lines[] = sprintf( __( 'Your message: %s', 'oxtilofastcal' ), $replacements['{client_message}'] );
			}

			$body_lines[] = '';
			$body_lines[] = __( 'You can manage your booking here:', 'oxtilofastcal' );
			$body_lines[] = $replacements['{edit_link}'];
			$body_lines[] = '';
			$body_lines[] = __( 'An ICS calendar invite is attached to this email.', 'oxtilofastcal' );

			$body    = implode( "\n", $body_lines );
			$headers = array( 'Content-Type: text/plain; charset=UTF-8' );
		}

		wp_mail( $email, $subject, $body, $headers, $attachments );
	}

	/**
	 * Parse template variables.
	 *
	 * @param string $content Template content.
	 * @param array  $replacements Key-value pairs of replacements.
	 * @return string
	 */
	private static function parse_template( string $content, array $replacements ): string {
		return str_replace( array_keys( $replacements ), array_values( $replacements ), $content );
	}

	/**
	 * Generate ICS for a single booking.
	 *
	 * @param array $row     Booking data.
	 * @param array $service Service data.
	 * @return string
	 */
	public static function generate_booking_ics( array $row, array $service, array $extras = array() ): string {
		$site_tz = wp_timezone();
		$utc_tz  = new DateTimeZone( 'UTC' );
		$id      = absint( $row['id'] ?? 0 );

		try {
			$start_local = new DateTimeImmutable( (string) $row['start_time'], $site_tz );
			$end_local   = new DateTimeImmutable( (string) $row['end_time'], $site_tz );
		} catch ( Exception $e ) {
			$start_local = new DateTimeImmutable( 'now', $site_tz );
			$end_local   = $start_local->add( new DateInterval( 'PT30M' ) );
		}

		$start_utc = $start_local->setTimezone( $utc_tz );
		$end_utc   = $end_local->setTimezone( $utc_tz );
		$now_utc   = new DateTimeImmutable( 'now', $utc_tz );

		$uid_host     = wp_parse_url( home_url(), PHP_URL_HOST ) ?: 'localhost';
		$uid          = 'oxtilofastcal-' . $id . '@' . $uid_host;
		
		// Get stored service_name and check if it matches a predefined template
		$stored_service_name = ! empty( $row['service_name'] ) ? sanitize_text_field( $row['service_name'] ) : '';
		$service_template_name = sanitize_text_field( $service['name'] ?? '' );
		$client_name = sanitize_text_field( $row['client_name'] ?? '' );
		
		// Check if stored service_name matches any predefined service template
		$services = oxtilofastcal_get_services();
		$is_predefined_service = false;
		foreach ( $services as $svc ) {
			if ( ! empty( $svc['name'] ) && $stored_service_name === $svc['name'] ) {
				$is_predefined_service = true;
				break;
			}
		}
		
		// Also check if no custom service_name was stored (fallback to template)
		if ( empty( $stored_service_name ) && ! empty( $service_template_name ) ) {
			$is_predefined_service = true;
			$stored_service_name = $service_template_name;
		}
		
		// Build summary: use client name for predefined services (Admin view), admin name (Client view), service_name for custom
		$target = $extras['target'] ?? 'admin';

		if ( $is_predefined_service ) {
			if ( 'client' === $target ) {
				// Client view: Use Admin Name from settings
				$general    = get_option( 'oxtilofastcal_general', array() );
				$admin_name = isset( $general['admin_name'] ) ? trim( $general['admin_name'] ) : '';
				
				// Fallback if admin name is not set
				$title = ! empty( $admin_name ) ? $admin_name : ( ! empty( $stored_service_name ) ? $stored_service_name : __( 'Booking', 'oxtilofastcal' ) );
				$summary = sprintf( '%s #%d', $title, $id );
			} elseif ( ! empty( $client_name ) ) {
				// Admin view: Use client name
				$summary = sprintf( '%s #%d', $client_name, $id );
			} else {
				$summary = sprintf( '%s #%d', $stored_service_name, $id );
			}
		} else {
			// Custom service name: use service_name as title
			$display_name = ! empty( $stored_service_name ) ? $stored_service_name : __( 'Service', 'oxtilofastcal' );
			$summary = sprintf( '%s #%d', $display_name, $id );
		}
		
		$method         = isset( $extras['method'] ) ? strtoupper( $extras['method'] ) : 'PUBLISH';
		$meet_link      = isset( $extras['meet_link'] ) ? (string) $extras['meet_link'] : '';
		$edit_link      = isset( $extras['edit_link'] ) ? (string) $extras['edit_link'] : '';
		$client_message = isset( $extras['client_message'] ) ? sanitize_textarea_field( $extras['client_message'] ) : '';

		$description_lines = array();
		if ( ! empty( $meet_link ) ) {
			$description_lines[] = __( 'Join Meeting:', 'oxtilofastcal' ) . ' ' . $meet_link;
		}
		if ( ! empty( $edit_link ) ) {
			$description_lines[] = '';
			$description_lines[] = __( 'Manage Booking:', 'oxtilofastcal' ) . ' ' . $edit_link;
		}
		if ( ! empty( $client_message ) ) {
			$description_lines[] = '';
			$description_lines[] = __( 'Message:', 'oxtilofastcal' ) . ' ' . $client_message;
		}
		
		$description = implode( "\n", $description_lines );

		$lines = array(
			'BEGIN:VCALENDAR',
			'VERSION:2.0',
			'CALSCALE:GREGORIAN',
			'METHOD:' . $method,
			'PRODID:-//Oxtilofastcal//Oxtilofastcal//EN',
			'BEGIN:VEVENT',
			'UID:' . oxtilofastcal_ics_escape_text( $uid ),
			'DTSTAMP:' . $now_utc->format( 'Ymd\THis\Z' ),
			'DTSTART:' . $start_utc->format( 'Ymd\THis\Z' ),
			'DTEND:' . $end_utc->format( 'Ymd\THis\Z' ),
			'SUMMARY:' . oxtilofastcal_ics_escape_text( $summary ),
			'SEQUENCE:' . (int) ( $row['sequence'] ?? 0 ),
		);

		if ( ! empty( $description ) ) {
			$lines[] = 'DESCRIPTION:' . oxtilofastcal_ics_escape_text( $description );
		}

		if ( 'CANCEL' === $method ) {
			$lines[] = 'STATUS:CANCELLED';
		}

		$lines[] = 'END:VEVENT';
		$lines[] = 'END:VCALENDAR';

		return implode( "\r\n", $lines ) . "\r\n";
	}

	/**
	 * Send admin + client notifications for a booking update (update or cancellation).
	 *
	 * @param int $booking_id Booking ID.
	 * @return bool
	 */
	public static function send_update( int $booking_id ): bool {
		$booking_id = absint( $booking_id );
		if ( $booking_id <= 0 ) {
			return false;
		}

		$row = Oxtilofastcal_Database::get_booking( $booking_id );
		if ( ! $row ) {
			return false;
		}

		$services   = oxtilofastcal_get_services();
		$service_id = isset( $row['service_id'] ) ? absint( $row['service_id'] ) : 0;
		$service    = $services[ $service_id ] ?? array(
			'name'     => __( 'Service', 'oxtilofastcal' ),
			'duration' => 0,
			'type'     => 'online',
		);
		$status = $row['status'] ?? 'confirmed';

		$tz = wp_timezone();
		try {
			$start = new DateTimeImmutable( (string) $row['start_time'], $tz );
			$end   = new DateTimeImmutable( (string) $row['end_time'], $tz );
		} catch ( Exception $e ) {
			return false;
		}

		$general     = get_option( 'oxtilofastcal_general', array() );
		$general     = is_array( $general ) ? $general : array();
		$admin_email = sanitize_email( $general['admin_notification_email'] ?? '' );
		
		if ( '' === $admin_email || ! is_email( $admin_email ) ) {
			$admin_email = (string) get_option( 'admin_email' );
		}

		$client_email = sanitize_email( $row['client_email'] ?? '' );
		$client_name  = sanitize_text_field( $row['client_name'] ?? '' );
		// Prefer stored service_name, fallback to service lookup by ID
		$service_name = ! empty( $row['service_name'] ) ? sanitize_text_field( $row['service_name'] ) : sanitize_text_field( $service['name'] ?? __( 'Service', 'oxtilofastcal' ) );
		$service_type = (string) ( $service['type'] ?? 'online' );

		// Prepare common variables.
		$date_fmt  = (string) get_option( 'date_format', 'Y-m-d' );
		$time_fmt  = (string) get_option( 'time_format', 'H:i' );
		$meet_link = esc_url_raw( $general['google_meet_link'] ?? '' );
		$site_name = get_bloginfo( 'name' );

		$when_line = sprintf(
			/* translators: 1: Date, 2: Start time, 3: End time */
			__( 'When: %1$s (%2$s - %3$s)', 'oxtilofastcal' ),
			$start->format( $date_fmt ),
			$start->format( $time_fmt ),
			$end->format( $time_fmt )
		);

		$replacements = array(
			'{booking_id}'         => (string) $booking_id,
			'{service_name}'       => $service_name,
			'{client_name}'        => $client_name,
			'{client_email}'       => $client_email,
			'{booking_date}'       => $start->format( $date_fmt ),
			'{booking_time_start}' => $start->format( $time_fmt ),
			'{booking_time_end}'   => $end->format( $time_fmt ),
			'{meet_link}'          => $meet_link,
			'{site_name}'          => $site_name,
		);

		// Generate ICS attachment
		$method = ( 'cancelled' === $status ) ? 'CANCEL' : 'PUBLISH';
		$ics_extras = array(
			'meet_link' => ( 'online' === $service_type ) ? $meet_link : '',
			'edit_link' => home_url( '/booking-manage/' . ( $row['booking_hash'] ?? '' ) . '/' ),
			'method'    => $method,
		);
		
		// Admin ICS
		$ics_extras['target'] = 'admin';
		$ics_string_admin     = self::generate_booking_ics( $row, $service, $ics_extras );
		$ics_file_admin       = self::create_ics_file( $booking_id, $ics_string_admin );
		$admin_attachments    = $ics_file_admin ? array( $ics_file_admin ) : array();

		// Client ICS
		$ics_extras['target'] = 'client';
		$ics_string_client    = self::generate_booking_ics( $row, $service, $ics_extras );
		$ics_file_client      = self::create_ics_file( $booking_id, $ics_string_client );
		$client_attachments   = $ics_file_client ? array( $ics_file_client ) : array();

		// Select template.
		$templates = get_option( 'oxtilofastcal_email_templates', array() );
		$templates = is_array( $templates ) ? $templates : array();

		if ( 'cancelled' === $status ) {
			$subject_tpl = $templates['cancel_subject'] ?? '';
			$body_tpl    = $templates['cancel_body'] ?? '';
			/* translators: %s: Service name */
			$default_subject = sprintf( __( 'Booking cancelled: %s', 'oxtilofastcal' ), $service_name );
			/* translators: %s: Client name */
			$default_body    = sprintf( __( 'Hello %s, your booking has been cancelled.', 'oxtilofastcal' ), $client_name ) . "\n\n" . $when_line;
		} else {
			$subject_tpl = $templates['update_subject'] ?? '';
			$body_tpl    = $templates['update_body'] ?? '';
			/* translators: %s: Service name */
			$default_subject = sprintf( __( 'Booking updated: %s', 'oxtilofastcal' ), $service_name );
			/* translators: %s: Client name */
			$default_body    = sprintf( __( 'Hello %s, your booking details have been updated.', 'oxtilofastcal' ), $client_name ) . "\n\n" . $when_line;
			if ( ! empty( $meet_link ) && 'online' === $service_type ) {
				/* translators: %s: Meeting link */
				$default_body .= "\n" . sprintf( __( 'Join Meeting: %s', 'oxtilofastcal' ), $meet_link );
			}
		}

		$subject = $default_subject;
		$body    = $default_body;
		$headers = array( 'Content-Type: text/plain; charset=UTF-8' );

		if ( ! empty( $subject_tpl ) && ! empty( $body_tpl ) ) {
			$subject = self::parse_template( $subject_tpl, $replacements );
			$body    = self::parse_template( $body_tpl, $replacements );
			$headers = array( 'Content-Type: text/html; charset=UTF-8' );
		}

		// Send to Client
		if ( $client_email && is_email( $client_email ) ) {
			wp_mail( $client_email, $subject, $body, $headers, $client_attachments );
		}

		// Send to Admin
		if ( $admin_email && is_email( $admin_email ) ) {
			// Reuse subject/body but maybe prefix for admin clarity? 
			// For simplicity and consistency with request, sending same notification details.
			// However, usually admins want to know WHO it is. The body already contains Client Name.
			wp_mail( $admin_email, $subject, $body, $headers, $admin_attachments );
		}
		
		// Cleanup ICS files.
		if ( $ics_file_admin && file_exists( $ics_file_admin ) ) {
			wp_delete_file( $ics_file_admin );
		}
		if ( $ics_file_client && file_exists( $ics_file_client ) ) {
			wp_delete_file( $ics_file_client );
		}

		return true;
	}

	/**
	 * Create a temporary ICS file.
	 *
	 * @param int    $booking_id Booking ID.
	 * @param string $content    ICS Content.
	 * @return string|null Path to file or null on failure.
	 */
	private static function create_ics_file( int $booking_id, string $content ): ?string {
		require_once ABSPATH . 'wp-admin/includes/file.php';

		global $wp_filesystem;
		if ( empty( $wp_filesystem ) ) {
			WP_Filesystem();
		}

		$tmp_file = wp_tempnam( 'oxtilofastcal-' . $booking_id );
		
		if ( ! $tmp_file || ! is_string( $tmp_file ) ) {
			return null;
		}

		$ics_file = preg_replace( '/\.tmp$/', '.ics', $tmp_file );
		if ( $ics_file === $tmp_file ) {
			$ics_file = $tmp_file . '.ics';
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.rename_rename
		if ( @rename( $tmp_file, $ics_file ) ) {
			$wp_filesystem->put_contents( $ics_file, $content );
			return $ics_file;
		}

		// Fallback
		$wp_filesystem->put_contents( $tmp_file, $content );
		return $tmp_file;
	}
}
