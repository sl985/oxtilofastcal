<?php
/**
 * Admin bookings page template.
 *
 * @package Oxtilofastcal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$action     = isset( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : 'list';
$booking_id = isset( $_GET['booking_id'] ) ? absint( wp_unslash( $_GET['booking_id'] ) ) : 0;
$base_url   = admin_url( 'admin.php?page=oxtilofastcal-bookings' );

// General settings for feed URL.
$general  = get_option( 'oxtilofastcal_general', array() );
$token    = isset( $general['calendar_feed_token'] ) ? (string) $general['calendar_feed_token'] : '';
$feed_url = ( $token && preg_match( '/^[a-zA-Z0-9]+$/', $token ) ) ? home_url( '/oxtilofastcal-feed/' . $token . '/' ) : '';

// Get services for forms.
$services_json = get_option( 'oxtilofastcal_services_json', '[]' );
$services      = json_decode( $services_json, true );
if ( ! is_array( $services ) ) {
	$services = array();
}

?>
<div class="wrap oxtilofastcal-admin">
	<h1 class="wp-heading-inline"><?php echo esc_html__( 'Bookings', 'oxtilofastcal' ); ?></h1>
	
	<?php if ( 'list' === $action ) : ?>
		<a href="<?php echo esc_url( add_query_arg( 'action', 'add', $base_url ) ); ?>" class="page-title-action"><?php echo esc_html__( 'Add New', 'oxtilofastcal' ); ?></a>
	<?php endif; ?>
	
	<?php if ( ! empty( $feed_url ) ) : ?>
		<div style="margin: 10px 0;">
			<strong><?php echo esc_html__( 'Channel URL (ICS Feed):', 'oxtilofastcal' ); ?></strong> 
			<a href="<?php echo esc_url( $feed_url ); ?>" target="_blank" class="code"><?php echo esc_html( $feed_url ); ?></a>
		</div>
	<?php endif; ?>

	<hr class="wp-header-end">

	<?php
	$msg = isset( $_GET['msg'] ) ? sanitize_text_field( wp_unslash( $_GET['msg'] ) ) : '';
	if ( $msg ) :
		?>
		<div class="notice notice-success is-dismissible">
			<p>
				<?php
				if ( 'updated' === $msg ) {
					echo esc_html__( 'Booking updated successfully. Email notification sent.', 'oxtilofastcal' );
				} elseif ( 'deleted' === $msg ) {
					echo esc_html__( 'Booking deleted successfully. Email notification sent.', 'oxtilofastcal' );
				} elseif ( 'created' === $msg ) {
					echo esc_html__( 'Booking created successfully. Email notification sent.', 'oxtilofastcal' );
				}
				?>
			</p>
		</div>
	<?php endif; ?>

	<?php if ( isset( $_GET['error'] ) ) : ?>
		<div class="notice notice-error is-dismissible">
			<p>
				<?php
				$error = sanitize_key( wp_unslash( $_GET['error'] ) );
				switch ( $error ) {
					case 'missing_data':
						echo esc_html__( 'Please fill in all required fields.', 'oxtilofastcal' );
						break;
					case 'invalid_email':
						echo esc_html__( 'Please enter a valid email address.', 'oxtilofastcal' );
						break;
					case 'invalid_dates':
						echo esc_html__( 'Invalid date/time values. End time must be after start time.', 'oxtilofastcal' );
						break;
					case 'db_error':
						echo esc_html__( 'Database error occurred. Please try again.', 'oxtilofastcal' );
						break;
					default:
						echo esc_html__( 'An error occurred. Please try again.', 'oxtilofastcal' );
						break;
				}
				?>
			</p>
		</div>
	<?php endif; ?>

	<?php if ( 'add' === $action ) : ?>
		<!-- ADD NEW BOOKING FORM -->
		<div class="card" style="max-width: 800px; padding: 20px; margin-top: 20px;">
			<h2><?php echo esc_html__( 'Add New Booking', 'oxtilofastcal' ); ?></h2>
			<p class="description" style="margin-bottom: 15px;">
				<?php echo esc_html__( 'As an administrator, you can create bookings at any time, including outside of regular working hours.', 'oxtilofastcal' ); ?>
			</p>
			
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="oxtilofastcal-admin-add-booking-form">
				<?php wp_nonce_field( 'oxtilofastcal_create_booking', 'oxtilofastcal_nonce' ); ?>
				<input type="hidden" name="action" value="oxtilofastcal_create_booking">

				<table class="form-table" role="presentation">
					<tr>
						<th><label for="client_name"><?php echo esc_html__( 'Client Name', 'oxtilofastcal' ); ?> <span class="required">*</span></label></th>
						<td><input type="text" name="client_name" id="client_name" class="regular-text" required></td>
					</tr>
					<tr>
						<th><label for="client_email"><?php echo esc_html__( 'Client Email', 'oxtilofastcal' ); ?> <span class="required">*</span></label></th>
						<td><input type="email" name="client_email" id="client_email" class="regular-text" required></td>
					</tr>
				<tr>
					<th><label for="service_name"><?php echo esc_html__( 'Service Name', 'oxtilofastcal' ); ?> <span class="required">*</span></label></th>
					<td>
						<input type="text" name="service_name" id="service_name" class="regular-text" required placeholder="<?php echo esc_attr__( 'Enter custom service name', 'oxtilofastcal' ); ?>">
						<p class="description"><?php echo esc_html__( 'Enter any service name. This will be the event title.', 'oxtilofastcal' ); ?></p>
					</td>
				</tr>
				<tr>
					<th><label for="service_template"><?php echo esc_html__( 'Service Template', 'oxtilofastcal' ); ?></label></th>
					<td>
						<select name="service_template" id="service_template">
							<option value=""><?php echo esc_html__( '— Select a template (optional) —', 'oxtilofastcal' ); ?></option>
							<?php foreach ( $services as $idx => $s ) : ?>
								<option value="<?php echo esc_attr( $idx ); ?>" data-name="<?php echo esc_attr( $s['name'] ?? '' ); ?>" data-duration="<?php echo esc_attr( $s['duration'] ?? 30 ); ?>" data-type="<?php echo esc_attr( $s['type'] ?? 'online' ); ?>">
									<?php echo esc_html( $s['name'] ?? 'Unknown' ); ?> (<?php echo esc_html( $s['duration'] ?? 30 ); ?> min)
								</option>
							<?php endforeach; ?>
						</select>
						<p class="description"><?php echo esc_html__( 'Optionally select a predefined service to auto-fill name and duration.', 'oxtilofastcal' ); ?></p>
					</td>
				</tr>
					<tr>
						<th><label for="booking_date"><?php echo esc_html__( 'Date', 'oxtilofastcal' ); ?> <span class="required">*</span></label></th>
						<td>
							<input type="date" name="booking_date" id="booking_date" value="<?php echo esc_attr( wp_date( 'Y-m-d' ) ); ?>" required>
							<button type="button" id="oxtilofastcal-check-availability" class="button" style="margin-left: 10px;">
								<?php echo esc_html__( 'Check Available Slots', 'oxtilofastcal' ); ?>
							</button>
						</td>
					</tr>
					<tr id="oxtilofastcal-available-slots-row" style="display: none;">
						<th><?php echo esc_html__( 'Available Slots', 'oxtilofastcal' ); ?></th>
						<td>
							<div id="oxtilofastcal-available-slots-container" style="max-height: 200px; overflow-y: auto; padding: 10px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px;">
								<p class="description"><?php echo esc_html__( 'Click "Check Available Slots" to see available times.', 'oxtilofastcal' ); ?></p>
							</div>
							<p class="description" style="margin-top: 5px;">
								<?php echo esc_html__( 'These are suggestions based on availability. You can still enter any custom time below.', 'oxtilofastcal' ); ?>
							</p>
						</td>
					</tr>
					<tr>
						<th><label for="start_time"><?php echo esc_html__( 'Start Time', 'oxtilofastcal' ); ?> <span class="required">*</span></label></th>
						<td>
							<input type="time" name="start_time" id="start_time" step="60" required>
							<p class="description"><?php echo esc_html__( 'You can enter any time, e.g., 17:15.', 'oxtilofastcal' ); ?></p>
						</td>
					</tr>
					<tr>
						<th><label for="end_time"><?php echo esc_html__( 'End Time', 'oxtilofastcal' ); ?> <span class="required">*</span></label></th>
						<td>
							<input type="time" name="end_time" id="end_time" step="60" required>
							<button type="button" id="oxtilofastcal-auto-end-time" class="button" style="margin-left: 10px;">
								<?php echo esc_html__( 'Auto-calculate from service duration', 'oxtilofastcal' ); ?>
							</button>
						</td>
					</tr>
					<tr>
						<th><label for="client_message"><?php echo esc_html__( 'Client Message', 'oxtilofastcal' ); ?></label></th>
						<td>
							<textarea name="client_message" id="client_message" rows="3" class="large-text"></textarea>
							<p class="description"><?php echo esc_html__( 'Optional message from the client.', 'oxtilofastcal' ); ?></p>
						</td>
					</tr>
					<tr>
						<th><label for="send_notification"><?php echo esc_html__( 'Send Email Notification', 'oxtilofastcal' ); ?></label></th>
						<td>
							<label>
								<input type="checkbox" name="send_notification" id="send_notification" value="1" checked>
								<?php echo esc_html__( 'Send confirmation emails to admin and client', 'oxtilofastcal' ); ?>
							</label>
						</td>
					</tr>
				</table>

				<p class="submit">
					<button type="submit" class="button button-primary"><?php echo esc_html__( 'Create Booking', 'oxtilofastcal' ); ?></button>
					<a href="<?php echo esc_url( $base_url ); ?>" class="button"><?php echo esc_html__( 'Cancel', 'oxtilofastcal' ); ?></a>
				</p>
			</form>
		</div>

		<script>
		jQuery(document).ready(function($) {
			var oxtilofastcalAdminNonce = '<?php echo esc_js( wp_create_nonce( 'oxtilofastcal_get_slots' ) ); ?>';
			var oxtilofastcalAjaxUrl = '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>';

			// Check available slots
			$('#oxtilofastcal-check-availability').on('click', function() {
				var date = $('#booking_date').val();
				var serviceId = $('#service_id').val();
				var $container = $('#oxtilofastcal-available-slots-container');
				var $row = $('#oxtilofastcal-available-slots-row');

				if (!date) {
					alert('<?php echo esc_js( __( 'Please select a date first.', 'oxtilofastcal' ) ); ?>');
					return;
				}

				$container.html('<p><span class="spinner is-active" style="float: none;"></span> <?php echo esc_js( __( 'Loading...', 'oxtilofastcal' ) ); ?></p>');
				$row.show();

				$.ajax({
					url: oxtilofastcalAjaxUrl,
					type: 'POST',
					data: {
						action: 'oxtilofastcal_get_slots',
						nonce: oxtilofastcalAdminNonce,
						date: date,
						service_id: serviceId
					},
					success: function(response) {
						if (response.success && response.data.slots && response.data.slots.length > 0) {
							var html = '<div class="oxtilofastcal-admin-slots" style="display: flex; flex-wrap: wrap; gap: 5px;">';
							response.data.slots.forEach(function(slot) {
								var startTime = slot.start.substring(11, 16); // Extract HH:MM
								var endTime = slot.end.substring(11, 16);
								html += '<button type="button" class="button oxtilofastcal-slot-btn" data-start="' + startTime + '" data-end="' + endTime + '" style="margin: 2px;">' + slot.label + '</button>';
							});
							html += '</div>';
							$container.html(html);

							// Slot click handler
							$('.oxtilofastcal-slot-btn').on('click', function() {
								$('#start_time').val($(this).data('start'));
								$('#end_time').val($(this).data('end'));
								$('.oxtilofastcal-slot-btn').removeClass('button-primary');
								$(this).addClass('button-primary');
							});
						} else {
							$container.html('<p class="description" style="color: #d63638;"><?php echo esc_js( __( 'No available slots for this date. You can still enter custom times.', 'oxtilofastcal' ) ); ?></p>');
						}
					},
					error: function() {
						$container.html('<p class="description" style="color: #d63638;"><?php echo esc_js( __( 'Error loading slots. You can still enter custom times.', 'oxtilofastcal' ) ); ?></p>');
					}
				});
			});

			// Auto-calculate end time based on service duration
			$('#oxtilofastcal-auto-end-time').on('click', function() {
				var startTime = $('#start_time').val();
				if (!startTime) {
					alert('<?php echo esc_js( __( 'Please enter a start time first.', 'oxtilofastcal' ) ); ?>');
					return;
				}

				var duration = parseInt($('#service_template option:selected').data('duration')) || 30;
				var parts = startTime.split(':');
				var hours = parseInt(parts[0]);
				var minutes = parseInt(parts[1]) + duration;

				hours += Math.floor(minutes / 60);
				minutes = minutes % 60;

				if (hours >= 24) {
					hours = hours - 24;
				}

				var endTime = String(hours).padStart(2, '0') + ':' + String(minutes).padStart(2, '0');
				$('#end_time').val(endTime);
			});

			// When service template is selected, auto-fill the service name and recalculate end time
			$('#service_template').on('change', function() {
				var $selected = $(this).find('option:selected');
				var name = $selected.data('name');
				if (name) {
					$('#service_name').val(name);
				}
				if ($('#start_time').val()) {
					$('#oxtilofastcal-auto-end-time').trigger('click');
				}
			});
		});
		</script>

	<?php elseif ( 'edit' === $action && $booking_id > 0 ) : ?>
		<?php
		$booking = Oxtilofastcal_Database::get_booking( $booking_id );
		if ( ! $booking ) {
			echo '<div class="notice notice-error"><p>' . esc_html__( 'Booking not found.', 'oxtilofastcal' ) . '</p></div>';
		} else {
			?>
			<div class="card" style="max-width: 600px; padding: 20px; margin-top: 20px;">
				<h2><?php echo esc_html__( 'Edit Booking', 'oxtilofastcal' ); ?> #<?php echo esc_html( $booking['id'] ); ?></h2>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php wp_nonce_field( 'oxtilofastcal_edit_booking', 'oxtilofastcal_nonce' ); ?>
					<input type="hidden" name="action" value="oxtilofastcal_save_booking">
					<input type="hidden" name="booking_id" value="<?php echo esc_attr( $booking['id'] ); ?>">

					<table class="form-table" role="presentation">
						<tr>
							<th><label for="client_name"><?php echo esc_html__( 'Client Name', 'oxtilofastcal' ); ?></label></th>
							<td><input type="text" name="client_name" id="client_name" class="regular-text" value="<?php echo esc_attr( $booking['client_name'] ); ?>" required></td>
						</tr>
						<tr>
							<th><label for="client_email"><?php echo esc_html__( 'Client Email', 'oxtilofastcal' ); ?></label></th>
							<td><input type="email" name="client_email" id="client_email" class="regular-text" value="<?php echo esc_attr( $booking['client_email'] ); ?>" required></td>
						</tr>
						<tr>
							<th><label for="service_name"><?php echo esc_html__( 'Service Name', 'oxtilofastcal' ); ?></label></th>
							<td>
								<?php
								// Get current service name - prefer stored service_name, fallback to service lookup
								$current_service_name = '';
								if ( ! empty( $booking['service_name'] ) ) {
									$current_service_name = $booking['service_name'];
								} else {
									$s_idx = (int) $booking['service_id'];
									$current_service_name = isset( $services[ $s_idx ]['name'] ) ? $services[ $s_idx ]['name'] : '';
								}
								?>
								<input type="text" name="service_name" id="service_name" class="regular-text" value="<?php echo esc_attr( $current_service_name ); ?>" required>
								<p class="description"><?php echo esc_html__( 'Enter any service name. This will be the event title.', 'oxtilofastcal' ); ?></p>
							</td>
						</tr>
						<tr>
							<th><label for="start_time"><?php echo esc_html__( 'Start Time', 'oxtilofastcal' ); ?></label></th>
							<td><input type="datetime-local" name="start_time" id="start_time" value="<?php echo esc_attr( str_replace( ' ', 'T', $booking['start_time'] ) ); ?>" required></td>
						</tr>
						<tr>
							<th><label for="end_time"><?php echo esc_html__( 'End Time', 'oxtilofastcal' ); ?></label></th>
							<td><input type="datetime-local" name="end_time" id="end_time" value="<?php echo esc_attr( str_replace( ' ', 'T', $booking['end_time'] ) ); ?>" required></td>
						</tr>
						<?php if ( ! empty( $booking['client_message'] ) ) : ?>
						<tr>
							<th><label for="client_message"><?php echo esc_html__( 'Client Message', 'oxtilofastcal' ); ?></label></th>
							<td>
								<textarea id="client_message" rows="3" class="large-text" readonly disabled style="background-color: #f0f0f1;"><?php echo esc_textarea( $booking['client_message'] ); ?></textarea>
								<p class="description"><?php echo esc_html__( 'Message from the client (read-only).', 'oxtilofastcal' ); ?></p>
							</td>
						</tr>
						<?php endif; ?>
						<tr>
							<th><label for="status"><?php echo esc_html__( 'Status', 'oxtilofastcal' ); ?></label></th>
							<td>
								<select name="status" id="status">
									<option value="confirmed" <?php selected( $booking['status'], 'confirmed' ); ?>><?php echo esc_html__( 'Confirmed', 'oxtilofastcal' ); ?></option>
									<option value="cancelled" <?php selected( $booking['status'], 'cancelled' ); ?>><?php echo esc_html__( 'Cancelled', 'oxtilofastcal' ); ?></option>
								</select>
							</td>
						</tr>
					</table>

					<p class="submit">
						<button type="submit" class="button button-primary"><?php echo esc_html__( 'Update Booking', 'oxtilofastcal' ); ?></button>
						<a href="<?php echo esc_url( $base_url ); ?>" class="button"><?php echo esc_html__( 'Cancel', 'oxtilofastcal' ); ?></a>
					</p>
				</form>
			</div>
			<?php
		}
	else :
		// LIST VIEW
		$paged = isset( $_GET['paged'] ) ? max( 1, absint( wp_unslash( $_GET['paged'] ) ) ) : 1;
		$limit = 20;
		$offset = ( $paged - 1 ) * $limit;

		$bookings = Oxtilofastcal_Database::get_all_bookings( $limit, $offset );
		$total    = Oxtilofastcal_Database::count_bookings();
		$pages    = ceil( $total / $limit );
		?>

		<table class="wp-list-table widefat fixed striped table-view-list posts">
			<thead>
				<tr>
					<th>ID</th>
					<th><?php echo esc_html__( 'Client', 'oxtilofastcal' ); ?></th>
					<th><?php echo esc_html__( 'Service', 'oxtilofastcal' ); ?></th>
					<th><?php echo esc_html__( 'Date / Time', 'oxtilofastcal' ); ?></th>
					<th><?php echo esc_html__( 'Status', 'oxtilofastcal' ); ?></th>
					<th><?php echo esc_html__( 'Actions', 'oxtilofastcal' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $bookings ) ) : ?>
					<tr><td colspan="6"><?php echo esc_html__( 'No bookings found.', 'oxtilofastcal' ); ?></td></tr>
				<?php else : ?>
					<?php foreach ( $bookings as $b ) : ?>
						<?php
						// Prefer stored service_name, fallback to service lookup by ID
						if ( ! empty( $b['service_name'] ) ) {
							$s_name = $b['service_name'];
						} else {
							$s_idx = (int) $b['service_id'];
							$s_name = isset( $services[ $s_idx ]['name'] ) ? $services[ $s_idx ]['name'] : __( 'Unknown Service', 'oxtilofastcal' );
						}
						$edit_url = add_query_arg( array( 'action' => 'edit', 'booking_id' => $b['id'] ), $base_url );
						$del_url  = wp_nonce_url( admin_url( 'admin-post.php?action=oxtilofastcal_delete_booking&booking_id=' . $b['id'] ), 'oxtilofastcal_delete_booking' );
						?>
						<tr>
							<td>#<?php echo esc_html( $b['id'] ); ?></td>
							<td>
								<strong><?php echo esc_html( $b['client_name'] ); ?></strong><br>
								<a href="mailto:<?php echo esc_attr( $b['client_email'] ); ?>"><?php echo esc_html( $b['client_email'] ); ?></a>
							</td>
							<td><?php echo esc_html( $s_name ); ?></td>
							<td>
								<?php echo esc_html( $b['start_time'] ); ?><br>
								<small><?php echo esc_html( $b['end_time'] ); ?></small>
							</td>
							<td>
								<?php if ( 'confirmed' === $b['status'] ) : ?>
									<span class="oxtilofastcal-badge oxtilofastcal-badge--success"><?php echo esc_html__( 'Confirmed', 'oxtilofastcal' ); ?></span>
								<?php else : ?>
									<span class="oxtilofastcal-badge oxtilofastcal-badge--error"><?php echo esc_html__( 'Cancelled', 'oxtilofastcal' ); ?></span>
								<?php endif; ?>
							</td>
							<td>
								<a href="<?php echo esc_url( $edit_url ); ?>" class="button button-small"><?php echo esc_html__( 'Edit', 'oxtilofastcal' ); ?></a>
								<a href="<?php echo esc_url( $del_url ); ?>" class="button button-small button-link-delete" onclick="return confirm('<?php echo esc_js( __( 'Are you sure?', 'oxtilofastcal' ) ); ?>');" style="color: #a00;"><?php echo esc_html__( 'Delete', 'oxtilofastcal' ); ?></a>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>

		<?php if ( $pages > 1 ) : ?>
			<div class="tablenav bottom">
				<div class="tablenav-pages">
					<?php
					echo wp_kses_post( paginate_links( array(
						'base'    => add_query_arg( 'paged', '%#%' ),
						'format'  => '',
						'prev_text' => '&laquo;',
						'next_text' => '&raquo;',
						'total'   => $pages,
						'current' => $paged,
					) ) );
					?>
				</div>
			</div>
		<?php endif; ?>

	<?php endif; ?>
</div>

<style>
.oxtilofastcal-badge {
	display: inline-block;
	padding: 3px 8px;
	border-radius: 3px;
	font-size: 12px;
	font-weight: 500;
}
.oxtilofastcal-badge--success {
	background: #e7f7ed;
	color: #107c10;
}
.oxtilofastcal-badge--error {
	background: #fde7e9;
	color: #d63638;
}
.required {
	color: #d63638;
}
</style>
