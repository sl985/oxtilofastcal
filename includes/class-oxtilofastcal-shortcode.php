<?php
/**
 * Shortcode for Oxtilofastcal.
 *
 * @package Oxtilofastcal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shortcode class.
 *
 * @since 0.5.0
 */
final class Oxtilofastcal_Shortcode {

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
		add_shortcode( 'oxtilofastcal_form', array( $this, 'render' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Enqueue frontend assets.
	 */
	public function enqueue_assets(): void {
		if ( ! oxtilofastcal_is_shortcode_present( 'oxtilofastcal_form' ) ) {
			return;
		}

		wp_enqueue_style( 'oxtilofastcal-frontend', OXTILOFASTCAL_PLUGIN_URL . 'assets/oxtilofastcal-frontend.css', array(), OXTILOFASTCAL_VERSION );
		wp_enqueue_script( 'oxtilofastcal-frontend', OXTILOFASTCAL_PLUGIN_URL . 'assets/oxtilofastcal-frontend.js', array( 'jquery' ), OXTILOFASTCAL_VERSION, true );

		wp_localize_script( 'oxtilofastcal-frontend', 'oxtilofastcalFrontend', array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'oxtilofastcal_get_slots' ),
			'i18n'    => array(
				'selectService' => __( 'Please select a service.', 'oxtilofastcal' ),
				'selectDate'    => __( 'Please choose a date.', 'oxtilofastcal' ),
				'loading'       => __( 'Loading available times…', 'oxtilofastcal' ),
				'noSlots'       => __( 'No available time slots for this date.', 'oxtilofastcal' ),
				'chooseTime'    => __( 'Choose a time slot', 'oxtilofastcal' ),
			),
		) );
	}

	/**
	 * Render shortcode.
	 *
	 * @param array|string $atts Shortcode attributes.
	 * @return string
	 */
	public function render( $atts ): string {
		// Ensure assets are loaded (fallback if detection failed).
		$this->maybe_enqueue_assets_inline();

		$services = oxtilofastcal_get_services();
		if ( empty( $services ) ) {
			return '<div class="oxtilofastcal-form oxtilofastcal-form--empty">' . esc_html__( 'No services available.', 'oxtilofastcal' ) . '</div>';
		}

		$return_url = get_permalink() ?: home_url( '/' );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$success = isset( $_GET['oxtilofastcal_success'] ) ? sanitize_text_field( wp_unslash( $_GET['oxtilofastcal_success'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$error = isset( $_GET['oxtilofastcal_error'] ) ? sanitize_text_field( wp_unslash( $_GET['oxtilofastcal_error'] ) ) : '';

		ob_start();
		include OXTILOFASTCAL_PLUGIN_DIR . 'templates/booking-form.php';
		return (string) ob_get_clean();
	}

	/**
	 * Enqueue assets inline if not already enqueued (fallback).
	 */
	private function maybe_enqueue_assets_inline(): void {
		// Check if already enqueued.
		if ( wp_style_is( 'oxtilofastcal-frontend', 'enqueued' ) ) {
			return;
		}

		// Enqueue now (will be in footer).
		wp_enqueue_style( 'oxtilofastcal-frontend', OXTILOFASTCAL_PLUGIN_URL . 'assets/oxtilofastcal-frontend.css', array(), OXTILOFASTCAL_VERSION );
		wp_enqueue_script( 'oxtilofastcal-frontend', OXTILOFASTCAL_PLUGIN_URL . 'assets/oxtilofastcal-frontend.js', array( 'jquery' ), OXTILOFASTCAL_VERSION, true );

		wp_localize_script( 'oxtilofastcal-frontend', 'oxtilofastcalFrontend', array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'oxtilofastcal_get_slots' ),
			'i18n'    => array(
				'selectService' => __( 'Please select a service.', 'oxtilofastcal' ),
				'selectDate'    => __( 'Please choose a date.', 'oxtilofastcal' ),
				'loading'       => __( 'Loading available times…', 'oxtilofastcal' ),
				'noSlots'       => __( 'No available time slots for this date.', 'oxtilofastcal' ),
				'chooseTime'    => __( 'Choose a time slot', 'oxtilofastcal' ),
			),
		) );
	}
}
