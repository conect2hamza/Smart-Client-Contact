<?php
/**
 * Central settings registry: defaults, access, and sanitization.
 *
 * @package SCCH
 */

namespace SCCH;

defined( 'ABSPATH' ) || exit;

/**
 * Every plugin option lives in one of the option groups below. Each group is a
 * single serialized array so a settings page saves atomically.
 */
class Settings {

	/**
	 * Runtime cache of loaded option groups.
	 *
	 * @var array<string,array>
	 */
	private static array $cache = array();

	/**
	 * Default values for all option groups.
	 *
	 * @return array<string,array>
	 */
	public static function defaults(): array {
		return array(
			// Every Appearance control is declared once in Design_Tokens and
			// flows from there into defaults, sanitizing, the CSS, and the
			// admin screen.
			'scch_appearance' => Design_Tokens::defaults(),
			'scch_contact'    => array(
				'phone_number' => '',
				'sms_number'   => '',
				'sms_body'     => '',
				'panel_title'  => __( 'How can we help?', 'smart-client-contact-hub' ),
				'panel_intro'  => __( 'Choose the fastest way to reach us.', 'smart-client-contact-hub' ),
				'cta_strategy' => __( 'Get Your Strategy', 'smart-client-contact-hub' ),
				'cta_call'     => __( 'Call Us', 'smart-client-contact-hub' ),
				'cta_text'     => __( 'Text Us', 'smart-client-contact-hub' ),
				'channels'     => array( 'form', 'call', 'sms' ),
			),
			'scch_form'       => array(
				'form_title'      => __( 'Get Your Free Strategy', 'smart-client-contact-hub' ),
				'success_message' => __( 'Thank you! Your request has been received. We will get back to you shortly.', 'smart-client-contact-hub' ),
				'error_message'   => __( 'Something went wrong. Please review the highlighted fields and try again.', 'smart-client-contact-hub' ),
				'redirect_url'    => '',
				'submit_label'    => __( 'Send My Request', 'smart-client-contact-hub' ),
				'fields'          => array(
					'name'    => array( 'enabled' => 1, 'required' => 1, 'hide_label' => 0, 'label' => __( 'Full Name', 'smart-client-contact-hub' ), 'placeholder' => __( 'Jane Smith', 'smart-client-contact-hub' ), 'order' => 1 ),
					'phone'   => array( 'enabled' => 1, 'required' => 1, 'hide_label' => 0, 'label' => __( 'Mobile Number', 'smart-client-contact-hub' ), 'placeholder' => __( '+1 555 000 1234', 'smart-client-contact-hub' ), 'order' => 2 ),
					'email'   => array( 'enabled' => 1, 'required' => 1, 'hide_label' => 0, 'label' => __( 'Email Address', 'smart-client-contact-hub' ), 'placeholder' => __( 'you@company.com', 'smart-client-contact-hub' ), 'order' => 3 ),
					'service' => array( 'enabled' => 1, 'required' => 1, 'hide_label' => 0, 'label' => __( 'Select Service', 'smart-client-contact-hub' ), 'placeholder' => __( 'Choose a service…', 'smart-client-contact-hub' ), 'order' => 4 ),
					'message' => array( 'enabled' => 1, 'required' => 1, 'hide_label' => 0, 'label' => __( 'Message', 'smart-client-contact-hub' ), 'placeholder' => __( 'Tell us about your project…', 'smart-client-contact-hub' ), 'order' => 5 ),
				),
			),
			'scch_captcha'    => array(
				'enabled'    => 1,
				'operations' => array( 'add', 'subtract' ),
				'label'      => __( 'Quick check: solve this', 'smart-client-contact-hub' ),
			),
			'scch_triggers'   => array(
				'enabled'   => 1,
				'selectors' => '',
			),
			'scch_email'      => array(
				'admin_enabled'     => 1,
				'admin_recipients'  => get_option( 'admin_email', '' ),
				'cc'                => '',
				'bcc'               => '',
				'reply_to_customer' => 1,
				'sender_name'       => get_option( 'blogname', '' ),
				'sender_email'      => '',
				'customer_enabled'  => 1,
				'response_time'     => __( 'within 1 business day', 'smart-client-contact-hub' ),
				'business_name'     => get_option( 'blogname', '' ),
				'business_contact'  => '',
				'logo_url'          => '',
				'brand_color'       => '#2563eb',
				'signature'         => get_option( 'blogname', '' ),
				'admin_subject'     => __( 'New lead from {customer_name} — {website_name}', 'smart-client-contact-hub' ),
				'admin_heading'     => __( 'You have a new lead', 'smart-client-contact-hub' ),
				'admin_body'        => __( "A new strategy request was submitted on {website_name}.\n\nName: {customer_name}\nPhone: {customer_phone}\nEmail: {customer_email}\nService: {service}\nDate: {submission_date}\n\nMessage:\n{message}", 'smart-client-contact-hub' ),
				'admin_footer'      => __( 'Sent automatically by Smart Client Contact Hub.', 'smart-client-contact-hub' ),
				'customer_subject'  => __( 'Thanks {customer_name} — we received your request', 'smart-client-contact-hub' ),
				'customer_heading'  => __( 'Thank you, {customer_name}!', 'smart-client-contact-hub' ),
				'customer_body'     => __( "We received your request regarding {service} and our team will respond {response_time}.\n\nYour submission summary:\nPhone: {customer_phone}\nEmail: {customer_email}\nMessage: {message}\nSubmitted: {submission_date}\n\nNeed us sooner? {business_contact}", 'smart-client-contact-hub' ),
				'customer_footer'   => __( '{business_name} — {website_name}', 'smart-client-contact-hub' ),
				'button_label'      => __( 'View Lead', 'smart-client-contact-hub' ),
			),
			'scch_general'    => array(
				'rate_limit_max'    => 5,
				'rate_limit_window' => 10, // Minutes.
				'log_enabled'       => 1,
			),
			'scch_uninstall'  => array(
				'delete_settings' => 0,
				'delete_leads'    => 0,
			),
		);
	}

