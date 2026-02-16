<?php
/**
 * Admin settings page template (Main).
 *
 * @package Oxtilofastcal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$general  = get_option( 'oxtilofastcal_general', array() );
$general  = is_array( $general ) ? $general : array();
$services = (string) get_option( 'oxtilofastcal_services_json', '[]' );
$hours    = get_option( 'oxtilofastcal_working_hours', array() );
$hours    = is_array( $hours ) ? $hours : array();
$feeds    = get_option( 'oxtilofastcal_ics_feeds', array() );
$feeds    = is_array( $feeds ) ? $feeds : array();

$token    = isset( $general['calendar_feed_token'] ) ? (string) $general['calendar_feed_token'] : '';
$feed_url = ( $token && preg_match( '/^[a-zA-Z0-9]+$/', $token ) ) ? home_url( '/oxtilofastcal-feed/' . $token . '/' ) : '';

$days = array(
	'mon' => __( 'Monday', 'oxtilo-fast-cal' ),
	'tue' => __( 'Tuesday', 'oxtilo-fast-cal' ),
	'wed' => __( 'Wednesday', 'oxtilo-fast-cal' ),
	'thu' => __( 'Thursday', 'oxtilo-fast-cal' ),
	'fri' => __( 'Friday', 'oxtilo-fast-cal' ),
	'sat' => __( 'Saturday', 'oxtilo-fast-cal' ),
	'sun' => __( 'Sunday', 'oxtilo-fast-cal' ),
);

$hours = wp_parse_args( $hours, array(
	'mon' => array( 'start' => '09:00', 'end' => '17:00', 'day_off' => 0 ),
	'tue' => array( 'start' => '09:00', 'end' => '17:00', 'day_off' => 0 ),
	'wed' => array( 'start' => '09:00', 'end' => '17:00', 'day_off' => 0 ),
	'thu' => array( 'start' => '09:00', 'end' => '17:00', 'day_off' => 0 ),
	'fri' => array( 'start' => '09:00', 'end' => '17:00', 'day_off' => 0 ),
	'sat' => array( 'start' => '', 'end' => '', 'day_off' => 1 ),
	'sun' => array( 'start' => '', 'end' => '', 'day_off' => 1 ),
) );
?>
<div class="wrap oxtilofastcal-admin">
	<h1><?php echo esc_html__( 'Oxtilo Fast Cal Settings', 'oxtilo-fast-cal' ); ?></h1>

	<div class="oxtilofastcal-admin__two-col">
		<div class="oxtilofastcal-admin__col">
			<h2><?php echo esc_html__( 'General', 'oxtilo-fast-cal' ); ?></h2>
			<form method="post" action="options.php">
				<?php settings_fields( 'oxtilofastcal_general_group' ); ?>

				<table class="form-table" role="presentation">
					<tr>
						<th scope="row">
							<label for="oxtilofastcal_admin_notification_email"><?php echo esc_html__( 'Admin notification email', 'oxtilo-fast-cal' ); ?></label>
						</th>
						<td>
							<input type="email" class="regular-text" id="oxtilofastcal_admin_notification_email" name="oxtilofastcal_general[admin_notification_email]" value="<?php echo esc_attr( $general['admin_notification_email'] ?? '' ); ?>" />
							<p class="description"><?php echo esc_html__( 'Where new booking notifications should be sent.', 'oxtilo-fast-cal' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="oxtilofastcal_admin_name"><?php echo esc_html__( 'Administrator Name', 'oxtilo-fast-cal' ); ?></label>
						</th>
						<td>
							<input type="text" class="regular-text" id="oxtilofastcal_admin_name" name="oxtilofastcal_general[admin_name]" value="<?php echo esc_attr( $general['admin_name'] ?? '' ); ?>" />
							<p class="description"><?php echo esc_html__( 'Used in ICS titles and email notifications.', 'oxtilo-fast-cal' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="oxtilofastcal_google_meet_link"><?php echo esc_html__( 'Google Meet link (optional)', 'oxtilo-fast-cal' ); ?></label>
						</th>
						<td>
							<input type="url" class="regular-text" id="oxtilofastcal_google_meet_link" name="oxtilofastcal_general[google_meet_link]" value="<?php echo esc_attr( $general['google_meet_link'] ?? '' ); ?>" />
							<p class="description"><?php echo esc_html__( 'Used for online meetings in email notifications.', 'oxtilo-fast-cal' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="oxtilofastcal_min_lead_time"><?php echo esc_html__( 'Minimum lead time (minutes)', 'oxtilo-fast-cal' ); ?></label>
						</th>
						<td>
							<input type="number" min="0" class="small-text" id="oxtilofastcal_min_lead_time" name="oxtilofastcal_general[min_lead_time]" value="<?php echo esc_attr( $general['min_lead_time'] ?? 60 ); ?>" />
							<p class="description"><?php echo esc_html__( 'How many minutes in advance must a booking be made.', 'oxtilo-fast-cal' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="oxtilofastcal_max_days_future"><?php echo esc_html__( 'Max days in future', 'oxtilo-fast-cal' ); ?></label>
						</th>
						<td>
							<input type="number" min="0" class="small-text" id="oxtilofastcal_max_days_future" name="oxtilofastcal_general[max_days_future]" value="<?php echo esc_attr( $general['max_days_future'] ?? 30 ); ?>" />
							<p class="description"><?php echo esc_html__( 'How many days in advance can a booking be made.', 'oxtilo-fast-cal' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="oxtilofastcal_time_format_display"><?php echo esc_html__( 'Time format (Frontend)', 'oxtilo-fast-cal' ); ?></label>
						</th>
						<td>
							<select id="oxtilofastcal_time_format_display" name="oxtilofastcal_general[time_format_display]">
								<option value="24h" <?php selected( $general['time_format_display'] ?? '24h', '24h' ); ?>>24h (14:00)</option>
								<option value="12h" <?php selected( $general['time_format_display'] ?? '24h', '12h' ); ?>>12h (02:00 PM)</option>
							</select>
							<p class="description"><?php echo esc_html__( 'Controls how times are displayed on the booking form.', 'oxtilo-fast-cal' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="oxtilofastcal_time_slot_interval"><?php echo esc_html__( 'Time slot interval', 'oxtilo-fast-cal' ); ?></label>
						</th>
						<td>
							<select id="oxtilofastcal_time_slot_interval" name="oxtilofastcal_general[time_slot_interval]">
								<option value="15" <?php selected( $general['time_slot_interval'] ?? 30, 15 ); ?>>15 <?php echo esc_html__( 'minutes', 'oxtilo-fast-cal' ); ?></option>
								<option value="30" <?php selected( $general['time_slot_interval'] ?? 30, 30 ); ?>>30 <?php echo esc_html__( 'minutes', 'oxtilo-fast-cal' ); ?></option>
								<option value="60" <?php selected( $general['time_slot_interval'] ?? 30, 60 ); ?>>60 <?php echo esc_html__( 'minutes', 'oxtilo-fast-cal' ); ?></option>
							</select>
							<p class="description"><?php echo esc_html__( 'Meeting start times will be aligned to this interval (e.g., 15 min: 16:00, 16:15, 16:30, 16:45).', 'oxtilo-fast-cal' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="oxtilofastcal_calendar_feed_token"><?php echo esc_html__( 'Private calendar feed token', 'oxtilo-fast-cal' ); ?></label>
						</th>
						<td>
							<input type="text" class="regular-text code" id="oxtilofastcal_calendar_feed_token" value="<?php echo esc_attr( $token ); ?>" readonly />
							<p>
								<button type="button" class="button" id="oxtilofastcal_generate_token_btn"><?php echo esc_html__( 'Generate new token', 'oxtilo-fast-cal' ); ?></button>
								<span id="oxtilofastcal_token_status" class="oxtilofastcal-admin__status" aria-live="polite"></span>
							</p>
							<p>
								<strong><?php echo esc_html__( 'Feed URL:', 'oxtilo-fast-cal' ); ?></strong><br />
								<input type="text" class="large-text code" id="oxtilofastcal_calendar_feed_url" value="<?php echo esc_attr( $feed_url ); ?>" readonly />
							</p>
							<p class="description"><?php echo esc_html__( 'You can add this URL to your calendar app as a read-only feed.', 'oxtilo-fast-cal' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="oxtilofastcal_include_manage_link"><?php echo esc_html__( 'Include "Manage Booking" link', 'oxtilo-fast-cal' ); ?></label>
						</th>
						<td>
							<label>
								<input type="checkbox" id="oxtilofastcal_include_manage_link" name="oxtilofastcal_general[include_manage_link]" value="1" <?php checked( ! empty( $general['include_manage_link'] ) ); ?> />
								<?php echo esc_html__( 'Add a link to edit/manage the booking in the calendar event description.', 'oxtilo-fast-cal' ); ?>
							</label>
							
							<?php if ( ! empty( $general['include_manage_link'] ) ) : ?>
								<div style="background: #fff3cd; border: 1px solid #ffc107; padding: 10px; border-radius: 4px; margin-top: 10px; display: inline-block;">
									<strong style="color: #856404;">⚠️ <?php echo esc_html__( 'Security Warning:', 'oxtilo-fast-cal' ); ?></strong>
									<span style="color: #856404;">
										<?php echo esc_html__( 'Enabling this option adds a direct link to manage the booking (with a secret token) to the calendar event. If your calendar feed URL is leaked, anyone with access to it can edit or cancel your bookings. Protect your feed URL carefully.', 'oxtilo-fast-cal' ); ?>
									</span>
								</div>
							<?php endif; ?>
						</td>
					</tr>
				</table>

				<?php submit_button( esc_html__( 'Save changes', 'oxtilo-fast-cal' ) ); ?>
			</form>
		</div>

		<div class="oxtilofastcal-admin__col">
			<h2><?php echo esc_html__( 'Shortcode', 'oxtilo-fast-cal' ); ?></h2>
			<p><?php echo esc_html__( 'Use this shortcode to display the booking form:', 'oxtilo-fast-cal' ); ?></p>
			<p><code>[oxtilofastcal_form]</code></p>

			<h2><?php echo esc_html__( 'Services', 'oxtilo-fast-cal' ); ?></h2>
			<form method="post" action="options.php">
				<?php settings_fields( 'oxtilofastcal_services_group' ); ?>
				<p class="description"><?php echo esc_html__( 'Edit services as JSON. Fields: name (string), duration (minutes), type (online|in_person).', 'oxtilo-fast-cal' ); ?></p>
				<textarea class="large-text code" rows="10" name="oxtilofastcal_services_json"><?php echo esc_textarea( $services ); ?></textarea>
				<?php submit_button( esc_html__( 'Save services', 'oxtilo-fast-cal' ) ); ?>
			</form>
		</div>
	</div>

	<hr />

	<h2><?php echo esc_html__( 'Working hours', 'oxtilo-fast-cal' ); ?></h2>
	<form method="post" action="options.php">
		<?php settings_fields( 'oxtilofastcal_working_hours_group' ); ?>

		<table class="widefat striped oxtilofastcal-hours">
			<thead>
				<tr>
					<th><?php echo esc_html__( 'Day', 'oxtilo-fast-cal' ); ?></th>
					<th><?php echo esc_html__( 'Start (HH:MM)', 'oxtilo-fast-cal' ); ?></th>
					<th><?php echo esc_html__( 'End (HH:MM)', 'oxtilo-fast-cal' ); ?></th>
					<th><?php echo esc_html__( 'Day off', 'oxtilo-fast-cal' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $days as $key => $label ) : ?>
					<?php
					$row = isset( $hours[ $key ] ) && is_array( $hours[ $key ] ) ? $hours[ $key ] : array();
					$row = wp_parse_args( $row, array( 'start' => '', 'end' => '', 'day_off' => 0 ) );
					?>
					<tr>
						<td><strong><?php echo esc_html( $label ); ?></strong></td>
						<td><input type="text" class="regular-text code" name="oxtilofastcal_working_hours[<?php echo esc_attr( $key ); ?>][start]" value="<?php echo esc_attr( $row['start'] ); ?>" /></td>
						<td><input type="text" class="regular-text code" name="oxtilofastcal_working_hours[<?php echo esc_attr( $key ); ?>][end]" value="<?php echo esc_attr( $row['end'] ); ?>" /></td>
						<td><label><input type="checkbox" name="oxtilofastcal_working_hours[<?php echo esc_attr( $key ); ?>][day_off]" value="1" <?php checked( (int) $row['day_off'], 1 ); ?> /> <?php echo esc_html__( 'Closed', 'oxtilo-fast-cal' ); ?></label></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<?php submit_button( esc_html__( 'Save working hours', 'oxtilo-fast-cal' ) ); ?>
	</form>

	<hr />

	<h2><?php echo esc_html__( 'External ICS feeds (optional)', 'oxtilo-fast-cal' ); ?></h2>
	<p class="description"><?php echo esc_html__( 'Events from these calendars will block availability (read-only).', 'oxtilo-fast-cal' ); ?></p>

	<form method="post" action="options.php">
		<?php settings_fields( 'oxtilofastcal_ics_group' ); ?>

		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="oxtilofastcal_ics_update_frequency"><?php echo esc_html__( 'Update frequency (minutes)', 'oxtilo-fast-cal' ); ?></label></th>
				<td>
					<input type="number" min="5" class="small-text code" id="oxtilofastcal_ics_update_frequency" name="oxtilofastcal_ics_feeds[update_frequency]" value="<?php echo esc_attr( $feeds['update_frequency'] ?? 60 ); ?>" />
					<p class="description"><?php echo esc_html__( 'How often to fetch external calendars (min. 5 minutes).', 'oxtilo-fast-cal' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="oxtilofastcal_ics_icloud"><?php echo esc_html__( 'ICS Calendar 1 URL', 'oxtilo-fast-cal' ); ?></label></th>
				<td>
					<div style="display:flex; align-items:center; gap: 10px;">
						<input type="url" class="large-text code" id="oxtilofastcal_ics_icloud" name="oxtilofastcal_ics_feeds[icloud]" value="<?php echo esc_attr( $feeds['icloud'] ?? '' ); ?>" />
						<button type="button" class="button oxtilofastcal-test-feed" data-input="#oxtilofastcal_ics_icloud"><?php echo esc_html__( 'Test & Check', 'oxtilo-fast-cal' ); ?></button>
					</div>
					<span class="oxtilofastcal-feed-status" style="margin-top:5px; display:block; font-style:italic;"></span>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="oxtilofastcal_ics_proton"><?php echo esc_html__( 'ICS Calendar 2 URL', 'oxtilo-fast-cal' ); ?></label></th>
				<td>
					<div style="display:flex; align-items:center; gap: 10px;">
						<input type="url" class="large-text code" id="oxtilofastcal_ics_proton" name="oxtilofastcal_ics_feeds[proton]" value="<?php echo esc_attr( $feeds['proton'] ?? '' ); ?>" />
						<button type="button" class="button oxtilofastcal-test-feed" data-input="#oxtilofastcal_ics_proton"><?php echo esc_html__( 'Test & Check', 'oxtilo-fast-cal' ); ?></button>
					</div>
					<span class="oxtilofastcal-feed-status" style="margin-top:5px; display:block; font-style:italic;"></span>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="oxtilofastcal_ics_holidays"><?php echo esc_html__( 'Holidays ICS URL', 'oxtilo-fast-cal' ); ?></label></th>
				<td>
					<div style="display:flex; align-items:center; gap: 10px;">
						<input type="url" class="large-text code" id="oxtilofastcal_ics_holidays" name="oxtilofastcal_ics_feeds[holidays]" value="<?php echo esc_attr( $feeds['holidays'] ?? '' ); ?>" />
						<button type="button" class="button oxtilofastcal-test-feed" data-input="#oxtilofastcal_ics_holidays"><?php echo esc_html__( 'Test & Check', 'oxtilo-fast-cal' ); ?></button>
					</div>
					<p class="description">
						<a href="https://www.thunderbird.net/pl/calendar/holidays/" target="_blank">https://www.thunderbird.net/pl/calendar/holidays/</a>
					</p>
					<span class="oxtilofastcal-feed-status" style="margin-top:5px; display:block; font-style:italic;"></span>
				</td>
			</tr>
		</table>

		<?php submit_button( esc_html__( 'Save ICS feeds', 'oxtilo-fast-cal' ) ); ?>
	</form>

	<hr />

	<h2><?php echo esc_html__( 'REST API', 'oxtilo-fast-cal' ); ?></h2>
	<p class="description"><?php echo esc_html__( 'External applications (e.g., Apple Shortcuts, Zapier) can integrate with Oxtilo Fast Cal via REST API.', 'oxtilo-fast-cal' ); ?></p>

	<?php
	$api_base_url = rest_url( 'oxtilofastcal/v1/' );
	$api_token    = isset( $general['api_token'] ) ? (string) $general['api_token'] : '';
	$example_date = wp_date( 'Y-m-d', strtotime( '+3 days' ) );
	?>

	<div class="oxtilofastcal-api-docs" style="background: #f9f9f9; padding: 20px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 20px;">
		<h3 style="margin-top: 0;"><?php echo esc_html__( 'Authentication', 'oxtilo-fast-cal' ); ?></h3>
		
		<div style="background: #fff3cd; border: 1px solid #ffc107; padding: 15px; border-radius: 4px; margin-bottom: 15px;">
			<strong style="color: #856404;">⚠️ <?php echo esc_html__( 'Security Note:', 'oxtilo-fast-cal' ); ?></strong>
			<p style="margin: 5px 0 0; color: #856404;">
				<?php echo esc_html__( 'The API token is separate from the Calendar Feed token. The Calendar Feed token (above) is read-only for ICS feeds. The API token below grants write access and should be kept secret.', 'oxtilo-fast-cal' ); ?>
			</p>
		</div>

		<table class="form-table" role="presentation">
			<tr>
				<th scope="row">
					<label for="oxtilofastcal_api_token"><?php echo esc_html__( 'API Token (write access)', 'oxtilo-fast-cal' ); ?></label>
				</th>
				<td>
					<input type="text" class="large-text code" id="oxtilofastcal_api_token" value="<?php echo esc_attr( $api_token ); ?>" readonly />
					<p>
						<button type="button" class="button" id="oxtilofastcal_generate_api_token_btn"><?php echo esc_html__( 'Generate new API token', 'oxtilo-fast-cal' ); ?></button>
						<span id="oxtilofastcal_api_token_status" class="oxtilofastcal-admin__status" aria-live="polite"></span>
					</p>
					<p class="description"><?php echo esc_html__( 'Used for REST API authentication. Do not share this token publicly.', 'oxtilo-fast-cal' ); ?></p>
				</td>
			</tr>
		</table>

		<p><?php echo esc_html__( 'All API requests require the API token in the HTTP header:', 'oxtilo-fast-cal' ); ?></p>
		<pre style="background: #1e1e1e; color: #d4d4d4; padding: 15px; border-radius: 4px; overflow-x: auto;">X-Oxtilofastcal-Token: <?php echo esc_html( $api_token ); ?></pre>

		<hr style="margin: 20px 0;" />

		<h3><?php echo esc_html__( 'GET /slots - Available Time Slots', 'oxtilo-fast-cal' ); ?></h3>
		<p><?php echo esc_html__( 'Returns available booking slots for a given date.', 'oxtilo-fast-cal' ); ?></p>
		
		<table class="widefat striped" style="margin: 10px 0;">
			<thead>
				<tr>
					<th style="width: 120px;"><?php echo esc_html__( 'Parameter', 'oxtilo-fast-cal' ); ?></th>
					<th style="width: 80px;"><?php echo esc_html__( 'Type', 'oxtilo-fast-cal' ); ?></th>
					<th style="width: 80px;"><?php echo esc_html__( 'Required', 'oxtilo-fast-cal' ); ?></th>
					<th><?php echo esc_html__( 'Description', 'oxtilo-fast-cal' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><code>date</code></td>
					<td><?php echo esc_html__( 'string', 'oxtilo-fast-cal' ); ?></td>
					<td><?php echo esc_html__( 'Yes', 'oxtilo-fast-cal' ); ?></td>
					<td><?php echo esc_html__( 'Date in YYYY-MM-DD format', 'oxtilo-fast-cal' ); ?></td>
				</tr>
				<tr>
					<td><code>service_id</code></td>
					<td><?php echo esc_html__( 'integer', 'oxtilo-fast-cal' ); ?></td>
					<td><?php echo esc_html__( 'No', 'oxtilo-fast-cal' ); ?></td>
					<td><?php echo esc_html__( 'Service index (default: 0)', 'oxtilo-fast-cal' ); ?></td>
				</tr>
				<tr>
					<td><code>duration</code></td>
					<td><?php echo esc_html__( 'integer', 'oxtilo-fast-cal' ); ?></td>
					<td><?php echo esc_html__( 'No', 'oxtilo-fast-cal' ); ?></td>
					<td><?php echo esc_html__( 'Custom duration in minutes (overrides service duration)', 'oxtilo-fast-cal' ); ?></td>
				</tr>
			</tbody>
		</table>

		<p><strong><?php echo esc_html__( 'Example request:', 'oxtilo-fast-cal' ); ?></strong></p>
		<pre style="background: #1e1e1e; color: #d4d4d4; padding: 15px; border-radius: 4px; overflow-x: auto;">curl -X GET "<?php echo esc_url( $api_base_url ); ?>slots?date=<?php echo esc_html( $example_date ); ?>&duration=60" \
  -H "X-Oxtilofastcal-Token: <?php echo esc_html( $api_token ); ?>"</pre>

		<p><strong><?php echo esc_html__( 'Example response:', 'oxtilo-fast-cal' ); ?></strong></p>
		<pre style="background: #1e1e1e; color: #d4d4d4; padding: 15px; border-radius: 4px; overflow-x: auto;">{
  "success": true,
  "date": "<?php echo esc_html( $example_date ); ?>",
  "slots": [
    { "start": "<?php echo esc_html( $example_date ); ?> 09:00:00", "end": "<?php echo esc_html( $example_date ); ?> 10:00:00", "label": "09:00 - 10:00" },
    { "start": "<?php echo esc_html( $example_date ); ?> 10:00:00", "end": "<?php echo esc_html( $example_date ); ?> 11:00:00", "label": "10:00 - 11:00" }
  ]
}</pre>

		<hr style="margin: 20px 0;" />

		<h3><?php echo esc_html__( 'POST /create - Create Booking', 'oxtilo-fast-cal' ); ?></h3>
		<p><?php echo esc_html__( 'Creates a new booking. Returns error 409 if the time slot conflicts with an existing booking.', 'oxtilo-fast-cal' ); ?></p>

		<table class="widefat striped" style="margin: 10px 0;">
			<thead>
				<tr>
					<th style="width: 120px;"><?php echo esc_html__( 'Parameter', 'oxtilo-fast-cal' ); ?></th>
					<th style="width: 80px;"><?php echo esc_html__( 'Type', 'oxtilo-fast-cal' ); ?></th>
					<th style="width: 80px;"><?php echo esc_html__( 'Required', 'oxtilo-fast-cal' ); ?></th>
					<th><?php echo esc_html__( 'Description', 'oxtilo-fast-cal' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><code>client_name</code></td>
					<td><?php echo esc_html__( 'string', 'oxtilo-fast-cal' ); ?></td>
					<td><?php echo esc_html__( 'Yes', 'oxtilo-fast-cal' ); ?></td>
					<td><?php echo esc_html__( 'Client full name', 'oxtilo-fast-cal' ); ?></td>
				</tr>
				<tr>
					<td><code>client_email</code></td>
					<td><?php echo esc_html__( 'string', 'oxtilo-fast-cal' ); ?></td>
					<td><?php echo esc_html__( 'Yes', 'oxtilo-fast-cal' ); ?></td>
					<td><?php echo esc_html__( 'Client email address', 'oxtilo-fast-cal' ); ?></td>
				</tr>
				<tr>
					<td><code>date</code></td>
					<td><?php echo esc_html__( 'string', 'oxtilo-fast-cal' ); ?></td>
					<td><?php echo esc_html__( 'Yes', 'oxtilo-fast-cal' ); ?></td>
					<td><?php echo esc_html__( 'Date in YYYY-MM-DD format', 'oxtilo-fast-cal' ); ?></td>
				</tr>
				<tr>
					<td><code>time</code></td>
					<td><?php echo esc_html__( 'string', 'oxtilo-fast-cal' ); ?></td>
					<td><?php echo esc_html__( 'Yes', 'oxtilo-fast-cal' ); ?></td>
					<td><?php echo esc_html__( 'Time in HH:MM format (24h)', 'oxtilo-fast-cal' ); ?></td>
				</tr>
				<tr>
					<td><code>duration</code></td>
					<td><?php echo esc_html__( 'integer', 'oxtilo-fast-cal' ); ?></td>
					<td><?php echo esc_html__( 'No', 'oxtilo-fast-cal' ); ?></td>
					<td><?php echo esc_html__( 'Duration in minutes (default: 60)', 'oxtilo-fast-cal' ); ?></td>
				</tr>
				<tr>
					<td><code>service_name</code></td>
					<td><?php echo esc_html__( 'string', 'oxtilo-fast-cal' ); ?></td>
					<td><?php echo esc_html__( 'No', 'oxtilo-fast-cal' ); ?></td>
					<td><?php echo esc_html__( 'Service name (default: "Rezerwacja API")', 'oxtilo-fast-cal' ); ?></td>
				</tr>
				<tr>
					<td><code>client_message</code></td>
					<td><?php echo esc_html__( 'string', 'oxtilo-fast-cal' ); ?></td>
					<td><?php echo esc_html__( 'No', 'oxtilo-fast-cal' ); ?></td>
					<td><?php echo esc_html__( 'Optional message from client', 'oxtilo-fast-cal' ); ?></td>
				</tr>
			</tbody>
		</table>

		<p><strong><?php echo esc_html__( 'Example request:', 'oxtilo-fast-cal' ); ?></strong></p>
		<pre style="background: #1e1e1e; color: #d4d4d4; padding: 15px; border-radius: 4px; overflow-x: auto;">curl -X POST "<?php echo esc_url( $api_base_url ); ?>create" \
  -H "X-Oxtilofastcal-Token: <?php echo esc_html( $api_token ); ?>" \
  -H "Content-Type: application/json" \
  -d '{
    "client_name": "Jan Kowalski",
    "client_email": "jan@example.com",
    "date": "<?php echo esc_html( $example_date ); ?>",
    "time": "10:00",
    "duration": 60,
    "service_name": "Konsultacja"
  }'</pre>

		<p><strong><?php echo esc_html__( 'Example response (success):', 'oxtilo-fast-cal' ); ?></strong></p>
		<pre style="background: #1e1e1e; color: #d4d4d4; padding: 15px; border-radius: 4px; overflow-x: auto;">{
  "success": true,
  "booking_id": 15,
  "message": "Rezerwacja utworzona pomyślnie."
}</pre>

		<p><strong><?php echo esc_html__( 'Example response (conflict):', 'oxtilo-fast-cal' ); ?></strong></p>
		<pre style="background: #1e1e1e; color: #d4d4d4; padding: 15px; border-radius: 4px; overflow-x: auto;">{
  "code": "conflict",
  "message": "Termin jest już zajęty.",
  "data": { "status": 409 }
}</pre>
	</div>
</div>
