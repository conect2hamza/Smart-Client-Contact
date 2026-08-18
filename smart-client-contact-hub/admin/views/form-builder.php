<?php
/**
 * Form builder: add, edit, delete, reorder and configure every form field.
 *
 * @package SCCH
 * @var \SCCH\Admin\Admin $admin
 */

use SCCH\Admin\Admin;
use SCCH\Form_Fields;
use SCCH\Settings;

defined( 'ABSPATH' ) || exit;

$scch_form   = Settings::group( 'scch_form' );
$scch_fields = Form_Fields::all();
$scch_types  = Form_Fields::types();

/**
 * Render one field card.
 *
 * The same closure renders the stored fields and, with a placeholder index,
 * the hidden templates the "Add field" button clones — so the markup for a
 * field exists in exactly one place.
 *
 * @param array  $field Field definition.
 * @param string $index Array index used in the input names.
 */
$scch_render_field = static function ( array $field, string $index ) use ( $scch_types ): void {
	$type    = (string) $field['type'];
	$def     = $scch_types[ $type ] ?? $scch_types['text'];
	$is_core = ! empty( $field['core'] );
	$key     = (string) $field['key'];
	$name    = 'scch_form[fields][' . $index . ']';
	$uid     = 'scch-fld-' . preg_replace( '/[^a-zA-Z0-9]/', '', $index );
	?>
	<div class="scch-fieldrow<?php echo empty( $field['enabled'] ) ? ' is-off' : ''; ?>" data-type="<?php echo esc_attr( $type ); ?>" data-core="<?php echo $is_core ? '1' : '0'; ?>">
		<div class="scch-fieldrow__bar">
			<span class="scch-fieldrow__handle">
				<button type="button" class="button button-small scch-move-up" aria-label="<?php esc_attr_e( 'Move up', 'smart-client-contact-hub' ); ?>">&uarr;</button>
				<button type="button" class="button button-small scch-move-down" aria-label="<?php esc_attr_e( 'Move down', 'smart-client-contact-hub' ); ?>">&darr;</button>
			</span>

			<strong class="scch-fieldrow__title"><?php echo esc_html( '' !== (string) $field['label'] ? $field['label'] : __( 'Untitled field', 'smart-client-contact-hub' ) ); ?></strong>

			<?php if ( $is_core ) : ?>
				<span class="scch-badge scch-badge--core"><?php esc_html_e( 'Built in', 'smart-client-contact-hub' ); ?></span>
			<?php endif; ?>

			<code class="scch-fieldrow__key"><?php echo esc_html( '' !== $key ? $key : __( 'new', 'smart-client-contact-hub' ) ); ?></code>

			<label class="scch-fieldrow__toggle">
				<input type="hidden" name="<?php echo esc_attr( $name ); ?>[enabled]" value="0" />
				<input type="checkbox" class="scch-field-enabled" name="<?php echo esc_attr( $name ); ?>[enabled]" value="1" <?php checked( (int) $field['enabled'], 1 ); ?> />
				<span><?php esc_html_e( 'Show this field', 'smart-client-contact-hub' ); ?></span>
			</label>

			<?php if ( ! $is_core ) : ?>
				<button type="button" class="button-link delete scch-remove-field"><?php esc_html_e( 'Delete', 'smart-client-contact-hub' ); ?></button>
			<?php endif; ?>
		</div>

		<?php if ( $is_core ) : ?>
			<input type="hidden" name="<?php echo esc_attr( $name ); ?>[type]" value="<?php echo esc_attr( $type ); ?>" />
		<?php else : ?>
			<input type="hidden" class="scch-field-key" name="<?php echo esc_attr( $name ); ?>[key]" value="<?php echo esc_attr( $key ); ?>" />
		<?php endif; ?>

		<div class="scch-fieldrow__grid">
			<p class="scch-f">
				<label for="<?php echo esc_attr( $uid ); ?>-label"><?php esc_html_e( 'Label', 'smart-client-contact-hub' ); ?></label>
				<input type="text" class="scch-field-label" id="<?php echo esc_attr( $uid ); ?>-label" name="<?php echo esc_attr( $name ); ?>[label]" value="<?php echo esc_attr( $field['label'] ); ?>" />
			</p>

			<p class="scch-f">
				<label for="<?php echo esc_attr( $uid ); ?>-type"><?php esc_html_e( 'Field type', 'smart-client-contact-hub' ); ?></label>
				<?php if ( $is_core ) : ?>
					<input type="text" id="<?php echo esc_attr( $uid ); ?>-type" value="<?php echo esc_attr( $def['label'] ); ?>" readonly disabled />
				<?php else : ?>
					<select class="scch-field-type" id="<?php echo esc_attr( $uid ); ?>-type" name="<?php echo esc_attr( $name ); ?>[type]">
						<?php foreach ( $scch_types as $scch_tv => $scch_td ) : ?>
							<option value="<?php echo esc_attr( $scch_tv ); ?>" <?php selected( $scch_tv, $type ); ?>
								data-options="<?php echo empty( $scch_td['options'] ) ? '0' : '1'; ?>"
								data-placeholder="<?php echo empty( $scch_td['placeholder'] ) ? '0' : '1'; ?>">
								<?php echo esc_html( $scch_td['label'] ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				<?php endif; ?>
			</p>

			<p class="scch-f" data-when="placeholder"<?php echo empty( $def['placeholder'] ) ? ' hidden' : ''; ?>>
				<label for="<?php echo esc_attr( $uid ); ?>-ph"><?php esc_html_e( 'Placeholder', 'smart-client-contact-hub' ); ?></label>
				<input type="text" id="<?php echo esc_attr( $uid ); ?>-ph" name="<?php echo esc_attr( $name ); ?>[placeholder]" value="<?php echo esc_attr( $field['placeholder'] ); ?>" />
				<span class="description scch-f__hint" data-when="hidden"<?php echo 'hidden' === $type ? '' : ' hidden'; ?>><?php esc_html_e( 'For a hidden field this is the value that gets submitted.', 'smart-client-contact-hub' ); ?></span>
			</p>

			<p class="scch-f">
				<label for="<?php echo esc_attr( $uid ); ?>-help"><?php esc_html_e( 'Help text under the field', 'smart-client-contact-hub' ); ?></label>
				<input type="text" id="<?php echo esc_attr( $uid ); ?>-help" name="<?php echo esc_attr( $name ); ?>[help]" value="<?php echo esc_attr( $field['help'] ); ?>" />
			</p>

			<p class="scch-f">
				<label for="<?php echo esc_attr( $uid ); ?>-width"><?php esc_html_e( 'Width', 'smart-client-contact-hub' ); ?></label>
				<select id="<?php echo esc_attr( $uid ); ?>-width" name="<?php echo esc_attr( $name ); ?>[width]">
					<option value="full" <?php selected( 'full', $field['width'] ); ?>><?php esc_html_e( 'Full width', 'smart-client-contact-hub' ); ?></option>
					<option value="half" <?php selected( 'half', $field['width'] ); ?>><?php esc_html_e( 'Half width (side by side)', 'smart-client-contact-hub' ); ?></option>
				</select>
			</p>

			<p class="scch-f scch-f--check">
				<label>
					<input type="hidden" name="<?php echo esc_attr( $name ); ?>[required]" value="0" />
					<input type="checkbox" name="<?php echo esc_attr( $name ); ?>[required]" value="1" <?php checked( (int) $field['required'], 1 ); ?> />
					<?php esc_html_e( 'Required', 'smart-client-contact-hub' ); ?>
				</label>
			</p>

			<p class="scch-f scch-f--check">
				<label>
					<input type="hidden" name="<?php echo esc_attr( $name ); ?>[hide_label]" value="0" />
					<input type="checkbox" name="<?php echo esc_attr( $name ); ?>[hide_label]" value="1" <?php checked( (int) $field['hide_label'], 1 ); ?> />
					<?php esc_html_e( 'Hide the label on the form', 'smart-client-contact-hub' ); ?>
				</label>
			</p>

			<?php if ( 'email' === $key ) : ?>
				<div class="scch-f scch-f--wide">
					<p class="description">
						<?php esc_html_e( 'Optional, like any other field. Without an address on a lead the plugin simply skips that lead\'s confirmation email and does not set Reply-To on your notification — everything else, including the notification itself, still works.', 'smart-client-contact-hub' ); ?>
					</p>
				</div>
			<?php endif; ?>

			<?php if ( 'service' === $key ) : ?>
				<div class="scch-f scch-f--wide">
					<p class="description">
						<?php esc_html_e( 'The choices in this dropdown come from the Services screen, so the same list stays in step across the site.', 'smart-client-contact-hub' ); ?>
					</p>
					<input type="hidden" name="<?php echo esc_attr( $name ); ?>[options]" value="" />
				</div>
			<?php else : ?>
				<div class="scch-f scch-f--wide" data-when="options"<?php echo empty( $def['options'] ) ? ' hidden' : ''; ?>>
					<label for="<?php echo esc_attr( $uid ); ?>-options"><?php esc_html_e( 'Choices', 'smart-client-contact-hub' ); ?></label>
					<textarea id="<?php echo esc_attr( $uid ); ?>-options" name="<?php echo esc_attr( $name ); ?>[options]" rows="4" placeholder="<?php echo esc_attr( __( "London\nManchester\nother|Somewhere else", 'smart-client-contact-hub' ) ); ?>"><?php echo esc_textarea( $field['options'] ); ?></textarea>
					<span class="description"><?php esc_html_e( 'One choice per line. Write value|Label to store a short value but show a longer label.', 'smart-client-contact-hub' ); ?></span>
				</div>
			<?php endif; ?>
		</div>
	</div>
	<?php
};
?>
<div class="wrap scch-wrap scch-formbuilder">
	<h1><?php esc_html_e( 'Form Builder', 'smart-client-contact-hub' ); ?></h1>
	<?php Admin::maybe_notice(); ?>

	<p class="description scch-formbuilder__intro">
		<?php esc_html_e( 'Build the form visitors fill in. Add as many fields as you need, rename any of them, switch one off without losing it, and use the arrows to reorder. Every field, including the built-in five, can be made optional or switched off. The five built-in fields have their own database columns, so they can be relabelled and reordered but not deleted or retyped.', 'smart-client-contact-hub' ); ?>
	</p>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="scch-dirty-watch">
		<?php wp_nonce_field( 'scch_save_settings' ); ?>
		<input type="hidden" name="action" value="scch_save_settings" />
		<input type="hidden" name="scch_group" value="scch_form" />

		<h2><?php esc_html_e( 'Fields', 'smart-client-contact-hub' ); ?></h2>

		<div id="scch-field-list">
			<?php foreach ( $scch_fields as $scch_key => $scch_field ) : ?>
				<?php $scch_render_field( $scch_field, (string) $scch_key ); ?>
			<?php endforeach; ?>
		</div>

		<p class="scch-formbuilder__add">
			<label class="screen-reader-text" for="scch-new-field-type"><?php esc_html_e( 'Field type to add', 'smart-client-contact-hub' ); ?></label>
			<select id="scch-new-field-type">
				<?php foreach ( $scch_types as $scch_tv => $scch_td ) : ?>
					<option value="<?php echo esc_attr( $scch_tv ); ?>"><?php echo esc_html( $scch_td['label'] ); ?></option>
				<?php endforeach; ?>
			</select>
			<button type="button" class="button" id="scch-add-field"><?php esc_html_e( '+ Add field', 'smart-client-contact-hub' ); ?></button>
		</p>

		<h2><?php esc_html_e( 'Form Text &amp; Behavior', 'smart-client-contact-hub' ); ?></h2>
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

		<div class="scch-savebar is-clean">
			<span class="scch-savebar__note"><?php esc_html_e( 'All changes saved', 'smart-client-contact-hub' ); ?></span>
			<?php submit_button( __( 'Save Form', 'smart-client-contact-hub' ), 'primary', 'submit', false ); ?>
		</div>
	</form>

	<?php
	// One hidden template per type, cloned by the Add button.
	foreach ( $scch_types as $scch_tv => $scch_td ) :
		?>
		<script type="text/html" class="scch-field-template" data-type="<?php echo esc_attr( $scch_tv ); ?>">
			<?php $scch_render_field( Form_Fields::blank( $scch_tv ), '__INDEX__' ); ?>
		</script>
	<?php endforeach; ?>
</div>
