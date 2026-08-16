<?php
/**
 * Front-end widget rendering and assets.
 *
 * @package SCCH
 */

namespace SCCH\Frontend;

use SCCH\Captcha;
use SCCH\Design_Tokens;
use SCCH\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Enqueues assets only when the widget renders and prints the widget markup
 * in the footer of every public page.
 */
class Frontend {

	/**
	 * Whether do_action( 'scch_open_chat' ) was fired for this request.
	 *
	 * @var bool
	 */
	private bool $auto_open = false;

	/**
	 * Register hooks.
	 */
	public function register(): void {
		add_action( 'wp_enqueue_scripts', array( $this, 'assets' ) );
		add_action( 'wp_footer', array( $this, 'render_widget' ) );
		add_shortcode( 'scch_trigger', array( $this, 'trigger_shortcode' ) );

		/**
		 * Developer hook: fire do_action( 'scch_open_chat' ) anywhere before
		 * wp_footer to auto-open the popup when the page loads.
		 */
		add_action( 'scch_open_chat', array( $this, 'flag_auto_open' ) );
	}

	/**
	 * Mark the popup to auto-open on this page load.
	 */
	public function flag_auto_open(): void {
		$this->auto_open = true;
	}

	/**
	 * [scch_trigger] — renders a button that opens the popup.
	 *
	 * Attributes: text, class, id, tag (button|a|span|div).
	 *
	 * @param array|string $atts Shortcode attributes.
	 */
	public function trigger_shortcode( $atts ): string {
		$atts = shortcode_atts(
			array(
				'text'  => __( 'Contact Us', 'smart-client-contact-hub' ),
				'class' => '',
				'id'    => '',
				'tag'   => 'button',
			),
			$atts,
			'scch_trigger'
		);

		$tag   = in_array( $atts['tag'], array( 'button', 'a', 'span', 'div' ), true ) ? $atts['tag'] : 'button';
		$class = trim( 'scch-trigger ' . $atts['class'] );

		return sprintf(
			'<%1$s data-scch-open%2$s class="%3$s"%4$s%5$s>%6$s</%1$s>',
			$tag,
			'button' === $tag ? ' type="button"' : '',
			esc_attr( $class ),
			$atts['id'] ? ' id="' . esc_attr( $atts['id'] ) . '"' : '',
			'a' === $tag ? ' href="#" role="button"' : '',
			esc_html( $atts['text'] )
		);
	}

	/**
	 * Whether the widget should render on the current request.
	 */
	private function should_render(): bool {
		if ( is_admin() || wp_doing_ajax() || is_embed() ) {
			return false;
		}

		/**
		 * Filter whether the contact hub widget renders on the current page.
		 *
		 * @param bool $render Default true.
		 */
		return (bool) apply_filters( 'scch_render_widget', true );
	}

	/**
	 * Enqueue front-end CSS/JS with settings-driven inline styles.
	 */
	public function assets(): void {
		if ( ! $this->should_render() ) {
			return;
		}

		wp_enqueue_style( 'scch-frontend', SCCH_URL . 'assets/css/frontend.css', array(), SCCH_VERSION );
		wp_add_inline_style( 'scch-frontend', $this->dynamic_css() );

		wp_enqueue_script( 'scch-frontend', SCCH_URL . 'assets/js/frontend.js', array(), SCCH_VERSION, true );

		wp_localize_script(
			'scch-frontend',
			'scchConfig',
			array(
				'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
				'nonce'          => wp_create_nonce( 'scch_frontend' ),
				'captchaEnabled' => Captcha::enabled(),
				'i18n'           => array(
					'sending'  => __( 'Sending…', 'smart-client-contact-hub' ),
					'netError' => __( 'Network error. Please try again.', 'smart-client-contact-hub' ),
					'expired'  => __( 'Your session expired. Please try sending again.', 'smart-client-contact-hub' ),
				),
			)
		);
	}

