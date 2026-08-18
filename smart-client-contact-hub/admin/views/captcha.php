<?php
/**
 * CAPTCHA settings view.
 *
 * @package SCCH
 * @var \SCCH\Admin\Admin $admin
 */

use SCCH\Admin\Admin;
use SCCH\Settings;

defined( 'ABSPATH' ) || exit;

$scch_cap = Settings::group( 'scch_captcha' );
?>
<div class="wrap scch-wrap">
	<h1><?php esc_html_e( 'CAPTCHA', 'smart-client-contact-hub' ); ?></h1>
	<?php Admin::maybe_notice(); ?>

	<div class="notice notice-info inline"><p>
		<?php esc_html_e( 'This is a built-in math CAPTCHA — no Google reCAPTCHA, Cloudflare, or any external service. Questions use two numbers between 1 and 9, answers are never negative, the answer is verified server-side, and every wrong attempt generates a brand-new question.', 'smart-client-contact-hub' ); ?>
	</p></div>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="scch-dirty-watch">
		<?php wp_nonce_field( 'scch_save_settings' ); ?>
		<input type="hidden" name="action" value="scch_save_settings" />
		<input type="hidden" name="scch_group" value="scch_captcha" />

		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><?php esc_html_e( 'Enable CAPTCHA', 'smart-client-contact-hub' ); ?></th>
				<td><label><input type="checkbox" name="scch_captcha[enabled]" value="1" <?php checked( $scch_cap['enabled'], 1 ); ?> /> <?php esc_html_e( 'Require the math check on form submission', 'smart-client-contact-hub' ); ?></label></td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Operations', 'smart-client-contact-hub' ); ?></th>
				<td>
					<fieldset>
						<label><input type="checkbox" name="scch_captcha[operations][]" value="add" <?php checked( in_array( 'add', $scch_cap['operations'], true ) ); ?> /> <?php esc_html_e( 'Addition (e.g. 2 + 3 = ?)', 'smart-client-contact-hub' ); ?></label><br />
						<label><input type="checkbox" name="scch_captcha[operations][]" value="subtract" <?php checked( in_array( 'subtract', $scch_cap['operations'], true ) ); ?> /> <?php esc_html_e( 'Subtraction (e.g. 8 − 2 = ?)', 'smart-client-contact-hub' ); ?></label>
					</fieldset>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="scch-cap-label"><?php esc_html_e( 'Field label', 'smart-client-contact-hub' ); ?></label></th>
				<td><input type="text" class="regular-text" id="scch-cap-label" name="scch_captcha[label]" value="<?php echo esc_attr( $scch_cap['label'] ); ?>" /></td>
			</tr>
		</table>

		<h2><?php esc_html_e( 'Spam Rate Limiting', 'smart-client-contact-hub' ); ?></h2>
		<p class="description">
			<?php esc_html_e( 'Limits apply per visitor IP address. If your site sits behind a proxy, load balancer, or CDN that does not forward the original visitor IP to WordPress, all visitors may be limited as a single client. Consult your host or CDN documentation on preserving the real client IP if this affects you.', 'smart-client-contact-hub' ); ?>
		</p>
		<?php $scch_g = Settings::group( 'scch_general' ); ?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="scch-rl-max"><?php esc_html_e( 'Max submissions per window', 'smart-client-contact-hub' ); ?></label></th>
				<td><input type="number" min="1" max="100" id="scch-rl-max" name="scch_general[rate_limit_max]" value="<?php echo esc_attr( $scch_g['rate_limit_max'] ); ?>" form="scch-general-form" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="scch-rl-window"><?php esc_html_e( 'Window (minutes)', 'smart-client-contact-hub' ); ?></label></th>
				<td><input type="number" min="1" max="1440" id="scch-rl-window" name="scch_general[rate_limit_window]" value="<?php echo esc_attr( $scch_g['rate_limit_window'] ); ?>" form="scch-general-form" /></td>
			</tr>
		</table>

		<?php submit_button( __( 'Save CAPTCHA Settings', 'smart-client-contact-hub' ) ); ?>
	</form>

	<form id="scch-general-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="scch-dirty-watch">
		<?php wp_nonce_field( 'scch_save_settings' ); ?>
		<input type="hidden" name="action" value="scch_save_settings" />
		<input type="hidden" name="scch_group" value="scch_general" />
		<input type="hidden" name="scch_general[log_enabled]" value="<?php echo esc_attr( (string) $scch_g['log_enabled'] ); ?>" />
		<?php submit_button( __( 'Save Rate Limiting', 'smart-client-contact-hub' ), 'secondary' ); ?>
	</form>
</div>
