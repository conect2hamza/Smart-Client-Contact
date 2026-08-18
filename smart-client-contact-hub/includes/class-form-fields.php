<?php
/**
 * Form field registry: core fields, custom fields, and their validation.
 *
 * @package SCCH
 */

namespace SCCH;

defined( 'ABSPATH' ) || exit;

/**
 * The lead form is built from a list of fields the administrator controls.
 *
 * Five fields are "core": name, phone, email, service and message. They map
 * onto their own database columns, so they can be relabelled, reordered and
 * (except email) switched off, but never deleted or retyped.
 *
 * Everything else is a custom field. Custom answers are stored together as
 * JSON on the lead row, which keeps the schema stable no matter how many
 * fields a site adds.
 */
class Form_Fields {

	/**
	 * Field keys that map to their own column and cannot be deleted.
	 */
	public const CORE = array( 'name', 'phone', 'email', 'service', 'message' );

	/**
	 * Most custom fields a form may hold.
	 */
	private const MAX_CUSTOM = 40;

	/**
	 * Longest stored answer for one custom field.
	 */
	private const MAX_VALUE = 2000;

	/**
	 * Available field types.
	 *
	 * options     Whether the type needs a list of choices.
	 * placeholder Whether placeholder text is meaningful for the type.
	 * multiple    Whether the answer may be an array.
	 *
	 * @return array<string,array>
	 */
	public static function types(): array {
		return array(
			'text'     => array(
				'label'       => __( 'Text', 'smart-client-contact-hub' ),
				'placeholder' => true,
				'options'     => false,
				'multiple'    => false,
			),
			'textarea' => array(
				'label'       => __( 'Paragraph', 'smart-client-contact-hub' ),
				'placeholder' => true,
				'options'     => false,
				'multiple'    => false,
			),
			'email'    => array(
				'label'       => __( 'Email address', 'smart-client-contact-hub' ),
				'placeholder' => true,
				'options'     => false,
				'multiple'    => false,
			),
			'tel'      => array(
				'label'       => __( 'Phone number', 'smart-client-contact-hub' ),
				'placeholder' => true,
				'options'     => false,
				'multiple'    => false,
			),
			'url'      => array(
				'label'       => __( 'Website address', 'smart-client-contact-hub' ),
				'placeholder' => true,
				'options'     => false,
				'multiple'    => false,
			),
			'number'   => array(
				'label'       => __( 'Number', 'smart-client-contact-hub' ),
				'placeholder' => true,
				'options'     => false,
				'multiple'    => false,
			),
			'date'     => array(
				'label'       => __( 'Date', 'smart-client-contact-hub' ),
				'placeholder' => false,
				'options'     => false,
				'multiple'    => false,
			),
			'time'     => array(
				'label'       => __( 'Time', 'smart-client-contact-hub' ),
				'placeholder' => false,
				'options'     => false,
				'multiple'    => false,
			),
			'select'   => array(
				'label'       => __( 'Dropdown', 'smart-client-contact-hub' ),
				'placeholder' => true,
				'options'     => true,
				'multiple'    => false,
			),
			'radio'    => array(
				'label'       => __( 'Radio buttons (pick one)', 'smart-client-contact-hub' ),
				'placeholder' => false,
				'options'     => true,
				'multiple'    => false,
			),
			'checkbox' => array(
				'label'       => __( 'Checkboxes (pick any)', 'smart-client-contact-hub' ),
				'placeholder' => false,
				'options'     => true,
				'multiple'    => true,
			),
			'consent'  => array(
				'label'       => __( 'Consent checkbox', 'smart-client-contact-hub' ),
				'placeholder' => false,
				'options'     => false,
				'multiple'    => false,
			),
			'hidden'   => array(
				'label'       => __( 'Hidden value', 'smart-client-contact-hub' ),
				'placeholder' => true,
				'options'     => false,
				'multiple'    => false,
			),
		);
	}

	/**
	 * The fixed type of each core field.
	 *
	 * @return array<string,string>
	 */
	public static function core_types(): array {
		return array(
			'name'    => 'text',
			'phone'   => 'tel',
			'email'   => 'email',
			'service' => 'select',
			'message' => 'textarea',
		);
	}