	/**
	 * Build CSS custom properties from Appearance settings.
	 *
	 * Emission is driven by the Design_Tokens schema: any field carrying a
	 * 'css' key becomes a custom property. Optional colors left empty emit
	 * nothing at all, so the fallback baked into the stylesheet's var() call
	 * applies — that is how "leave empty to inherit" is implemented, and it
	 * keeps the generated CSS to only what the site actually overrode.
	 */
	private function dynamic_css(): string {
		$a = Settings::group( 'scch_appearance' );

		$dark_keys = Design_Tokens::dark_keys();
		$light     = array();
		$dark      = array();

		foreach ( Design_Tokens::fields() as $key => $field ) {
			if ( empty( $field['css'] ) ) {
				continue;
			}

			$value = self::token_value( $a[ $key ] ?? '', $field );

			if ( '' === $value ) {
				continue;
			}

			if ( in_array( $key, $dark_keys, true ) ) {
				$dark[] = $field['css'] . ':' . $value;
			} else {
				$light[] = $field['css'] . ':' . $value;
			}
		}

		// Composites: values built from more than one control.
		$light[] = '--scch-gradient:' . $this->gradient( $a );
		$light[] = '--scch-btn-bg:' . ( empty( $a['use_gradient'] ) ? $this->hex( $a['button_bg'], '#2563eb' ) : $this->gradient( $a ) );
		$light[] = '--scch-border:' . (int) $a['border_width'] . 'px solid ' . $this->border_color( $a['border_color'] ?? '' );
		$light[] = '--scch-shadow:' . $this->shadow(
			! empty( $a['shadow'] ),
			$a['shadow_offset'] ?? 8,
			$a['shadow_blur'] ?? 24,
			$a['shadow_color'] ?? '#000000',
			$a['shadow_opacity'] ?? 22
		);
		$light[] = '--scch-panel-shadow:' . $this->shadow(
			true,
			20,
			$a['panel_shadow_blur'] ?? 50,
			$a['panel_shadow_color'] ?? '#000000',
			$a['panel_shadow_opacity'] ?? 25
		);
		$light[] = '--scch-panel-border:' . (int) $a['panel_border_width'] . 'px solid ' . $this->border_color( $a['panel_border_color'] ?? '' );
		$light[] = '--scch-overlay-bg:' . (
			empty( $a['overlay'] )
				? 'transparent'
				: $this->rgba( $a['overlay_color'] ?? '#111827', $a['overlay_opacity'] ?? 35 )
		);

		// The header intro is softened only while it inherits the header
		// color; an explicitly chosen color renders at full strength.
		if ( '' !== $this->hex( $a['intro_color'] ?? '', '' ) ) {
			$light[] = '--scch-intro-opacity:1';
		}

		$css = '.scch-root{' . implode( ';', $light ) . ';}';

		if ( empty( $a['overlay'] ) ) {
			$css .= '.scch-root .scch-overlay{backdrop-filter:none;}';
		}

		// Submit hover: emitted only when set, so the default brightness
		// effect survives otherwise. filter:none keeps a chosen color exact.
		$hover_bg   = $this->hex( $a['submit_hover_bg'] ?? '', '' );
		$hover_text = $this->hex( $a['submit_hover_text'] ?? '', '' );
		if ( '' !== $hover_bg || '' !== $hover_text ) {
			$rules = '' !== $hover_bg ? 'background:' . $hover_bg . ';filter:none;' : '';
			$rules .= '' !== $hover_text ? 'color:' . $hover_text . ';' : '';
			$css   .= '.scch-root .scch-submit:hover:not(:disabled){' . $rules . '}';
		}

		// Dark palette. "Always dark" applies it unconditionally; "follow
		// visitor preference" scopes it to the media query, matching the
		// selector the stylesheet uses so these overrides win.
		$dark_css = $dark ? implode( ';', $dark ) . ';' : '';

		if ( 'dark' === $a['dark_mode'] ) {
			$css .= '.scch-root{color-scheme:dark;' . $dark_css . '}';
		} else {
			$css .= '.scch-root{color-scheme:' . ( 'light' === $a['dark_mode'] ? 'light' : 'light dark' ) . ';}';
			if ( '' !== $dark_css && 'light' !== $a['dark_mode'] ) {
				$css .= '@media (prefers-color-scheme: dark){.scch-root:not([data-forced-light]){' . $dark_css . '}}';
			}
		}

		return $css;
	}

	/**
	 * Format a single token value for CSS output.
	 *
	 * @param mixed $value Stored value.
	 * @param array $field Field definition.
	 */
	private static function token_value( $value, array $field ): string {
		switch ( $field['type'] ) {
			case 'px':
				return (int) $value . 'px';
			case 'pct':
				return (int) $value . '%';
			case 'num':
				return (string) (int) $value;
			case 'dec':
				return (string) (float) $value . ( $field['unit'] ?? '' );
			case 'font':
				$font = trim( (string) $value );
				// Already restricted to font-stack characters on save.
				return '' === $font ? '' : $font;
			case 'select':
				return isset( $field['options'][ (string) $value ] ) ? (string) $value : '';
			case 'color':
				if ( 'transparent' === $value ) {
					return 'transparent';
				}
				return (string) ( sanitize_hex_color( (string) $value ) ?: '' );
		}

		return '';
	}