	/**
	 * Get a full option group merged with defaults.
	 *
	 * @param string $group Option name, e.g. 'scch_appearance'.
	 * @return array
	 */
	public static function group( string $group ): array {
		if ( ! isset( self::$cache[ $group ] ) ) {
			$defaults              = self::defaults()[ $group ] ?? array();
			$stored                = get_option( $group, array() );
			self::$cache[ $group ] = wp_parse_args( is_array( $stored ) ? $stored : array(), $defaults );
		}
		return self::$cache[ $group ];
	}

	/**
	 * Get a single value from a group.
	 *
	 * @param string $group   Option group name.
	 * @param string $key     Key within the group.
	 * @param mixed  $fallback Fallback if missing.
	 * @return mixed
	 */
	public static function get( string $group, string $key, $fallback = null ) {
		$values = self::group( $group );
		return $values[ $key ] ?? $fallback;
	}

	/**
	 * Ordered, enabled form fields.
	 *
	 * @return array<string,array>
	 */
	public static function form_fields(): array {
		$fields = self::get( 'scch_form', 'fields', array() );
		uasort( $fields, static fn( $a, $b ) => (int) ( $a['order'] ?? 0 ) <=> (int) ( $b['order'] ?? 0 ) );
		return array_filter( $fields, static fn( $f ) => ! empty( $f['enabled'] ) );
	}

	/**
	 * Services list.
	 *
	 * @return array<int,array{id:string,label:string}>
	 */
	public static function services(): array {
		$services = get_option( 'scch_services', array() );
		return is_array( $services ) ? array_values( $services ) : array();
	}

	/**
	 * Flush runtime cache (used after saves and in tests).
	 */
	public static function flush_cache(): void {
		self::$cache = array();
	}
}
