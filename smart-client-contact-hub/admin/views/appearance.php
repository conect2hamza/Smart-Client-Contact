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

	<?php
	/*
	 * There are well over a hundred controls across these tabs, so the search
	 * box looks through all of them at once — label and help text alike — and
	 * says which tab each match lives on. Without it, finding one control
	 * means remembering which tab it is on.
	 */
	$scch_total = count( Design_Tokens::fields() );
	?>
	<div class="scch-optbar">
		<label class="screen-reader-text" for="scch-opt-search"><?php esc_html_e( 'Search appearance settings', 'smart-client-contact-hub' ); ?></label>
		<input type="search" id="scch-opt-search" class="scch-optbar__input"
			autocomplete="off"
			placeholder="<?php echo esc_attr( sprintf(
				/* translators: %d: number of settings. */
				__( 'Search all %d settings — try "hover", "radius", or "submit"', 'smart-client-contact-hub' ),
				$scch_total
			) ); ?>" />
		<span class="scch-optbar__count" role="status" aria-live="polite"></span>
		<button type="button" class="button-link scch-optbar__clear" hidden><?php esc_html_e( 'Clear', 'smart-client-contact-hub' ); ?></button>
	</div>

	<nav class="nav-tab-wrapper scch-tabs" aria-label="<?php esc_attr_e( 'Appearance sections', 'smart-client-contact-hub' ); ?>">
		<?php foreach ( $scch_schema as $scch_key => $scch_group ) : ?>
			<?php // A real link, so deep links and no-JS both work; JS switches in place to keep unsaved edits. ?>
			<a class="nav-tab <?php echo $scch_key === $scch_section ? 'nav-tab-active' : ''; ?>"
				data-section="<?php echo esc_attr( $scch_key ); ?>"
				href="<?php echo esc_url( add_query_arg( 'section', $scch_key, $scch_base ) ); ?>"
				<?php echo $scch_key === $scch_section ? 'aria-current="page"' : ''; ?>>
				<?php echo esc_html( $scch_group['label'] ); ?>
				<span class="scch-tabcount" title="<?php esc_attr_e( 'Settings changed from their default', 'smart-client-contact-hub' ); ?>" hidden></span>
			</a>
		<?php endforeach; ?>
	</nav>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="scch-dirty-watch">
		<?php wp_nonce_field( 'scch_save_settings' ); ?>
		<input type="hidden" name="action" value="scch_save_settings" />
		<input type="hidden" name="scch_group" value="scch_appearance" />
		<?php // The save redirects back via the referer, which carries the active section. ?>

		<?php
		/*
		 * Every section renders its real controls, including the ones behind
		 * an inactive tab. That is what lets the tabs switch without a page
		 * load — so unsaved edits survive — and lets the search box look
		 * across all of them at once. Colour pickers are the expensive part,
		 * so a section's are initialised the first time it is revealed.
		 */
		foreach ( $scch_schema as $scch_key => $scch_group ) :
			$scch_visible = $scch_key === $scch_section;
			?>
			<section class="scch-panel-section" data-section="<?php echo esc_attr( $scch_key ); ?>"
				data-label="<?php echo esc_attr( $scch_group['label'] ); ?>" <?php echo $scch_visible ? '' : 'hidden'; ?>>
				<h2 class="scch-section__title"><?php echo esc_html( $scch_group['label'] ); ?></h2>
				<?php if ( ! empty( $scch_group['intro'] ) ) : ?>
					<p class="description scch-section__intro"><?php echo esc_html( $scch_group['intro'] ); ?></p>
				<?php endif; ?>

				<table class="form-table" role="presentation">
					<?php
					foreach ( $scch_group['fields'] as $scch_name => $scch_field ) :
						$scch_id    = 'scch-f-' . str_replace( '_', '-', $scch_name );
						$scch_value = $scch_a[ $scch_name ] ?? $scch_field['default'];
						$scch_input = 'scch_appearance[' . $scch_name . ']';

						// The icon picker renders its own custom-URL input.
						if ( ! empty( $scch_field['hidden'] ) ) {
							continue;
						}

						// What the search box matches on, and what "changed"
						// is measured against.
						$scch_haystack = strtolower( trim(
							$scch_field['label'] . ' ' . ( $scch_field['help'] ?? '' ) . ' ' . $scch_group['label'] . ' ' . str_replace( '_', ' ', $scch_name )
						) );
						?>
						<tr class="scch-opt"
							data-search="<?php echo esc_attr( $scch_haystack ); ?>"
							data-default="<?php echo esc_attr( (string) $scch_field['default'] ); ?>"
							data-label="<?php echo esc_attr( $scch_field['label'] ); ?>">
							<th scope="row">
								<?php if ( in_array( $scch_field['type'], array( 'toggle', 'icon' ), true ) ) : ?>
									<?php echo esc_html( $scch_field['label'] ); ?>
								<?php else : ?>
									<label for="<?php echo esc_attr( $scch_id ); ?>"><?php echo esc_html( $scch_field['label'] ); ?></label>
								<?php endif; ?>
							</th>
							<td>
								<?php
								switch ( $scch_field['type'] ) :
									case 'icon':
										$picker_name     = $scch_input;
										$picker_value    = (string) $scch_value;
										$picker_uid      = $scch_id;
										$picker_url_name = 'scch_appearance[' . $scch_field['url_key'] . ']';
										$picker_url      = (string) ( $scch_a[ $scch_field['url_key'] ] ?? '' );
										include SCCH_PATH . 'admin/views/partials/icon-picker.php';
										break;

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

								<?php // Shown by JS only while this control differs from its default. ?>
								<button type="button" class="button-link scch-opt__reset" hidden>
									<?php esc_html_e( 'Reset to default', 'smart-client-contact-hub' ); ?>
								</button>
							</td>
						</tr>
					<?php endforeach; ?>
				</table>
			</section>
		<?php endforeach; ?>

		<p class="scch-noresults" hidden><?php esc_html_e( 'No settings match that search.', 'smart-client-contact-hub' ); ?></p>

		<div class="scch-savebar is-clean">
			<span class="scch-savebar__note"><?php esc_html_e( 'All changes saved', 'smart-client-contact-hub' ); ?></span>
			<?php submit_button( __( 'Save Appearance', 'smart-client-contact-hub' ), 'primary', 'submit', false ); ?>
		</div>
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
