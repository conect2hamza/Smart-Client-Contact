<?php
/**
 * Front-end widget markup.
 *
 * Variables provided by Frontend::render_widget():
 *
 * @var array      $appearance Appearance settings.
 * @var array      $contact    Contact settings.
 * @var array      $form       Form settings.
 * @var array      $fields     Enabled, ordered form fields.
 * @var array      $services   Services list.
 * @var array      $channels   Enabled channels, in display order.
 * @var bool       $captcha    Whether the CAPTCHA field renders.
 *
 * @package SCCH
 */

use SCCH\Frontend\Frontend;

defined( 'ABSPATH' ) || exit;

$scch_position  = 'bottom-left' === $appearance['position'] ? 'scch-pos-left' : 'scch-pos-right';
$scch_animation = in_array( $appearance['animation'], array( 'fade', 'scale', 'bounce', 'pulse', 'none' ), true ) ? $appearance['animation'] : 'none';
$scch_has_form  = false;
foreach ( $channels as $scch_row ) {
	if ( 'form' === $scch_row['type'] ) {
		$scch_has_form = true;
		break;
	}
}
?>
<div id="scch-root" class="scch-root <?php echo esc_attr( $scch_position ); ?>" data-animation="<?php echo esc_attr( $scch_animation ); ?>"<?php echo 'light' === ( $appearance['dark_mode'] ?? 'auto' ) ? ' data-forced-light' : ''; ?>>

	<button type="button" id="scch-launcher" class="scch-launcher scch-anim-<?php echo esc_attr( $scch_animation ); ?>"
		aria-haspopup="dialog" aria-expanded="false" aria-controls="scch-panel"
		aria-label="<?php echo esc_attr( $contact['panel_title'] ); ?>">
		<span class="scch-launcher-icon scch-icon-open"><?php echo Frontend::icon( $appearance['icon'], $appearance['custom_icon_url'] ); // phpcs:ignore WordPress.Security.EscapeOutput -- static inline SVG / escaped img. ?></span>
		<span class="scch-launcher-icon scch-icon-close" hidden><?php echo Frontend::icon( 'close' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
	</button>

	<div class="scch-overlay" id="scch-overlay" hidden></div>

	<div id="scch-panel" class="scch-panel" role="dialog" aria-modal="true" tabindex="-1"
		aria-labelledby="scch-panel-title" hidden>

		<div class="scch-panel-header">
			<h2 id="scch-panel-title" class="scch-panel-title"><?php echo esc_html( $contact['panel_title'] ); ?></h2>
			<p class="scch-panel-intro"><?php echo esc_html( $contact['panel_intro'] ); ?></p>
			<button type="button" class="scch-close" data-scch-close aria-label="<?php esc_attr_e( 'Close contact panel', 'smart-client-contact-hub' ); ?>">
				<?php echo Frontend::icon( 'close' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</button>
		</div>

		<!-- Step 1: channel choice -->
		<div class="scch-view scch-view-channels" data-scch-view="channels">
			<?php
			foreach ( $channels as $scch_channel ) :
				/*
				 * Per-channel colors ride on the same custom properties the
				 * stylesheet already reads, so an override here cascades to
				 * that one button without any extra rules. Only validated hex
				 * values reach this attribute.
				 */
				$scch_style = '';
				foreach ( array(
					'--scch-cicon-bg'      => $scch_channel['icon_bg'],
					'--scch-cicon-color'   => $scch_channel['icon_color'],
					'--scch-channel-text'  => $scch_channel['text_color'],
				) as $scch_prop => $scch_val ) {
					if ( '' !== (string) $scch_val ) {
						$scch_style .= $scch_prop . ':' . $scch_val . ';';
					}
				}

				$scch_classes = 'scch-channel scch-channel--' . sanitize_html_class( $scch_channel['id'] ?: $scch_channel['type'] );
				$scch_is_form = 'form' === $scch_channel['type'];
				$scch_ext     = ! $scch_is_form && preg_match( '#^https?://#i', (string) $scch_channel['url'] );
				?>
				<?php if ( $scch_is_form ) : ?>
					<button type="button" class="<?php echo esc_attr( $scch_classes ); ?>" data-scch-goto="form"
						<?php echo '' !== $scch_style ? 'style="' . esc_attr( $scch_style ) . '"' : ''; ?>>
				<?php else : ?>
					<a class="<?php echo esc_attr( $scch_classes ); ?>" href="<?php echo esc_url( $scch_channel['url'] ); ?>"
						<?php echo $scch_ext && ! empty( $scch_channel['new_tab'] ) ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>
						<?php echo '' !== $scch_style ? 'style="' . esc_attr( $scch_style ) . '"' : ''; ?>>
				<?php endif; ?>

					<span class="scch-channel-icon">
						<?php
						if ( ! empty( $scch_channel['raw_icon'] ) ) {
							// Inline SVG supplied through the scch_channels filter.
							echo wp_kses( $scch_channel['raw_icon'], Frontend::svg_kses() ); // phpcs:ignore WordPress.Security.EscapeOutput -- kses-filtered SVG.
						} else {
							echo Frontend::icon( $scch_channel['icon'], $scch_channel['icon_url'] ); // phpcs:ignore WordPress.Security.EscapeOutput -- static inline SVG / escaped img.
						}
						?>
					</span>
					<span class="scch-channel-text">
						<span class="scch-channel-label"><?php echo esc_html( $scch_channel['label'] ); ?></span>
						<?php if ( '' !== trim( (string) $scch_channel['description'] ) ) : ?>
							<span class="scch-channel-desc"><?php echo esc_html( $scch_channel['description'] ); ?></span>
						<?php endif; ?>
					</span>

				<?php if ( $scch_is_form ) : ?>
					</button>
				<?php else : ?>
					</a>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>

		<!-- Step 2: strategy form. Omitted entirely when the form channel is off. -->
		<?php if ( $scch_has_form ) : ?>
		<div class="scch-view scch-view-form" data-scch-view="form" hidden>
			<button type="button" class="scch-back" data-scch-goto="channels">&larr; <?php esc_html_e( 'Back', 'smart-client-contact-hub' ); ?></button>
			<h3 class="scch-form-title"><?php echo esc_html( $form['form_title'] ); ?></h3>

			<form id="scch-form" novalidate>
				<?php foreach ( $fields as $key => $field ) : ?>
					<?php
					$scch_id       = 'scch-field-' . $key;
					$scch_required = ! empty( $field['required'] );
					?>
					<div class="scch-field" data-field="<?php echo esc_attr( $key ); ?>">
						<label for="<?php echo esc_attr( $scch_id ); ?>"<?php echo ! empty( $field['hide_label'] ) ? ' class="scch-label-hidden"' : ''; ?>>
							<?php echo esc_html( $field['label'] ); ?>
							<?php if ( $scch_required ) : ?><span class="scch-req" aria-hidden="true">*</span><?php endif; ?>
						</label>

						<?php if ( 'service' === $key ) : ?>
							<select id="<?php echo esc_attr( $scch_id ); ?>" name="service" <?php echo $scch_required ? 'required aria-required="true"' : ''; ?>>
								<option value=""><?php echo esc_html( $field['placeholder'] ); ?></option>
								<?php foreach ( $services as $service ) : ?>
									<option value="<?php echo esc_attr( $service['id'] ); ?>"><?php echo esc_html( $service['label'] ); ?></option>
								<?php endforeach; ?>
							</select>
						<?php elseif ( 'message' === $key ) : ?>
							<textarea id="<?php echo esc_attr( $scch_id ); ?>" name="message" rows="4" maxlength="1000"
								placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"
								<?php echo $scch_required ? 'required aria-required="true"' : ''; ?>></textarea>
						<?php else : ?>
							<?php
							$scch_type = 'email' === $key ? 'email' : ( 'phone' === $key ? 'tel' : 'text' );
							$scch_attr = 'name' === $key ? 'minlength="3" maxlength="80"' : '';
							?>
							<input type="<?php echo esc_attr( $scch_type ); ?>" id="<?php echo esc_attr( $scch_id ); ?>"
								name="<?php echo esc_attr( $key ); ?>" <?php echo $scch_attr; // phpcs:ignore WordPress.Security.EscapeOutput -- static attribute string. ?>
								placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"
								autocomplete="<?php echo esc_attr( 'email' === $key ? 'email' : ( 'phone' === $key ? 'tel' : 'name' ) ); ?>"
								<?php echo $scch_required ? 'required aria-required="true"' : ''; ?> />
						<?php endif; ?>

						<p class="scch-field-error" role="alert" hidden></p>
					</div>
				<?php endforeach; ?>

				<?php if ( $captcha ) : ?>
					<?php
					/*
					 * Question and token are intentionally empty in the markup.
					 * This template is printed into pages that may be cached
					 * and served to many visitors, so the challenge is issued
					 * per visitor over AJAX when the form view opens.
					 */
					?>
					<div class="scch-field scch-captcha" data-field="captcha">
						<label for="scch-captcha-answer">
							<?php echo esc_html( \SCCH\Settings::get( 'scch_captcha', 'label' ) ); ?>
							<span class="scch-req" aria-hidden="true">*</span>
						</label>
						<div class="scch-captcha-row">
							<span class="scch-captcha-question" id="scch-captcha-question" aria-live="polite"><?php echo esc_html__( 'Loading…', 'smart-client-contact-hub' ); ?></span>
							<input type="text" inputmode="numeric" pattern="[0-9]*" id="scch-captcha-answer"
								name="captcha_answer" required aria-required="true" aria-describedby="scch-captcha-question"
								autocomplete="off" />
							<button type="button" class="scch-captcha-refresh" aria-label="<?php esc_attr_e( 'Get a new question', 'smart-client-contact-hub' ); ?>">
								<?php echo Frontend::icon( 'refresh' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
							</button>
						</div>
						<input type="hidden" name="captcha_token" id="scch-captcha-token" value="" />
						<p class="scch-field-error" role="alert" hidden></p>
					</div>
				<?php endif; ?>

				<!-- Honeypot: invisible to humans, tempting to bots. -->
				<div class="scch-hp" aria-hidden="true">
					<label for="scch-website">Website</label>
					<input type="text" id="scch-website" name="scch_website" tabindex="-1" autocomplete="off" />
				</div>

				<p class="scch-form-feedback" role="status" aria-live="polite" hidden></p>

				<button type="submit" class="scch-submit"><?php echo esc_html( $form['submit_label'] ); ?></button>
			</form>
		</div>
		<?php endif; ?>

		<!-- Step 3: success -->
		<div class="scch-view scch-view-success" data-scch-view="success" hidden>
			<div class="scch-success-icon" aria-hidden="true">✓</div>
			<p class="scch-success-message"></p>
		</div>
	</div>
</div>
