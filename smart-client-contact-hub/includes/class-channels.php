<?php
/**
 * Contact channel registry and storage.
 *
 * @package SCCH
 */

namespace SCCH;

defined( 'ABSPATH' ) || exit;

/**
 * Channels are the buttons on the first step of the popup: the lead form,
 * a phone call, WhatsApp, Telegram, and so on.
 *
 * Each one is an editable row with its own label, description, destination,
 * icon, and colors, and can be switched on or off without being deleted.
 * Rows are stored in display order in a single option.
 */
class Channels {

	/**
	 * Option holding the channel rows.
	 */
	public const OPTION = 'scch_channel_items';

	/**
	 * Maximum rows accepted on save. Generous, but bounded.
	 */
	private const MAX_ROWS = 30;

	/**
	 * Channel types and how each one behaves.
	 *
	 * value  false, or the field definition for the destination input.
	 * extra  false, or the field definition for an optional prefilled message.
	 * brand  Suggested icon tile color, offered as the default for new rows.
	 *
	 * @return array<string,array>
	 */
	public static function types(): array {
		return array(
			'form'      => array(
				'label' => __( 'Lead form', 'smart-client-contact-hub' ),
				'icon'  => 'rocket',
				'value' => false,
				'extra' => false,
				'brand' => '',
				'help'  => __( 'Opens the contact form inside the popup.', 'smart-client-contact-hub' ),
			),
			'call'      => array(
				'label' => __( 'Phone call', 'smart-client-contact-hub' ),
				'icon'  => 'phone',
				'value' => array(
					'label'       => __( 'Phone number', 'smart-client-contact-hub' ),
					'placeholder' => '+15550001234',
				),
				'extra' => false,
				'brand' => '',
				'help'  => __( 'Starts a call with tel:. Use international format.', 'smart-client-contact-hub' ),
			),
			'sms'       => array(
				'label' => __( 'SMS / text message', 'smart-client-contact-hub' ),
				'icon'  => 'sms',
				'value' => array(
					'label'       => __( 'Mobile number', 'smart-client-contact-hub' ),
					'placeholder' => '+15550001234',
				),
				'extra' => array(
					'label'       => __( 'Pre-filled message', 'smart-client-contact-hub' ),
					'placeholder' => __( 'Hi! I would like to know more about…', 'smart-client-contact-hub' ),
				),
				'brand' => '',
				'help'  => __( 'Opens the messaging app with sms:.', 'smart-client-contact-hub' ),
			),
			'whatsapp'  => array(
				'label' => __( 'WhatsApp', 'smart-client-contact-hub' ),
				'icon'  => 'whatsapp',
				'value' => array(
					'label'       => __( 'WhatsApp number', 'smart-client-contact-hub' ),
					'placeholder' => '+15550001234',
				),
				'extra' => array(
					'label'       => __( 'Pre-filled message', 'smart-client-contact-hub' ),
					'placeholder' => __( 'Hi! I would like to know more about…', 'smart-client-contact-hub' ),
				),
				'brand' => '#25d366',
				'help'  => __( 'Opens wa.me with your number. Include the country code.', 'smart-client-contact-hub' ),
			),
			'telegram'  => array(
				'label' => __( 'Telegram', 'smart-client-contact-hub' ),
				'icon'  => 'telegram',
				'value' => array(
					'label'       => __( 'Username or link', 'smart-client-contact-hub' ),
					'placeholder' => '@yourhandle',
				),
				'extra' => false,
				'brand' => '#26a5e4',
				'help'  => __( 'A @username, or a full t.me link.', 'smart-client-contact-hub' ),
			),
			'messenger' => array(
				'label' => __( 'Facebook Messenger', 'smart-client-contact-hub' ),
				'icon'  => 'messenger',
				'value' => array(
					'label'       => __( 'Page username or link', 'smart-client-contact-hub' ),
					'placeholder' => 'yourpage',
				),
				'extra' => false,
				'brand' => '#0084ff',
				'help'  => __( 'Your Page username, or a full m.me link.', 'smart-client-contact-hub' ),
			),
			'email'     => array(
				'label' => __( 'Email', 'smart-client-contact-hub' ),
				'icon'  => 'mail',
				'value' => array(
					'label'       => __( 'Email address', 'smart-client-contact-hub' ),
					'placeholder' => 'hello@example.com',
				),
				'extra' => array(
					'label'       => __( 'Pre-filled subject', 'smart-client-contact-hub' ),
					'placeholder' => __( 'Website enquiry', 'smart-client-contact-hub' ),
				),
				'brand' => '',
				'help'  => __( 'Opens the visitor\'s mail app with mailto:.', 'smart-client-contact-hub' ),
			),
			'link'      => array(
				'label' => __( 'Custom link', 'smart-client-contact-hub' ),
				'icon'  => 'link',
				'value' => array(
					'label'       => __( 'URL', 'smart-client-contact-hub' ),
					'placeholder' => 'https://example.com/book-a-call',
				),
				'extra' => false,
				'brand' => '',
				'help'  => __( 'Anything else — Viber, Skype, Calendly, a booking page. Pair it with a custom icon.', 'smart-client-contact-hub' ),
			),
		);
	}

