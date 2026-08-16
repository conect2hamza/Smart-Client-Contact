<?php
/**
 * Single lead detail view.
 *
 * @package SCCH
 * @var \SCCH\Admin\Admin $admin
 * @var object|null       $lead
 */

use SCCH\Admin\Admin;
use SCCH\Lead_Repository;

defined( 'ABSPATH' ) || exit;
?>
<div class="wrap scch-wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Lead Detail', 'smart-client-contact-hub' ); ?></h1>
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=scch-leads' ) ); ?>" class="page-title-action"><?php esc_html_e( 'Back to Leads', 'smart-client-contact-hub' ); ?></a>
	<hr class="wp-header-end" />
	<?php Admin::maybe_notice(); ?>

	<?php if ( ! $lead ) : ?>
		<div class="notice notice-error inline"><p><?php esc_html_e( 'Lead not found. It may have been deleted.', 'smart-client-contact-hub' ); ?></p></div>
	<?php else : ?>
		<div class="scch-detail">
			<table class="widefat striped scch-table">
				<tbody>
					<tr><th scope="row"><?php esc_html_e( 'Lead ID', 'smart-client-contact-hub' ); ?></th><td>#<?php echo (int) $lead->id; ?></td></tr>
					<tr><th scope="row"><?php esc_html_e( 'Name', 'smart-client-contact-hub' ); ?></th><td><?php echo esc_html( $lead->name ); ?></td></tr>
					<tr><th scope="row"><?php esc_html_e( 'Phone', 'smart-client-contact-hub' ); ?></th><td><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $lead->phone ) ); ?>"><?php echo esc_html( $lead->phone ); ?></a></td></tr>
					<tr><th scope="row"><?php esc_html_e( 'Email', 'smart-client-contact-hub' ); ?></th><td><a href="mailto:<?php echo esc_attr( $lead->email ); ?>"><?php echo esc_html( $lead->email ); ?></a></td></tr>
					<tr><th scope="row"><?php esc_html_e( 'Service', 'smart-client-contact-hub' ); ?></th><td><?php echo esc_html( $lead->service ); ?></td></tr>
					<tr><th scope="row"><?php esc_html_e( 'Message', 'smart-client-contact-hub' ); ?></th><td><?php echo wp_kses_post( wpautop( esc_html( (string) $lead->message ) ) ); ?></td></tr>
					<tr><th scope="row"><?php esc_html_e( 'Submitted', 'smart-client-contact-hub' ); ?></th><td><?php echo esc_html( mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $lead->submission_date ) ); ?></td></tr>
					<tr><th scope="row"><?php esc_html_e( 'IP Address', 'smart-client-contact-hub' ); ?></th><td><?php echo esc_html( $lead->ip_address ); ?></td></tr>
					<tr><th scope="row"><?php esc_html_e( 'User Agent', 'smart-client-contact-hub' ); ?></th><td><code class="scch-ua"><?php echo esc_html( $lead->user_agent ); ?></code></td></tr>
				</tbody>
			</table>

			<div class="scch-detail__actions">
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php wp_nonce_field( 'scch_lead_action' ); ?>
					<input type="hidden" name="action" value="scch_lead_action" />
					<input type="hidden" name="task" value="status" />
					<input type="hidden" name="lead_id" value="<?php echo (int) $lead->id; ?>" />
					<label for="scch-lead-status"><?php esc_html_e( 'Status', 'smart-client-contact-hub' ); ?></label>
					<select id="scch-lead-status" name="status">
						<?php foreach ( Lead_Repository::STATUSES as $scch_status ) : ?>
							<option value="<?php echo esc_attr( $scch_status ); ?>" <?php selected( $lead->status, $scch_status ); ?>><?php echo esc_html( ucfirst( $scch_status ) ); ?></option>
						<?php endforeach; ?>
					</select>
					<?php submit_button( __( 'Update Status', 'smart-client-contact-hub' ), 'primary', 'submit', false ); ?>
				</form>

				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="return confirm('<?php echo esc_js( __( 'Delete this lead permanently?', 'smart-client-contact-hub' ) ); ?>');">
					<?php wp_nonce_field( 'scch_lead_action' ); ?>
					<input type="hidden" name="action" value="scch_lead_action" />
					<input type="hidden" name="task" value="delete" />
					<input type="hidden" name="lead_id" value="<?php echo (int) $lead->id; ?>" />
					<?php submit_button( __( 'Delete Lead', 'smart-client-contact-hub' ), 'delete', 'submit', false ); ?>
				</form>
			</div>
		</div>
	<?php endif; ?>
</div>
