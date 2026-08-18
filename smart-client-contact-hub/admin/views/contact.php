<?php
/**
 * Contact settings view: the wording of the popup header.
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

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="scch-dirty-watch">
		<?php wp_nonce_field( 'scch_save_settings' ); ?>
		<input type="hidden" name="action" value="scch_save_settings" />
		<input type="hidden" name="scch_group" value="scch_contact" />

		<div class="notice notice-info inline"><p>
			<?php
			printf(
				/* translators: %s: Channels admin URL. */
				wp_kses_post( __( 'Phone numbers, WhatsApp, and every other contact button now live on the <a href="%s">Channels</a> screen, where each one has its own label, destination, icon, and colors.', 'smart-client-contact-hub' ) ),
				esc_url( admin_url( 'admin.php?page=scch-channels' ) )
			);
			?>
		</p></div>

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
		</table>

		<div class="scch-savebar is-clean">
			<span class="scch-savebar__note"><?php esc_html_e( 'All changes saved', 'smart-client-contact-hub' ); ?></span>
			<?php submit_button( __( 'Save Contact Settings', 'smart-client-contact-hub' ), 'primary', 'submit', false ); ?>
		</div>
	</form>
</div>
