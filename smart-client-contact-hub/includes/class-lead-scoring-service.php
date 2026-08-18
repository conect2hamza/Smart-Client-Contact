<?php
/**
 * Rule-based lead scoring.
 *
 * @package SCCH
 */

namespace SCCH;

defined( 'ABSPATH' ) || exit;

/**
 * Scores a lead 0-100 from configurable rules.
 *
 * Deliberately deterministic and local: no AI, no external service, no
 * network call. Every rule is a weight an administrator can change or switch
 * off, and the total is clamped to 100 so the band thresholds stay meaningful
 * however the weights are tuned.
 */
class Lead_Scoring_Service {

	/**
	 * Option holding rule weights and the enabled flag per rule.
	 */
	public const OPTION = 'scch_scoring_rules';

	/**
	 * Free email domains, used to tell a business address from a personal one.
	 *
	 * @var string[]
	 */
	private const FREE_DOMAINS = array(
		'gmail.com', 'googlemail.com', 'yahoo.com', 'yahoo.co.uk', 'hotmail.com',
		'hotmail.co.uk', 'outlook.com', 'live.com', 'msn.com', 'aol.com',
		'icloud.com', 'me.com', 'mac.com', 'proton.me', 'protonmail.com',
		'gmx.com', 'gmx.net', 'mail.com', 'yandex.com', 'zoho.com',
	);

	/**
	 * The rules, with shipped weights.
	 *
	 * @return array<string,array{label:string,help:string,weight:int}>
	 */
	public static function rules(): array {
		return array(
			'has_phone'       => array(
				'label'  => __( 'Phone number provided', 'smart-client-contact-hub' ),
				'help'   => __( 'A reachable phone number is the strongest signal of intent.', 'smart-client-contact-hub' ),
				'weight' => 20,
			),
			'business_email'  => array(
				'label'  => __( 'Business email address', 'smart-client-contact-hub' ),
				'help'   => __( 'Not a free consumer domain such as gmail.com.', 'smart-client-contact-hub' ),
				'weight' => 15,
			),
			'service_chosen'  => array(
				'label'  => __( 'Service selected', 'smart-client-contact-hub' ),
				'help'   => __( 'They told you what they want.', 'smart-client-contact-hub' ),
				'weight' => 15,
			),
			'detailed_message' => array(
				'label'  => __( 'Detailed message', 'smart-client-contact-hub' ),
				'help'   => __( 'Longer than 120 characters.', 'smart-client-contact-hub' ),
				'weight' => 15,
			),
			'has_message'     => array(
				'label'  => __( 'Any message written', 'smart-client-contact-hub' ),
				'help'   => __( 'Awarded for a message of any length.', 'smart-client-contact-hub' ),
				'weight' => 5,
			),
			'direct_channel'  => array(
				'label'  => __( 'Reached out on a direct channel', 'smart-client-contact-hub' ),
				'help'   => __( 'WhatsApp, phone, or SMS rather than the form.', 'smart-client-contact-hub' ),
				'weight' => 10,
			),
			'campaign_traffic' => array(
				'label'  => __( 'Came from a tracked campaign', 'smart-client-contact-hub' ),
				'help'   => __( 'A UTM campaign was present on the landing page.', 'smart-client-contact-hub' ),
				'weight' => 10,
			),
			'returning'       => array(
				'label'  => __( 'Returning visitor', 'smart-client-contact-hub' ),
				'help'   => __( 'Had visited before submitting, per a first-party cookie.', 'smart-client-contact-hub' ),
				'weight' => 5,
			),
			'known_contact'   => array(
				'label'  => __( 'Has contacted you before', 'smart-client-contact-hub' ),
				'help'   => __( 'An earlier lead exists with the same email address.', 'smart-client-contact-hub' ),
				'weight' => 10,
			),
			'has_value'       => array(
				'label'  => __( 'Estimated value recorded', 'smart-client-contact-hub' ),
				'help'   => __( 'Someone put a number against the opportunity.', 'smart-client-contact-hub' ),
				'weight' => 10,
			),
		);
	}

	/**
	 * Current configuration merged over the shipped rules.
	 *
	 * @return array<string,array{label:string,help:string,weight:int,enabled:int}>
	 */
	public static function config(): array {
		$stored = get_option( self::OPTION, array() );
		$stored = is_array( $stored ) ? $stored : array();
		$out    = array();

		foreach ( self::rules() as $key => $rule ) {
			$rule['weight']  = isset( $stored[ $key ]['weight'] )
				? max( 0, min( 100, (int) $stored[ $key ]['weight'] ) )
				: $rule['weight'];
			$rule['enabled'] = isset( $stored[ $key ]['enabled'] ) ? (int) (bool) $stored[ $key ]['enabled'] : 1;
			$out[ $key ]     = $rule;
		}

		return $out;
	}