	/**
	 * Whether a key is a core field.
	 *
	 * @param string $key Field key.
	 */
	public static function is_core( string $key ): bool {
		return in_array( $key, self::CORE, true );
	}

	/**
	 * A blank custom field.
	 *
	 * @param string $type Field type.
	 * @return array<string,mixed>
	 */
	public static function blank( string $type = 'text' ): array {
		$types = self::types();

		return array(
			'key'         => '',
			'type'        => isset( $types[ $type ] ) ? $type : 'text',
			'enabled'     => 1,
			'required'    => 0,
			'hide_label'  => 0,
			'label'       => '',
			'placeholder' => '',
			'help'        => '',
			'options'     => '',
			'width'       => 'full',
			'order'       => 999,
		);
	}

	/**
	 * Every configured field, core and custom, in display order.
	 *
	 * @return array<string,array>
	 */
	public static function all(): array {
		$stored = Settings::get( 'scch_form', 'fields', array() );
		$stored = is_array( $stored ) ? $stored : array();
		$types  = self::core_types();
		$out    = array();

		foreach ( $stored as $key => $field ) {
			$key = (string) $key;

			if ( ! is_array( $field ) ) {
				continue;
			}

			$field = array_merge( self::blank(), $field );

			// A core field's key and type are not the administrator's to change.
			$field['key']  = $key;
			$field['type'] = self::is_core( $key ) ? $types[ $key ] : $field['type'];
			$field['core'] = self::is_core( $key );

			$out[ $key ] = $field;
		}

		uasort( $out, static fn( $a, $b ) => (int) $a['order'] <=> (int) $b['order'] );

		return $out;
	}

	/**
	 * The fields that actually render, in order.
	 *
	 * @return array<string,array>
	 */
	public static function enabled(): array {
		return array_filter( self::all(), static fn( $f ) => ! empty( $f['enabled'] ) );
	}

	/**
	 * Custom (non-core) enabled fields.
	 *
	 * @return array<string,array>
	 */
	public static function custom(): array {
		return array_filter( self::all(), static fn( $f ) => empty( $f['core'] ) );
	}

	/**
	 * Choices for an options-backed field, parsed one per line.
	 *
	 * A line may be "value|Label" to separate the stored value from what the
	 * visitor sees; a bare line is used for both.
	 *
	 * @param string $raw Raw options text.
	 * @return array<string,string> value => label.
	 */
	public static function options( string $raw ): array {
		$out = array();

		foreach ( preg_split( '/[\r\n]+/', $raw ) as $line ) {
			$line = trim( (string) $line );

			if ( '' === $line ) {
				continue;
			}

			if ( false !== strpos( $line, '|' ) ) {
				list( $value, $label ) = array_map( 'trim', explode( '|', $line, 2 ) );
			} else {
				$value = $line;
				$label = $line;
			}

			if ( '' === $value ) {
				continue;
			}

			$out[ $value ] = '' === $label ? $value : $label;
		}

		return $out;
	}

	/**
	 * Sanitize the posted field list. Order is the submitted order.
	 *
	 * @param array $rows    Posted rows.
	 * @param array $current Currently stored fields, used to keep core config.
	 * @return array<string,array>
	 */
	public static function sanitize( array $rows, array $current ): array {
		$types  = self::types();
		$ctypes = self::core_types();
		$clean  = array();
		$order  = 1;
		$custom = 0;

		foreach ( $rows as $posted_key => $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}

			$is_core = self::is_core( (string) $posted_key );

			if ( $is_core ) {
				$key  = (string) $posted_key;
				$type = $ctypes[ $key ];
			} else {
				if ( $custom >= self::MAX_CUSTOM ) {
					continue;
				}

				$label = sanitize_text_field( $row['label'] ?? '' );
				$key   = self::make_key( $row['key'] ?? '', $label, array_keys( $clean ) );

				if ( '' === $key || '' === $label ) {
					// A custom field with no label has nothing to render. This
					// is also how the admin deletes one.
					continue;
				}

				$type = isset( $types[ $row['type'] ?? '' ] ) ? $row['type'] : 'text';
				$custom++;
			}

			$clean[ $key ] = array(
				'key'         => $key,
				'type'        => $type,
				'enabled'     => empty( $row['enabled'] ) ? 0 : 1,
				'required'    => empty( $row['required'] ) ? 0 : 1,
				'hide_label'  => empty( $row['hide_label'] ) ? 0 : 1,
				'label'       => sanitize_text_field( $row['label'] ?? '' ),
				'placeholder' => sanitize_text_field( $row['placeholder'] ?? '' ),
				'help'        => sanitize_text_field( $row['help'] ?? '' ),
				'options'     => sanitize_textarea_field( $row['options'] ?? '' ),
				'width'       => in_array( $row['width'] ?? '', array( 'full', 'half' ), true ) ? $row['width'] : 'full',
				'order'       => $order++,
			);
		}

