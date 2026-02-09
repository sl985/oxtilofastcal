<?php
/**
 * Helper functions for Oxtilofastcal.
 *
 * @package Oxtilofastcal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * RFC 5545 TEXT escaping helper for ICS files.
 *
 * @param string $text Text to escape.
 * @return string
 */
function oxtilofastcal_ics_escape_text( string $text ): string {
	$text = str_replace( '\\', '\\\\', $text );
	$text = str_replace( ';', '\;', $text );
	$text = str_replace( ',', '\,', $text );
	$text = str_replace( array( "\r\n", "\r", "\n" ), '\n', $text );
	return $text;
}

/**
 * Check if a shortcode or Oxtilofastcal block is present on the current page.
 *
 * @param string $shortcode_tag Shortcode tag to check.
 * @return bool
 */
function oxtilofastcal_is_shortcode_present( string $shortcode_tag ): bool {
	if ( is_admin() ) {
		return false;
	}

	if ( is_singular() ) {
		$post = get_post();
		if ( $post ) {
			$content = (string) $post->post_content;

			// Check classic shortcode.
			if ( has_shortcode( $content, $shortcode_tag ) ) {
				return true;
			}

			// Check for shortcode in Gutenberg shortcode block.
			if ( strpos( $content, '[' . $shortcode_tag ) !== false ) {
				return true;
			}

			// Check for native Oxtilofastcal block.
			if ( function_exists( 'has_block' ) && has_block( 'oxtilofastcal/booking-form', $post ) ) {
				return true;
			}

			// Check if the post has blocks and any block contains the shortcode.
			if ( function_exists( 'has_blocks' ) && has_blocks( $post ) ) {
				$blocks = parse_blocks( $content );
				if ( oxtilofastcal_blocks_contain_shortcode( $blocks, $shortcode_tag ) ) {
					return true;
				}
			}
		}
	}

	return (bool) apply_filters( 'oxtilofastcal_force_enqueue_frontend_assets', false, $shortcode_tag );
}

/**
 * Recursively check if blocks contain a shortcode.
 *
 * @param array  $blocks        Parsed blocks.
 * @param string $shortcode_tag Shortcode tag to find.
 * @return bool
 */
function oxtilofastcal_blocks_contain_shortcode( array $blocks, string $shortcode_tag ): bool {
	foreach ( $blocks as $block ) {
		// Check shortcode block.
		if ( 'core/shortcode' === ( $block['blockName'] ?? '' ) ) {
			$inner_html = $block['innerHTML'] ?? '';
			if ( strpos( $inner_html, '[' . $shortcode_tag ) !== false ) {
				return true;
			}
		}

		// Check inner blocks recursively.
		if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
			if ( oxtilofastcal_blocks_contain_shortcode( $block['innerBlocks'], $shortcode_tag ) ) {
				return true;
			}
		}

		// Also check innerHTML for any block (shortcode might be in paragraph, etc.).
		$inner_html = $block['innerHTML'] ?? '';
		if ( strpos( $inner_html, '[' . $shortcode_tag ) !== false ) {
			return true;
		}
	}

	return false;
}

/**
 * Get the list of services.
 *
 * @return array<int,array{name:string,duration:int,type:string}>
 */
function oxtilofastcal_get_services(): array {
	$services_json = (string) get_option( 'oxtilofastcal_services_json', '[]' );
	$services      = json_decode( $services_json, true );
	return is_array( $services ) ? $services : array();
}

/**
 * Format slot label for display.
 *
 * @param string $start Y-m-d H:i:s in site TZ.
 * @param string $end   Y-m-d H:i:s in site TZ.
 * @return string
 */
function oxtilofastcal_format_slot_label( string $start, string $end ): string {
	$tz = wp_timezone();

	try {
		$s = new DateTimeImmutable( $start, $tz );
		$e = new DateTimeImmutable( $end, $tz );
	} catch ( Exception $ex ) {
		return '';
	}

	$general = get_option( 'oxtilofastcal_general', array() );
	$display_format = $general['time_format_display'] ?? '';

	if ( '12h' === $display_format ) {
		$time_format = 'h:i A';
	} elseif ( '24h' === $display_format ) {
		$time_format = 'H:i';
	} else {
		$time_format = (string) get_option( 'time_format', 'H:i' );
	}

	return $s->format( $time_format ) . ' - ' . $e->format( $time_format );
}

/**
 * Generate a cryptographically secure alphanumeric token.
 *
 * @param int $length Token length (minimum 16).
 * @return string
 */
function oxtilofastcal_generate_alnum_token( int $length = 32 ): string {
	$length = max( 16, absint( $length ) );
	return wp_generate_password( $length, false, false );
}

/**
 * Polyfill for str_ends_with() for PHP < 8.0.
 *
 * @param string $haystack The string to search in.
 * @param string $needle   The substring to search for.
 * @return bool
 */
function oxtilofastcal_str_ends_with( string $haystack, string $needle ): bool {
	if ( function_exists( 'str_ends_with' ) ) {
		return str_ends_with( $haystack, $needle );
	}

	if ( '' === $needle ) {
		return true;
	}

	$len = strlen( $needle );
	return substr( $haystack, -$len ) === $needle;
}

/**
 * Sanitize time in HH:MM format.
 *
 * @param string $time Time string.
 * @return string Sanitized time or empty string if invalid.
 */
function oxtilofastcal_sanitize_time_hhmm( string $time ): string {
	$time = trim( $time );
	if ( '' === $time ) {
		return '';
	}
	return preg_match( '/^(?:[01]\d|2[0-3]):[0-5]\d$/', $time ) ? $time : '';
}
