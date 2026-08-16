<?php
/**
 * Notifications view: delivery settings, recipients, test email.
 *
 * @package SCCH
 * @var \SCCH\Admin\Admin $admin
 */

use SCCH\Admin\Admin;
use SCCH\Settings;

defined( 'ABSPATH' ) || exit;

$scch_e = Settings::group( 'scch_email' );
$scch_g = Settings::group( 'scch_general' );
?>
<div class="wrap scch-wrap">
	<h1><?php esc_html_e( 'Notifications', 'smart-client-contact-hub' ); ?></h1>
	<?php Admin::maybe_notice(); ?>

	<div class="notice notice-info inline"><p>
		<?php esc_html_e( 'All email goes through wp_mail(). Any SMTP provider configured at the WordPress level — Gmail, Brevo, Mailgun, Amazon SES, SendGrid, Outlook, Postmark — is supported automatically without extra configuration here.', 'smart-client-contact-hub' ); ?>
	</p></div>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'scch_save_settings' ); ?>
		<input type="hidden" name="action" value="scch_save_settings" />
		<input type="hidden" name="scch_group" value="scch_email" />

		<h2><?php esc_html_e( 'Administrator Notification', 'smart-client-contact-hub' ); ?></h2>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><?php esc_html_e( 'Enable admin email', 'smart-client-contact-hub' ); ?></th>
				<td><input type="hidden" name="scch_email[admin_enabled]" value="0" /><label><input type="checkbox" name="scch_email[admin_enabled]" value="1" <?php checked( $scch_e['admin_enabled'], 1 ); ?> /> <?php esc_html_e( 'Send a notification for every new lead', 'smart-client-contact-hub' ); ?></label></td>
			</tr>
			<tr>
				<th scope="row"><label for="scch-recipients"><?php esc_html_e( 'Recipients', 'smart-client-contact-hub' ); ?></label></th>
				<td>
					<input type="text" class="large-text" id="scch-recipients" name="scch_email[admin_recipients]" value="<?php echo esc_attr( $scch_e['admin_recipients'] ); ?>" />
					<p class="description"><?php esc_html_e( 'Comma-separated list for multiple recipients.', 'smart-client-contact-hub' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="scch-cc"><?php esc_html_e( 'CC', 'smart-client-contact-hub' ); ?></label></th>
				<td><input type="text" class="large-text" id="scch-cc" name="scch_email[cc]" value="<?php echo esc_attr( $scch_e['cc'] ); ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="scch-bcc"><?php esc_html_e( 'BCC', 'smart-client-contact-hub' ); ?></label></th>
				<td><input type="text" class="large-text" id="scch-bcc" name="scch_email[bcc]" value="<?php echo esc_attr( $scch_e['bcc'] ); ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Reply-To', 'smart-client-contact-hub' ); ?></th>
				<td><input type="hidden" name="scch_email[reply_to_customer]" value="0" /><label><input type="checkbox" name="scch_email[reply_to_customer]" value="1" <?php checked( $scch_e['reply_to_customer'], 1 ); ?> /> <?php esc_html_e( 'Set Reply-To to the customer so you can reply directly', 'smart-client-contact-hub' ); ?></label></td>
			</tr>
		</table>

		<h2><?php esc_html_e( 'Sender', 'smart-client-contact-hub' ); ?></h2>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="scch-sender-name"><?php esc_html_e( 'Sender name', 'smart-client-contact-hub' ); ?></label></th>
				<td><input type="text" class="regular-text" id="scch-sender-name" name="scch_email[sender_name]" value="<?php echo esc_attr( $scch_e['sender_name'] ); ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="scch-sender-email"><?php esc_html_e( 'Sender email', 'smart-client-contact-hub' ); ?></label></th>
				<td>
					<input type="email" class="regular-text" id="scch-sender-email" name="scch_email[sender_email]" value="<?php echo esc_attr( $scch_e['sender_email'] ); ?>" />
					<p class="description"><?php esc_html_e( 'Leave empty to let WordPress / your SMTP plugin decide the From address (recommended for deliverability).', 'smart-client-contact-hub' ); ?></p>
				</td>
			</tr>
		</table>

		<h2><?php esc_html_e( 'Customer Confirmation', 'smart-client-contact-hub' ); ?></h2>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><?php esc_html_e( 'Enable confirmation email', 'smart-client-contact-hub' ); ?></th>
				<td><input type="hidden" name="scch_email[customer_enabled]" value="0" /><label><input type="checkbox" name="scch_email[customer_enabled]" value="1" <?php checked( $scch_e['customer_enabled'], 1 ); ?> /> <?php esc_html_e( 'Automatically send a thank-you email to the customer', 'smart-client-contact-hub' ); ?></label></td>
			</tr>
		</table>


		<?php submit_button( __( 'Save Notifications', 'smart-client-contact-hub' ) ); ?>
	</form>

	<hr />
	<h2><?php esc_html_e( 'Test Email', 'smart-client-contact-hub' ); ?></h2>
	<p>
		<input type="email" class="regular-text" id="scch-test-recipient" value="<?php echo esc_attr( wp_get_current_user()->user_email ); ?>" />
		<button type="button" class="button button-secondary" id="scch-send-test"><?php esc_html_e( 'Send Test Email', 'smart-client-contact-hub' ); ?></button>
		<span id="scch-test-result" role="status" aria-live="polite"></span>
	</p>
</div>
