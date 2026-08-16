<?php
/**
 * Email template builder view.
 *
 * @package SCCH
 * @var \SCCH\Admin\Admin $admin
 */

use SCCH\Admin\Admin;
use SCCH\Settings;

defined( 'ABSPATH' ) || exit;

$scch_e = Settings::group( 'scch_email' );
?>
<div class="wrap scch-wrap">
	<h1><?php esc_html_e( 'Email Templates', 'smart-client-contact-hub' ); ?></h1>
	<?php Admin::maybe_notice(); ?>

	<div class="notice notice-info inline"><p>
		<?php esc_html_e( 'Available placeholders:', 'smart-client-contact-hub' ); ?>
		<code>{customer_name}</code> <code>{customer_email}</code> <code>{customer_phone}</code> <code>{service}</code> <code>{message}</code> <code>{submission_date}</code> <code>{website_name}</code> <code>{business_name}</code> <code>{business_contact}</code> <code>{response_time}</code> <code>{view_lead_link}</code>
	</p></div>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'scch_save_settings' ); ?>
		<input type="hidden" name="action" value="scch_save_settings" />
		<input type="hidden" name="scch_group" value="scch_email" />

		<h2><?php esc_html_e( 'Branding', 'smart-client-contact-hub' ); ?></h2>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="scch-logo"><?php esc_html_e( 'Logo URL', 'smart-client-contact-hub' ); ?></label></th>
				<td>
					<input type="url" class="regular-text" id="scch-logo" name="scch_email[logo_url]" value="<?php echo esc_attr( $scch_e['logo_url'] ); ?>" />
					<button type="button" class="button scch-media-btn" data-target="#scch-logo"><?php esc_html_e( 'Choose from Media Library', 'smart-client-contact-hub' ); ?></button>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Brand Color', 'smart-client-contact-hub' ); ?></th>
				<td><input type="text" class="scch-color" name="scch_email[brand_color]" value="<?php echo esc_attr( $scch_e['brand_color'] ); ?>" data-default-color="#2563eb" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="scch-signature"><?php esc_html_e( 'Signature', 'smart-client-contact-hub' ); ?></label></th>
				<td><input type="text" class="regular-text" id="scch-signature" name="scch_email[signature]" value="<?php echo esc_attr( $scch_e['signature'] ); ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="scch-btn-label"><?php esc_html_e( 'Button label (admin email)', 'smart-client-contact-hub' ); ?></label></th>
				<td><input type="text" class="regular-text" id="scch-btn-label" name="scch_email[button_label]" value="<?php echo esc_attr( $scch_e['button_label'] ); ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="scch-business-name"><?php esc_html_e( 'Business name', 'smart-client-contact-hub' ); ?></label></th>
				<td><input type="text" class="regular-text" id="scch-business-name" name="scch_email[business_name]" value="<?php echo esc_attr( $scch_e['business_name'] ); ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="scch-business-contact"><?php esc_html_e( 'Business contact info', 'smart-client-contact-hub' ); ?></label></th>
				<td><input type="text" class="regular-text" id="scch-business-contact" name="scch_email[business_contact]" value="<?php echo esc_attr( $scch_e['business_contact'] ); ?>" placeholder="<?php esc_attr_e( 'e.g. Call us at +1 555 000 1234', 'smart-client-contact-hub' ); ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="scch-response-time"><?php esc_html_e( 'Expected response time', 'smart-client-contact-hub' ); ?></label></th>
				<td><input type="text" class="regular-text" id="scch-response-time" name="scch_email[response_time]" value="<?php echo esc_attr( $scch_e['response_time'] ); ?>" /></td>
			</tr>
		</table>

		<h2><?php esc_html_e( 'Administrator Notification', 'smart-client-contact-hub' ); ?></h2>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="scch-a-subject"><?php esc_html_e( 'Subject', 'smart-client-contact-hub' ); ?></label></th>
				<td><input type="text" class="large-text" id="scch-a-subject" name="scch_email[admin_subject]" value="<?php echo esc_attr( $scch_e['admin_subject'] ); ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="scch-a-heading"><?php esc_html_e( 'Heading', 'smart-client-contact-hub' ); ?></label></th>
				<td><input type="text" class="large-text" id="scch-a-heading" name="scch_email[admin_heading]" value="<?php echo esc_attr( $scch_e['admin_heading'] ); ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="scch-a-body"><?php esc_html_e( 'Body', 'smart-client-contact-hub' ); ?></label></th>
				<td><textarea class="large-text" rows="9" id="scch-a-body" name="scch_email[admin_body]"><?php echo esc_textarea( $scch_e['admin_body'] ); ?></textarea></td>
			</tr>
			<tr>
				<th scope="row"><label for="scch-a-footer"><?php esc_html_e( 'Footer', 'smart-client-contact-hub' ); ?></label></th>
				<td><input type="text" class="large-text" id="scch-a-footer" name="scch_email[admin_footer]" value="<?php echo esc_attr( $scch_e['admin_footer'] ); ?>" /></td>
			</tr>
		</table>

		<h2><?php esc_html_e( 'Customer Confirmation', 'smart-client-contact-hub' ); ?></h2>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="scch-c-subject"><?php esc_html_e( 'Subject', 'smart-client-contact-hub' ); ?></label></th>
				<td><input type="text" class="large-text" id="scch-c-subject" name="scch_email[customer_subject]" value="<?php echo esc_attr( $scch_e['customer_subject'] ); ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="scch-c-heading"><?php esc_html_e( 'Heading', 'smart-client-contact-hub' ); ?></label></th>
				<td><input type="text" class="large-text" id="scch-c-heading" name="scch_email[customer_heading]" value="<?php echo esc_attr( $scch_e['customer_heading'] ); ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="scch-c-body"><?php esc_html_e( 'Body', 'smart-client-contact-hub' ); ?></label></th>
				<td><textarea class="large-text" rows="9" id="scch-c-body" name="scch_email[customer_body]"><?php echo esc_textarea( $scch_e['customer_body'] ); ?></textarea></td>
			</tr>
			<tr>
				<th scope="row"><label for="scch-c-footer"><?php esc_html_e( 'Footer', 'smart-client-contact-hub' ); ?></label></th>
				<td><input type="text" class="large-text" id="scch-c-footer" name="scch_email[customer_footer]" value="<?php echo esc_attr( $scch_e['customer_footer'] ); ?>" /></td>
			</tr>
		</table>


		<?php submit_button( __( 'Save Templates', 'smart-client-contact-hub' ) ); ?>
	</form>
</div>
