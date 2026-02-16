<?php
/**
 * Admin settings page template (Email Templates).
 *
 * @package Oxtilofastcal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$email_templates = get_option( 'oxtilofastcal_email_templates', array() );
$email_templates = is_array( $email_templates ) ? $email_templates : array();
?>
<div class="wrap oxtilofastcal-admin">
	<h1><?php echo esc_html__( 'Email Templates', 'oxtilo-fast-cal' ); ?></h1>
	<p class="description"><?php echo esc_html__( 'Customize email notifications sent to admin and clients. Use variables below to insert dynamic content.', 'oxtilo-fast-cal' ); ?></p>

	<div class="oxtilofastcal-email-variables" style="background: #f5f5f5; padding: 15px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 20px;">
		<strong><?php echo esc_html__( 'Available variables:', 'oxtilo-fast-cal' ); ?></strong>
		<div style="display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px;">
			<code class="oxtilofastcal-var-btn" data-var="{booking_id}" title="<?php echo esc_attr__( 'Booking ID number', 'oxtilo-fast-cal' ); ?>">{booking_id}</code>
			<code class="oxtilofastcal-var-btn" data-var="{service_name}" title="<?php echo esc_attr__( 'Name of the booked service', 'oxtilo-fast-cal' ); ?>">{service_name}</code>
			<code class="oxtilofastcal-var-btn" data-var="{client_name}" title="<?php echo esc_attr__( 'Client full name', 'oxtilo-fast-cal' ); ?>">{client_name}</code>
			<code class="oxtilofastcal-var-btn" data-var="{client_email}" title="<?php echo esc_attr__( 'Client email address', 'oxtilo-fast-cal' ); ?>">{client_email}</code>
			<code class="oxtilofastcal-var-btn" data-var="{booking_date}" title="<?php echo esc_attr__( 'Date of the booking', 'oxtilo-fast-cal' ); ?>">{booking_date}</code>
			<code class="oxtilofastcal-var-btn" data-var="{booking_time_start}" title="<?php echo esc_attr__( 'Start time', 'oxtilo-fast-cal' ); ?>">{booking_time_start}</code>
			<code class="oxtilofastcal-var-btn" data-var="{booking_time_end}" title="<?php echo esc_attr__( 'End time', 'oxtilo-fast-cal' ); ?>">{booking_time_end}</code>
			<code class="oxtilofastcal-var-btn" data-var="{meet_link}" title="<?php echo esc_attr__( 'Google Meet link (if set)', 'oxtilo-fast-cal' ); ?>">{meet_link}</code>
			<code class="oxtilofastcal-var-btn" data-var="{site_name}" title="<?php echo esc_attr__( 'Website name', 'oxtilo-fast-cal' ); ?>">{site_name}</code>
		</div>
		<p class="description" style="margin-top: 10px;"><?php echo esc_html__( 'Click on a variable to copy it, then paste into the editor.', 'oxtilo-fast-cal' ); ?></p>
	</div>

	<form method="post" action="options.php" id="oxtilofastcal-email-templates-form">
		<?php settings_fields( 'oxtilofastcal_email_templates_group' ); ?>

		<div class="oxtilofastcal-admin__two-col">
			<div class="oxtilofastcal-admin__col">
				<h3><?php echo esc_html__( 'Admin Notification (New Booking)', 'oxtilo-fast-cal' ); ?></h3>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row">
							<label for="oxtilofastcal_admin_subject"><?php echo esc_html__( 'Subject', 'oxtilo-fast-cal' ); ?></label>
						</th>
						<td>
							<input type="text" class="large-text" id="oxtilofastcal_admin_subject" name="oxtilofastcal_email_templates[admin_subject]" value="<?php echo esc_attr( $email_templates['admin_subject'] ?? '' ); ?>" placeholder="<?php echo esc_attr__( 'New booking confirmed (#{booking_id})', 'oxtilo-fast-cal' ); ?>" />
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="oxtilofastcal_admin_body"><?php echo esc_html__( 'Body (HTML)', 'oxtilo-fast-cal' ); ?></label>
						</th>
						<td>
							<textarea class="large-text code oxtilofastcal-email-editor" rows="12" id="oxtilofastcal_admin_body" name="oxtilofastcal_email_templates[admin_body]" placeholder="<?php echo esc_attr__( 'A new booking has been confirmed...', 'oxtilo-fast-cal' ); ?>"><?php echo esc_textarea( $email_templates['admin_body'] ?? '' ); ?></textarea>
							<p class="description"><?php echo esc_html__( 'Leave empty to use default template. HTML is supported.', 'oxtilo-fast-cal' ); ?></p>
						</td>
					</tr>
				</table>
			</div>

			<div class="oxtilofastcal-admin__col">
				<h3><?php echo esc_html__( 'Client Notification (New Booking)', 'oxtilo-fast-cal' ); ?></h3>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row">
							<label for="oxtilofastcal_client_subject"><?php echo esc_html__( 'Subject', 'oxtilo-fast-cal' ); ?></label>
						</th>
						<td>
							<input type="text" class="large-text" id="oxtilofastcal_client_subject" name="oxtilofastcal_email_templates[client_subject]" value="<?php echo esc_attr( $email_templates['client_subject'] ?? '' ); ?>" placeholder="<?php echo esc_attr__( 'Booking confirmed: {service_name}', 'oxtilo-fast-cal' ); ?>" />
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="oxtilofastcal_client_body"><?php echo esc_html__( 'Body (HTML)', 'oxtilo-fast-cal' ); ?></label>
						</th>
						<td>
							<textarea class="large-text code oxtilofastcal-email-editor" rows="12" id="oxtilofastcal_client_body" name="oxtilofastcal_email_templates[client_body]" placeholder="<?php echo esc_attr__( 'Hello {client_name}, your booking is confirmed...', 'oxtilo-fast-cal' ); ?>"><?php echo esc_textarea( $email_templates['client_body'] ?? '' ); ?></textarea>
							<p class="description"><?php echo esc_html__( 'Leave empty to use default template. HTML is supported.', 'oxtilo-fast-cal' ); ?></p>
						</td>
					</tr>
				</table>
			</div>
		</div>
		
		<hr>
		
		<div class="oxtilofastcal-admin__two-col">
			<div class="oxtilofastcal-admin__col">
				<h3><?php echo esc_html__( 'Client Notification (Booking Update)', 'oxtilo-fast-cal' ); ?></h3>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row">
							<label for="oxtilofastcal_update_subject"><?php echo esc_html__( 'Subject', 'oxtilo-fast-cal' ); ?></label>
						</th>
						<td>
							<input type="text" class="large-text" id="oxtilofastcal_update_subject" name="oxtilofastcal_email_templates[update_subject]" value="<?php echo esc_attr( $email_templates['update_subject'] ?? '' ); ?>" placeholder="<?php echo esc_attr__( 'Booking updated: {service_name}', 'oxtilo-fast-cal' ); ?>" />
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="oxtilofastcal_update_body"><?php echo esc_html__( 'Body (HTML)', 'oxtilo-fast-cal' ); ?></label>
						</th>
						<td>
							<textarea class="large-text code oxtilofastcal-email-editor" rows="12" id="oxtilofastcal_update_body" name="oxtilofastcal_email_templates[update_body]" placeholder="<?php echo esc_attr__( 'Hello {client_name}, your booking details have been updated...', 'oxtilo-fast-cal' ); ?>"><?php echo esc_textarea( $email_templates['update_body'] ?? '' ); ?></textarea>
							<p class="description"><?php echo esc_html__( 'Leave empty to use default template.', 'oxtilo-fast-cal' ); ?></p>
						</td>
					</tr>
				</table>
			</div>
			
			<div class="oxtilofastcal-admin__col">
				<h3><?php echo esc_html__( 'Client Notification (Booking Cancellation)', 'oxtilo-fast-cal' ); ?></h3>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row">
							<label for="oxtilofastcal_cancel_subject"><?php echo esc_html__( 'Subject', 'oxtilo-fast-cal' ); ?></label>
						</th>
						<td>
							<input type="text" class="large-text" id="oxtilofastcal_cancel_subject" name="oxtilofastcal_email_templates[cancel_subject]" value="<?php echo esc_attr( $email_templates['cancel_subject'] ?? '' ); ?>" placeholder="<?php echo esc_attr__( 'Booking cancelled: {service_name}', 'oxtilo-fast-cal' ); ?>" />
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="oxtilofastcal_cancel_body"><?php echo esc_html__( 'Body (HTML)', 'oxtilo-fast-cal' ); ?></label>
						</th>
						<td>
							<textarea class="large-text code oxtilofastcal-email-editor" rows="12" id="oxtilofastcal_cancel_body" name="oxtilofastcal_email_templates[cancel_body]" placeholder="<?php echo esc_attr__( 'Hello {client_name}, your booking has been cancelled...', 'oxtilo-fast-cal' ); ?>"><?php echo esc_textarea( $email_templates['cancel_body'] ?? '' ); ?></textarea>
							<p class="description"><?php echo esc_html__( 'Leave empty to use default template.', 'oxtilo-fast-cal' ); ?></p>
						</td>
					</tr>
				</table>
			</div>
		</div>

		<?php submit_button( esc_html__( 'Save email templates', 'oxtilo-fast-cal' ) ); ?>
	</form>
</div>
