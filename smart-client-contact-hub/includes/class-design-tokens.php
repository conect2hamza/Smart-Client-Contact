<?php
/**
 * Design token registry for the Appearance screen.
 *
 * @package SCCH
 */

namespace SCCH;

defined( 'ABSPATH' ) || exit;

/**
 * Single source of truth for every customizable design value.
 *
 * One declarative schema drives four consumers, so a new control is added in
 * exactly one place and can never drift between them:
 *
 *   Settings::defaults()   — default value for each key
 *   Admin::sanitize_group() — type-aware sanitizing on save
 *   Frontend::dynamic_css() — the CSS custom property emitted to the page
 *   admin/views/appearance.php — the rendered control, in its section
 *
 * Field types:
 *   color    Hex color. 'empty' => true allows a blank value, which emits no
 *            custom property at all so the stylesheet's var() fallback wins.
 *   px       Integer with a px unit.
 *   pct      Integer with a % unit.
 *   num      Unitless integer.
 *   dec      Decimal (line height).
 *   toggle   0 or 1.
 *   select   One of 'options'.
 *   text     Free text, sanitize_text_field.
 *   font     CSS font stack, restricted character set.
 *   url      URL.
 */
class Design_Tokens {

	/**
	 * Section and field definitions.
	 *
	 * @return array<string,array>
	 */
	public static function schema(): array {
		return array(

			/* ---------------------------------------------------------- */
			'layout'   => array(
				'label'  => __( 'Layout & Behavior', 'smart-client-contact-hub' ),
				'intro'  => __( 'Where the widget sits and how it announces itself.', 'smart-client-contact-hub' ),
				'fields' => array(
					'position'      => array(
						'type'    => 'select',
						'default' => 'bottom-right',
						'label'   => __( 'Screen position', 'smart-client-contact-hub' ),
						'options' => array(
							'bottom-right' => __( 'Bottom right', 'smart-client-contact-hub' ),
							'bottom-left'  => __( 'Bottom left', 'smart-client-contact-hub' ),
						),
					),
					'animation'     => array(
						'type'    => 'select',
						'default' => 'pulse',
						'label'   => __( 'Launcher animation', 'smart-client-contact-hub' ),
						'options' => array(
							'pulse'  => __( 'Pulse', 'smart-client-contact-hub' ),
							'bounce' => __( 'Bounce', 'smart-client-contact-hub' ),
							'scale'  => __( 'Scale in', 'smart-client-contact-hub' ),
							'fade'   => __( 'Fade in', 'smart-client-contact-hub' ),
							'none'   => __( 'None', 'smart-client-contact-hub' ),
						),
					),
					'button_margin' => array(
						'type'    => 'px',
						'default' => 24,
						'min'     => 0,
						'max'     => 200,
						'css'     => '--scch-btn-margin',
						'label'   => __( 'Distance from screen edge', 'smart-client-contact-hub' ),
					),
					'z_index'       => array(
						'type'    => 'num',
						'default' => 99990,
						'min'     => 1,
						'max'     => 2147483000,
						'css'     => '--scch-z',
						'label'   => __( 'Z-index', 'smart-client-contact-hub' ),
						'help'    => __( 'Raise this if another fixed element covers the widget.', 'smart-client-contact-hub' ),
					),
					'dark_mode'     => array(
						'type'    => 'select',
						'default' => 'auto',
						'label'   => __( 'Color scheme', 'smart-client-contact-hub' ),
						'options' => array(
							'auto'  => __( 'Follow visitor preference', 'smart-client-contact-hub' ),
							'light' => __( 'Always light', 'smart-client-contact-hub' ),
							'dark'  => __( 'Always dark', 'smart-client-contact-hub' ),
						),
					),
				),
			),

			/* ---------------------------------------------------------- */
			'brand'    => array(
				'label'  => __( 'Brand Colors', 'smart-client-contact-hub' ),
				'intro'  => __( 'The two colors every gradient and accent in the widget is derived from. Individual elements below can override them.', 'smart-client-contact-hub' ),
				'fields' => array(
					'primary_color'   => array(
						'type'    => 'color',
						'default' => '#2563eb',
						'css'     => '--scch-primary',
						'label'   => __( 'Primary color', 'smart-client-contact-hub' ),
					),
					'secondary_color' => array(
						'type'    => 'color',
						'default' => '#7c3aed',
						'css'     => '--scch-secondary',
						'label'   => __( 'Secondary color', 'smart-client-contact-hub' ),
					),
					'gradient_angle'  => array(
						'type'    => 'num',
						'default' => 135,
						'min'     => 0,
						'max'     => 360,
						'label'   => __( 'Gradient angle (degrees)', 'smart-client-contact-hub' ),
					),
					'use_gradient'    => array(
						'type'    => 'toggle',
						'default' => 1,
						'label'   => __( 'Use the gradient on the launcher', 'smart-client-contact-hub' ),
						'help'    => __( 'Off uses the solid launcher background below.', 'smart-client-contact-hub' ),
					),
				),
			),

			/* ---------------------------------------------------------- */
			'launcher' => array(
				'label'  => __( 'Floating Button', 'smart-client-contact-hub' ),
				'intro'  => __( 'The button visitors see before they open anything.', 'smart-client-contact-hub' ),
				'fields' => array(
					'icon'              => array(
						'type'     => 'icon',
						'default'  => 'chat-bubble',
						'label'    => __( 'Icon', 'smart-client-contact-hub' ),
						'url_key'  => 'custom_icon_url',
						'help'     => __( 'Built-in icons are inline SVG — no icon font is loaded. Choose "your own image" to upload instead.', 'smart-client-contact-hub' ),
					),
					'custom_icon_url'   => array(
						'type'    => 'url',
						'default' => '',
						'label'   => __( 'Custom icon', 'smart-client-contact-hub' ),
						'hidden'  => true,
					),
					'button_size'       => array(
						'type'    => 'px',
						'default' => 60,
						'min'     => 36,
						'max'     => 140,
						'css'     => '--scch-btn-size',
						'label'   => __( 'Button size', 'smart-client-contact-hub' ),
					),
					'icon_size'         => array(
						'type'    => 'px',
						'default' => 28,
						'min'     => 12,
						'max'     => 96,
						'css'     => '--scch-icon-size',
						'label'   => __( 'Icon size', 'smart-client-contact-hub' ),
					),
					'button_bg'         => array(
						'type'    => 'color',
						'default' => '#2563eb',
						'label'   => __( 'Solid background', 'smart-client-contact-hub' ),
						'help'    => __( 'Used when the brand gradient is switched off.', 'smart-client-contact-hub' ),
					),
					'button_hover_bg'   => array(
						'type'    => 'color',
						'default' => '',
						'empty'   => true,
						'css'     => '--scch-btn-hover-bg',
						'label'   => __( 'Background on hover', 'smart-client-contact-hub' ),
						'help'    => __( 'Leave empty to keep the normal background.', 'smart-client-contact-hub' ),
					),
					'icon_color'        => array(
						'type'    => 'color',
						'default' => '#ffffff',
						'css'     => '--scch-icon-color',
						'label'   => __( 'Icon color', 'smart-client-contact-hub' ),
					),
					'icon_hover_color'  => array(
						'type'    => 'color',
						'default' => '',
						'empty'   => true,
						'css'     => '--scch-icon-hover-color',
						'label'   => __( 'Icon color on hover', 'smart-client-contact-hub' ),
					),
					'border_radius'     => array(
						'type'    => 'pct',
						'default' => 50,
						'min'     => 0,
						'max'     => 100,
						'css'     => '--scch-btn-radius',
						'label'   => __( 'Corner rounding', 'smart-client-contact-hub' ),
						'help'    => __( '50% is a circle, 0% is a square.', 'smart-client-contact-hub' ),
					),
					'border_width'      => array(
						'type'    => 'px',
						'default' => 0,
						'min'     => 0,
						'max'     => 12,
						'label'   => __( 'Border width', 'smart-client-contact-hub' ),
					),
					'border_color'      => array(
						'type'         => 'color',
						'default'      => 'transparent',
						'empty'        => true,
						'transparent'  => true,
						'label'        => __( 'Border color', 'smart-client-contact-hub' ),
					),
					'shadow'            => array(
						'type'    => 'toggle',
						'default' => 1,
						'label'   => __( 'Drop shadow', 'smart-client-contact-hub' ),
					),
					'shadow_color'      => array(
						'type'    => 'color',
						'default' => '#000000',
						'label'   => __( 'Shadow color', 'smart-client-contact-hub' ),
					),
					'shadow_opacity'    => array(
						'type'    => 'num',
						'default' => 22,
						'min'     => 0,
						'max'     => 100,
						'label'   => __( 'Shadow opacity (%)', 'smart-client-contact-hub' ),
					),
					'shadow_blur'       => array(
						'type'    => 'px',
						'default' => 24,
						'min'     => 0,
						'max'     => 80,
						'label'   => __( 'Shadow blur', 'smart-client-contact-hub' ),
					),
					'shadow_offset'     => array(
						'type'    => 'px',
						'default' => 8,
						'min'     => -40,
						'max'     => 40,
						'label'   => __( 'Shadow vertical offset', 'smart-client-contact-hub' ),
					),
				),
			),

			/* ---------------------------------------------------------- */
			'typography' => array(
				'label'  => __( 'Typography', 'smart-client-contact-hub' ),
				'intro'  => __( 'Type applies across the whole widget. Individual sizes are set in their own sections.', 'smart-client-contact-hub' ),
				'fields' => array(
					'font_family'         => array(
						'type'    => 'font',
						'default' => 'inherit',
						'css'     => '--scch-font',
						'label'   => __( 'Body font stack', 'smart-client-contact-hub' ),
						'help'    => __( 'Use "inherit" to match your theme, or any CSS font stack.', 'smart-client-contact-hub' ),
					),
					'heading_font_family' => array(
						'type'    => 'font',
						'default' => '',
						'empty'   => true,
						'css'     => '--scch-font-heading',
						'label'   => __( 'Heading font stack', 'smart-client-contact-hub' ),
						'help'    => __( 'Leave empty to use the body font for headings too.', 'smart-client-contact-hub' ),
					),
					'base_font_size'      => array(
						'type'    => 'px',
						'default' => 15,
						'min'     => 10,
						'max'     => 24,
						'css'     => '--scch-fs-base',
						'label'   => __( 'Base font size', 'smart-client-contact-hub' ),
					),
					'line_height'         => array(
						'type'    => 'dec',
						'default' => '1.5',
						'min'     => 1,
						'max'     => 2.5,
						'css'     => '--scch-lh',
						'label'   => __( 'Line height', 'smart-client-contact-hub' ),
					),
					'letter_spacing'      => array(
						'type'    => 'dec',
						'default' => '0',
						'min'     => -2,
						'max'     => 5,
						'unit'    => 'px',
						'css'     => '--scch-tracking',
						'label'   => __( 'Letter spacing (px)', 'smart-client-contact-hub' ),
					),
				),
			),

			/* ---------------------------------------------------------- */
			'panel'    => array(
				'label'  => __( 'Popup Container', 'smart-client-contact-hub' ),
				'intro'  => __( 'The panel that opens above the button.', 'smart-client-contact-hub' ),
				'fields' => array(
					'popup_width'         => array(
						'type'    => 'px',
						'default' => 380,
						'min'     => 260,
						'max'     => 720,
						'css'     => '--scch-popup-width',
						'label'   => __( 'Width', 'smart-client-contact-hub' ),
					),
					'popup_radius'        => array(
						'type'    => 'px',
						'default' => 16,
						'min'     => 0,
						'max'     => 48,
						'css'     => '--scch-popup-radius',
						'label'   => __( 'Corner radius', 'smart-client-contact-hub' ),
					),
					'surface_color'       => array(
						'type'    => 'color',
						'default' => '#ffffff',
						'css'     => '--scch-surface',
						'label'   => __( 'Panel background', 'smart-client-contact-hub' ),
					),
					'text_color'          => array(
						'type'    => 'color',
						'default' => '#111827',
						'css'     => '--scch-text',
						'label'   => __( 'Body text color', 'smart-client-contact-hub' ),
					),
					'muted_color'         => array(
						'type'    => 'color',
						'default' => '#6b7280',
						'css'     => '--scch-muted',
						'label'   => __( 'Muted text color', 'smart-client-contact-hub' ),
					),
					'line_color'          => array(
						'type'    => 'color',
						'default' => '#e5e7eb',
						'css'     => '--scch-line',
						'label'   => __( 'Divider / border color', 'smart-client-contact-hub' ),
					),
					'panel_border_width'  => array(
						'type'    => 'px',
						'default' => 0,
						'min'     => 0,
						'max'     => 8,
						'label'   => __( 'Panel border width', 'smart-client-contact-hub' ),
					),
					'panel_border_color'  => array(
						'type'    => 'color',
						'default' => '',
						'empty'   => true,
						'label'   => __( 'Panel border color', 'smart-client-contact-hub' ),
					),
					'panel_padding_y'     => array(
						'type'    => 'px',
						'default' => 18,
						'min'     => 0,
						'max'     => 64,
						'css'     => '--scch-view-pad-y',
						'label'   => __( 'Content padding (vertical)', 'smart-client-contact-hub' ),
					),
					'panel_padding_x'     => array(
						'type'    => 'px',
						'default' => 22,
						'min'     => 0,
						'max'     => 64,
						'css'     => '--scch-view-pad-x',
						'label'   => __( 'Content padding (horizontal)', 'smart-client-contact-hub' ),
					),
					'panel_shadow_color'  => array(
						'type'    => 'color',
						'default' => '#000000',
						'label'   => __( 'Panel shadow color', 'smart-client-contact-hub' ),
					),
					'panel_shadow_opacity' => array(
						'type'    => 'num',
						'default' => 25,
						'min'     => 0,
						'max'     => 100,
						'label'   => __( 'Panel shadow opacity (%)', 'smart-client-contact-hub' ),
					),
					'panel_shadow_blur'   => array(
						'type'    => 'px',
						'default' => 50,
						'min'     => 0,
						'max'     => 120,
						'label'   => __( 'Panel shadow blur', 'smart-client-contact-hub' ),
					),
					'overlay'             => array(
						'type'    => 'toggle',
						'default' => 1,
						'label'   => __( 'Dim the page behind the popup', 'smart-client-contact-hub' ),
					),
					'overlay_color'       => array(
						'type'    => 'color',
						'default' => '#111827',
						'label'   => __( 'Overlay color', 'smart-client-contact-hub' ),
					),
					'overlay_opacity'     => array(
						'type'    => 'num',
						'default' => 35,
						'min'     => 0,
						'max'     => 100,
						'label'   => __( 'Overlay opacity (%)', 'smart-client-contact-hub' ),
					),
					'overlay_blur'        => array(
						'type'    => 'px',
						'default' => 2,
						'min'     => 0,
						'max'     => 20,
						'css'     => '--scch-overlay-blur',
						'label'   => __( 'Overlay blur', 'smart-client-contact-hub' ),
					),
				),
			),

			/* ---------------------------------------------------------- */
			'header'   => array(
				'label'  => __( 'Popup Header', 'smart-client-contact-hub' ),
				'intro'  => __( 'The colored band at the top of the panel, with the title and close button.', 'smart-client-contact-hub' ),
				'fields' => array(
					'heading_bg'         => array(
						'type'    => 'color',
						'default' => '',
						'empty'   => true,
						'css'     => '--scch-heading-bg',
						'label'   => __( 'Header background', 'smart-client-contact-hub' ),
						'help'    => __( 'Leave empty to use the brand gradient.', 'smart-client-contact-hub' ),
					),
					'heading_text'       => array(
						'type'    => 'color',
						'default' => '#ffffff',
						'css'     => '--scch-heading-text',
						'label'   => __( 'Header text color', 'smart-client-contact-hub' ),
					),
					'heading_pad_y'      => array(
						'type'    => 'px',
						'default' => 22,
						'min'     => 0,
						'max'     => 64,
						'css'     => '--scch-heading-pad-y',
						'label'   => __( 'Header padding (vertical)', 'smart-client-contact-hub' ),
					),
					'heading_pad_x'      => array(
						'type'    => 'px',
						'default' => 22,
						'min'     => 0,
						'max'     => 64,
						'css'     => '--scch-heading-pad-x',
						'label'   => __( 'Header padding (horizontal)', 'smart-client-contact-hub' ),
					),
					'title_size'         => array(
						'type'    => 'px',
						'default' => 18,
						'min'     => 11,
						'max'     => 40,
						'css'     => '--scch-title-size',
						'label'   => __( 'Title size', 'smart-client-contact-hub' ),
					),
					'title_weight'       => array(
						'type'    => 'select',
						'default' => '700',
						'css'     => '--scch-title-weight',
						'label'   => __( 'Title weight', 'smart-client-contact-hub' ),
						'options' => self::weights(),
					),
					'intro_size'         => array(
						'type'    => 'px',
						'default' => 13,
						'min'     => 10,
						'max'     => 24,
						'css'     => '--scch-intro-size',
						'label'   => __( 'Intro text size', 'smart-client-contact-hub' ),
					),
					'intro_color'        => array(
						'type'    => 'color',
						'default' => '',
						'empty'   => true,
						'css'     => '--scch-intro-color',
						'label'   => __( 'Intro text color', 'smart-client-contact-hub' ),
						'help'    => __( 'Leave empty to use a softened header text color.', 'smart-client-contact-hub' ),
					),
					'close_bg'           => array(
						'type'    => 'color',
						'default' => '',
						'empty'   => true,
						'css'     => '--scch-close-bg',
						'label'   => __( 'Close button background', 'smart-client-contact-hub' ),
					),
					'close_color'        => array(
						'type'    => 'color',
						'default' => '#ffffff',
						'css'     => '--scch-close-color',
						'label'   => __( 'Close button icon color', 'smart-client-contact-hub' ),
					),
					'close_hover_bg'     => array(
						'type'    => 'color',
						'default' => '',
						'empty'   => true,
						'css'     => '--scch-close-hover-bg',
						'label'   => __( 'Close button background on hover', 'smart-client-contact-hub' ),
					),
					'close_size'         => array(
						'type'    => 'px',
						'default' => 32,
						'min'     => 20,
						'max'     => 56,
						'css'     => '--scch-close-size',
						'label'   => __( 'Close button size', 'smart-client-contact-hub' ),
					),
					'close_radius'       => array(
						'type'    => 'pct',
						'default' => 50,
						'min'     => 0,
						'max'     => 50,
						'css'     => '--scch-close-radius',
						'label'   => __( 'Close button rounding', 'smart-client-contact-hub' ),
					),
				),
			),

			/* ---------------------------------------------------------- */
			'channels' => array(
				'label'  => __( 'Channel Buttons', 'smart-client-contact-hub' ),
				'intro'  => __( 'The stacked choices on the first step — the form, call, and text options.', 'smart-client-contact-hub' ),
				'fields' => array(
					'channel_bg'           => array(
						'type'    => 'color',
						'default' => '',
						'empty'   => true,
						'css'     => '--scch-channel-bg',
						'label'   => __( 'Background', 'smart-client-contact-hub' ),
						'help'    => __( 'Leave empty to match the panel background.', 'smart-client-contact-hub' ),
					),
					'channel_text'         => array(
						'type'    => 'color',
						'default' => '',
						'empty'   => true,
						'css'     => '--scch-channel-text',
						'label'   => __( 'Label color', 'smart-client-contact-hub' ),
					),
					'channel_border_width' => array(
						'type'    => 'px',
						'default' => 1,
						'min'     => 0,
						'max'     => 6,
						'css'     => '--scch-channel-bw',
						'label'   => __( 'Border width', 'smart-client-contact-hub' ),
					),
					'channel_border_color' => array(
						'type'    => 'color',
						'default' => '',
						'empty'   => true,
						'css'     => '--scch-channel-bc',
						'label'   => __( 'Border color', 'smart-client-contact-hub' ),
					),
					'channel_radius'       => array(
						'type'    => 'px',
						'default' => 12,
						'min'     => 0,
						'max'     => 40,
						'css'     => '--scch-channel-radius',
						'label'   => __( 'Corner radius', 'smart-client-contact-hub' ),
					),
					'channel_pad_y'        => array(
						'type'    => 'px',
						'default' => 14,
						'min'     => 0,
						'max'     => 40,
						'css'     => '--scch-channel-pad-y',
						'label'   => __( 'Padding (vertical)', 'smart-client-contact-hub' ),
					),
					'channel_pad_x'        => array(
						'type'    => 'px',
						'default' => 16,
						'min'     => 0,
						'max'     => 40,
						'css'     => '--scch-channel-pad-x',
						'label'   => __( 'Padding (horizontal)', 'smart-client-contact-hub' ),
					),
					'channel_gap'          => array(
						'type'    => 'px',
						'default' => 10,
						'min'     => 0,
						'max'     => 32,
						'css'     => '--scch-channel-gap',
						'label'   => __( 'Space between buttons', 'smart-client-contact-hub' ),
					),
					'channel_font_size'    => array(
						'type'    => 'px',
						'default' => 15,
						'min'     => 10,
						'max'     => 28,
						'css'     => '--scch-channel-fs',
						'label'   => __( 'Label size', 'smart-client-contact-hub' ),
					),
					'channel_font_weight'  => array(
						'type'    => 'select',
						'default' => '600',
						'css'     => '--scch-channel-fw',
						'label'   => __( 'Label weight', 'smart-client-contact-hub' ),
						'options' => self::weights(),
					),
					'channel_desc_size'    => array(
						'type'    => 'px',
						'default' => 12,
						'min'     => 8,
						'max'     => 22,
						'css'     => '--scch-channel-desc-fs',
						'label'   => __( 'Small-text size', 'smart-client-contact-hub' ),
						'help'    => __( 'The optional second line under a channel label.', 'smart-client-contact-hub' ),
					),
					'channel_desc_color'   => array(
						'type'    => 'color',
						'default' => '',
						'empty'   => true,
						'css'     => '--scch-channel-desc-color',
						'label'   => __( 'Small-text color', 'smart-client-contact-hub' ),
					),
					'channel_hover_bg'     => array(
						'type'    => 'color',
						'default' => '',
						'empty'   => true,
						'css'     => '--scch-channel-hover-bg',
						'label'   => __( 'Background on hover', 'smart-client-contact-hub' ),
					),
					'channel_hover_border' => array(
						'type'    => 'color',
						'default' => '',
						'empty'   => true,
						'css'     => '--scch-channel-hover-bc',
						'label'   => __( 'Border color on hover', 'smart-client-contact-hub' ),
						'help'    => __( 'Leave empty to use the primary color.', 'smart-client-contact-hub' ),
					),
					'channel_icon_bg'      => array(
						'type'    => 'color',
						'default' => '',
						'empty'   => true,
						'css'     => '--scch-cicon-bg',
						'label'   => __( 'Icon tile background', 'smart-client-contact-hub' ),
						'help'    => __( 'Leave empty to use the brand gradient.', 'smart-client-contact-hub' ),
					),
					'channel_icon_color'   => array(
						'type'    => 'color',
						'default' => '#ffffff',
						'css'     => '--scch-cicon-color',
						'label'   => __( 'Icon color', 'smart-client-contact-hub' ),
					),
					'channel_icon_box'     => array(
						'type'    => 'px',
						'default' => 40,
						'min'     => 0,
						'max'     => 80,
						'css'     => '--scch-cicon-box',
						'label'   => __( 'Icon tile size', 'smart-client-contact-hub' ),
					),
					'channel_icon_size'    => array(
						'type'    => 'px',
						'default' => 20,
						'min'     => 10,
						'max'     => 48,
						'css'     => '--scch-cicon-size',
						'label'   => __( 'Icon glyph size', 'smart-client-contact-hub' ),
					),
					'channel_icon_radius'  => array(
						'type'    => 'px',
						'default' => 10,
						'min'     => 0,
						'max'     => 40,
						'css'     => '--scch-cicon-radius',
						'label'   => __( 'Icon tile radius', 'smart-client-contact-hub' ),
					),
				),
			),

			/* ---------------------------------------------------------- */
			'form'     => array(
				'label'  => __( 'Form Fields', 'smart-client-contact-hub' ),
				'intro'  => __( 'Labels, inputs, and the CAPTCHA row on the lead form.', 'smart-client-contact-hub' ),
				'fields' => array(
					'form_title_size'    => array(
						'type'    => 'px',
						'default' => 16,
						'min'     => 11,
						'max'     => 32,
						'css'     => '--scch-form-title-size',
						'label'   => __( 'Form title size', 'smart-client-contact-hub' ),
					),
					'form_title_weight'  => array(
						'type'    => 'select',
						'default' => '700',
						'css'     => '--scch-form-title-weight',
						'label'   => __( 'Form title weight', 'smart-client-contact-hub' ),
						'options' => self::weights(),
					),
					'back_color'         => array(
						'type'    => 'color',
						'default' => '',
						'empty'   => true,
						'css'     => '--scch-back-color',
						'label'   => __( '"Back" link color', 'smart-client-contact-hub' ),
					),
					'label_color'        => array(
						'type'    => 'color',
						'default' => '',
						'empty'   => true,
						'css'     => '--scch-label-color',
						'label'   => __( 'Field label color', 'smart-client-contact-hub' ),
					),
					'label_size'         => array(
						'type'    => 'px',
						'default' => 13,
						'min'     => 9,
						'max'     => 22,
						'css'     => '--scch-label-size',
						'label'   => __( 'Field label size', 'smart-client-contact-hub' ),
					),
					'label_weight'       => array(
						'type'    => 'select',
						'default' => '600',
						'css'     => '--scch-label-weight',
						'label'   => __( 'Field label weight', 'smart-client-contact-hub' ),
						'options' => self::weights(),
					),
					'field_gap'          => array(
						'type'    => 'px',
						'default' => 14,
						'min'     => 0,
						'max'     => 40,
						'css'     => '--scch-field-gap',
						'label'   => __( 'Space between fields', 'smart-client-contact-hub' ),
					),
					'input_bg'           => array(
						'type'    => 'color',
						'default' => '',
						'empty'   => true,
						'css'     => '--scch-input-bg',
						'label'   => __( 'Input background', 'smart-client-contact-hub' ),
					),
					'input_text'         => array(
						'type'    => 'color',
						'default' => '',
						'empty'   => true,
						'css'     => '--scch-input-text',
						'label'   => __( 'Input text color', 'smart-client-contact-hub' ),
					),
					'placeholder_color'  => array(
						'type'    => 'color',
						'default' => '',
						'empty'   => true,
						'css'     => '--scch-placeholder',
						'label'   => __( 'Placeholder color', 'smart-client-contact-hub' ),
					),
					'input_border_width' => array(
						'type'    => 'px',
						'default' => 1,
						'min'     => 0,
						'max'     => 6,
						'css'     => '--scch-input-bw',
						'label'   => __( 'Input border width', 'smart-client-contact-hub' ),
					),
					'input_border_color' => array(
						'type'    => 'color',
						'default' => '',
						'empty'   => true,
						'css'     => '--scch-input-bc',
						'label'   => __( 'Input border color', 'smart-client-contact-hub' ),
					),
					'input_radius'       => array(
						'type'    => 'px',
						'default' => 9,
						'min'     => 0,
						'max'     => 40,
						'css'     => '--scch-input-radius',
						'label'   => __( 'Input corner radius', 'smart-client-contact-hub' ),
					),
					'input_pad_y'        => array(
						'type'    => 'px',
						'default' => 10,
						'min'     => 0,
						'max'     => 32,
						'css'     => '--scch-input-pad-y',
						'label'   => __( 'Input padding (vertical)', 'smart-client-contact-hub' ),
					),
					'input_pad_x'        => array(
						'type'    => 'px',
						'default' => 12,
						'min'     => 0,
						'max'     => 32,
						'css'     => '--scch-input-pad-x',
						'label'   => __( 'Input padding (horizontal)', 'smart-client-contact-hub' ),
					),
					'input_font_size'    => array(
						'type'    => 'px',
						'default' => 14,
						'min'     => 10,
						'max'     => 24,
						'css'     => '--scch-input-fs',
						'label'   => __( 'Input text size', 'smart-client-contact-hub' ),
					),
					'focus_color'        => array(
						'type'    => 'color',
						'default' => '',
						'empty'   => true,
						'css'     => '--scch-focus',
						'label'   => __( 'Focus highlight color', 'smart-client-contact-hub' ),
						'help'    => __( 'Leave empty to use the primary color.', 'smart-client-contact-hub' ),
					),
					'captcha_bg'         => array(
						'type'    => 'color',
						'default' => '',
						'empty'   => true,
						'css'     => '--scch-captcha-bg',
						'label'   => __( 'CAPTCHA question background', 'smart-client-contact-hub' ),
					),
					'captcha_text'       => array(
						'type'    => 'color',
						'default' => '',
						'empty'   => true,
						'css'     => '--scch-captcha-text',
						'label'   => __( 'CAPTCHA question text color', 'smart-client-contact-hub' ),
					),
				),
			),

			/* ---------------------------------------------------------- */
			'submit'   => array(
				'label'  => __( 'Submit Button', 'smart-client-contact-hub' ),
				'intro'  => __( 'The button that sends the lead form.', 'smart-client-contact-hub' ),
				'fields' => array(
					'submit_bg'          => array(
						'type'    => 'color',
						'default' => '',
						'empty'   => true,
						'css'     => '--scch-submit-bg',
						'label'   => __( 'Background', 'smart-client-contact-hub' ),
						'help'    => __( 'Leave empty to use the brand gradient.', 'smart-client-contact-hub' ),
					),
					'submit_text'        => array(
						'type'    => 'color',
						'default' => '#ffffff',
						'css'     => '--scch-submit-text',
						'label'   => __( 'Text color', 'smart-client-contact-hub' ),
					),
					'submit_hover_bg'    => array(
						'type'    => 'color',
						'default' => '',
						'empty'   => true,
						'label'   => __( 'Background on hover', 'smart-client-contact-hub' ),
						'help'    => __( 'Leave empty to keep the default brightness effect.', 'smart-client-contact-hub' ),
					),
					'submit_hover_text'  => array(
						'type'    => 'color',
						'default' => '',
						'empty'   => true,
						'label'   => __( 'Text color on hover', 'smart-client-contact-hub' ),
					),
					'submit_radius'      => array(
						'type'    => 'px',
						'default' => 10,
						'min'     => 0,
						'max'     => 40,
						'css'     => '--scch-submit-radius',
						'label'   => __( 'Corner radius', 'smart-client-contact-hub' ),
					),
					'submit_pad'         => array(
						'type'    => 'px',
						'default' => 13,
						'min'     => 4,
						'max'     => 40,
						'css'     => '--scch-submit-pad',
						'label'   => __( 'Padding', 'smart-client-contact-hub' ),
					),
					'submit_font_size'   => array(
						'type'    => 'px',
						'default' => 15,
						'min'     => 10,
						'max'     => 28,
						'css'     => '--scch-submit-fs',
						'label'   => __( 'Text size', 'smart-client-contact-hub' ),
					),
					'submit_font_weight' => array(
						'type'    => 'select',
						'default' => '700',
						'css'     => '--scch-submit-fw',
						'label'   => __( 'Text weight', 'smart-client-contact-hub' ),
						'options' => self::weights(),
					),
					'submit_border_width' => array(
						'type'    => 'px',
						'default' => 0,
						'min'     => 0,
						'max'     => 6,
						'css'     => '--scch-submit-bw',
						'label'   => __( 'Border width', 'smart-client-contact-hub' ),
					),
					'submit_border_color' => array(
						'type'    => 'color',
						'default' => '',
						'empty'   => true,
						'css'     => '--scch-submit-bc',
						'label'   => __( 'Border color', 'smart-client-contact-hub' ),
					),
				),
			),

			/* ---------------------------------------------------------- */
			'states'   => array(
				'label'  => __( 'Messages & Success', 'smart-client-contact-hub' ),
				'intro'  => __( 'Validation errors and the confirmation step.', 'smart-client-contact-hub' ),
				'fields' => array(
					'error_color'        => array(
						'type'    => 'color',
						'default' => '#dc2626',
						'css'     => '--scch-error',
						'label'   => __( 'Error color', 'smart-client-contact-hub' ),
					),
					'success_color'      => array(
						'type'    => 'color',
						'default' => '#16a34a',
						'css'     => '--scch-success',
						'label'   => __( 'Success color', 'smart-client-contact-hub' ),
					),
					'success_icon_color' => array(
						'type'    => 'color',
						'default' => '#ffffff',
						'css'     => '--scch-success-icon-color',
						'label'   => __( 'Success check color', 'smart-client-contact-hub' ),
					),
					'success_icon_size'  => array(
						'type'    => 'px',
						'default' => 56,
						'min'     => 24,
						'max'     => 120,
						'css'     => '--scch-success-box',
						'label'   => __( 'Success circle size', 'smart-client-contact-hub' ),
					),
					'success_text_size'  => array(
						'type'    => 'px',
						'default' => 15,
						'min'     => 10,
						'max'     => 28,
						'css'     => '--scch-success-fs',
						'label'   => __( 'Success message size', 'smart-client-contact-hub' ),
					),
				),
			),

			/* ---------------------------------------------------------- */
			'dark'     => array(
				'label'  => __( 'Dark Mode Palette', 'smart-client-contact-hub' ),
				'intro'  => __( 'Used when the color scheme above is "Always dark", or "Follow visitor preference" and the visitor\'s device is in dark mode. Leave a color empty to keep the built-in dark value.', 'smart-client-contact-hub' ),
				'fields' => array(
					'dark_surface_color' => array(
						'type'    => 'color',
						'default' => '',
						'empty'   => true,
						'css'     => '--scch-surface',
						'label'   => __( 'Panel background', 'smart-client-contact-hub' ),
					),
					'dark_text_color'    => array(
						'type'    => 'color',
						'default' => '',
						'empty'   => true,
						'css'     => '--scch-text',
						'label'   => __( 'Body text color', 'smart-client-contact-hub' ),
					),
					'dark_muted_color'   => array(
						'type'    => 'color',
						'default' => '',
						'empty'   => true,
						'css'     => '--scch-muted',
						'label'   => __( 'Muted text color', 'smart-client-contact-hub' ),
					),
					'dark_line_color'    => array(
						'type'    => 'color',
						'default' => '',
						'empty'   => true,
						'css'     => '--scch-line',
						'label'   => __( 'Divider / border color', 'smart-client-contact-hub' ),
					),
					'dark_input_bg'      => array(
						'type'    => 'color',
						'default' => '',
						'empty'   => true,
						'css'     => '--scch-input-bg',
						'label'   => __( 'Input background', 'smart-client-contact-hub' ),
					),
					'dark_channel_bg'    => array(
						'type'    => 'color',
						'default' => '',
						'empty'   => true,
						'css'     => '--scch-channel-bg',
						'label'   => __( 'Channel button background', 'smart-client-contact-hub' ),
					),
				),
			),
		);
	}