		// A core field missing from the post keeps whatever it had.
		foreach ( self::CORE as $key ) {
			if ( ! isset( $clean[ $key ] ) ) {
				$clean[ $key ]          = array_merge( self::blank( $ctypes[ $key ] ), (array) ( $current[ $key ] ?? array() ) );
				$clean[ $key ]['key']   = $key;
				$clean[ $key ]['type']  = $ctypes[ $key ];
				$clean[ $key ]['order'] = $order++;
			}
		}

		// Confirmations and Reply-To depend on the email address.
		$clean['email']['enabled']  = 1;
		$clean['email']['required'] = 1;

		return $clean;
	}

	/**
	 * Build a stable, unique key for a custom field.
	 *
	 * @param string $requested Existing or requested key.
	 * @param string $label     Field label, used when no key is given.
	 * @param array  $taken     Keys already used.
	 */
	private static function make_key( string $requested, string $label, array $taken ): string {
		$key = sanitize_key( str_replace( '-', '_', sanitize_title( '' !== $requested ? $requested : $label ) ) );
		$key = trim( preg_replace( '/_+/', '_', $key ), '_' );

		if ( '' === $key ) {
			return '';
		}

		/*
		 * Never let a custom field shadow a core column, a security field, or
		 * one of the attribution values the form posts alongside the answers —
		 * they all share the one POST namespace.
		 */
		$reserved = array(
			'captcha_answer',
			'captcha_token',
			'nonce',
			'action',
			'scch_website',
			'referrer',
			'landing_page',
			'device',
			'returning',
			'utm_source',
			'utm_medium',
			'utm_campaign',
			'utm_term',
			'utm_content',
		);

		if ( self::is_core( $key ) || in_array( $key, $reserved, true ) ) {
			$key = 'field_' . $key;
		}

		$base = $key;
		$n    = 2;
		while ( in_array( $key, $taken, true ) ) {
			$key = $base . '_' . $n;
			$n++;
		}

		return mb_substr( $key, 0, 40 );
	}

	/**
	 * Validate and sanitize one submitted custom answer.
	 *
	 * @param array $field Field definition.
	 * @param mixed $value Raw submitted value, already unslashed.
	 * @return array{value:mixed,error:string}
	 */
	public static function validate( array $field, $value ): array {
		$type     = (string) $field['type'];
		$types    = self::types();
		$multiple = ! empty( $types[ $type ]['multiple'] );
		$required = ! empty( $field['required'] );
		$label    = (string) $field['label'];

		/* translators: %s: field label. */
		$missing = sprintf( __( '%s is required.', 'smart-client-contact-hub' ), $label );

		if ( 'consent' === $type ) {
			$checked = ! empty( $value );

			if ( $required && ! $checked ) {
				return array( 'value' => '', 'error' => $missing );
			}

			return array( 'value' => $checked ? 'yes' : 'no', 'error' => '' );
		}

		if ( $multiple ) {
			$choices  = self::options( (string) $field['options'] );
			$selected = array_values( array_intersect( array_keys( $choices ), (array) $value ) );

			if ( $required && ! $selected ) {
				return array( 'value' => array(), 'error' => $missing );
			}

			return array( 'value' => $selected, 'error' => '' );
		}

		$value = is_array( $value ) ? '' : trim( sanitize_textarea_field( (string) $value ) );

		if ( '' === $value ) {
			return array( 'value' => '', 'error' => $required ? $missing : '' );
		}

		if ( mb_strlen( $value ) > self::MAX_VALUE ) {
			return array(
				'value' => '',
				/* translators: 1: field label, 2: maximum number of characters. */
				'error' => sprintf( __( '%1$s must be %2$d characters or fewer.', 'smart-client-contact-hub' ), $label, self::MAX_VALUE ),
			);
		}

		switch ( $type ) {
			case 'select':
			case 'radio':
				$choices = self::options( (string) $field['options'] );

				if ( ! isset( $choices[ $value ] ) ) {
					/* translators: %s: field label. */
					return array( 'value' => '', 'error' => sprintf( __( 'Please choose a valid option for %s.', 'smart-client-contact-hub' ), $label ) );
				}
				break;

			case 'email':
				if ( ! is_email( $value ) ) {
					/* translators: %s: field label. */
					return array( 'value' => '', 'error' => sprintf( __( 'Please enter a valid email address for %s.', 'smart-client-contact-hub' ), $label ) );
				}
				break;

			case 'url':
				$value = esc_url_raw( $value );

				if ( '' === $value ) {
					/* translators: %s: field label. */
					return array( 'value' => '', 'error' => sprintf( __( 'Please enter a valid web address for %s.', 'smart-client-contact-hub' ), $label ) );
				}
				break;

			case 'number':
				if ( ! is_numeric( $value ) ) {
					/* translators: %s: field label. */
					return array( 'value' => '', 'error' => sprintf( __( 'Please enter a number for %s.', 'smart-client-contact-hub' ), $label ) );
				}
				break;

			case 'tel':
				$value = trim( (string) preg_replace( '/[^0-9+\-\s().]/', '', $value ) );

				if ( strlen( preg_replace( '/\D/', '', $value ) ) < 4 ) {
					/* translators: %s: field label. */
					return array( 'value' => '', 'error' => sprintf( __( 'Please enter a valid phone number for %s.', 'smart-client-contact-hub' ), $label ) );
				}
				break;

			case 'date':
				if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ) ) {
					/* translators: %s: field label. */
					return array( 'value' => '', 'error' => sprintf( __( 'Please enter a valid date for %s.', 'smart-client-contact-hub' ), $label ) );
				}
				break;

			case 'time':
				if ( ! preg_match( '/^\d{2}:\d{2}(:\d{2})?$/', $value ) ) {
					/* translators: %s: field label. */
					return array( 'value' => '', 'error' => sprintf( __( 'Please enter a valid time for %s.', 'smart-client-contact-hub' ), $label ) );
				}
				break;
		}

		return array( 'value' => $value, 'error' => '' );
	}

	/**
	 * Render a stored answer as readable text.
	 *
	 * @param array $field Field definition.
	 * @param mixed $value Stored value.
	 */
	public static function display( array $field, $value ): string {
		if ( is_array( $value ) ) {
			$choices = self::options( (string) ( $field['options'] ?? '' ) );
			$labels  = array();

			foreach ( $value as $item ) {
				$labels[] = $choices[ $item ] ?? (string) $item;
			}

			return implode( ', ', $labels );
		}

		$value = (string) $value;

		if ( 'consent' === ( $field['type'] ?? '' ) ) {
			return 'yes' === $value ? __( 'Yes', 'smart-client-contact-hub' ) : __( 'No', 'smart-client-contact-hub' );
		}

		if ( in_array( $field['type'] ?? '', array( 'select', 'radio' ), true ) ) {
			$choices = self::options( (string) ( $field['options'] ?? '' ) );
			return $choices[ $value ] ?? $value;
		}

		return $value;
	}

	/**
	 * Decode the custom answers stored on a lead row.
	 *
	 * @param string|null $json Stored JSON.
	 * @return array<string,mixed>
	 */
	public static function decode( ?string $json ): array {
		if ( ! $json ) {
			return array();
		}

		$data = json_decode( $json, true );

		return is_array( $data ) ? $data : array();
	}
}