	/**
	 * Icon choices offered per channel, on top of "custom upload".
	 *
	 * @return array<string,string>
	 */
	public static function icon_choices(): array {
		return array(
			'chat-bubble' => __( 'Message bubble', 'smart-client-contact-hub' ),
			'rocket'      => __( 'Rocket', 'smart-client-contact-hub' ),
			'phone'       => __( 'Phone', 'smart-client-contact-hub' ),
			'sms'         => __( 'Message', 'smart-client-contact-hub' ),
			'whatsapp'    => __( 'WhatsApp', 'smart-client-contact-hub' ),
			'telegram'    => __( 'Telegram', 'smart-client-contact-hub' ),
			'messenger'   => __( 'Messenger', 'smart-client-contact-hub' ),
			'mail'        => __( 'Envelope', 'smart-client-contact-hub' ),
			'headset'     => __( 'Headset', 'smart-client-contact-hub' ),
			'link'        => __( 'Link', 'smart-client-contact-hub' ),
			'custom'      => __( 'Custom upload', 'smart-client-contact-hub' ),
		);
	}

	/**
	 * A blank row, used for new entries in the admin and as the shape every
	 * stored row is normalized to.
	 *
	 * @param string $type Channel type.
	 * @return array<string,mixed>
	 */
	public static function blank( string $type = 'link' ): array {
		$types = self::types();
		$type  = isset( $types[ $type ] ) ? $type : 'link';

		return array(
			'id'          => '',
			'type'        => $type,
			'enabled'     => 1,
			'label'       => $types[ $type ]['label'],
			'description' => '',
			'value'       => '',
			'extra'       => '',
			'icon'        => $types[ $type ]['icon'],
			'icon_url'    => '',
			'icon_color'  => '',
			'icon_bg'     => $types[ $type ]['brand'],
			'text_color'  => '',
			'new_tab'     => 1,
		);
	}

	/**
	 * Stored rows, normalized. Falls back to a set migrated from the legacy
	 * Contact Settings so an upgrading site keeps exactly what it had.
	 *
	 * @return array<int,array>
	 */
	public static function raw(): array {
		$stored = get_option( self::OPTION, null );

		if ( ! is_array( $stored ) ) {
			return self::from_legacy();
		}

		$rows = array();
		foreach ( $stored as $row ) {
			if ( is_array( $row ) ) {
				$rows[] = array_merge( self::blank( $row['type'] ?? 'link' ), $row );
			}
		}

		return $rows;
	}

