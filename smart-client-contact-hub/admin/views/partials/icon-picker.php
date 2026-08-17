<?php
/**
 * Visual icon picker.
 *
 * Radio inputs styled as a grid of swatches, so it works without JavaScript
 * and is keyboard navigable as a normal radio group. Shared by the launcher
 * control on Appearance and by every row on Channels.
 *
 * Required variables:
 *
 * @var string $picker_name    Field name, e.g. "scch_appearance[icon]".
 * @var string $picker_value   Currently selected icon id.
 * @var string $picker_uid     Unique id fragment for input ids.
 * @var string $picker_url     Current custom icon URL.
 * @var string $picker_url_name Field name for the custom icon URL.
 *
 * @package SCCH
 */

use SCCH\Icons;

defined( 'ABSPATH' ) || exit;

$scch_groups  = Icons::groups();
$scch_grouped = Icons::grouped();
$scch_custom  = 'custom' === $picker_value;
?>
<div class="scch-iconpick" data-uid="<?php echo esc_attr( $picker_uid ); ?>">
	<?php foreach ( $scch_grouped as $scch_g => $scch_icons ) : ?>
		<?php if ( empty( $scch_icons ) ) { continue; } ?>
		<p class="scch-iconpick__group"><?php echo esc_html( $scch_groups[ $scch_g ] ); ?></p>
		<div class="scch-iconpick__grid" role="group" aria-label="<?php echo esc_attr( $scch_groups[ $scch_g ] ); ?>">
			<?php foreach ( $scch_icons as $scch_id => $scch_icon ) : ?>
				<?php $scch_iid = $picker_uid . '-icon-' . $scch_id; ?>
				<input type="radio" class="scch-iconpick__radio" id="<?php echo esc_attr( $scch_iid ); ?>"
					name="<?php echo esc_attr( $picker_name ); ?>" value="<?php echo esc_attr( $scch_id ); ?>"
					<?php checked( $picker_value, $scch_id ); ?> />
				<label class="scch-iconpick__opt" for="<?php echo esc_attr( $scch_iid ); ?>" title="<?php echo esc_attr( $scch_icon['label'] ); ?>">
					<?php echo Icons::svg( $scch_id ); // phpcs:ignore WordPress.Security.EscapeOutput -- static inline SVG from the library. ?>
					<span class="screen-reader-text"><?php echo esc_html( $scch_icon['label'] ); ?></span>
				</label>
			<?php endforeach; ?>
		</div>
	<?php endforeach; ?>

	<p class="scch-iconpick__group"><?php esc_html_e( 'Your own image', 'smart-client-contact-hub' ); ?></p>
	<div class="scch-iconpick__grid">
		<input type="radio" class="scch-iconpick__radio scch-iconpick__radio--custom" id="<?php echo esc_attr( $picker_uid ); ?>-icon-custom"
			name="<?php echo esc_attr( $picker_name ); ?>" value="custom" <?php checked( $picker_value, 'custom' ); ?> />
		<label class="scch-iconpick__opt scch-iconpick__opt--custom" for="<?php echo esc_attr( $picker_uid ); ?>-icon-custom" title="<?php esc_attr_e( 'Custom upload', 'smart-client-contact-hub' ); ?>">
			<?php if ( '' !== $picker_url ) : ?>
				<img src="<?php echo esc_url( $picker_url ); ?>" alt="" />
			<?php else : ?>
				<span aria-hidden="true">+</span>
			<?php endif; ?>
			<span class="screen-reader-text"><?php esc_html_e( 'Custom upload', 'smart-client-contact-hub' ); ?></span>
		</label>
	</div>

	<p class="scch-iconpick__custom"<?php echo $scch_custom ? '' : ' hidden'; ?>>
		<label class="screen-reader-text" for="<?php echo esc_attr( $picker_uid ); ?>-iconurl"><?php esc_html_e( 'Custom icon image URL', 'smart-client-contact-hub' ); ?></label>
		<span class="scch-f__inline">
			<input type="url" id="<?php echo esc_attr( $picker_uid ); ?>-iconurl"
				name="<?php echo esc_attr( $picker_url_name ); ?>"
				value="<?php echo esc_attr( $picker_url ); ?>"
				placeholder="<?php esc_attr_e( 'https://…/icon.png', 'smart-client-contact-hub' ); ?>" />
			<button type="button" class="button scch-media-btn" data-target="#<?php echo esc_attr( $picker_uid ); ?>-iconurl"><?php esc_html_e( 'Choose image', 'smart-client-contact-hub' ); ?></button>
		</span>
		<span class="description"><?php esc_html_e( 'SVG or PNG. A square image around 64×64 works best.', 'smart-client-contact-hub' ); ?></span>
	</p>
</div>
