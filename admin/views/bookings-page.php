<?php
/**
 * Admin bookings page template.
 *
 * @package Oxtilofastcal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display-only routing parameter.
$action     = isset( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : 'list';
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display-only routing parameter.
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
	<h1 class="wp-heading-inline"><?php echo esc_html__( 'Bookings', 'oxtilo-fast-cal' ); ?></h1>
	
	<?php if ( 'list' === $action ) : ?>
		<a href="<?php echo esc_url( add_query_arg( 'action', 'add', $base_url ) ); ?>" class="page-title-action"><?php echo esc_html__( 'Add New', 'oxtilo-fast-cal' ); ?></a>
	<?php endif; ?>
	
	<?php if ( ! empty( $feed_url ) ) : ?>
		<div style="margin: 10px 0;">
			<strong><?php echo esc_html__( 'Channel URL (ICS Feed):', 'oxtilo-fast-cal' ); ?></strong> 
			<a href="<?php echo esc_url( $feed_url ); ?>" target="_blank" class="code"><?php echo esc_html( $feed_url ); ?></a>
		</div>
	<?php endif; ?>

	<hr class="wp-header-end">

	<?php
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display-only status message parameter.
	$msg = isset( $_GET['msg'] ) ? sanitize_text_field( wp_unslash( $_GET['msg'] ) ) : '';
	if ( $msg ) :
		?>
		<div class="notice notice-success is-dismissible">
			<p>
				<?php
				if ( 'updated' === $msg ) {
					echo esc_html__( 'Booking updated successfully. Email notification sent.', 'oxtilo-fast-cal' );
				} elseif ( 'deleted' === $msg ) {
					echo esc_html__( 'Booking deleted successfully. Email notification sent.', 'oxtilo-fast-cal' );
				} elseif ( 'created' === $msg ) {
					echo esc_html__( 'Booking created successfully. Email notification sent.', 'oxtilo-fast-cal' );
				}
				?>
			</p>
		</div>
	<?php endif; ?>

	<?php
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display-only error message parameter.
	if ( isset( $_GET['error'] ) ) :
	?>
		<div class="notice notice-error is-dismissible">
			<p>
				<?php
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display-only error message parameter.
				$error = sanitize_key( wp_unslash( $_GET['error'] ) );
				switch ( $error ) {
					case 'missing_data':
						echo esc_html__( 'Please fill in all required fields.', 'oxtilo-fast-cal' );
						break;
					case 'invalid_email':
						echo esc_html__( 'Please enter a valid email address.', 'oxtilo-fast-cal' );
						break;
					case 'invalid_dates':
						echo esc_html__( 'Invalid date/time values. End time must be after start time.', 'oxtilo-fast-cal' );
						break;
					case 'db_error':
						echo esc_html__( 'Database error occurred. Please try again.', 'oxtilo-fast-cal' );
						break;
					default:
						echo esc_html__( 'An error occurred. Please try again.', 'oxtilo-fast-cal' );
						break;
				}
				?>
			</p>
		</div>
	<?php endif; ?>

	<?php if ( 'add' === $action ) : ?>
		<!-- ADD NEW BOOKING FORM -->
		<div class="card" style="max-width: 800px; padding: 20px; margin-top: 20px;">
			<h2><?php echo esc_html__( 'Add New Booking', 'oxtilo-fast-cal' ); ?></h2>
			<p class="description" style="margin-bottom: 15px;">
				<?php echo esc_html__( 'As an administrator, you can create bookings at any time, including outside of regular working hours.', 'oxtilo-fast-cal' ); ?>
			</p>
			
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="oxtilofastcal-admin-add-booking-form">
				<?php wp_nonce_field( 'oxtilofastcal_create_booking', 'oxtilofastcal_nonce' ); ?>
				<input type="hidden" name="action" value="oxtilofastcal_create_booking">

				<table class="form-table" role="presentation">
					<tr>
						<th><label for="client_name"><?php echo esc_html__( 'Client Name', 'oxtilo-fast-cal' ); ?> <span class="required">*</span></label></th>
						<td><input type="text" name="client_name" id="client_name" class="regular-text" required></td>
					</tr>
					<tr>
						<th><label for="client_email"><?php echo esc_html__( 'Client Email', 'oxtilo-fast-cal' ); ?> <span class="required">*</span></label></th>
						<td><input type="email" name="client_email" id="client_email" class="regular-text" required></td>
					</tr>
				<tr>
					<th><label for="service_name"><?php echo esc_html__( 'Service Name', 'oxtilo-fast-cal' ); ?> <span class="required">*</span></label></th>
					<td>
						<input type="text" name="service_name" id="service_name" class="regular-text" required placeholder="<?php echo esc_attr__( 'Enter custom service name', 'oxtilo-fast-cal' ); ?>">
						<p class="description"><?php echo esc_html__( 'Enter any service name. This will be the event title.', 'oxtilo-fast-cal' ); ?></p>
					</td>
				</tr>
				<tr>
					<th><label for="service_template"><?php echo esc_html__( 'Service Template', 'oxtilo-fast-cal' ); ?></label></th>
					<td>
						<select name="service_template" id="service_template">
							<option value=""><?php echo esc_html__( '— Select a template (optional) —', 'oxtilo-fast-cal' ); ?></option>
							<?php foreach ( $services as $idx => $s ) : ?>
								<option value="<?php echo esc_attr( $idx ); ?>" data-name="<?php echo esc_attr( $s['name'] ?? '' ); ?>" data-duration="<?php echo esc_attr( $s['duration'] ?? 30 ); ?>" data-type="<?php echo esc_attr( $s['type'] ?? 'online' ); ?>">
									<?php echo esc_html( $s['name'] ?? 'Unknown' ); ?> (<?php echo esc_html( $s['duration'] ?? 30 ); ?> min)
								</option>
							<?php endforeach; ?>
						</select>
						<p class="description"><?php echo esc_html__( 'Optionally select a predefined service to auto-fill name and duration.', 'oxtilo-fast-cal' ); ?></p>
					</td>
				</tr>
					<tr>
						<th><label for="booking_date"><?php echo esc_html__( 'Date', 'oxtilo-fast-cal' ); ?> <span class="required">*</span></label></th>
						<td>
							<input type="date" name="booking_date" id="booking_date" value="<?php echo esc_attr( wp_date( 'Y-m-d' ) ); ?>" required>
							<button type="button" id="oxtilofastcal-check-availability" class="button" style="margin-left: 10px;">
								<?php echo esc_html__( 'Check Available Slots', 'oxtilo-fast-cal' ); ?>
							</button>
						</td>
					</tr>
					<tr id="oxtilofastcal-available-slots-row" style="display: none;">
						<th><?php echo esc_html__( 'Available Slots', 'oxtilo-fast-cal' ); ?></th>
						<td>
							<div id="oxtilofastcal-available-slots-container" style="max-height: 200px; overflow-y: auto; padding: 10px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px;">
								<p class="description"><?php echo esc_html__( 'Click "Check Available Slots" to see available times.', 'oxtilo-fast-cal' ); ?></p>
							</div>
							<p class="description" style="margin-top: 5px;">
								<?php echo esc_html__( 'These are suggestions based on availability. You can still enter any custom time below.', 'oxtilo-fast-cal' ); ?>
							</p>
						</td>
					</tr>
					<tr>
						<th><label for="start_time"><?php echo esc_html__( 'Start Time', 'oxtilo-fast-cal' ); ?> <span class="required">*</span></label></th>
						<td>
							<input type="time" name="start_time" id="start_time" step="60" required>
							<p class="description"><?php echo esc_html__( 'You can enter any time, e.g., 17:15.', 'oxtilo-fast-cal' ); ?></p>
						</td>
					</tr>
					<tr>
						<th><label for="end_time"><?php echo esc_html__( 'End Time', 'oxtilo-fast-cal' ); ?> <span class="required">*</span></label></th>
						<td>
							<input type="time" name="end_time" id="end_time" step="60" required>
							<button type="button" id="oxtilofastcal-auto-end-time" class="button" style="margin-left: 10px;">
								<?php echo esc_html__( 'Auto-calculate from service duration', 'oxtilo-fast-cal' ); ?>
							</button>
						</td>
					</tr>
					<tr>
						<th><label for="client_message"><?php echo esc_html__( 'Client Message', 'oxtilo-fast-cal' ); ?></label></th>
						<td>
							<textarea name="client_message" id="client_message" rows="3" class="large-text"></textarea>
							<p class="description"><?php echo esc_html__( 'Optional message from the client.', 'oxtilo-fast-cal' ); ?></p>
						</td>
					</tr>
					<tr>
						<th><label for="send_notification"><?php echo esc_html__( 'Send Email Notification', 'oxtilo-fast-cal' ); ?></label></th>
						<td>
							<label>
								<input type="checkbox" name="send_notification" id="send_notification" value="1" checked>
								<?php echo esc_html__( 'Send confirmation emails to admin and client', 'oxtilo-fast-cal' ); ?>
							</label>
						</td>
					</tr>
				</table>

				<p class="submit">
					<button type="submit" class="button button-primary"><?php echo esc_html__( 'Create Booking', 'oxtilo-fast-cal' ); ?></button>
					<a href="<?php echo esc_url( $base_url ); ?>" class="button"><?php echo esc_html__( 'Cancel', 'oxtilo-fast-cal' ); ?></a>
				</p>
			</form>
		</div>


	<?php elseif ( 'edit' === $action && $booking_id > 0 ) : ?>
		<?php
		$booking = Oxtilofastcal_Database::get_booking( $booking_id );
		if ( ! $booking ) {
			echo '<div class="notice notice-error"><p>' . esc_html__( 'Booking not found.', 'oxtilo-fast-cal' ) . '</p></div>';
		} else {
			?>
			<div class="card" style="max-width: 600px; padding: 20px; margin-top: 20px;">
				<h2><?php echo esc_html__( 'Edit Booking', 'oxtilo-fast-cal' ); ?> #<?php echo esc_html( $booking['id'] ); ?></h2>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php wp_nonce_field( 'oxtilofastcal_edit_booking', 'oxtilofastcal_nonce' ); ?>
					<input type="hidden" name="action" value="oxtilofastcal_save_booking">
					<input type="hidden" name="booking_id" value="<?php echo esc_attr( $booking['id'] ); ?>">

					<table class="form-table" role="presentation">
						<tr>
							<th><label for="client_name"><?php echo esc_html__( 'Client Name', 'oxtilo-fast-cal' ); ?></label></th>
							<td><input type="text" name="client_name" id="client_name" class="regular-text" value="<?php echo esc_attr( $booking['client_name'] ); ?>" required></td>
						</tr>
						<tr>
							<th><label for="client_email"><?php echo esc_html__( 'Client Email', 'oxtilo-fast-cal' ); ?></label></th>
							<td><input type="email" name="client_email" id="client_email" class="regular-text" value="<?php echo esc_attr( $booking['client_email'] ); ?>" required></td>
						</tr>
						<tr>
							<th><label for="service_name"><?php echo esc_html__( 'Service Name', 'oxtilo-fast-cal' ); ?></label></th>
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
								<p class="description"><?php echo esc_html__( 'Enter any service name. This will be the event title.', 'oxtilo-fast-cal' ); ?></p>
							</td>
						</tr>
						<tr>
							<th><label for="start_time"><?php echo esc_html__( 'Start Time', 'oxtilo-fast-cal' ); ?></label></th>
							<td><input type="datetime-local" name="start_time" id="start_time" value="<?php echo esc_attr( str_replace( ' ', 'T', $booking['start_time'] ) ); ?>" required></td>
						</tr>
						<tr>
							<th><label for="end_time"><?php echo esc_html__( 'End Time', 'oxtilo-fast-cal' ); ?></label></th>
							<td><input type="datetime-local" name="end_time" id="end_time" value="<?php echo esc_attr( str_replace( ' ', 'T', $booking['end_time'] ) ); ?>" required></td>
						</tr>
						<?php if ( ! empty( $booking['client_message'] ) ) : ?>
						<tr>
							<th><label for="client_message"><?php echo esc_html__( 'Client Message', 'oxtilo-fast-cal' ); ?></label></th>
							<td>
								<textarea id="client_message" rows="3" class="large-text" readonly disabled style="background-color: #f0f0f1;"><?php echo esc_textarea( $booking['client_message'] ); ?></textarea>
								<p class="description"><?php echo esc_html__( 'Message from the client (read-only).', 'oxtilo-fast-cal' ); ?></p>
							</td>
						</tr>
						<?php endif; ?>
						<tr>
							<th><label for="status"><?php echo esc_html__( 'Status', 'oxtilo-fast-cal' ); ?></label></th>
							<td>
								<select name="status" id="status">
									<option value="confirmed" <?php selected( $booking['status'], 'confirmed' ); ?>><?php echo esc_html__( 'Confirmed', 'oxtilo-fast-cal' ); ?></option>
									<option value="cancelled" <?php selected( $booking['status'], 'cancelled' ); ?>><?php echo esc_html__( 'Cancelled', 'oxtilo-fast-cal' ); ?></option>
								</select>
							</td>
						</tr>
					</table>

					<p class="submit">
						<button type="submit" class="button button-primary"><?php echo esc_html__( 'Update Booking', 'oxtilo-fast-cal' ); ?></button>
						<a href="<?php echo esc_url( $base_url ); ?>" class="button"><?php echo esc_html__( 'Cancel', 'oxtilo-fast-cal' ); ?></a>
					</p>
				</form>
			</div>
			<?php
		}
	else :
		// LIST VIEW
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display-only pagination parameter.
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
					<th><?php echo esc_html__( 'Client', 'oxtilo-fast-cal' ); ?></th>
					<th><?php echo esc_html__( 'Service', 'oxtilo-fast-cal' ); ?></th>
					<th><?php echo esc_html__( 'Date / Time', 'oxtilo-fast-cal' ); ?></th>
					<th><?php echo esc_html__( 'Status', 'oxtilo-fast-cal' ); ?></th>
					<th><?php echo esc_html__( 'Actions', 'oxtilo-fast-cal' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $bookings ) ) : ?>
					<tr><td colspan="6"><?php echo esc_html__( 'No bookings found.', 'oxtilo-fast-cal' ); ?></td></tr>
				<?php else : ?>
					<?php foreach ( $bookings as $b ) : ?>
						<?php
						// Prefer stored service_name, fallback to service lookup by ID
						if ( ! empty( $b['service_name'] ) ) {
							$s_name = $b['service_name'];
						} else {
							$s_idx = (int) $b['service_id'];
							$s_name = isset( $services[ $s_idx ]['name'] ) ? $services[ $s_idx ]['name'] : __( 'Unknown Service', 'oxtilo-fast-cal' );
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
									<span class="oxtilofastcal-badge oxtilofastcal-badge--success"><?php echo esc_html__( 'Confirmed', 'oxtilo-fast-cal' ); ?></span>
								<?php else : ?>
									<span class="oxtilofastcal-badge oxtilofastcal-badge--error"><?php echo esc_html__( 'Cancelled', 'oxtilo-fast-cal' ); ?></span>
								<?php endif; ?>
							</td>
							<td>
								<a href="<?php echo esc_url( $edit_url ); ?>" class="button button-small"><?php echo esc_html__( 'Edit', 'oxtilo-fast-cal' ); ?></a>
								<a href="<?php echo esc_url( $del_url ); ?>" class="button button-small button-link-delete" onclick="return confirm('<?php echo esc_js( __( 'Are you sure?', 'oxtilo-fast-cal' ) ); ?>');" style="color: #a00;"><?php echo esc_html__( 'Delete', 'oxtilo-fast-cal' ); ?></a>
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