	/**
	 * Font weight choices shared by several controls.
	 *
	 * @return array<string,string>
	 */
	private static function weights(): array {
		return array(
			'400' => __( 'Regular (400)', 'smart-client-contact-hub' ),
			'500' => __( 'Medium (500)', 'smart-client-contact-hub' ),
			'600' => __( 'Semibold (600)', 'smart-client-contact-hub' ),
			'700' => __( 'Bold (700)', 'smart-client-contact-hub' ),
			'800' => __( 'Extrabold (800)', 'smart-client-contact-hub' ),
		);
	}

	/**
	 * Flat map of every field key to its definition.
	 *
	 * @return array<string,array>
	 */
	public static function fields(): array {
		static $flat = null;

		if ( null === $flat ) {
			$flat = array();
			foreach ( self::schema() as $section ) {
				foreach ( $section['fields'] as $key => $field ) {
					$flat[ $key ] = $field;
				}
			}
		}

		return $flat;
	}

	/**
	 * Default value for every field, ready for Settings::defaults().
	 *
	 * @return array<string,mixed>
	 */
	public static function defaults(): array {
		$defaults = array();
		foreach ( self::fields() as $key => $field ) {
			$defaults[ $key ] = $field['default'];
		}
		return $defaults;
	}

	/**
	 * Keys belonging to the dark-mode section, which are emitted separately.
	 *
	 * @return string[]
	 */
	public static function dark_keys(): array {
		return array_keys( self::schema()['dark']['fields'] );
	}
}
