<?php
/**
 * Gutenberg blocks registration for Oxtilofastcal.
 *
 * @package Oxtilofastcal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Blocks registration class.
 *
 * @since 0.5.2
 */
final class Oxtilofastcal_Blocks {

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
		// Register definition.
		$this->register_blocks();
	}

	/**
	 * Register Gutenberg blocks manually.
	 */
	public function register_blocks(): void {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		// 1. Register editor script.
		wp_register_script(
			'oxtilofastcal-block-editor',
			OXTILOFASTCAL_PLUGIN_URL . 'assets/oxtilofastcal-block-editor.js',
			array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n' ),
			OXTILOFASTCAL_VERSION,
			true
		);

		// 2. Register editor style.
		wp_register_style(
			'oxtilofastcal-block-editor',
			OXTILOFASTCAL_PLUGIN_URL . 'assets/oxtilofastcal-block-editor.css',
			array(),
			OXTILOFASTCAL_VERSION
		);
		
		// 3. Register Translations.
		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( 'oxtilofastcal-block-editor', 'oxtilofastcal' );
		}

		// 4. Register Block Type (Dynamic).
		// We use render_callback to ensure it works everywhere (widgets, etc).
		register_block_type(
			'oxtilofastcal/booking-form',
			array(
				'api_version'     => 2,
				'title'           => __( 'Oxtilofastcal Form', 'oxtilofastcal' ),
				'category'        => 'widgets',
				'icon'            => 'calendar-alt',
				'attributes'      => array(),
				'editor_script'   => 'oxtilofastcal-block-editor',
				'editor_style'    => 'oxtilofastcal-block-editor',
				'render_callback' => array( Oxtilofastcal_Shortcode::instance(), 'render' ),
			)
		);
	}
}
