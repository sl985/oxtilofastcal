<?php
/**
 * Admin settings page template (Diagnostics).
 *
 * @package Oxtilofastcal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap oxtilofastcal-admin">
	<h1><?php echo esc_html__( 'Diagnostics', 'oxtilo-fast-cal' ); ?></h1>
	<p class="description"><?php echo esc_html__( 'Check which events are being loaded from ICS sources and how they affect availability.', 'oxtilo-fast-cal' ); ?></p>
	
	<?php
	$wp_tz = wp_timezone();
	$wp_tz_name = $wp_tz->getName();
	$is_utc = in_array( $wp_tz_name, array( 'UTC', '+00:00', '' ), true );
	if ( $is_utc ) :
	?>
	<div class="notice notice-warning inline" style="margin: 10px 0;">
		<p><strong><?php echo esc_html__( 'Warning:', 'oxtilo-fast-cal' ); ?></strong> 
		<?php echo esc_html__( 'WordPress timezone is set to UTC. This may cause issues with ICS calendars from different timezones.', 'oxtilo-fast-cal' ); ?>
		<a href="<?php echo esc_url( admin_url( 'options-general.php' ) ); ?>"><?php echo esc_html__( 'Change timezone in Settings → General', 'oxtilo-fast-cal' ); ?></a>
		</p>
	</div>
	<?php endif; ?>

	<table class="form-table" role="presentation">
		<tr>
			<th scope="row"><label for="oxtilofastcal_diag_date"><?php echo esc_html__( 'Date to check', 'oxtilo-fast-cal' ); ?></label></th>
			<td>
				<input type="date" class="regular-text code" id="oxtilofastcal_diag_date" value="<?php echo esc_attr( wp_date( 'Y-m-d', strtotime( '+1 day' ) ) ); ?>" />
				<button type="button" class="button button-primary" id="oxtilofastcal_run_diagnostics"><?php echo esc_html__( 'Run Diagnostics', 'oxtilo-fast-cal' ); ?></button>
			</td>
		</tr>
	</table>

	<div id="oxtilofastcal_diag_results" style="display:none; margin-top: 20px;">
		<h3><?php echo esc_html__( 'Results', 'oxtilo-fast-cal' ); ?></h3>
		<div id="oxtilofastcal_diag_content" style="background: #f5f5f5; padding: 15px; border: 1px solid #ddd; border-radius: 4px; font-family: monospace; white-space: pre-wrap; max-height: 500px; overflow-y: auto;"></div>
	</div>
</div>