	/**
	 * The brand gradient built from the two brand colors and the angle.
	 *
	 * @param array $a Appearance settings.
	 */
	private function gradient( array $a ): string {
		return sprintf(
			'linear-gradient(%ddeg, %s 0%%, %s 100%%)',
			(int) ( $a['gradient_angle'] ?? 135 ),
			$this->hex( $a['primary_color'] ?? '', '#2563eb' ),
			$this->hex( $a['secondary_color'] ?? '', '#7c3aed' )
		);
	}

	/**
	 * A box-shadow built from color, opacity, blur, and offset.
	 *
	 * @param bool  $enabled Whether a shadow is drawn at all.
	 * @param mixed $offset  Vertical offset in px.
	 * @param mixed $blur    Blur radius in px.
	 * @param mixed $color   Hex color.
	 * @param mixed $opacity Opacity percentage.
	 */
	private function shadow( bool $enabled, $offset, $blur, $color, $opacity ): string {
		if ( ! $enabled ) {
			return 'none';
		}

		return sprintf( '0 %dpx %dpx %s', (int) $offset, (int) $blur, $this->rgba( $color, $opacity ) );
	}

	/**
	 * Convert a hex color plus an opacity percentage to an rgba() string.
	 *
	 * @param mixed $color   Hex color.
	 * @param mixed $opacity Percentage, 0–100.
	 */
	private function rgba( $color, $opacity ): string {
		$hex = ltrim( $this->hex( $color, '#000000' ), '#' );

		if ( 3 === strlen( $hex ) ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}

		$alpha = max( 0, min( 100, (int) $opacity ) ) / 100;

		return sprintf(
			'rgba(%d,%d,%d,%s)',
			hexdec( substr( $hex, 0, 2 ) ),
			hexdec( substr( $hex, 2, 2 ) ),
			hexdec( substr( $hex, 4, 2 ) ),
			rtrim( rtrim( number_format( $alpha, 2, '.', '' ), '0' ), '.' ) ?: '0'
		);
	}

	/**
	 * Validate a hex color with a fallback.
	 *
	 * @param mixed  $value    Raw color.
	 * @param string $fallback Value used when invalid or empty.
	 */
	private function hex( $value, string $fallback ): string {
		return (string) ( sanitize_hex_color( (string) $value ) ?: $fallback );
	}

	/**
	 * Border color allowing the transparent keyword.
	 *
	 * @param mixed $value Raw color.
	 */
	private function border_color( $value ): string {
		if ( 'transparent' === $value || '' === $value ) {
			return 'transparent';
		}
		return $this->hex( $value, 'transparent' );
	}

	/**
	 * Print the widget template in the footer.
	 */
	public function render_widget(): void {
		if ( ! $this->should_render() ) {
			return;
		}

		$t         = Settings::group( 'scch_triggers' );
		$selectors = array_values( array_unique( array_filter( array_map( 'trim', preg_split( '/[\r\n]+/', (string) $t['selectors'] ) ) ) ) );

		/**
		 * Filter the external trigger configuration handed to the frontend.
		 *
		 * @param array $config { enabled: bool, selectors: string[], autoOpen: bool }
		 */
		$triggers = apply_filters(
			'scch_external_trigger',
			array(
				'enabled'   => ! empty( $t['enabled'] ),
				'selectors' => $selectors,
				'autoOpen'  => $this->auto_open,
			)
		);

		// Printed at wp_footer (before the footer script prints), so
		// do_action( 'scch_open_chat' ) fired anywhere earlier is honored.
		wp_add_inline_script( 'scch-frontend', 'window.scchTriggers = ' . wp_json_encode( $triggers ) . ';', 'before' );

		$appearance = Settings::group( 'scch_appearance' );
		$contact    = Settings::group( 'scch_contact' );
		$form       = Settings::group( 'scch_form' );
		$fields     = Settings::form_fields();
		$services   = Settings::services();

		// No challenge is generated here. Doing so wrote two options rows on
		// every single page view and embedded a single-use token into markup
		// that a page cache then served to every visitor. The widget renders
		// the CAPTCHA field empty and the script fetches a challenge when the
		// visitor opens the form. See Ajax::refresh_captcha().
		$captcha = Captcha::enabled();

		include SCCH_PATH . 'templates/widget.php';
	}

