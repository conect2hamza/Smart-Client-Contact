<?php
/**
 * Help view.
 *
 * @package SCCH
 * @var \SCCH\Admin\Admin $admin
 */

use SCCH\Admin\Admin;
use SCCH\Settings;

defined( 'ABSPATH' ) || exit;

$scch_u = Settings::group( 'scch_uninstall' );
?>
<div class="wrap scch-wrap">
	<h1><?php esc_html_e( 'Help', 'smart-client-contact-hub' ); ?></h1>
	<?php Admin::maybe_notice(); ?>

	<h2><?php esc_html_e( 'Getting started', 'smart-client-contact-hub' ); ?></h2>
	<ol>
		<li><?php esc_html_e( 'Contact Settings — add the phone number for "Call Us" and the mobile number for "Text Us".', 'smart-client-contact-hub' ); ?></li>
		<li><?php esc_html_e( 'Services — define the options for the "Select Service" dropdown.', 'smart-client-contact-hub' ); ?></li>
		<li><?php esc_html_e( 'Notifications — confirm the recipient address and send yourself a test email.', 'smart-client-contact-hub' ); ?></li>
		<li><?php esc_html_e( 'Appearance — match the widget to your brand. The widget then shows automatically on every public page.', 'smart-client-contact-hub' ); ?></li>
	</ol>

	<h2><?php esc_html_e( 'Email delivery / SMTP', 'smart-client-contact-hub' ); ?></h2>
	<p><?php esc_html_e( 'The plugin sends everything through the standard wp_mail() function. If your site already routes wp_mail() through Gmail, Brevo, Mailgun, Amazon SES, SendGrid, Outlook or Postmark (for example via an SMTP plugin or hosting configuration), Smart Client Contact Hub uses that path automatically. Nothing extra to configure here.', 'smart-client-contact-hub' ); ?></p>

	<h2><?php esc_html_e( 'Hiding the widget on specific pages', 'smart-client-contact-hub' ); ?></h2>
	<p><?php esc_html_e( 'Developers can return false from the scch_render_widget filter to hide the widget on selected pages:', 'smart-client-contact-hub' ); ?></p>
	<pre><code>add_filter( 'scch_render_widget', fn( $show ) =&gt; is_page( 'checkout' ) ? false : $show );</code></pre>

	<h2><?php esc_html_e( 'Adding channels (WhatsApp, Telegram, …)', 'smart-client-contact-hub' ); ?></h2>
	<p><?php esc_html_e( 'Extra contact channels can be registered with the scch_channels filter without modifying plugin core:', 'smart-client-contact-hub' ); ?></p>
	<pre><code>add_filter( 'scch_channels', function ( $channels ) {
	$channels[] = array(
		'id'    =&gt; 'whatsapp',
		'label' =&gt; 'WhatsApp',
		'url'   =&gt; 'https://wa.me/15550001234',
		'icon'  =&gt; '&lt;svg …&gt;&lt;/svg&gt;',
	);
	return $channels;
} );</code></pre>

	<h2><?php esc_html_e( 'Uninstall behavior', 'smart-client-contact-hub' ); ?></h2>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'scch_save_settings' ); ?>
		<input type="hidden" name="action" value="scch_save_settings" />
		<input type="hidden" name="scch_group" value="scch_uninstall" />
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><?php esc_html_e( 'When the plugin is deleted', 'smart-client-contact-hub' ); ?></th>
				<td>
					<fieldset>
						<label><input type="checkbox" name="scch_uninstall[delete_settings]" value="1" <?php checked( $scch_u['delete_settings'], 1 ); ?> /> <?php esc_html_e( 'Delete plugin settings', 'smart-client-contact-hub' ); ?></label><br />
						<label><input type="checkbox" name="scch_uninstall[delete_leads]" value="1" <?php checked( $scch_u['delete_leads'], 1 ); ?> /> <?php esc_html_e( 'Delete leads and email logs (drops the database tables)', 'smart-client-contact-hub' ); ?></label>
						<p class="description"><?php esc_html_e( 'Leave both unchecked to keep all data after uninstalling.', 'smart-client-contact-hub' ); ?></p>
					</fieldset>
				</td>
			</tr>
		</table>
		<?php submit_button( __( 'Save Uninstall Options', 'smart-client-contact-hub' ) ); ?>
	</form>
</div>
