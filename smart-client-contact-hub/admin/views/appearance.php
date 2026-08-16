<?php
/**
 * Appearance settings view.
 *
 * Every control is rendered from the Design_Tokens schema, so adding a design
 * option never means touching this file.
 *
 * @package SCCH
 * @var \SCCH\Admin\Admin $admin
 */

use SCCH\Admin\Admin;
use SCCH\Design_Tokens;
use SCCH\Settings;

defined( 'ABSPATH' ) || exit;

$scch_a       = Settings::group( 'scch_appearance' );
$scch_schema  = Design_Tokens::schema();
$scch_section = isset( $_GET['section'] ) ? sanitize_key( wp_unslash( $_GET['section'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- view routing only.
$scch_section = isset( $scch_schema[ $scch_section ] ) ? $scch_section : (string) array_key_first( $scch_schema );
$scch_base    = admin_url( 'admin.php?page=scch-appearance' );
?>
<div class="wrap scch-wrap scch-appearance">
	<h1><?php esc_html_e( 'Appearance', 'smart-client-contact-hub' ); ?></h1>
	<?php Admin::maybe_notice(); ?>

	<p class="description scch-appearance__intro">
		<?php esc_html_e( 'Every visual detail of the widget is editable here. Colors left empty fall back to the shipped design, so you only need to set what you actually want to change.', 'smart-client-contact-hub' ); ?>
	</p>

	<nav class="nav-tab-wrapper scch-tabs" aria-label="<?php esc_attr_e( 'Appearance sections', 'smart-client-contact-hub' ); ?>">
		<?php foreach ( $scch_schema as $scch_key => $scch_group ) : ?>
			<a class="nav-tab <?php echo $scch_key === $scch_section ? 'nav-tab-active' : ''; ?>"
				href="<?php echo esc_url( add_query_arg( 'section', $scch_key, $scch_base ) ); ?>"
				<?php echo $scch_key === $scch_section ? 'aria-current="page"' : ''; ?>>
				<?php echo esc_html( $scch_group['label'] ); ?>
			</a>
		<?php endforeach; ?>
	</nav>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'scch_save_settings' ); ?>
		<input type="hidden" name="action" value="scch_save_settings" />
		<input type="hidden" name="scch_group" value="scch_appearance" />
		<?php // The save redirects back via the referer, which carries the active section. ?>

		<?php
		foreach ( $scch_schema as $scch_key => $scch_group ) :
			$scch_visible = $scch_key === $scch_section;
			?>
			<section class="scch-panel-section" <?php echo $scch_visible ? '' : 'hidden'; ?>>
				<?php if ( $scch_visible ) : ?>
					<h2><?php echo esc_html( $scch_group['label'] ); ?></h2>
					<?php if ( ! empty( $scch_group['intro'] ) ) : ?>
						<p class="description"><?php echo esc_html( $scch_group['intro'] ); ?></p>
					<?php endif; ?>
				<?php endif; ?>

				<table class="form-table" role="presentation">
					<?php
					foreach ( $scch_group['fields'] as $scch_name => $scch_field ) :
						$scch_id    = 'scch-f-' . str_replace( '_', '-', $scch_name );
						$scch_value = $scch_a[ $scch_name ] ?? $scch_field['default'];
						$scch_input = 'scch_appearance[' . $scch_name . ']';

						/*
						 * Fields outside the visible section still have to be
						 * posted: the sanitizer rebuilds the whole group on
						 * every save, so an omitted field would be reset to
						 * its default. Hidden sections submit their current
						 * values unchanged.
						 */
						if ( ! $scch_visible ) {
							if ( 'toggle' === $scch_field['type'] ) {
								printf(
									'<input type="hidden" name="%s" value="%s" />',
									esc_attr( $scch_input ),
									esc_attr( empty( $scch_value ) ? '0' : '1' )
								);
							} else {
								printf(
									'<input type="hidden" name="%s" value="%s" />',
									esc_attr( $scch_input ),
									esc_attr( (string) $scch_value )
								);
							}
							continue;
						}
						?>
						<tr>
							<th scope="row">
								<?php if ( 'toggle' === $scch_field['type'] ) : ?>
									<?php echo esc_html( $scch_field['label'] ); ?>
								<?php else : ?>
									<label for="<?php echo esc_attr( $scch_id ); ?>"><?php echo esc_html( $scch_field['label'] ); ?></label>
								<?php endif; ?>
							</th>
							<td>
								<?php
								switch ( $scch_field['type'] ) :
									case 'toggle':
										?>
										<input type="hidden" name="<?php echo esc_attr( $scch_input ); ?>" value="0" />
										<label>
											<input type="checkbox" id="<?php echo esc_attr( $scch_id ); ?>" name="<?php echo esc_attr( $scch_input ); ?>" value="1" <?php checked( (int) $scch_value, 1 ); ?> />
											<?php esc_html_e( 'Enabled', 'smart-client-contact-hub' ); ?>
										</label>
										<?php
										break;

									case 'select':
										?>
										<select id="<?php echo esc_attr( $scch_id ); ?>" name="<?php echo esc_attr( $scch_input ); ?>">
											<?php foreach ( $scch_field['options'] as $scch_ov => $scch_ol ) : ?>
												<option value="<?php echo esc_attr( $scch_ov ); ?>" <?php selected( (string) $scch_value, (string) $scch_ov ); ?>><?php echo esc_html( $scch_ol ); ?></option>
											<?php endforeach; ?>
										</select>
										<?php
										break;

									case 'color':
										?>
										<input type="text" class="scch-color" id="<?php echo esc_attr( $scch_id ); ?>"
											name="<?php echo esc_attr( $scch_input ); ?>"
											value="<?php echo esc_attr( (string) $scch_value ); ?>"
											data-alpha-enabled="false"
											<?php echo empty( $scch_field['empty'] ) ? 'data-default-color="' . esc_attr( (string) $scch_field['default'] ) . '"' : ''; ?> />
										<?php
										break;

									case 'px':
									case 'pct':
									case 'num':
										?>
										<input type="number" class="small-text" id="<?php echo esc_attr( $scch_id ); ?>"
											name="<?php echo esc_attr( $scch_input ); ?>"
											value="<?php echo esc_attr( (string) $scch_value ); ?>"
											min="<?php echo esc_attr( (string) ( $scch_field['min'] ?? 0 ) ); ?>"
											max="<?php echo esc_attr( (string) ( $scch_field['max'] ?? 9999 ) ); ?>"
											step="1" />
										<span class="scch-unit"><?php echo esc_html( 'px' === $scch_field['type'] ? 'px' : ( 'pct' === $scch_field['type'] ? '%' : '' ) ); ?></span>
										<?php
										break;

									case 'dec':
										?>
										<input type="number" class="small-text" id="<?php echo esc_attr( $scch_id ); ?>"
											name="<?php echo esc_attr( $scch_input ); ?>"
											value="<?php echo esc_attr( (string) $scch_value ); ?>"
											min="<?php echo esc_attr( (string) ( $scch_field['min'] ?? 0 ) ); ?>"
											max="<?php echo esc_attr( (string) ( $scch_field['max'] ?? 10 ) ); ?>"
											step="0.05" />
										<span class="scch-unit"><?php echo esc_html( $scch_field['unit'] ?? '' ); ?></span>
										<?php
										break;

									case 'url':
										?>
										<input type="url" class="regular-text" id="<?php echo esc_attr( $scch_id ); ?>"
											name="<?php echo esc_attr( $scch_input ); ?>"
											value="<?php echo esc_attr( (string) $scch_value ); ?>" />
										<?php if ( ! empty( $scch_field['media'] ) ) : ?>
											<button type="button" class="button scch-media-btn" data-target="#<?php echo esc_attr( $scch_id ); ?>"><?php esc_html_e( 'Choose from Media Library', 'smart-client-contact-hub' ); ?></button>
										<?php endif; ?>
										<?php
										break;

									default:
										?>
										<input type="text" class="regular-text" id="<?php echo esc_attr( $scch_id ); ?>"
											name="<?php echo esc_attr( $scch_input ); ?>"
											value="<?php echo esc_attr( (string) $scch_value ); ?>" />
										<?php
								endswitch;
								?>

								<?php if ( ! empty( $scch_field['help'] ) ) : ?>
									<p class="description"><?php echo esc_html( $scch_field['help'] ); ?></p>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</table>
			</section>
		<?php endforeach; ?>

		<?php submit_button( __( 'Save Appearance', 'smart-client-contact-hub' ) ); ?>
	</form>

	<hr />

	<h2><?php esc_html_e( 'Start over', 'smart-client-contact-hub' ); ?></h2>
	<p class="description"><?php esc_html_e( 'Restores every Appearance value on every tab to its shipped default. Other settings are untouched.', 'smart-client-contact-hub' ); ?></p>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
		onsubmit="return confirm('<?php echo esc_js( __( 'Reset all Appearance settings to their defaults? This cannot be undone.', 'smart-client-contact-hub' ) ); ?>');">
		<?php wp_nonce_field( 'scch_reset_appearance' ); ?>
		<input type="hidden" name="action" value="scch_reset_appearance" />
		<?php submit_button( __( 'Reset Appearance to Defaults', 'smart-client-contact-hub' ), 'delete', 'submit', false ); ?>
	</form>
</div>
