<?php
/**
 * Plugin Name: Oxtilo Fast Cal
 * Description: A secure and flexible booking management system for WordPress. Features robust availability handling, ICS calendar synchronization, email notifications, and a full REST API. Includes built-in Polish translations.
 * Version: 0.9.8
 * Author: Slawomir Klimek
 * Author URI: https://oxtilo.pl
 * Text Domain: oxtilo-fast-cal
 * Requires PHP: 7.4
 * Requires at least: 5.8
 * License: GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.txt
 *
 * @package Oxtilofastcal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin constants.
define( 'OXTILOFASTCAL_VERSION', '0.9.8' );
define( 'OXTILOFASTCAL_PLUGIN_FILE', __FILE__ );
define( 'OXTILOFASTCAL_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'OXTILOFASTCAL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Autoload includes.
require_once OXTILOFASTCAL_PLUGIN_DIR . 'includes/helpers.php';
require_once OXTILOFASTCAL_PLUGIN_DIR . 'includes/class-oxtilofastcal-i18n-fallback.php';
require_once OXTILOFASTCAL_PLUGIN_DIR . 'includes/class-oxtilofastcal-ics-parser.php';
require_once OXTILOFASTCAL_PLUGIN_DIR . 'includes/class-oxtilofastcal-database.php';
require_once OXTILOFASTCAL_PLUGIN_DIR . 'includes/class-oxtilofastcal-availability.php';
require_once OXTILOFASTCAL_PLUGIN_DIR . 'includes/class-oxtilofastcal-cron.php';
require_once OXTILOFASTCAL_PLUGIN_DIR . 'includes/class-oxtilofastcal-notifications.php';
require_once OXTILOFASTCAL_PLUGIN_DIR . 'includes/class-oxtilofastcal-shortcode.php';
require_once OXTILOFASTCAL_PLUGIN_DIR . 'includes/class-oxtilofastcal-blocks.php';
require_once OXTILOFASTCAL_PLUGIN_DIR . 'includes/class-oxtilofastcal-ajax.php';
require_once OXTILOFASTCAL_PLUGIN_DIR . 'includes/class-oxtilofastcal-ics-feed.php';
require_once OXTILOFASTCAL_PLUGIN_DIR . 'includes/class-oxtilofastcal-manager.php';
require_once OXTILOFASTCAL_PLUGIN_DIR . 'includes/class-oxtilofastcal-api.php';
require_once OXTILOFASTCAL_PLUGIN_DIR . 'includes/class-oxtilofastcal-rate-limiter.php';

// Admin includes.
if ( is_admin() ) {
	require_once OXTILOFASTCAL_PLUGIN_DIR . 'admin/class-oxtilofastcal-admin.php';
	require_once OXTILOFASTCAL_PLUGIN_DIR . 'admin/class-oxtilofastcal-admin-settings.php';
}

require_once OXTILOFASTCAL_PLUGIN_DIR . 'includes/class-oxtilofastcal-plugin.php';

// Register Gutenberg blocks early - must be on 'init' hook.
add_action(
	'init',
	static function () {
		Oxtilofastcal_Blocks::instance();
	},
	5
);

/**
 * Returns the main plugin instance.
 *
 * @return Oxtilofastcal_Plugin
 */
function oxtilofastcal(): Oxtilofastcal_Plugin {
	return Oxtilofastcal_Plugin::instance();
}

// Initialize the plugin.
add_action(
	'plugins_loaded',
	static function () {
		oxtilofastcal();
	}
);

// Activation/Deactivation hooks.
register_activation_hook( __FILE__, array( 'Oxtilofastcal_Database', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Oxtilofastcal_Cron', 'deactivate' ) );