	/**
	 * Inline SVG for a named built-in icon, or an uploaded custom SVG/image.
	 *
	 * @param string $name       Icon key.
	 * @param string $custom_url Custom icon URL from settings.
	 */
	public static function icon( string $name, string $custom_url = '' ): string {
		if ( 'custom' === $name && $custom_url ) {
			return sprintf(
				'<img src="%s" alt="" aria-hidden="true" class="scch-custom-icon" />',
				esc_url( $custom_url )
			);
		}

		$icons = array(
			'chat-bubble' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 3C6.48 3 2 6.94 2 11.8c0 2.5 1.2 4.75 3.14 6.35-.14 1.14-.62 2.6-1.72 3.6 1.9-.06 3.62-.8 4.9-1.7 1.14.36 2.38.55 3.68.55 5.52 0 10-3.94 10-8.8S17.52 3 12 3z"/></svg>',
			'phone'       => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M6.62 10.79a15.05 15.05 0 0 0 6.59 6.59l2.2-2.2a1 1 0 0 1 1.02-.24c1.12.37 2.33.57 3.57.57a1 1 0 0 1 1 1V20a1 1 0 0 1-1 1C10.85 21 3 13.15 3 3.5a1 1 0 0 1 1-1H7.5a1 1 0 0 1 1 1c0 1.24.2 2.45.57 3.57a1 1 0 0 1-.25 1.02l-2.2 2.2z"/></svg>',
			'sms'         => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20 2H4a2 2 0 0 0-2 2v18l4-4h14a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2zM8 11H6V9h2v2zm5 0h-2V9h2v2zm5 0h-2V9h2v2z"/></svg>',
			'rocket'      => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2c3.5 1.5 6 5 6 9 0 1.4-.3 2.7-.8 3.9L20 18l-3 1-1 3-3.1-2.8c-.6.1-1.3.2-1.9.2s-1.3-.1-1.9-.2L6 22l-1-3-3-1 2.8-3.1C4.3 13.7 4 12.4 4 11c0-4 2.5-7.5 6-9h2zm0 5a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/></svg>',
			'headset'     => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a9 9 0 0 0-9 9v7a3 3 0 0 0 3 3h2v-8H5v-2a7 7 0 1 1 14 0v2h-3v8h2a3 3 0 0 0 3-3v-7a9 9 0 0 0-9-9z"/></svg>',
			'close'       => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.3 5.71 12 12.01l-6.3-6.3-1.4 1.42 6.29 6.29-6.3 6.3 1.42 1.41 6.29-6.29 6.3 6.3 1.41-1.42-6.29-6.3 6.3-6.29z"/></svg>',
			'refresh'     => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.65 6.35A8 8 0 1 0 19.73 14h-2.08A6 6 0 1 1 12 6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/></svg>',
		);

		return $icons[ $name ] ?? $icons['chat-bubble'];
	}

	/**
	 * Allowed tags/attributes for inline SVG icons registered through the
	 * scch_channels filter, for use with wp_kses().
	 *
	 * @return array<string,array<string,bool>>
	 */
	public static function svg_kses(): array {
		return array(
			'svg'    => array(
				'xmlns'        => true,
				'viewbox'      => true,
				'fill'         => true,
				'width'        => true,
				'height'       => true,
				'aria-hidden'  => true,
				'focusable'    => true,
				'class'        => true,
			),
			'path'   => array(
				'd'               => true,
				'fill'            => true,
				'fill-rule'       => true,
				'clip-rule'       => true,
				'stroke'          => true,
				'stroke-width'    => true,
				'stroke-linecap'  => true,
				'stroke-linejoin' => true,
			),
			'g'      => array( 'fill' => true, 'stroke' => true ),
			'circle' => array( 'cx' => true, 'cy' => true, 'r' => true, 'fill' => true ),
			'rect'   => array( 'x' => true, 'y' => true, 'width' => true, 'height' => true, 'rx' => true, 'fill' => true ),
			'line'   => array( 'x1' => true, 'y1' => true, 'x2' => true, 'y2' => true, 'stroke' => true ),
		);
	}
}
