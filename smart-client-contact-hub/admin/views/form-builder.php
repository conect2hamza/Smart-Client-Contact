<?php
/**
 * Form builder view: enable/label/placeholder/required/reorder per field.
 *
 * @package SCCH
 * @var \SCCH\Admin\Admin $admin
 */

use SCCH\Admin\Admin;
use SCCH\Settings;

defined( 'ABSPATH' ) || exit;

$scch_form   = Settings::group( 'scch_form' );
$scch_fields = $scch_form['fields'];
uasort( $scch_fields, static fn( $a, $b ) => (int) ( $a['order'] ?? 0 ) <=> (int) ( $b['order'] ?? 0 ) );
?>
<div class="wrap scch-wrap">
	<h1><?php esc_html_e( 'Form Builder', 'smart-client-contact-hub' ); ?></h1>
	<?php Admin::maybe_notice(); ?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'scch_save_settings' ); ?>
		<input type="hidden" name="action" value="scch_save_settings" />
		<input type="hidden" name="scch_group" value="scch_form" />

		<h2><?php esc_html_e( 'Fields', 'smart-client-contact-hub' ); ?></h2>
		<p class="description"><?php esc_html_e( 'Use the arrows to reorder. The email field always stays enabled and required because confirmations and Reply-To depend on it.', 'smart-client-contact-hub' ); ?></p>

		<table class="widefat striped scch-table" id="scch-fields-table">
			<thead>
				<tr>
					<th class="scch-col-order"><?php esc_html_e( 'Order', 'smart-client-contact-hub' ); ?></th>
					<th><?php esc_html_e( 'Field', 'smart-client-contact-hub' ); ?></th>
					<th><?php esc_html_e( 'Enabled', 'smart-client-contact-hub' ); ?></th>
					<th><?php esc_html_e( 'Required', 'smart-client-contact-hub' ); ?></th>
					<th><?php esc_html_e( 'Label', 'smart-client-contact-hub' ); ?></th>
					<th><?php esc_html_e( 'Hide Label', 'smart-client-contact-hub' ); ?></th>
					<th><?php esc_html_e( 'Placeholder', 'smart-client-contact-hub' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $scch_fields as $scch_key => $scch_field ) : ?>
					<tr>
						<td class="scch-col-order">
							<button type="button" class="button button-small scch-move-up" aria-label="<?php esc_attr_e( 'Move up', 'smart-client-contact-hub' ); ?>">↑</button>
							<button type="button" class="button button-small scch-move-down" aria-label="<?php esc_attr_e( 'Move down', 'smart-client-contact-hub' ); ?>">↓</button>
						</td>
						<td><strong><?php echo esc_html( ucfirst( $scch_key ) ); ?></strong></td>
						<td><input type="checkbox" name="scch_form[fields][<?php echo esc_attr( $scch_key ); ?>][enabled]" value="1" <?php checked( $scch_field['enabled'], 1 ); ?> <?php disabled( 'email' === $scch_key ); ?> />
							<?php if ( 'email' === $scch_key ) : ?><input type="hidden" name="scch_form[fields][email][enabled]" value="1" /><?php endif; ?>
						</td>
						<td><input type="checkbox" name="scch_form[fields][<?php echo esc_attr( $scch_key ); ?>][required]" value="1" <?php checked( $scch_field['required'], 1 ); ?> <?php disabled( 'email' === $scch_key ); ?> />
							<?php if ( 'email' === $scch_key ) : ?><input type="hidden" name="scch_form[fields][email][required]" value="1" /><?php endif; ?>
						</td>
						<td><input type="text" name="scch_form[fields][<?php echo esc_attr( $scch_key ); ?>][label]" value="<?php echo esc_attr( $scch_field['label'] ); ?>" /></td>
						<td><input type="checkbox" name="scch_form[fields][<?php echo esc_attr( $scch_key ); ?>][hide_label]" value="1" <?php checked( ! empty( $scch_field['hide_label'] ) ); ?> aria-label="<?php esc_attr_e( 'Hide this label on the frontend form', 'smart-client-contact-hub' ); ?>" /></td>
						<td><input type="text" name="scch_form[fields][<?php echo esc_attr( $scch_key ); ?>][placeholder]" value="<?php echo esc_attr( $scch_field['placeholder'] ); ?>" /></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<h2><?php esc_html_e( 'Form Text & Behavior', 'smart-client-contact-hub' ); ?></h2>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="scch-form-title"><?php esc_html_e( 'Form title', 'smart-client-contact-hub' ); ?></label></th>
				<td><input type="text" class="regular-text" id="scch-form-title" name="scch_form[form_title]" value="<?php echo esc_attr( $scch_form['form_title'] ); ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="scch-submit-label"><?php esc_html_e( 'Submit button label', 'smart-client-contact-hub' ); ?></label></th>
				<td><input type="text" class="regular-text" id="scch-submit-label" name="scch_form[submit_label]" value="<?php echo esc_attr( $scch_form['submit_label'] ); ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="scch-success"><?php esc_html_e( 'Success message', 'smart-client-contact-hub' ); ?></label></th>
				<td><input type="text" class="large-text" id="scch-success" name="scch_form[success_message]" value="<?php echo esc_attr( $scch_form['success_message'] ); ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="scch-error"><?php esc_html_e( 'Error message', 'smart-client-contact-hub' ); ?></label></th>
				<td><input type="text" class="large-text" id="scch-error" name="scch_form[error_message]" value="<?php echo esc_attr( $scch_form['error_message'] ); ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="scch-redirect"><?php esc_html_e( 'Redirect URL after success (optional)', 'smart-client-contact-hub' ); ?></label></th>
				<td>
					<input type="url" class="regular-text" id="scch-redirect" name="scch_form[redirect_url]" value="<?php echo esc_attr( $scch_form['redirect_url'] ); ?>" placeholder="<?php echo esc_attr( home_url( '/thank-you/' ) ); ?>" />
					<p class="description"><?php esc_html_e( 'Leave empty to show the success message inline instead of redirecting.', 'smart-client-contact-hub' ); ?></p>
				</td>
			</tr>
		</table>

		<?php submit_button( __( 'Save Form', 'smart-client-contact-hub' ) ); ?>
	</form>
</div>
