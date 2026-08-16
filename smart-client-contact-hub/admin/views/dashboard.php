<?php
/**
 * Dashboard view.
 *
 * @package SCCH
 * @var \SCCH\Admin\Admin $admin
 * @var array             $stats
 * @var array             $recent
 */

use SCCH\Admin\Admin;
use SCCH\Settings;

defined( 'ABSPATH' ) || exit;

$scch_contact = Settings::group( 'scch_contact' );
?>
<div class="wrap scch-wrap">
	<h1><?php esc_html_e( 'Smart Client Contact Hub', 'smart-client-contact-hub' ); ?></h1>
	<?php Admin::maybe_notice(); ?>

	<div class="scch-cards">
		<div class="scch-card"><span class="scch-card__num"><?php echo esc_html( number_format_i18n( $stats['total'] ) ); ?></span><span class="scch-card__label"><?php esc_html_e( 'Total Leads', 'smart-client-contact-hub' ); ?></span></div>
		<div class="scch-card"><span class="scch-card__num"><?php echo esc_html( number_format_i18n( $stats['new'] ) ); ?></span><span class="scch-card__label"><?php esc_html_e( 'New (Unhandled)', 'smart-client-contact-hub' ); ?></span></div>
		<div class="scch-card"><span class="scch-card__num"><?php echo esc_html( number_format_i18n( $stats['today'] ) ); ?></span><span class="scch-card__label"><?php esc_html_e( 'Today', 'smart-client-contact-hub' ); ?></span></div>
		<div class="scch-card"><span class="scch-card__num"><?php echo esc_html( number_format_i18n( $stats['week'] ) ); ?></span><span class="scch-card__label"><?php esc_html_e( 'Last 7 Days', 'smart-client-contact-hub' ); ?></span></div>
	</div>

	<?php if ( empty( $scch_contact['phone_number'] ) && empty( $scch_contact['sms_number'] ) ) : ?>
		<div class="notice notice-warning inline"><p>
			<?php
			printf(
				/* translators: %s: Contact Settings admin URL. */
				wp_kses_post( __( 'The Call Us / Text Us buttons need a phone number. Add one under <a href="%s">Contact Settings</a>.', 'smart-client-contact-hub' ) ),
				esc_url( admin_url( 'admin.php?page=scch-contact' ) )
			);
			?>
		</p></div>
	<?php endif; ?>

	<h2><?php esc_html_e( 'Latest Leads', 'smart-client-contact-hub' ); ?></h2>
	<?php if ( empty( $recent ) ) : ?>
		<p><?php esc_html_e( 'No leads yet. Once a visitor submits the widget form, the lead will show up here.', 'smart-client-contact-hub' ); ?></p>
	<?php else : ?>
		<table class="widefat striped scch-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Name', 'smart-client-contact-hub' ); ?></th>
					<th><?php esc_html_e( 'Service', 'smart-client-contact-hub' ); ?></th>
					<th><?php esc_html_e( 'Status', 'smart-client-contact-hub' ); ?></th>
					<th><?php esc_html_e( 'Date', 'smart-client-contact-hub' ); ?></th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $recent as $scch_lead ) : ?>
					<tr>
						<td><strong><?php echo esc_html( $scch_lead->name ); ?></strong></td>
						<td><?php echo esc_html( $scch_lead->service ); ?></td>
						<td><span class="scch-badge scch-badge--<?php echo esc_attr( $scch_lead->status ); ?>"><?php echo esc_html( ucfirst( $scch_lead->status ) ); ?></span></td>
						<td><?php echo esc_html( mysql2date( get_option( 'date_format' ), $scch_lead->submission_date ) ); ?></td>
						<td><a class="button button-small" href="<?php echo esc_url( admin_url( 'admin.php?page=scch-leads&lead=' . (int) $scch_lead->id ) ); ?>"><?php esc_html_e( 'View', 'smart-client-contact-hub' ); ?></a></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<p><a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=scch-leads' ) ); ?>"><?php esc_html_e( 'View all leads', 'smart-client-contact-hub' ); ?></a></p>
	<?php endif; ?>
</div>