	/**
	 * Enabled channels with their destination resolved, ready to render.
	 *
	 * A row with no usable destination is dropped rather than rendered as a
	 * dead button — the one exception is the lead form, which has no URL.
	 *
	 * @return array<int,array>
	 */
	public static function all(): array {
		$out = array();

		foreach ( self::raw() as $row ) {
			if ( empty( $row['enabled'] ) ) {
				continue;
			}

			if ( '' === trim( (string) $row['label'] ) ) {
				continue;
			}

			if ( 'form' === $row['type'] ) {
				$row['url'] = '';
				$out[]      = $row;
				continue;
			}

			$url = self::url( $row );
			if ( '' === $url ) {
				continue;
			}

			$row['url'] = $url;
			$out[]      = $row;
		}

		/**
		 * Extra contact channels registered in code.
		 *
		 * Entries are appended after the channels configured in the admin.
		 * Each: array{ id:string, label:string, url:string, icon?:string
		 * (inline SVG), description?:string }
		 *
		 * @param array $extra_channels Default empty.
		 */
		foreach ( (array) apply_filters( 'scch_channels', array() ) as $extra ) {
			if ( empty( $extra['label'] ) || empty( $extra['url'] ) ) {
				continue;
			}
			$out[] = array_merge(
				self::blank( 'link' ),
				array(
					'id'       => sanitize_html_class( $extra['id'] ?? 'custom' ),
					'label'    => (string) $extra['label'],
					'url'      => (string) $extra['url'],
					'raw_icon' => isset( $extra['icon'] ) ? (string) $extra['icon'] : '',
				),
				isset( $extra['description'] ) ? array( 'description' => (string) $extra['description'] ) : array()
			);
		}

		return $out;
	}

