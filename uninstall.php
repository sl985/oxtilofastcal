<?php
/**
 * Oxtilofastcal Uninstall
 *
 * Fired when the plugin is uninstalled.
 * Removes all plugin data from the database.
 *
 * @package Oxtilofastcal
 */

// Exit if not called by WordPress.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Main uninstall class.
 */
final class Oxtilofastcal_Uninstaller {

	/**
	 * Run uninstall routines.
	 */
	public static function uninstall(): void {
		// Check if user wants to delete data.
		$general = get_option( 'oxtilofastcal_general', array() );
		$general = is_array( $general ) ? $general : array();

		// Only delete data if explicitly enabled (safety measure).
		// By default, we'll delete everything. 
		// You can add an option 'delete_data_on_uninstall' to make this configurable.
		$delete_data = apply_filters( 'oxtilofastcal_delete_data_on_uninstall', true );

		if ( ! $delete_data ) {
			return;
		}

		self::delete_tables();
		self::delete_options();
		self::delete_transients();
		self::delete_cron_events();
		self::flush_rewrite_rules();
	}

	/**
	 * Delete custom database tables.
	 */
	private static function delete_tables(): void {
		global $wpdb;

		$table_name = $wpdb->prefix . 'oxtilofastcal_bookings';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange
		$wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );
	}

	/**
	 * Delete all plugin options.
	 */
	private static function delete_options(): void {
		$options = array(
			'oxtilofastcal_general',
			'oxtilofastcal_services_json',
			'oxtilofastcal_working_hours',
			'oxtilofastcal_ics_feeds',
			'oxtilofastcal_email_templates',
			'oxtilofastcal_security',
			'oxtilofastcal_db_version',
		);

		foreach ( $options as $option ) {
			delete_option( $option );
		}

		// For multisite, delete options from all sites.
		if ( is_multisite() ) {
			$sites = get_sites( array( 'fields' => 'ids' ) );
			foreach ( $sites as $site_id ) {
				switch_to_blog( $site_id );

				foreach ( $options as $option ) {
					delete_option( $option );
				}

				// Also drop tables on each site.
				global $wpdb;
				$table_name = $wpdb->prefix . 'oxtilofastcal_bookings';
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange
				$wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );

				restore_current_blog();
			}
		}
	}

	/**
	 * Delete all plugin transients.
	 */
	private static function delete_transients(): void {
		global $wpdb;

		// Delete ICS cache transients.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$wpdb->query(
			"DELETE FROM {$wpdb->options} 
			WHERE option_name LIKE '_transient_oxtilofastcal_%' 
			OR option_name LIKE '_transient_timeout_oxtilofastcal_%'"
		);

		// For multisite.
		if ( is_multisite() ) {
			$sites = get_sites( array( 'fields' => 'ids' ) );
			foreach ( $sites as $site_id ) {
				switch_to_blog( $site_id );

				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				$wpdb->query(
					"DELETE FROM {$wpdb->options} 
					WHERE option_name LIKE '_transient_oxtilofastcal_%' 
					OR option_name LIKE '_transient_timeout_oxtilofastcal_%'"
				);

				restore_current_blog();
			}
		}
	}

	/**
	 * Remove scheduled cron events.
	 */
	private static function delete_cron_events(): void {
		$cron_hooks = array(
			'oxtilofastcal_cleanup_daily',
		);

		foreach ( $cron_hooks as $hook ) {
			$timestamp = wp_next_scheduled( $hook );
			if ( $timestamp ) {
				wp_unschedule_event( $timestamp, $hook );
			}

			// Clear all events for this hook.
			wp_unschedule_hook( $hook );
		}
	}

	/**
	 * Flush rewrite rules to remove custom endpoints.
	 */
	private static function flush_rewrite_rules(): void {
		flush_rewrite_rules();
	}
}

// Run uninstall.
Oxtilofastcal_Uninstaller::uninstall();
