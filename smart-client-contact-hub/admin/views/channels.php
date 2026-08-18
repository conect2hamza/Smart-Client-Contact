<?php
/**
 * Contact channels view: add, reorder, and configure the popup buttons.
 *
 * @package SCCH
 * @var \SCCH\Admin\Admin $admin
 */

use SCCH\Admin\Admin;
use SCCH\Channels;

defined( 'ABSPATH' ) || exit;

$scch_rows  = Channels::raw();
$scch_types = Channels::types();

/**
 * Render one channel row. Used for the stored rows and, with a placeholder
 * index, for the template the "Add channel" button clones.
 *
 * @param array  $row   Channel row.
 * @param string $index Array index for the field names.
 */
$scch_render_row = static function ( array $row, string $index ) use ( $scch_types ): void {
	$type = $row['type'];
	$def  = $scch_types[ $type ] ?? $scch_types['link'];
	$name = 'channels[' . $index . ']';
	$uid  = 'scch-ch-' . preg_replace( '/[^a-zA-Z0-9]/', '', $index );
	?>
	<div class="scch-channel-row<?php echo empty( $row['enabled'] ) ? ' is-off' : ''; ?>" data-type="<?php echo esc_attr( $type ); ?>">
		<div class="scch-channel-row__bar">
			<span class="scch-channel-row__handle">
				<button type="button" class="button button-small scch-move-up" aria-label="<?php esc_attr_e( 'Move up', 'smart-client-contact-hub' ); ?>">&uarr;</button>
				<button type="button" class="button button-small scch-move-down" aria-label="<?php esc_attr_e( 'Move down', 'smart-client-contact-hub' ); ?>">&darr;</button>
			</span>

			<strong class="scch-channel-row__type"><?php echo esc_html( $def['label'] ); ?></strong>

			<label class="scch-channel-row__toggle">
				<input type="hidden" name="<?php echo esc_attr( $name ); ?>[enabled]" value="0" />
				<input type="checkbox" class="scch-channel-enabled" name="<?php echo esc_attr( $name ); ?>[enabled]" value="1" <?php checked( (int) $row['enabled'], 1 ); ?> />
				<span><?php esc_html_e( 'Show this button', 'smart-client-contact-hub' ); ?></span>
			</label>

			<button type="button" class="button-link delete scch-remove-channel"><?php esc_html_e( 'Remove', 'smart-client-contact-hub' ); ?></button>
		</div>

		<input type="hidden" name="<?php echo esc_attr( $name ); ?>[type]" value="<?php echo esc_attr( $type ); ?>" />
		<input type="hidden" name="<?php echo esc_attr( $name ); ?>[id]" value="<?php echo esc_attr( $row['id'] ); ?>" />

		<?php if ( ! empty( $def['help'] ) ) : ?>
			<p class="description scch-channel-row__help"><?php echo esc_html( $def['help'] ); ?></p>
		<?php endif; ?>

		<div class="scch-channel-row__grid">
			<p class="scch-f">
				<label for="<?php echo esc_attr( $uid ); ?>-label"><?php esc_html_e( 'Button text', 'smart-client-contact-hub' ); ?></label>
				<input type="text" id="<?php echo esc_attr( $uid ); ?>-label" name="<?php echo esc_attr( $name ); ?>[label]" value="<?php echo esc_attr( $row['label'] ); ?>" />
			</p>

			<p class="scch-f">
				<label for="<?php echo esc_attr( $uid ); ?>-desc"><?php esc_html_e( 'Small text under it (optional)', 'smart-client-contact-hub' ); ?></label>
				<input type="text" id="<?php echo esc_attr( $uid ); ?>-desc" name="<?php echo esc_attr( $name ); ?>[description]" value="<?php echo esc_attr( $row['description'] ); ?>" placeholder="<?php esc_attr_e( 'Replies in a few minutes', 'smart-client-contact-hub' ); ?>" />
			</p>

			<?php if ( ! empty( $def['value'] ) ) : ?>
				<p class="scch-f">
					<label for="<?php echo esc_attr( $uid ); ?>-value"><?php echo esc_html( $def['value']['label'] ); ?></label>
					<input type="text" id="<?php echo esc_attr( $uid ); ?>-value" name="<?php echo esc_attr( $name ); ?>[value]" value="<?php echo esc_attr( $row['value'] ); ?>" placeholder="<?php echo esc_attr( $def['value']['placeholder'] ); ?>" />
				</p>
			<?php else : ?>
				<input type="hidden" name="<?php echo esc_attr( $name ); ?>[value]" value="" />
			<?php endif; ?>

			<?php if ( ! empty( $def['extra'] ) ) : ?>
				<p class="scch-f">
					<label for="<?php echo esc_attr( $uid ); ?>-extra"><?php echo esc_html( $def['extra']['label'] ); ?></label>
					<input type="text" id="<?php echo esc_attr( $uid ); ?>-extra" name="<?php echo esc_attr( $name ); ?>[extra]" value="<?php echo esc_attr( $row['extra'] ); ?>" placeholder="<?php echo esc_attr( $def['extra']['placeholder'] ); ?>" />
				</p>
			<?php else : ?>
				<input type="hidden" name="<?php echo esc_attr( $name ); ?>[extra]" value="" />
			<?php endif; ?>

			<div class="scch-f scch-f--wide">
				<span class="scch-f__label"><?php esc_html_e( 'Icon', 'smart-client-contact-hub' ); ?></span>
				<?php
				$picker_name     = $name . '[icon]';
				$picker_value    = $row['icon'];
				$picker_uid      = $uid;
				$picker_url      = $row['icon_url'];
				$picker_url_name = $name . '[icon_url]';
				include SCCH_PATH . 'admin/views/partials/icon-picker.php';
				?>
			</div>

			<p class="scch-f">
				<label for="<?php echo esc_attr( $uid ); ?>-iconbg"><?php esc_html_e( 'Icon tile color', 'smart-client-contact-hub' ); ?></label>
				<input type="text" class="scch-color" id="<?php echo esc_attr( $uid ); ?>-iconbg" name="<?php echo esc_attr( $name ); ?>[icon_bg]" value="<?php echo esc_attr( $row['icon_bg'] ); ?>" />
			</p>

			<p class="scch-f">
				<label for="<?php echo esc_attr( $uid ); ?>-iconcolor"><?php esc_html_e( 'Icon color', 'smart-client-contact-hub' ); ?></label>
				<input type="text" class="scch-color" id="<?php echo esc_attr( $uid ); ?>-iconcolor" name="<?php echo esc_attr( $name ); ?>[icon_color]" value="<?php echo esc_attr( $row['icon_color'] ); ?>" />
			</p>

			<p class="scch-f">
				<label for="<?php echo esc_attr( $uid ); ?>-textcolor"><?php esc_html_e( 'Text color', 'smart-client-contact-hub' ); ?></label>
				<input type="text" class="scch-color" id="<?php echo esc_attr( $uid ); ?>-textcolor" name="<?php echo esc_attr( $name ); ?>[text_color]" value="<?php echo esc_attr( $row['text_color'] ); ?>" />
			</p>

			<p class="scch-f">
				<label for="<?php echo esc_attr( $uid ); ?>-iconbghover"><?php esc_html_e( 'Icon tile color on hover', 'smart-client-contact-hub' ); ?></label>
				<input type="text" class="scch-color" id="<?php echo esc_attr( $uid ); ?>-iconbghover" name="<?php echo esc_attr( $name ); ?>[icon_bg_hover]" value="<?php echo esc_attr( $row['icon_bg_hover'] ); ?>" />
			</p>

			<p class="scch-f">
				<label for="<?php echo esc_attr( $uid ); ?>-iconcolorhover"><?php esc_html_e( 'Icon color on hover', 'smart-client-contact-hub' ); ?></label>
				<input type="text" class="scch-color" id="<?php echo esc_attr( $uid ); ?>-iconcolorhover" name="<?php echo esc_attr( $name ); ?>[icon_color_hover]" value="<?php echo esc_attr( $row['icon_color_hover'] ); ?>" />
			</p>

			<p class="scch-f">
				<label for="<?php echo esc_attr( $uid ); ?>-textcolorhover"><?php esc_html_e( 'Text color on hover', 'smart-client-contact-hub' ); ?></label>
				<input type="text" class="scch-color" id="<?php echo esc_attr( $uid ); ?>-textcolorhover" name="<?php echo esc_attr( $name ); ?>[text_color_hover]" value="<?php echo esc_attr( $row['text_color_hover'] ); ?>" />
				<span class="description"><?php esc_html_e( 'Leave any hover color empty and that part stays as it is when hovered.', 'smart-client-contact-hub' ); ?></span>
			</p>

			<?php if ( 'form' !== $type ) : ?>
				<p class="scch-f scch-f--check">
					<label>
						<input type="hidden" name="<?php echo esc_attr( $name ); ?>[new_tab]" value="0" />
						<input type="checkbox" name="<?php echo esc_attr( $name ); ?>[new_tab]" value="1" <?php checked( (int) $row['new_tab'], 1 ); ?> />
						<?php esc_html_e( 'Open in a new tab', 'smart-client-contact-hub' ); ?>
					</label>
				</p>
			<?php else : ?>
				<input type="hidden" name="<?php echo esc_attr( $name ); ?>[new_tab]" value="0" />
			<?php endif; ?>
		</div>
	</div>
	<?php
};
?>
<div class="wrap scch-wrap scch-channels">
	<h1><?php esc_html_e( 'Contact Channels', 'smart-client-contact-hub' ); ?></h1>
	<?php Admin::maybe_notice(); ?>

	<p class="description scch-channels__intro">
		<?php esc_html_e( 'These are the buttons visitors see when they open the popup. Add as many as you like, rename any of them, switch one off without deleting it, and drag the order with the arrows. Colors set here apply to that button only.', 'smart-client-contact-hub' ); ?>
	</p>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="scch-dirty-watch">
		<?php wp_nonce_field( 'scch_save_channels' ); ?>
		<input type="hidden" name="action" value="scch_save_channels" />

		<div id="scch-channel-list">
			<?php foreach ( $scch_rows as $scch_i => $scch_row ) : ?>
				<?php $scch_render_row( $scch_row, (string) $scch_i ); ?>
			<?php endforeach; ?>
		</div>

		<p class="scch-channels__add">
			<label class="screen-reader-text" for="scch-new-channel-type"><?php esc_html_e( 'Channel type to add', 'smart-client-contact-hub' ); ?></label>
			<select id="scch-new-channel-type">
				<?php foreach ( $scch_types as $scch_tv => $scch_td ) : ?>
					<option value="<?php echo esc_attr( $scch_tv ); ?>"><?php echo esc_html( $scch_td['label'] ); ?></option>
				<?php endforeach; ?>
			</select>
			<button type="button" class="button" id="scch-add-channel"><?php esc_html_e( '+ Add channel', 'smart-client-contact-hub' ); ?></button>
		</p>

		<div class="scch-savebar is-clean">
			<span class="scch-savebar__note"><?php esc_html_e( 'All changes saved', 'smart-client-contact-hub' ); ?></span>
			<?php submit_button( __( 'Save Channels', 'smart-client-contact-hub' ), 'primary', 'submit', false ); ?>
		</div>
	</form>

	<?php
	// One hidden template per type, cloned by the Add button. Rendering them
	// server-side keeps the field markup in one place instead of duplicating
	// it in JavaScript.
	foreach ( $scch_types as $scch_tv => $scch_td ) :
		?>
		<script type="text/html" class="scch-channel-template" data-type="<?php echo esc_attr( $scch_tv ); ?>">
			<?php $scch_render_row( Channels::blank( $scch_tv ), '__INDEX__' ); ?>
		</script>
	<?php endforeach; ?>
</div>
