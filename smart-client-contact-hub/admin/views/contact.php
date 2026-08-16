<?php
/**
 * Contact settings view: phone, SMS, panel wording, channels.
 *
 * @package SCCH
 * @var \SCCH\Admin\Admin $admin
 */

use SCCH\Admin\Admin;
use SCCH\Settings;

defined( 'ABSPATH' ) || exit;

$scch_c = Settings::group( 'scch_contact' );
?>
<div class="wrap scch-wrap">
	<h1><?php esc_html_e( 'Contact Settings', 'smart-client-contact-hub' ); ?></h1>
	<?php Admin::maybe_notice(); ?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'scch_save_settings' ); ?>
		<input type="hidden" name="action" value="scch_save_settings" />
		<input type="hidden" name="scch_group" value="scch_contact" />

		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="scch-phone"><?php esc_html_e( 'Call Us — phone number', 'smart-client-contact-hub' ); ?></label></th>
				<td>
					<input type="text" class="regular-text" id="scch-phone" name="scch_contact[phone_number]" value="<?php echo esc_attr( $scch_c['phone_number'] ); ?>" placeholder="+15550001234" />
					<p class="description"><?php esc_html_e( 'Launched via tel: when a visitor taps "Call Us". Use international format for best results.', 'smart-client-contact-hub' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="scch-sms"><?php esc_html_e( 'Text Us — mobile number', 'smart-client-contact-hub' ); ?></label></th>
				<td>
					<input type="text" class="regular-text" id="scch-sms" name="scch_contact[sms_number]" value="<?php echo esc_attr( $scch_c['sms_number'] ); ?>" placeholder="+15550001234" />
					<p class="description"><?php esc_html_e( 'Launched via sms: when a visitor taps "Text Us".', 'smart-client-contact-hub' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="scch-sms-body"><?php esc_html_e( 'Pre-filled SMS text (optional)', 'smart-client-contact-hub' ); ?></label></th>
				<td><input type="text" class="regular-text" id="scch-sms-body" name="scch_contact[sms_body]" value="<?php echo esc_attr( $scch_c['sms_body'] ); ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Enabled channels', 'smart-client-contact-hub' ); ?></th>
				<td>
					<fieldset>
						<label><input type="checkbox" name="scch_contact[channels][]" value="form" <?php checked( in_array( 'form', $scch_c['channels'], true ) ); ?> /> <?php esc_html_e( 'Get Your Strategy (lead form)', 'smart-client-contact-hub' ); ?></label><br />
						<label><input type="checkbox" name="scch_contact[channels][]" value="call" <?php checked( in_array( 'call', $scch_c['channels'], true ) ); ?> /> <?php esc_html_e( 'Call Us', 'smart-client-contact-hub' ); ?></label><br />
						<label><input type="checkbox" name="scch_contact[channels][]" value="sms" <?php checked( in_array( 'sms', $scch_c['channels'], true ) ); ?> /> <?php esc_html_e( 'Text Us', 'smart-client-contact-hub' ); ?></label>
						<p class="description"><?php esc_html_e( 'Developers can register additional channels (e.g. WhatsApp, Telegram) with the scch_channels filter — no core changes needed.', 'smart-client-contact-hub' ); ?></p>
					</fieldset>
				</td>
			</tr>
		</table>

		<h2><?php esc_html_e( 'Panel Wording', 'smart-client-contact-hub' ); ?></h2>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="scch-panel-title"><?php esc_html_e( 'Panel title', 'smart-client-contact-hub' ); ?></label></th>
				<td><input type="text" class="regular-text" id="scch-panel-title" name="scch_contact[panel_title]" value="<?php echo esc_attr( $scch_c['panel_title'] ); ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="scch-panel-intro"><?php esc_html_e( 'Panel intro', 'smart-client-contact-hub' ); ?></label></th>
				<td><input type="text" class="regular-text" id="scch-panel-intro" name="scch_contact[panel_intro]" value="<?php echo esc_attr( $scch_c['panel_intro'] ); ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="scch-cta1"><?php esc_html_e( 'Option 1 label', 'smart-client-contact-hub' ); ?></label></th>
				<td><input type="text" class="regular-text" id="scch-cta1" name="scch_contact[cta_strategy]" value="<?php echo esc_attr( $scch_c['cta_strategy'] ); ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="scch-cta2"><?php esc_html_e( 'Option 2 label', 'smart-client-contact-hub' ); ?></label></th>
				<td><input type="text" class="regular-text" id="scch-cta2" name="scch_contact[cta_call]" value="<?php echo esc_attr( $scch_c['cta_call'] ); ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="scch-cta3"><?php esc_html_e( 'Option 3 label', 'smart-client-contact-hub' ); ?></label></th>
				<td><input type="text" class="regular-text" id="scch-cta3" name="scch_contact[cta_text]" value="<?php echo esc_attr( $scch_c['cta_text'] ); ?>" /></td>
			</tr>
		</table>

		<?php submit_button( __( 'Save Contact Settings', 'smart-client-contact-hub' ) ); ?>
	</form>
</div>
