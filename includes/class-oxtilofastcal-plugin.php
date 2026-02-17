<?php
/**
 * Main plugin class.
 *
 * @package Oxtilofastcal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main Oxtilofastcal Plugin class.
 *
 * @since 0.5.0
 */
final class Oxtilofastcal_Plugin {

	/**
	 * Single instance of the class.
	 *
	 * @var Oxtilofastcal_Plugin|null
	 */
	private static ?self $instance = null;

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
		$this->init_hooks();
	}

	/**
	 * Initialize hooks.
	 */
	private function init_hooks(): void {
		// Load textdomain.
		add_action( 'init', array( $this, 'load_textdomain' ) );

		// Initialize components on init with priority 0 (early).
		add_action( 'init', array( $this, 'init_components' ), 0 );

		// Check for updates.
		add_action( 'plugins_loaded', array( $this, 'check_update' ) );
	}

	/**
	 * Check if plugin needs update (DB migration).
	 */
	public function check_update(): void {
		$current_version = get_option( 'oxtilofastcal_version', '0.0.0' );
		if ( version_compare( $current_version, OXTILOFASTCAL_VERSION, '<' ) ) {
			Oxtilofastcal_Database::activate();
			update_option( 'oxtilofastcal_version', OXTILOFASTCAL_VERSION );
		}
	}

	/**
	 * Load plugin textdomain.
	 */
	public function load_textdomain(): void {


		// Built-in Polish translations without .mo compilation.
		Oxtilofastcal_I18n_Fallback::init_if_needed();
	}

	/**
	 * Initialize plugin components.
	 */
	public function init_components(): void {
		// ICS Feed endpoint.
		Oxtilofastcal_ICS_Feed::instance();

		// Shortcode.
		Oxtilofastcal_Shortcode::instance();

		// Note: Gutenberg blocks are registered separately in oxtilo-fast-cal.php
		// to ensure proper timing during the 'init' hook.

		// AJAX handlers.
		Oxtilofastcal_Ajax::instance();

		// REST API.
		Oxtilofastcal_API::instance();

		// Booking Manager.
		Oxtilofastcal_Manager::instance();

		// Cron jobs.
		Oxtilofastcal_Cron::instance();

		// Admin.
		if ( is_admin() ) {
			Oxtilofastcal_Admin::instance();
		}
	}

	/**
	 * Get plugin version.
	 *
	 * @return string
	 */
	public function get_version(): string {
		return OXTILOFASTCAL_VERSION;
	}
}
