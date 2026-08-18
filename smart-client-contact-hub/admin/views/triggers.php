<?php
/**
 * External Trigger Integration view: JS API docs, data attribute,
 * custom selectors, and shortcode reference.
 *
 * @package SCCH
 * @var \SCCH\Admin\Admin $admin
 */

use SCCH\Admin\Admin;
use SCCH\Settings;

defined( 'ABSPATH' ) || exit;

$scch_t = Settings::group( 'scch_triggers' );
?>
<div class="wrap scch-wrap">
	<h1><?php esc_html_e( 'External Trigger Integration', 'smart-client-contact-hub' ); ?></h1>
	<?php Admin::maybe_notice(); ?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="scch-dirty-watch">
		<?php wp_nonce_field( 'scch_save_settings' ); ?>
		<input type="hidden" name="action" value="scch_save_settings" />
		<input type="hidden" name="scch_group" value="scch_triggers" />

		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><?php esc_html_e( 'Enable External Triggers', 'smart-client-contact-hub' ); ?></th>
				<td>
					<label><input type="checkbox" name="scch_triggers[enabled]" value="1" <?php checked( $scch_t['enabled'], 1 ); ?> /> <?php esc_html_e( 'Let elements outside the widget open the contact popup (data attribute, custom selectors, shortcode buttons).', 'smart-client-contact-hub' ); ?></label>
					<p class="description"><?php esc_html_e( 'The JavaScript API and custom browser events stay available to developers regardless of this setting.', 'smart-client-contact-hub' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="scch-trigger-selectors"><?php esc_html_e( 'Custom CSS Selectors', 'smart-client-contact-hub' ); ?></label></th>
				<td>
					<textarea id="scch-trigger-selectors" name="scch_triggers[selectors]" rows="8" class="large-text code" placeholder=".hero-button&#10;.contact-btn&#10;#contact-now"><?php echo esc_textarea( $scch_t['selectors'] ); ?></textarea>
					<p class="description"><?php esc_html_e( 'One CSS selector per line. Clicking any matching element opens the popup — no coding needed. Duplicates are removed automatically; invalid selectors are ignored safely on the frontend.', 'smart-client-contact-hub' ); ?></p>
				</td>
			</tr>
		</table>

		<div class="scch-savebar is-clean">
			<span class="scch-savebar__note"><?php esc_html_e( 'All changes saved', 'smart-client-contact-hub' ); ?></span>
			<?php submit_button( __( 'Save Triggers', 'smart-client-contact-hub' ), 'primary', 'submit', false ); ?>
		</div>
	</form>

	<h2><?php esc_html_e( 'Ways to open the popup', 'smart-client-contact-hub' ); ?></h2>

	<h3><?php esc_html_e( '1. HTML data attribute (works in any page builder)', 'smart-client-contact-hub' ); ?></h3>
	<p><?php esc_html_e( 'Add the attribute below to any button, link, or element. Works with Elementor, Gutenberg, Divi, Bricks, Oxygen, WPBakery, and plain HTML — including elements added dynamically after page load.', 'smart-client-contact-hub' ); ?></p>
	<pre><code>&lt;button data-scch-open&gt;Free Consultation&lt;/button&gt;</code></pre>

	<h3><?php esc_html_e( '2. Shortcode', 'smart-client-contact-hub' ); ?></h3>
	<pre><code>[scch_trigger text="Free Consultation" class="btn btn-primary" id="contact-btn"]</code></pre>

	<h3><?php esc_html_e( '3. JavaScript API (for developers)', 'smart-client-contact-hub' ); ?></h3>
	<pre><code>window.SCCH.open();
window.SCCH.close();
window.SCCH.toggle();
window.SCCH.isOpen();</code></pre>

	<h3><?php esc_html_e( '4. Custom browser events (for developers)', 'smart-client-contact-hub' ); ?></h3>
	<pre><code>window.dispatchEvent( new CustomEvent( 'scch:open' ) );
window.dispatchEvent( new CustomEvent( 'scch:close' ) );
window.dispatchEvent( new CustomEvent( 'scch:toggle' ) );</code></pre>

	<h3><?php esc_html_e( '5. PHP hooks (for developers)', 'smart-client-contact-hub' ); ?></h3>
	<pre><code>// Auto-open the popup on page load (call before wp_footer):
do_action( 'scch_open_chat' );

// Filter the trigger configuration (enable state + selectors):
add_filter( 'scch_external_trigger', function ( $config ) {
    $config['selectors'][] = '.my-theme-cta';
    return $config;
} );</code></pre>
</div>