	/**
	 * Persist rule configuration.
	 *
	 * @param array $raw Posted rules.
	 */
	public static function save( array $raw ): void {
		$clean = array();

		foreach ( array_keys( self::rules() ) as $key ) {
			$clean[ $key ] = array(
				'weight'  => max( 0, min( 100, (int) ( $raw[ $key ]['weight'] ?? 0 ) ) ),
				'enabled' => empty( $raw[ $key ]['enabled'] ) ? 0 : 1,
			);
		}

		update_option( self::OPTION, $clean );
	}

	/**
	 * Which rules a lead satisfies, and the resulting score.
	 *
	 * @param array $lead Lead data. Accepts the submission array or a row.
	 * @return array{score:int,band:string,matched:array<string,int>}
	 */
	public static function evaluate( array $lead ): array {
		$config  = self::config();
		$matched = array();
		$total   = 0;

		foreach ( $config as $key => $rule ) {
			if ( empty( $rule['enabled'] ) || $rule['weight'] <= 0 ) {
				continue;
			}

			if ( ! self::matches( $key, $lead ) ) {
				continue;
			}

			$matched[ $key ] = (int) $rule['weight'];
			$total          += (int) $rule['weight'];
		}

		$score = max( 0, min( 100, $total ) );

		/**
		 * Filter a computed lead score before it is stored.
		 *
		 * @param int   $score   0-100.
		 * @param array $lead    Lead data.
		 * @param array $matched Rule key => weight for every rule that matched.
		 */
		$score = (int) apply_filters( 'scch_lead_score', $score, $lead, $matched );
		$score = max( 0, min( 100, $score ) );

		return array(
			'score'   => $score,
			'band'    => self::band( $score ),
			'matched' => $matched,
		);
	}

	/**
	 * Whether one rule is satisfied.
	 *
	 * @param string $key  Rule key.
	 * @param array  $lead Lead data.
	 */
	private static function matches( string $key, array $lead ): bool {
		$message = (string) ( $lead['message'] ?? '' );

		switch ( $key ) {
			case 'has_phone':
				return strlen( preg_replace( '/\D/', '', (string) ( $lead['phone'] ?? '' ) ) ) >= 6;

			case 'business_email':
				return self::is_business_email( (string) ( $lead['email'] ?? '' ) );

			case 'service_chosen':
				return '' !== trim( (string) ( $lead['service'] ?? '' ) );

			case 'detailed_message':
				return mb_strlen( trim( $message ) ) > 120;

			case 'has_message':
				return '' !== trim( $message );

			case 'direct_channel':
				return in_array( (string) ( $lead['channel'] ?? 'form' ), array( 'whatsapp', 'call', 'sms', 'telegram', 'messenger' ), true );

			case 'campaign_traffic':
				return '' !== trim( (string) ( $lead['utm_campaign'] ?? '' ) );

			case 'returning':
				return ! empty( $lead['returning'] );

			case 'known_contact':
				return ! empty( $lead['known_contact'] );

			case 'has_value':
				return (float) ( $lead['estimated_value'] ?? 0 ) > 0;
		}

		return false;
	}

	/**
	 * Whether an address looks like a business domain.
	 *
	 * @param string $email Email address.
	 */
	public static function is_business_email( string $email ): bool {
		if ( ! is_email( $email ) || false === strpos( $email, '@' ) ) {
			return false;
		}

		$domain = strtolower( substr( strrchr( $email, '@' ), 1 ) );

		return '' !== $domain && ! in_array( $domain, self::FREE_DOMAINS, true );
	}

	/**
	 * Band for a score.
	 *
	 * @param int $score 0-100.
	 */
	public static function band( int $score ): string {
		if ( $score >= 70 ) {
			return 'hot';
		}
		if ( $score >= 40 ) {
			return 'warm';
		}
		return 'cold';
	}

	/**
	 * Human label for a band.
	 *
	 * @param string $band cold|warm|hot.
	 */
	public static function band_label( string $band ): string {
		$labels = array(
			'hot'  => __( 'Hot', 'smart-client-contact-hub' ),
			'warm' => __( 'Warm', 'smart-client-contact-hub' ),
			'cold' => __( 'Cold', 'smart-client-contact-hub' ),
		);

		return $labels[ $band ] ?? $labels['cold'];
	}
}
