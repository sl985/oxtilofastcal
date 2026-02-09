<?php
/**
 * Booking form template.
 *
 * @package Oxtilofastcal
 *
 * @var array  $services   Available services.
 * @var string $return_url Return URL.
 * @var string $success    Success flag.
 * @var string $error      Error code.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$general  = get_option( 'oxtilofastcal_general', array() );
$max_days = isset( $general['max_days_future'] ) ? absint( $general['max_days_future'] ) : 30;
$max_date_val = '';
if ( $max_days > 0 ) {
	$max_dt = new DateTimeImmutable( 'now', wp_timezone() );
	$max_dt = $max_dt->add( new DateInterval( 'P' . $max_days . 'D' ) );
	$max_date_val = $max_dt->format( 'Y-m-d' );
}

$today_dt = new DateTimeImmutable( 'now', wp_timezone() );
$tomorrow_dt = $today_dt->add( new DateInterval( 'P1D' ) );
$day_after_dt = $today_dt->add( new DateInterval( 'P2D' ) );
?>
<div class="oxtilofastcal-form" data-oxtilofastcal-form="1">

	<?php if ( '1' === $success ) : ?>
		<div class="oxtilofastcal-notice oxtilofastcal-notice--success">
			<?php echo esc_html__( 'Your booking has been confirmed.', 'oxtilofastcal' ); ?>
		</div>
	<?php endif; ?>

	<?php if ( $error ) : ?>
		<div class="oxtilofastcal-notice oxtilofastcal-notice--error">
			<?php
			switch ( $error ) {
				case 'unavailable':
					echo esc_html__( 'That time slot is no longer available. Please choose another.', 'oxtilofastcal' );
					break;
				case 'invalid_details':
					echo esc_html__( 'Please enter a valid name and email address.', 'oxtilofastcal' );
					break;
				default:
					echo esc_html__( 'We could not complete your booking. Please try again.', 'oxtilofastcal' );
					break;
			}
			?>
		</div>
	<?php endif; ?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="oxtilofastcal-form__form">
		<input type="hidden" name="action" value="oxtilofastcal_submit_booking" />
		<input type="hidden" name="return_url" value="<?php echo esc_attr( $return_url ); ?>" />
		<?php
		$security = get_option( 'oxtilofastcal_security', array() );
		if ( ! empty( $security['antibot_enabled'] ) ) :
		?>
			<?php wp_nonce_field( 'oxtilofastcal_booking_action', 'oxtilofastcal_security' ); ?>
			
			<div style="position: absolute; left: -9999px;">
				<input type="text" name="oxtilofastcal_website" value="" tabindex="-1" autocomplete="off" />
			</div>
			<input type="hidden" name="oxtilofastcal_valid" id="oxtilofastcal_valid" value="" />

			<script>
			(function() {
				var start_time = new Date().getTime();
				var form = document.querySelector('.oxtilofastcal-form__form');
				if (form) {
					form.addEventListener('submit', function() {
						var now = new Date().getTime();
						var duration = now - start_time;
						var validField = document.getElementById('oxtilofastcal_valid');
						if (validField) {
							validField.value = 'human_verified_' + duration;
						}
					});
				}
			})();
			</script>
		<?php else : ?>
			<?php wp_nonce_field( 'oxtilofastcal_submit_booking', 'oxtilofastcal_booking_nonce' ); ?>
		<?php endif; ?>

		<div class="oxtilofastcal-step">
			<label for="oxtilofastcal_service"><?php echo esc_html__( 'Select a service', 'oxtilofastcal' ); ?></label>
			<select id="oxtilofastcal_service" name="service_id" required>
				<option value=""><?php echo esc_html__( 'Choose…', 'oxtilofastcal' ); ?></option>
				<?php foreach ( $services as $idx => $service ) : ?>
					<?php
					$name     = isset( $service['name'] ) ? (string) $service['name'] : '';
					$duration = isset( $service['duration'] ) ? absint( $service['duration'] ) : 0;
					$type     = isset( $service['type'] ) ? (string) $service['type'] : 'online';
					if ( '' === $name ) {
						continue;
					}
					$label = $name;
					$selected = ( 0 === $idx ) ? 'selected' : '';
					if ( $duration > 0 ) {
						$label .= ' (' . sprintf(
							/* translators: %d: Service duration in minutes */
							__( '%d min', 'oxtilofastcal' ),
							$duration
						) . ')';
					}
					?>
					<option value="<?php echo esc_attr( (int) $idx ); ?>" data-type="<?php echo esc_attr( $type ); ?>" <?php echo esc_attr( $selected ); ?>>
						<?php echo esc_html( $label ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>

		<div class="oxtilofastcal-step">
			<label for="oxtilofastcal_date"><?php echo esc_html__( 'Choose a date', 'oxtilofastcal' ); ?></label>
			
			<div class="oxtilofastcal-date-buttons">
				<button type="button" class="oxtilofastcal-date-btn" data-date="<?php echo esc_attr( $today_dt->format( 'Y-m-d' ) ); ?>"><?php echo esc_html__( 'Today', 'oxtilofastcal' ); ?></button>
				<button type="button" class="oxtilofastcal-date-btn" data-date="<?php echo esc_attr( $tomorrow_dt->format( 'Y-m-d' ) ); ?>"><?php echo esc_html__( 'Tomorrow', 'oxtilofastcal' ); ?></button>
				<button type="button" class="oxtilofastcal-date-btn" data-date="<?php echo esc_attr( $day_after_dt->format( 'Y-m-d' ) ); ?>"><?php echo esc_html__( 'Day after', 'oxtilofastcal' ); ?></button>
			</div>
			
			<input type="date" id="oxtilofastcal_date" name="date" min="<?php echo esc_attr( $today_dt->format( 'Y-m-d' ) ); ?>" <?php if ( $max_date_val ) : ?>max="<?php echo esc_attr( $max_date_val ); ?>"<?php endif; ?> autocomplete="off" required />
			<div class="oxtilofastcal-help">
				<?php echo esc_html__( 'Pick a date to see available times.', 'oxtilofastcal' ); ?>
			</div>
		</div>

		<div class="oxtilofastcal-step">
			<div class="oxtilofastcal-step__title"><?php echo esc_html__( 'Select a time slot', 'oxtilofastcal' ); ?></div>
			<div id="oxtilofastcal_slots" class="oxtilofastcal-slots" aria-live="polite">
				<div class="oxtilofastcal-slots__placeholder">
					<?php echo esc_html__( 'Please choose a service and date.', 'oxtilofastcal' ); ?>
				</div>
			</div>
			<input type="hidden" name="slot_start" id="oxtilofastcal_slot_start" value="" />
		</div>

		<div class="oxtilofastcal-step">
			<label for="oxtilofastcal_name"><?php echo esc_html__( 'Your name', 'oxtilofastcal' ); ?></label>
			<input type="text" id="oxtilofastcal_name" name="client_name" required />
		</div>

		<div class="oxtilofastcal-step">
			<label for="oxtilofastcal_email"><?php echo esc_html__( 'Your email', 'oxtilofastcal' ); ?></label>
			<input type="email" id="oxtilofastcal_email" name="client_email" required />
		</div>
		
		<div class="oxtilofastcal-step">
			<label for="oxtilofastcal_message"><?php echo esc_html__( 'Add message (optional)', 'oxtilofastcal' ); ?></label>
			<textarea id="oxtilofastcal_message" name="client_message" rows="3"></textarea>
		</div>

		<button type="submit" class="oxtilofastcal-submit">
			<?php echo esc_html__( 'Confirm Booking', 'oxtilofastcal' ); ?>
		</button>
	</form>
</div>
