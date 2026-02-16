<?php
/**
 * Admin settings page template (Security).
 *
 * @package Oxtilofastcal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$security = get_option( 'oxtilofastcal_security', array() );
$security = is_array( $security ) ? $security : array();

// Rate limiter instance for WAF detection.
$rate_limiter = Oxtilofastcal_Rate_Limiter::instance();
$waf_info     = $rate_limiter->get_waf_info();
$current_ip   = $rate_limiter->get_client_ip();

// Default values.
$enabled  = ! empty( $security['rate_limit_enabled'] );
$requests = isset( $security['rate_limit_requests'] ) ? absint( $security['rate_limit_requests'] ) : 30;
$window   = isset( $security['rate_limit_window'] ) ? absint( $security['rate_limit_window'] ) : 60;
$source   = isset( $security['ip_source'] ) ? $security['ip_source'] : 'auto';

// IP source options.
$ip_sources = array(
	'auto'             => __( 'Auto-detect (recommended)', 'oxtilo-fast-cal' ),
	'remote_addr'      => 'REMOTE_ADDR (' . __( 'Direct connection', 'oxtilo-fast-cal' ) . ')',
	'cf_connecting_ip' => 'CF-Connecting-IP (Cloudflare)',
	'x_forwarded_for'  => 'X-Forwarded-For (' . __( 'Generic proxy', 'oxtilo-fast-cal' ) . ')',
	'x_real_ip'        => 'X-Real-IP (Nginx)',
	'sucuri'           => 'X-Sucuri-ClientIP (Sucuri)',
	'cloudfront'       => 'CloudFront-Viewer-Address (AWS CloudFront)',
	'fastly'           => 'Fastly-Client-IP (Fastly)',
);
?>
<div class="wrap oxtilofastcal-admin">
	<h1><?php echo esc_html__( 'Security Settings', 'oxtilo-fast-cal' ); ?></h1>
	<p class="description"><?php echo esc_html__( 'Configure rate limiting to protect against booking spam, brute force attacks, and abuse.', 'oxtilo-fast-cal' ); ?></p>

	<?php if ( false !== $waf_info['provider'] ) : ?>
		<div class="notice notice-info inline" style="margin: 15px 0;">
			<p>
				<strong>🔍 <?php echo esc_html__( 'WAF/CDN Detected:', 'oxtilo-fast-cal' ); ?></strong>
				<?php echo esc_html( $waf_info['provider'] ); ?>
			</p>
			<?php if ( $waf_info['warning'] ) : ?>
				<p><?php echo wp_kses( $waf_info['warning'], array( 'a' => array( 'href' => true, 'target' => true ) ) ); ?></p>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<form method="post" action="options.php">
		<?php settings_fields( 'oxtilofastcal_security_group' ); ?>

		<h2><?php echo esc_html__( 'Anti-Bot Protection', 'oxtilo-fast-cal' ); ?></h2>

		<table class="form-table" role="presentation">
			<tr>
				<th scope="row">
					<?php echo esc_html__( 'Enable Anti-Bot', 'oxtilo-fast-cal' ); ?>
				</th>
				<td>
					<label for="oxtilofastcal_antibot_enabled">
						<input type="checkbox" id="oxtilofastcal_antibot_enabled" name="oxtilofastcal_security[antibot_enabled]" value="1" <?php checked( ! empty( $security['antibot_enabled'] ) ); ?> />
						<?php echo esc_html__( 'Enable advanced anti-bot protection', 'oxtilo-fast-cal' ); ?>
					</label>
					<p class="description">
						<?php echo esc_html__( 'Adds Honeypot, Nonce, and Time Trap fields to the booking form. Protects against spam bots without Captcha.', 'oxtilo-fast-cal' ); ?>
					</p>
				</td>
			</tr>
		</table>

		<hr />

		<h2><?php echo esc_html__( 'Rate Limiting', 'oxtilo-fast-cal' ); ?></h2>

		<table class="form-table" role="presentation">
			<tr>
				<th scope="row">
					<?php echo esc_html__( 'Enable Rate Limiting', 'oxtilo-fast-cal' ); ?>
				</th>
				<td>
					<label for="oxtilofastcal_rate_limit_enabled">
						<input type="checkbox" id="oxtilofastcal_rate_limit_enabled" name="oxtilofastcal_security[rate_limit_enabled]" value="1" <?php checked( $enabled ); ?> />
						<?php echo esc_html__( 'Enable rate limiting for public endpoints', 'oxtilo-fast-cal' ); ?>
					</label>
					<p class="description">
						<?php echo esc_html__( 'Protects the booking form, time slots AJAX, REST API, and booking management page from abuse.', 'oxtilo-fast-cal' ); ?>
					</p>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="oxtilofastcal_rate_limit_requests"><?php echo esc_html__( 'Max Requests', 'oxtilo-fast-cal' ); ?></label>
				</th>
				<td>
					<input type="number" min="5" max="1000" step="1" class="small-text" id="oxtilofastcal_rate_limit_requests" name="oxtilofastcal_security[rate_limit_requests]" value="<?php echo esc_attr( $requests ); ?>" />
					<?php echo esc_html__( 'requests per', 'oxtilo-fast-cal' ); ?>
					<input type="number" min="10" max="3600" step="1" class="small-text" id="oxtilofastcal_rate_limit_window" name="oxtilofastcal_security[rate_limit_window]" value="<?php echo esc_attr( $window ); ?>" />
					<?php echo esc_html__( 'seconds', 'oxtilo-fast-cal' ); ?>
					<p class="description">
						<?php
						echo esc_html(
							sprintf(
								/* translators: 1: number of requests, 2: time window in seconds */
								__( 'Default: %1$d requests per %2$d seconds (1 minute). Applies per IP address per endpoint type.', 'oxtilo-fast-cal' ),
								30,
								60
							)
						);
						?>
					</p>
				</td>
			</tr>
		</table>

		<hr />

		<h2><?php echo esc_html__( 'IP Address Detection', 'oxtilo-fast-cal' ); ?></h2>

		<div class="notice notice-warning inline" style="margin: 10px 0;">
			<p>
				<strong>⚠️ <?php echo esc_html__( 'Important:', 'oxtilo-fast-cal' ); ?></strong>
				<?php echo esc_html__( 'If your site uses a CDN, WAF, or reverse proxy (e.g., Cloudflare, Sucuri, AWS CloudFront), the visitor\'s real IP may be in a different header. Incorrect configuration may cause rate limiting to apply to all users as if they shared the same IP.', 'oxtilo-fast-cal' ); ?>
			</p>
		</div>

		<table class="form-table" role="presentation">
			<tr>
				<th scope="row">
					<label for="oxtilofastcal_ip_source"><?php echo esc_html__( 'IP Source', 'oxtilo-fast-cal' ); ?></label>
				</th>
				<td>
					<select id="oxtilofastcal_ip_source" name="oxtilofastcal_security[ip_source]">
						<?php foreach ( $ip_sources as $key => $label ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $source, $key ); ?>>
								<?php echo esc_html( $label ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<p class="description">
						<?php echo esc_html__( 'Choose how to determine the visitor\'s real IP address. "Auto-detect" will detect common WAF/CDN providers automatically.', 'oxtilo-fast-cal' ); ?>
					</p>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<?php echo esc_html__( 'Current Detection', 'oxtilo-fast-cal' ); ?>
				</th>
				<td>
					<code><?php echo esc_html( $current_ip ?: __( '(unknown)', 'oxtilo-fast-cal' ) ); ?></code>
					<p class="description">
						<?php echo esc_html__( 'This is your IP address as detected with the current settings. If this shows the CDN/proxy IP instead of your real IP, adjust the IP Source setting above.', 'oxtilo-fast-cal' ); ?>
					</p>
				</td>
			</tr>
		</table>

		<hr />

		<h2><?php echo esc_html__( 'Protected Endpoints', 'oxtilo-fast-cal' ); ?></h2>
		<p class="description"><?php echo esc_html__( 'When rate limiting is enabled, the following endpoints are protected:', 'oxtilo-fast-cal' ); ?></p>

		<table class="widefat striped" style="max-width: 800px; margin: 15px 0;">
			<thead>
				<tr>
					<th><?php echo esc_html__( 'Endpoint', 'oxtilo-fast-cal' ); ?></th>
					<th><?php echo esc_html__( 'Description', 'oxtilo-fast-cal' ); ?></th>
					<th><?php echo esc_html__( 'Risk Without Protection', 'oxtilo-fast-cal' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><code>admin-post.php</code></td>
					<td><?php echo esc_html__( 'Booking form submission', 'oxtilo-fast-cal' ); ?></td>
					<td><?php echo esc_html__( 'Booking spam, calendar flooding', 'oxtilo-fast-cal' ); ?></td>
				</tr>
				<tr>
					<td><code>admin-ajax.php (get_slots)</code></td>
					<td><?php echo esc_html__( 'Available time slots retrieval', 'oxtilo-fast-cal' ); ?></td>
					<td><?php echo esc_html__( 'DoS, excessive server load', 'oxtilo-fast-cal' ); ?></td>
				</tr>
				<tr>
					<td><code>/wp-json/oxtilofastcal/v1/slots</code></td>
					<td><?php echo esc_html__( 'REST API - Get slots', 'oxtilo-fast-cal' ); ?></td>
					<td><?php echo esc_html__( 'DoS, excessive server load', 'oxtilo-fast-cal' ); ?></td>
				</tr>
				<tr>
					<td><code>/wp-json/oxtilofastcal/v1/create</code></td>
					<td><?php echo esc_html__( 'REST API - Create booking', 'oxtilo-fast-cal' ); ?></td>
					<td><?php echo esc_html__( 'Booking spam via API', 'oxtilo-fast-cal' ); ?></td>
				</tr>
				<tr>
					<td><code>/booking-manage/{hash}</code></td>
					<td><?php echo esc_html__( 'Booking management page', 'oxtilo-fast-cal' ); ?></td>
					<td><?php echo esc_html__( 'Hash enumeration, booking hijacking', 'oxtilo-fast-cal' ); ?></td>
				</tr>
			</tbody>
		</table>

		<?php submit_button( esc_html__( 'Save Security Settings', 'oxtilo-fast-cal' ) ); ?>
	</form>
</div>
