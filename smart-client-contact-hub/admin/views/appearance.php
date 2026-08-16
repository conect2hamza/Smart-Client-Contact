<?php
/**
 * Appearance settings view.
 *
 * @package SCCH
 * @var \SCCH\Admin\Admin $admin
 */

use SCCH\Admin\Admin;
use SCCH\Settings;

defined( 'ABSPATH' ) || exit;

$scch_a = Settings::group( 'scch_appearance' );
?>
<div class="wrap scch-wrap">
	<h1><?php esc_html_e( 'Appearance', 'smart-client-contact-hub' ); ?></h1>
	<?php Admin::maybe_notice(); ?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'scch_save_settings' ); ?>
		<input type="hidden" name="action" value="scch_save_settings" />
		<input type="hidden" name="scch_group" value="scch_appearance" />

		<h2><?php esc_html_e( 'Floating Button', 'smart-client-contact-hub' ); ?></h2>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="scch-position"><?php esc_html_e( 'Position', 'smart-client-contact-hub' ); ?></label></th>
				<td>
					<select id="scch-position" name="scch_appearance[position]">
						<option value="bottom-right" <?php selected( $scch_a['position'], 'bottom-right' ); ?>><?php esc_html_e( 'Bottom Right', 'smart-client-contact-hub' ); ?></option>
						<option value="bottom-left" <?php selected( $scch_a['position'], 'bottom-left' ); ?>><?php esc_html_e( 'Bottom Left', 'smart-client-contact-hub' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="scch-animation"><?php esc_html_e( 'Animation', 'smart-client-contact-hub' ); ?></label></th>
				<td>
					<select id="scch-animation" name="scch_appearance[animation]">
						<?php foreach ( array( 'fade', 'scale', 'bounce', 'pulse', 'none' ) as $scch_anim ) : ?>
							<option value="<?php echo esc_attr( $scch_anim ); ?>" <?php selected( $scch_a['animation'], $scch_anim ); ?>><?php echo esc_html( ucfirst( $scch_anim ) ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="scch-icon"><?php esc_html_e( 'Icon', 'smart-client-contact-hub' ); ?></label></th>
				<td>
					<select id="scch-icon" name="scch_appearance[icon]">
						<option value="chat-bubble" <?php selected( $scch_a['icon'], 'chat-bubble' ); ?>><?php esc_html_e( 'Message Bubble (default)', 'smart-client-contact-hub' ); ?></option>
						<option value="phone" <?php selected( $scch_a['icon'], 'phone' ); ?>><?php esc_html_e( 'Phone', 'smart-client-contact-hub' ); ?></option>
						<option value="mail" <?php selected( $scch_a['icon'], 'mail' ); ?>><?php esc_html_e( 'Envelope', 'smart-client-contact-hub' ); ?></option>
						<option value="headset" <?php selected( $scch_a['icon'], 'headset' ); ?>><?php esc_html_e( 'Headset', 'smart-client-contact-hub' ); ?></option>
						<option value="custom" <?php selected( $scch_a['icon'], 'custom' ); ?>><?php esc_html_e( 'Custom SVG / image upload', 'smart-client-contact-hub' ); ?></option>
					</select>
					<p class="description"><?php esc_html_e( 'Built-in icons are inline SVG — no icon font is loaded, keeping the frontend dependency-free and fast.', 'smart-client-contact-hub' ); ?></p>
				</td>
			</tr>
			<tr class="scch-custom-icon-row">
				<th scope="row"><label for="scch-custom-icon"><?php esc_html_e( 'Custom Icon (SVG/PNG)', 'smart-client-contact-hub' ); ?></label></th>
				<td>
					<input type="url" class="regular-text" id="scch-custom-icon" name="scch_appearance[custom_icon_url]" value="<?php echo esc_attr( $scch_a['custom_icon_url'] ); ?>" />
					<button type="button" class="button scch-media-btn" data-target="#scch-custom-icon"><?php esc_html_e( 'Choose from Media Library', 'smart-client-contact-hub' ); ?></button>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="scch-icon-size"><?php esc_html_e( 'Icon Size (px)', 'smart-client-contact-hub' ); ?></label></th>
				<td><input type="number" min="12" max="96" id="scch-icon-size" name="scch_appearance[icon_size]" value="<?php echo esc_attr( $scch_a['icon_size'] ); ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="scch-button-size"><?php esc_html_e( 'Button Size (px)', 'smart-client-contact-hub' ); ?></label></th>
				<td><input type="number" min="36" max="140" id="scch-button-size" name="scch_appearance[button_size]" value="<?php echo esc_attr( $scch_a['button_size'] ); ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="scch-button-margin"><?php esc_html_e( 'Edge Margin (px)', 'smart-client-contact-hub' ); ?></label></th>
				<td><input type="number" min="0" max="120" id="scch-button-margin" name="scch_appearance[button_margin]" value="<?php echo esc_attr( $scch_a['button_margin'] ); ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="scch-border-radius"><?php esc_html_e( 'Border Radius (%)', 'smart-client-contact-hub' ); ?></label></th>
				<td><input type="number" min="0" max="100" id="scch-border-radius" name="scch_appearance[border_radius]" value="<?php echo esc_attr( $scch_a['border_radius'] ); ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="scch-border-width"><?php esc_html_e( 'Border Width (px)', 'smart-client-contact-hub' ); ?></label></th>
				<td>
					<input type="number" min="0" max="12" id="scch-border-width" name="scch_appearance[border_width]" value="<?php echo esc_attr( $scch_a['border_width'] ); ?>" />
					<input type="text" class="scch-color" name="scch_appearance[border_color]" value="<?php echo esc_attr( $scch_a['border_color'] ); ?>" data-default-color="transparent" />
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Shadow', 'smart-client-contact-hub' ); ?></th>
				<td><label><input type="checkbox" name="scch_appearance[shadow]" value="1" <?php checked( $scch_a['shadow'], 1 ); ?> /> <?php esc_html_e( 'Show drop shadow under the button and popup', 'smart-client-contact-hub' ); ?></label></td>
			</tr>
		</table>

		<h2><?php esc_html_e( 'Colors & Branding', 'smart-client-contact-hub' ); ?></h2>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><?php esc_html_e( 'Primary Color', 'smart-client-contact-hub' ); ?></th>
				<td><input type="text" class="scch-color" name="scch_appearance[primary_color]" value="<?php echo esc_attr( $scch_a['primary_color'] ); ?>" data-default-color="#2563eb" /></td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Secondary Color', 'smart-client-contact-hub' ); ?></th>
				<td><input type="text" class="scch-color" name="scch_appearance[secondary_color]" value="<?php echo esc_attr( $scch_a['secondary_color'] ); ?>" data-default-color="#7c3aed" /></td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Background Style', 'smart-client-contact-hub' ); ?></th>
				<td>
					<label><input type="checkbox" name="scch_appearance[use_gradient]" value="1" <?php checked( $scch_a['use_gradient'], 1 ); ?> /> <?php esc_html_e( 'Use gradient (primary → secondary). Uncheck for solid background:', 'smart-client-contact-hub' ); ?></label>
					<input type="text" class="scch-color" name="scch_appearance[button_bg]" value="<?php echo esc_attr( $scch_a['button_bg'] ); ?>" data-default-color="#2563eb" />
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Icon Color', 'smart-client-contact-hub' ); ?></th>
				<td><input type="text" class="scch-color" name="scch_appearance[icon_color]" value="<?php echo esc_attr( $scch_a['icon_color'] ); ?>" data-default-color="#ffffff" /></td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Panel Text Color', 'smart-client-contact-hub' ); ?></th>
				<td>
					<input type="text" class="scch-color" name="scch_appearance[text_color]" value="<?php echo esc_attr( $scch_a['text_color'] ); ?>" data-default-color="#111827" />
					<p class="description"><?php esc_html_e( 'Body text inside the popup: field labels, messages, success text.', 'smart-client-contact-hub' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Heading Background Color', 'smart-client-contact-hub' ); ?></th>
				<td>
					<input type="text" class="scch-color" name="scch_appearance[heading_bg]" value="<?php echo esc_attr( $scch_a['heading_bg'] ); ?>" />
					<p class="description"><?php esc_html_e( 'Background of the popup header. Clear the color to use the primary → secondary gradient.', 'smart-client-contact-hub' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Heading Text Color', 'smart-client-contact-hub' ); ?></th>
				<td><input type="text" class="scch-color" name="scch_appearance[heading_text]" value="<?php echo esc_attr( $scch_a['heading_text'] ); ?>" data-default-color="#ffffff" /></td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Submit Button Background Color', 'smart-client-contact-hub' ); ?></th>
				<td>
					<input type="text" class="scch-color" name="scch_appearance[submit_bg]" value="<?php echo esc_attr( $scch_a['submit_bg'] ); ?>" />
					<p class="description"><?php esc_html_e( 'Clear the color to use the primary → secondary gradient.', 'smart-client-contact-hub' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Submit Button Text Color', 'smart-client-contact-hub' ); ?></th>
				<td><input type="text" class="scch-color" name="scch_appearance[submit_text]" value="<?php echo esc_attr( $scch_a['submit_text'] ); ?>" data-default-color="#ffffff" /></td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Submit Button Hover Background Color', 'smart-client-contact-hub' ); ?></th>
				<td>
					<input type="text" class="scch-color" name="scch_appearance[submit_hover_bg]" value="<?php echo esc_attr( $scch_a['submit_hover_bg'] ); ?>" />
					<p class="description"><?php esc_html_e( 'Clear the color to keep the default hover effect (slight brightness increase).', 'smart-client-contact-hub' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Submit Button Hover Text Color', 'smart-client-contact-hub' ); ?></th>
				<td>
					<input type="text" class="scch-color" name="scch_appearance[submit_hover_text]" value="<?php echo esc_attr( $scch_a['submit_hover_text'] ); ?>" />
					<p class="description"><?php esc_html_e( 'Clear the color to keep the normal text color on hover.', 'smart-client-contact-hub' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Dark Mode Text Color', 'smart-client-contact-hub' ); ?></th>
				<td>
					<input type="text" class="scch-color" name="scch_appearance[dark_text_color]" value="<?php echo esc_attr( $scch_a['dark_text_color'] ); ?>" />
					<p class="description"><?php esc_html_e( 'Overall popup text color used when the visitor\'s device is in dark mode. Clear the color to keep the built-in dark palette. Does not affect the heading or submit button, which have their own colors.', 'smart-client-contact-hub' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="scch-font"><?php esc_html_e( 'Typography (font family)', 'smart-client-contact-hub' ); ?></label></th>
				<td>
					<input type="text" class="regular-text" id="scch-font" name="scch_appearance[font_family]" value="<?php echo esc_attr( $scch_a['font_family'] ); ?>" />
					<p class="description"><?php esc_html_e( 'Use "inherit" to match your theme, or any CSS font stack.', 'smart-client-contact-hub' ); ?></p>
				</td>
			</tr>
		</table>

		<h2><?php esc_html_e( 'Popup', 'smart-client-contact-hub' ); ?></h2>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="scch-popup-width"><?php esc_html_e( 'Popup Width (px)', 'smart-client-contact-hub' ); ?></label></th>
				<td><input type="number" min="280" max="640" id="scch-popup-width" name="scch_appearance[popup_width]" value="<?php echo esc_attr( $scch_a['popup_width'] ); ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="scch-popup-radius"><?php esc_html_e( 'Popup Corner Radius (px)', 'smart-client-contact-hub' ); ?></label></th>
				<td><input type="number" min="0" max="48" id="scch-popup-radius" name="scch_appearance[popup_radius]" value="<?php echo esc_attr( $scch_a['popup_radius'] ); ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Overlay', 'smart-client-contact-hub' ); ?></th>
				<td><label><input type="checkbox" name="scch_appearance[overlay]" value="1" <?php checked( $scch_a['overlay'], 1 ); ?> /> <?php esc_html_e( 'Dim the page behind the popup on mobile', 'smart-client-contact-hub' ); ?></label></td>
			</tr>
			<tr>
				<th scope="row"><label for="scch-dark"><?php esc_html_e( 'Dark Mode', 'smart-client-contact-hub' ); ?></label></th>
				<td>
					<select id="scch-dark" name="scch_appearance[dark_mode]">
						<option value="auto" <?php selected( $scch_a['dark_mode'], 'auto' ); ?>><?php esc_html_e( 'Auto (follow visitor preference)', 'smart-client-contact-hub' ); ?></option>
						<option value="light" <?php selected( $scch_a['dark_mode'], 'light' ); ?>><?php esc_html_e( 'Always Light', 'smart-client-contact-hub' ); ?></option>
						<option value="dark" <?php selected( $scch_a['dark_mode'], 'dark' ); ?>><?php esc_html_e( 'Always Dark', 'smart-client-contact-hub' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="scch-zindex"><?php esc_html_e( 'Z-Index', 'smart-client-contact-hub' ); ?></label></th>
				<td><input type="number" min="1" id="scch-zindex" name="scch_appearance[z_index]" value="<?php echo esc_attr( $scch_a['z_index'] ); ?>" /></td>
			</tr>
		</table>

		<?php submit_button( __( 'Save Appearance', 'smart-client-contact-hub' ) ); ?>
	</form>
</div>
