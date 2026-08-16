<?php
/**
 * Front-end widget rendering and assets.
 *
 * @package SCCH
 */

namespace SCCH\Frontend;

use SCCH\Captcha;
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
				),
			)
		);
	}

	/**
	 * Build CSS custom properties from Appearance settings.
	 */
	private function dynamic_css(): string {
		$a = Settings::group( 'scch_appearance' );

		$background = ! empty( $a['use_gradient'] )
			? sprintf( 'linear-gradient(135deg, %s 0%%, %s 100%%)', $a['primary_color'], $a['secondary_color'] )
			: $a['button_bg'];

		$shadow = ! empty( $a['shadow'] ) ? '0 8px 24px rgba(0,0,0,.22)' : 'none';

		$css = sprintf(
			':root{--scch-primary:%1$s;--scch-secondary:%2$s;--scch-btn-bg:%3$s;--scch-icon-color:%4$s;--scch-icon-size:%5$dpx;--scch-btn-size:%6$dpx;--scch-btn-margin:%7$dpx;--scch-btn-radius:%8$d%%;--scch-border:%9$dpx solid %10$s;--scch-shadow:%11$s;--scch-popup-width:%12$dpx;--scch-popup-radius:%13$dpx;--scch-font:%14$s;--scch-z:%15$d;}',
			sanitize_hex_color( $a['primary_color'] ) ?: '#2563eb',
			sanitize_hex_color( $a['secondary_color'] ) ?: '#7c3aed',
			esc_attr( $background ),
			sanitize_hex_color( $a['icon_color'] ) ?: '#ffffff',
			(int) $a['icon_size'],
			(int) $a['button_size'],
			(int) $a['button_margin'],
			(int) $a['border_radius'],
			(int) $a['border_width'],
			'transparent' === $a['border_color'] ? 'transparent' : ( sanitize_hex_color( $a['border_color'] ) ?: 'transparent' ),
			$shadow,
			(int) $a['popup_width'],
			(int) $a['popup_radius'],
			'inherit' === $a['font_family'] ? 'inherit' : esc_attr( $a['font_family'] ),
			(int) $a['z_index']
		);

		if ( empty( $a['overlay'] ) ) {
			$css .= '.scch-overlay{background:transparent !important;backdrop-filter:none !important;}';
		}

		// Panel text, heading, and submit button colors. Empty bg = primary→secondary gradient.
		$gradient   = sprintf(
			'linear-gradient(135deg, %s 0%%, %s 100%%)',
			sanitize_hex_color( $a['primary_color'] ) ?: '#2563eb',
			sanitize_hex_color( $a['secondary_color'] ) ?: '#7c3aed'
		);
		$heading_bg = sanitize_hex_color( $a['heading_bg'] ?? '' ) ?: $gradient;
		$submit_bg  = sanitize_hex_color( $a['submit_bg'] ?? '' ) ?: $gradient;

		$css .= sprintf(
			'.scch-root{--scch-text:%1$s;--scch-heading-bg:%2$s;--scch-heading-text:%3$s;--scch-submit-bg:%4$s;--scch-submit-text:%5$s;}',
			sanitize_hex_color( $a['text_color'] ?? '' ) ?: '#111827',
			esc_attr( $heading_bg ),
			sanitize_hex_color( $a['heading_text'] ?? '' ) ?: '#ffffff',
			esc_attr( $submit_bg ),
			sanitize_hex_color( $a['submit_text'] ?? '' ) ?: '#ffffff'
		);

		// Direct rules with concrete values: higher specificity than the
		// stylesheet and independent of custom-property support, so CSS
		// optimizers or aggressive theme styles can't break the colors.
		$heading_text = sanitize_hex_color( $a['heading_text'] ?? '' ) ?: '#ffffff';
		$css         .= sprintf(
			'.scch-root .scch-panel-header{background:%1$s;color:%2$s;}.scch-root .scch-panel-title{color:%2$s;}.scch-root .scch-submit{background:%3$s;color:%4$s;}',
			esc_attr( $heading_bg ),
			$heading_text,
			esc_attr( $submit_bg ),
			sanitize_hex_color( $a['submit_text'] ?? '' ) ?: '#ffffff'
		);

		// Custom hover colors: emitted only when set, so the default
		// brightness hover effect stays intact otherwise.
		$hover_bg   = sanitize_hex_color( $a['submit_hover_bg'] ?? '' );
		$hover_text = sanitize_hex_color( $a['submit_hover_text'] ?? '' );
		if ( $hover_bg || $hover_text ) {
			$hover_rules = '';
			if ( $hover_bg ) {
				// filter:none so brightness doesn't distort the exact chosen color.
				$hover_rules .= 'background:' . $hover_bg . ';filter:none;';
			}
			if ( $hover_text ) {
				$hover_rules .= 'color:' . $hover_text . ';';
			}
			$css .= '.scch-root .scch-submit:hover:not(:disabled){' . $hover_rules . '}';
		}

		// Dark mode overall text color: same selector as the stylesheet's
		// dark palette, printed later, so it wins only when set.
		$dark_text = sanitize_hex_color( $a['dark_text_color'] ?? '' );
		if ( $dark_text ) {
			$css .= '@media (prefers-color-scheme: dark){.scch-root:not([data-forced-light]){--scch-text:' . $dark_text . ';}}';
		}

		if ( 'dark' === $a['dark_mode'] ) {
			$css .= '.scch-root{color-scheme:dark;}';
		} elseif ( 'light' === $a['dark_mode'] ) {
			$css .= '.scch-root{color-scheme:light;}';
		}

		return $css;
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
		$captcha    = Captcha::enabled() ? Captcha::generate() : null;

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
