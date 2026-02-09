<?php
/**
 * Cron jobs for Oxtilofastcal.
 *
 * @package Oxtilofastcal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cron class.
 *
 * @since 0.5.0
 */
final class Oxtilofastcal_Cron {

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
		add_action( 'oxtilofastcal_cleanup_daily', array( $this, 'run_cleanup' ) );
	}

	/**
	 * Schedule cron events.
	 */
	public static function schedule_events(): void {
		if ( ! wp_next_scheduled( 'oxtilofastcal_cleanup_daily' ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'oxtilofastcal_cleanup_daily' );
		}
	}

	/**
	 * Deactivation handler - unschedule events.
	 */
	public static function deactivate(): void {
		$ts = wp_next_scheduled( 'oxtilofastcal_cleanup_daily' );
		if ( $ts ) {
			wp_unschedule_event( $ts, 'oxtilofastcal_cleanup_daily' );
		}
		flush_rewrite_rules();
	}

	/**
	 * Run cleanup - delete old bookings.
	 */
	public function run_cleanup(): void {
		Oxtilofastcal_Database::delete_old_bookings( 730 ); // 2 years.
	}
}
