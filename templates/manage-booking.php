<?php
/**
 * Manage booking template.
 *
 * @package Oxtilofastcal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$booking = get_query_var( 'oxtilofastcal_booking' );
if ( ! $booking ) {
	return;
}

$updated = isset( $_GET['updated'] ) ? sanitize_text_field( wp_unslash( $_GET['updated'] ) ) : '';
$error   = isset( $_GET['error'] ) ? sanitize_text_field( wp_unslash( $_GET['error'] ) ) : '';

$services = oxtilofastcal_get_services();
$service  = $services[ $booking['service_id'] ] ?? array( 'name' => 'Unknown Service' );

get_header();
?>

<div class="wrap oxtilofastcal-manage-container" style="max-width: 800px; margin: 40px auto; padding: 20px;">
	<h1><?php esc_html_e( 'Manage Booking', 'oxtilofastcal' ); ?> #<?php echo esc_html( $booking['id'] ); ?></h1>

	<?php if ( 'cancelled' === $updated ) : ?>
		<div class="oxtilofastcal-notice oxtilofastcal-notice--success" style="background: #e7f7ed; color: #107c10; padding: 15px; margin-bottom: 20px; border-left: 4px solid #107c10;">
			<?php esc_html_e( 'Your booking has been cancelled.', 'oxtilofastcal' ); ?>
		</div>
	<?php elseif ( 'rescheduled' === $updated ) : ?>
		<div class="oxtilofastcal-notice oxtilofastcal-notice--success" style="background: #e7f7ed; color: #107c10; padding: 15px; margin-bottom: 20px; border-left: 4px solid #107c10;">
			<?php esc_html_e( 'Your booking has been successfully rescheduled.', 'oxtilofastcal' ); ?>
		</div>
	<?php endif; ?>

	<?php if ( $error ) : ?>
		<div class="oxtilofastcal-notice oxtilofastcal-notice--error" style="background: #fde7e9; color: #d63638; padding: 15px; margin-bottom: 20px; border-left: 4px solid #d63638;">
			<?php
			switch ( $error ) {
				case 'missing_details':
					esc_html_e( 'Please select a date and time.', 'oxtilofastcal' );
					break;
				case 'invalid_date':
					esc_html_e( 'The selected date is invalid or the time slot is no longer available.', 'oxtilofastcal' );
					break;
				case 'invalid_service':
					esc_html_e( 'Invalid service selected.', 'oxtilofastcal' );
					break;
				default:
					esc_html_e( 'An error occurred during booking update. Please try again.', 'oxtilofastcal' );
					break;
			}
			?>
		</div>
	<?php endif; ?>

	<div class="oxtilofastcal-booking-details" style="background: #f9f9f9; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
		<p><strong><?php esc_html_e( 'Service:', 'oxtilofastcal' ); ?></strong> <?php echo esc_html( $service['name'] ); ?></p>
		<p><strong><?php esc_html_e( 'Date:', 'oxtilofastcal' ); ?></strong> <?php echo esc_html( $booking['start_time'] ); ?></p>
		<p><strong><?php esc_html_e( 'Status:', 'oxtilofastcal' ); ?></strong> 
			<span class="status-<?php echo esc_attr( $booking['status'] ); ?>" style="text-transform: capitalize;">
				<?php 
				$status = $booking['status'];
				if ( 'confirmed' === $status ) {
					esc_html_e( 'Confirmed', 'oxtilofastcal' );
				} elseif ( 'cancelled' === $status ) {
					esc_html_e( 'Cancelled', 'oxtilofastcal' );
				} else {
					echo esc_html( $status );
				}
				?>
			</span>
		</p>
	</div>

	<?php if ( 'cancelled' !== $booking['status'] ) : ?>
		<div class="oxtilofastcal-actions">
			<h2 style="font-size: 1.5rem; margin-bottom: 15px;"><?php esc_html_e( 'Actions', 'oxtilofastcal' ); ?></h2>
			
			<!-- Toggle Reschedule -->
			<button type="button" id="oxtilofastcal-btn-reschedule" onclick="document.getElementById('oxtilofastcal-reschedule-form').style.display='block'; this.style.display='none';" class="button button-primary" style="margin-right: 15px; padding: 10px 20px; cursor: pointer;">
				<?php esc_html_e( 'Reschedule Booking', 'oxtilofastcal' ); ?>
			</button>

			<!-- Cancel Form -->
			<form method="post" onsubmit="return confirm('<?php esc_attr_e( 'Are you sure you want to cancel this booking?', 'oxtilofastcal' ); ?>');" style="display: inline-block;">
				<?php wp_nonce_field( 'oxtilofastcal_manage_' . $booking['id'] ); ?>
				<input type="hidden" name="oxtilofastcal_action" value="cancel_booking">
				<button type="submit" class="button button-secondary" style="color: #d63638; border-color: #d63638; background: transparent; padding: 10px 20px; cursor: pointer;">
					<?php esc_html_e( 'Cancel Booking', 'oxtilofastcal' ); ?>
				</button>
			</form>
		</div>

		<!-- Reschedule Form (Hidden by default) -->
		<div id="oxtilofastcal-reschedule-form" style="display: none; margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
			<h3><?php esc_html_e( 'Choose a new date and time', 'oxtilofastcal' ); ?></h3>
			
			<form method="post">
				<?php wp_nonce_field( 'oxtilofastcal_manage_' . $booking['id'] ); ?>
				<input type="hidden" name="oxtilofastcal_action" value="reschedule_booking">
				
				<!-- Reusing the Booking Form Structure for JS compatibility -->
				<div class="oxtilofastcal-form" data-oxtilofastcal-form="1">
					<div class="oxtilofastcal-step">
						<label for="oxtilofastcal_service"><?php echo esc_html__( 'Service', 'oxtilofastcal' ); ?></label>
						<select id="oxtilofastcal_service" name="service_id" required>
							<?php foreach ( $services as $idx => $s ) : ?>
								<option value="<?php echo esc_attr( $idx ); ?>" <?php selected( $idx, $booking['service_id'] ); ?>>
									<?php echo esc_html( $s['name'] ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>

					<div class="oxtilofastcal-step">
						<label for="oxtilofastcal_date"><?php echo esc_html__( 'New Date', 'oxtilofastcal' ); ?></label>
						<input type="date" id="oxtilofastcal_date" name="date" min="<?php echo esc_attr( wp_date( 'Y-m-d' ) ); ?>" required />
					</div>

					<div class="oxtilofastcal-step">
						<div id="oxtilofastcal_slots" class="oxtilofastcal-slots"></div>
						<input type="hidden" name="slot_start" id="oxtilofastcal_slot_start" value="" required />
					</div>

					<button type="submit" class="button button-primary" style="margin-top: 15px;">
						<?php esc_html_e( 'Confirm Reschedule', 'oxtilofastcal' ); ?>
					</button>
					
					<button type="button" onclick="document.getElementById('oxtilofastcal-reschedule-form').style.display='none'; document.getElementById('oxtilofastcal-btn-reschedule').style.display='inline-block';" class="button" style="margin-top: 15px; margin-left: 10px;">
						<?php esc_html_e( 'Cancel', 'oxtilofastcal' ); ?>
					</button>
				</div>
			</form>
		</div>
	<?php endif; ?>
</div>

<?php
get_footer();