	/**
	 * Whether the lead form is available as a channel.
	 */
	public static function form_enabled(): bool {
		foreach ( self::raw() as $row ) {
			if ( 'form' === $row['type'] && ! empty( $row['enabled'] ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Build the destination URL for a row, or '' when it has none.
	 *
	 * @param array $row Channel row.
	 */
	public static function url( array $row ): string {
		$value = trim( (string) ( $row['value'] ?? '' ) );
		$extra = trim( (string) ( $row['extra'] ?? '' ) );

		if ( '' === $value ) {
			return '';
		}

		switch ( $row['type'] ) {
			case 'call':
				$digits = self::digits( $value );
				return '' === $digits ? '' : 'tel:' . $digits;

			case 'sms':
				$digits = self::digits( $value );
				if ( '' === $digits ) {
					return '';
				}
				// "?&body=" is the form both iOS and Android accept.
				return 'sms:' . $digits . ( '' !== $extra ? '?&body=' . rawurlencode( $extra ) : '' );

			case 'whatsapp':
				// wa.me takes digits only, no plus and no separators.
				$digits = ltrim( self::digits( $value ), '+' );
				if ( '' === $digits ) {
					return '';
				}
				return 'https://wa.me/' . $digits . ( '' !== $extra ? '?text=' . rawurlencode( $extra ) : '' );

			case 'telegram':
				return self::handle_url( $value, 'https://t.me/' );

			case 'messenger':
				return self::handle_url( $value, 'https://m.me/' );

			case 'email':
				if ( ! is_email( $value ) ) {
					return '';
				}
				return 'mailto:' . $value . ( '' !== $extra ? '?subject=' . rawurlencode( $extra ) : '' );

			case 'link':
				return esc_url_raw( $value );
		}

		return '';
	}

	/**
	 * Accept either a bare handle or an already-complete profile URL.
	 *
	 * @param string $value Raw value.
	 * @param string $base  Base URL for a bare handle.
	 */
	private static function handle_url( string $value, string $base ): string {
		if ( preg_match( '#^https?://#i', $value ) ) {
			return esc_url_raw( $value );
		}

		// Slashes are stripped, so a pasted path cannot escape the base URL.
		// Dots are legal inside a handle but never lead one, and leaving them
		// would turn "../foo" into a handle that reads like a path.
		$handle = preg_replace( '/[^A-Za-z0-9_.+]/', '', ltrim( $value, '@' ) );
		$handle = trim( (string) $handle, '.' );

		return '' === $handle ? '' : $base . $handle;
	}

	/**
	 * Keep only the characters a dialable number may contain.
	 *
	 * @param string $value Raw value.
	 */
	private static function digits( string $value ): string {
		$clean = preg_replace( '/[^0-9+]/', '', $value );
		return strlen( preg_replace( '/\D/', '', (string) $clean ) ) < 4 ? '' : (string) $clean;
	}

	/**
	 * Sanitize posted rows. Order is taken from the submitted order.
	 *
	 * @param array $rows Raw posted rows.
	 * @return array<int,array>
	 */
	public static function sanitize( array $rows ): array {
		$types = self::types();
		$icons = self::icon_choices();
		$clean = array();
		$seen  = array();

		foreach ( array_slice( $rows, 0, self::MAX_ROWS ) as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}

			$type = isset( $types[ $row['type'] ?? '' ] ) ? $row['type'] : 'link';

			$label = sanitize_text_field( $row['label'] ?? '' );
			if ( '' === $label ) {
				// A row with no label has nothing to render; drop it. This is
				// also how the admin deletes a row.
				continue;
			}

			// The id doubles as a CSS class on the rendered button, so prefer
			// something readable: the stored id, else the label, else the type.
			$id = sanitize_html_class( sanitize_title( $row['id'] ?? '' ) );
			if ( '' === $id ) {
				$id = sanitize_html_class( sanitize_title( $label ) );
			}
			if ( '' === $id ) {
				$id = $type;
			}
			while ( in_array( $id, $seen, true ) ) {
				$id .= '-' . wp_rand( 10, 99 );
			}
			$seen[] = $id;

			$icon = isset( $icons[ $row['icon'] ?? '' ] ) ? $row['icon'] : $types[ $type ]['icon'];

			$clean[] = array(
				'id'          => $id,
				'type'        => $type,
				'enabled'     => empty( $row['enabled'] ) ? 0 : 1,
				'label'       => $label,
				'description' => sanitize_text_field( $row['description'] ?? '' ),
				'value'       => 'link' === $type
					? esc_url_raw( trim( (string) ( $row['value'] ?? '' ) ) )
					: sanitize_text_field( $row['value'] ?? '' ),
				'extra'       => sanitize_text_field( $row['extra'] ?? '' ),
				'icon'        => $icon,
				'icon_url'    => esc_url_raw( $row['icon_url'] ?? '' ),
				'icon_color'  => self::color( $row['icon_color'] ?? '' ),
				'icon_bg'     => self::color( $row['icon_bg'] ?? '' ),
				'text_color'  => self::color( $row['text_color'] ?? '' ),
				'new_tab'     => empty( $row['new_tab'] ) ? 0 : 1,
			);
		}

		return $clean;
	}

	/**
	 * Optional hex color: invalid or empty becomes '' so nothing is emitted.
	 *
	 * @param mixed $value Raw color.
	 */
	private static function color( $value ): string {
		return (string) ( sanitize_hex_color( trim( (string) $value ) ) ?: '' );
	}

	/**
	 * Build the initial channel set from the pre-1.0.5 Contact Settings, so
	 * an upgrading site sees exactly the buttons it had before.
	 *
	 * @return array<int,array>
	 */
	public static function from_legacy(): array {
		$contact = Settings::group( 'scch_contact' );
		$active  = (array) ( $contact['channels'] ?? array( 'form', 'call', 'sms' ) );
		$rows    = array();

		$legacy = array(
			'form' => array( 'cta_strategy', '' ),
			'call' => array( 'cta_call', 'phone_number' ),
			'sms'  => array( 'cta_text', 'sms_number' ),
		);

		foreach ( $legacy as $type => $keys ) {
			list( $label_key, $value_key ) = $keys;

			$row            = self::blank( $type );
			$row['id']      = $type;
			$row['enabled'] = in_array( $type, $active, true ) ? 1 : 0;
			$row['label']   = (string) ( $contact[ $label_key ] ?? $row['label'] );
			$row['value']   = '' === $value_key ? '' : (string) ( $contact[ $value_key ] ?? '' );

			if ( 'sms' === $type ) {
				$row['extra'] = (string) ( $contact['sms_body'] ?? '' );
			}

			$rows[] = $row;
		}

		return $rows;
	}

	/**
	 * Store the migrated set once, so the admin screen has rows to edit.
	 */
	public static function maybe_migrate(): void {
		if ( null === get_option( self::OPTION, null ) ) {
			add_option( self::OPTION, self::from_legacy() );
		}
	}
}
