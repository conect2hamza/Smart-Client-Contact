<?php
/**
 * Where a lead came from.
 *
 * @package SCCH
 */

namespace SCCH;

defined( 'ABSPATH' ) || exit;

/**
 * Normalizes the attribution fields the browser reports at submission time.
 *
 * The plugin does not track page views or build visitor profiles: these
 * values are read once, from the page the visitor was on when they submitted,
 * and stored on that lead only. Nothing is written to any third party.
 */
class Attribution {

	/**
	 * Search engines and networks recognized as named sources.
	 *
	 * @return array<string,string> Host fragment => source label.
	 */
	private static function known_hosts(): array {
		return array(
			'google.'      => 'google',
			'bing.'        => 'bing',
			'duckduckgo.'  => 'duckduckgo',
			'yahoo.'       => 'yahoo',
			'yandex.'      => 'yandex',
			'baidu.'       => 'baidu',
			'facebook.'    => 'facebook',
			'fb.'          => 'facebook',
			'instagram.'   => 'instagram',
			'linkedin.'    => 'linkedin',
			'lnkd.in'      => 'linkedin',
			't.co'         => 'x',
			'twitter.'     => 'x',
			'x.com'        => 'x',
			'youtube.'     => 'youtube',
			'pinterest.'   => 'pinterest',
			'reddit.'      => 'reddit',
			'tiktok.'      => 'tiktok',
			'whatsapp.'    => 'whatsapp',
			't.me'         => 'telegram',
		);
	}

	/**
	 * Build the attribution fields from a posted payload.
	 *
	 * @param array $post Raw $_POST, already unslashed by the caller.
	 * @return array<string,string>
	 */
	public static function from_request( array $post ): array {
		$utm = array();

		foreach ( array( 'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content' ) as $key ) {
			$utm[ $key ] = mb_substr( sanitize_text_field( $post[ $key ] ?? '' ), 0, 100 );
		}

		$referrer = esc_url_raw( (string) ( $post['referrer'] ?? '' ) );
		$landing  = esc_url_raw( (string) ( $post['landing_page'] ?? '' ) );

		return array_merge(
			$utm,
			array(
				'referrer'     => mb_substr( $referrer, 0, 255 ),
				'landing_page' => mb_substr( $landing, 0, 255 ),
				'device'       => self::device( (string) ( $post['device'] ?? '' ) ),
				'source'       => self::source( $utm['utm_source'], $referrer ),
			),
		);
	}

	/**
	 * Resolve a single source label.
	 *
	 * A UTM source always wins because it was set deliberately. Otherwise the
	 * referring host decides, with no referrer meaning direct.
	 *
	 * @param string $utm_source UTM source, if any.
	 * @param string $referrer   Referring URL, if any.
	 */
	public static function source( string $utm_source, string $referrer ): string {
		$utm_source = strtolower( trim( $utm_source ) );

		if ( '' !== $utm_source ) {
			return mb_substr( preg_replace( '/[^a-z0-9._-]/', '', $utm_source ), 0, 60 );
		}

		$referrer = trim( $referrer );

		if ( '' === $referrer ) {
			return 'direct';
		}

		$host = strtolower( (string) wp_parse_url( $referrer, PHP_URL_HOST ) );

		if ( '' === $host ) {
			return 'direct';
		}

		// A referrer from this site is internal navigation, not a source.
		$own = strtolower( (string) wp_parse_url( home_url(), PHP_URL_HOST ) );
		if ( '' !== $own && ( $host === $own || str_ends_with( $host, '.' . $own ) ) ) {
			return 'direct';
		}

		foreach ( self::known_hosts() as $fragment => $label ) {
			if ( false !== strpos( $host, $fragment ) ) {
				return $label;
			}
		}

		return mb_substr( preg_replace( '/^www\./', '', $host ), 0, 60 );
	}

	/**
	 * Coerce the reported device to one of three buckets.
	 *
	 * @param string $value Raw value.
	 */
	public static function device( string $value ): string {
		$value = strtolower( trim( $value ) );

		return in_array( $value, array( 'mobile', 'tablet', 'desktop' ), true ) ? $value : '';
	}

	/**
	 * Human label for a stored source key.
	 *
	 * @param string $source Source key.
	 */
	public static function label( string $source ): string {
		$known = array(
			'direct'   => __( 'Direct', 'smart-client-contact-hub' ),
			'google'   => 'Google',
			'bing'     => 'Bing',
			'facebook' => 'Facebook',
			'instagram' => 'Instagram',
			'linkedin' => 'LinkedIn',
			'x'        => 'X',
			'youtube'  => 'YouTube',
			'tiktok'   => 'TikTok',
			'whatsapp' => 'WhatsApp',
			'telegram' => 'Telegram',
			'referral' => __( 'Referral', 'smart-client-contact-hub' ),
		);

		if ( '' === $source ) {
			return __( 'Unknown', 'smart-client-contact-hub' );
		}

		return $known[ $source ] ?? $source;
	}
}
