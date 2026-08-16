<?php
/**
 * Services manager view: create / edit / delete / sort.
 *
 * @package SCCH
 * @var \SCCH\Admin\Admin $admin
 */

use SCCH\Admin\Admin;
use SCCH\Settings;

defined( 'ABSPATH' ) || exit;

$scch_services = Settings::services();
?>
<div class="wrap scch-wrap">
	<h1><?php esc_html_e( 'Services', 'smart-client-contact-hub' ); ?></h1>
	<?php Admin::maybe_notice(); ?>
	<p class="description"><?php esc_html_e( 'These options populate the "Select Service" dropdown on the lead form. Reorder with the arrows; the saved order is the display order.', 'smart-client-contact-hub' ); ?></p>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'scch_save_services' ); ?>
		<input type="hidden" name="action" value="scch_save_services" />

		<table class="widefat striped scch-table" id="scch-services-table">
			<thead>
				<tr>
					<th class="scch-col-order"><?php esc_html_e( 'Order', 'smart-client-contact-hub' ); ?></th>
					<th><?php esc_html_e( 'Service Name', 'smart-client-contact-hub' ); ?></th>
					<th class="scch-col-actions"><?php esc_html_e( 'Actions', 'smart-client-contact-hub' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $scch_services as $scch_i => $scch_service ) : ?>
					<tr>
						<td class="scch-col-order">
							<button type="button" class="button button-small scch-move-up" aria-label="<?php esc_attr_e( 'Move up', 'smart-client-contact-hub' ); ?>">↑</button>
							<button type="button" class="button button-small scch-move-down" aria-label="<?php esc_attr_e( 'Move down', 'smart-client-contact-hub' ); ?>">↓</button>
						</td>
						<td>
							<input type="hidden" name="services[<?php echo (int) $scch_i; ?>][id]" value="<?php echo esc_attr( $scch_service['id'] ); ?>" />
							<input type="text" class="regular-text" name="services[<?php echo (int) $scch_i; ?>][label]" value="<?php echo esc_attr( $scch_service['label'] ); ?>" required />
						</td>
						<td class="scch-col-actions"><button type="button" class="button button-small scch-remove-row"><?php esc_html_e( 'Delete', 'smart-client-contact-hub' ); ?></button></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<p>
			<button type="button" class="button" id="scch-add-service"><?php esc_html_e( '+ Add Service', 'smart-client-contact-hub' ); ?></button>
		</p>

		<?php submit_button( __( 'Save Services', 'smart-client-contact-hub' ) ); ?>
	</form>
</div>
