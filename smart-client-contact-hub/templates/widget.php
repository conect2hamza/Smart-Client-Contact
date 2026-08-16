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
 * @var array|null $captcha    {token, question} or null when disabled.
 *
 * @package SCCH
 */

use SCCH\Frontend\Frontend;

defined( 'ABSPATH' ) || exit;

$scch_position  = 'bottom-left' === $appearance['position'] ? 'scch-pos-left' : 'scch-pos-right';
$scch_animation = in_array( $appearance['animation'], array( 'fade', 'scale', 'bounce', 'pulse', 'none' ), true ) ? $appearance['animation'] : 'none';
$scch_channels  = (array) $contact['channels'];
$scch_tel       = preg_replace( '/[^0-9+]/', '', (string) $contact['phone_number'] );
$scch_sms       = preg_replace( '/[^0-9+]/', '', (string) $contact['sms_number'] );
$scch_sms_href  = 'sms:' . $scch_sms . ( '' !== trim( (string) $contact['sms_body'] ) ? '?&body=' . rawurlencode( $contact['sms_body'] ) : '' );
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
			<?php if ( in_array( 'form', $scch_channels, true ) ) : ?>
				<button type="button" class="scch-channel" data-scch-goto="form">
					<span class="scch-channel-icon"><?php echo Frontend::icon( 'rocket' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
					<span class="scch-channel-label"><?php echo esc_html( $contact['cta_strategy'] ); ?></span>
				</button>
			<?php endif; ?>

			<?php if ( in_array( 'call', $scch_channels, true ) && $scch_tel ) : ?>
				<a class="scch-channel" href="<?php echo esc_url( 'tel:' . $scch_tel ); ?>">
					<span class="scch-channel-icon"><?php echo Frontend::icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
					<span class="scch-channel-label"><?php echo esc_html( $contact['cta_call'] ); ?></span>
				</a>
			<?php endif; ?>

			<?php if ( in_array( 'sms', $scch_channels, true ) && $scch_sms ) : ?>
				<a class="scch-channel" href="<?php echo esc_url( $scch_sms_href ); ?>">
					<span class="scch-channel-icon"><?php echo Frontend::icon( 'sms' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
					<span class="scch-channel-label"><?php echo esc_html( $contact['cta_text'] ); ?></span>
				</a>
			<?php endif; ?>

			<?php
			/**
			 * Extra contact channels (e.g. WhatsApp, Telegram) registered
			 * without modifying plugin core. Each entry:
			 * array{ id:string, label:string, url:string, icon?:string (inline SVG) }
			 *
			 * @param array $extra_channels Default empty.
			 */
			$scch_extra = apply_filters( 'scch_channels', array() );
			foreach ( (array) $scch_extra as $scch_channel ) :
				if ( empty( $scch_channel['label'] ) || empty( $scch_channel['url'] ) ) {
					continue;
				}
				?>
				<a class="scch-channel scch-channel--<?php echo esc_attr( sanitize_html_class( $scch_channel['id'] ?? 'custom' ) ); ?>" href="<?php echo esc_url( $scch_channel['url'] ); ?>">
					<span class="scch-channel-icon"><?php echo isset( $scch_channel['icon'] ) ? wp_kses( $scch_channel['icon'], Frontend::svg_kses() ) : Frontend::icon( 'chat-bubble' ); // phpcs:ignore WordPress.Security.EscapeOutput -- kses-filtered SVG / static inline SVG. ?></span>
					<span class="scch-channel-label"><?php echo esc_html( $scch_channel['label'] ); ?></span>
				</a>
			<?php endforeach; ?>
		</div>

		<!-- Step 2: strategy form. Omitted entirely when the form channel is off. -->
		<?php if ( in_array( 'form', $scch_channels, true ) ) : ?>
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
