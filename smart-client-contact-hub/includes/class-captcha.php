<?php
/**
 * Built-in math CAPTCHA. No external services.
 *
 * @package SCCH
 */

namespace SCCH;

defined( 'ABSPATH' ) || exit;

/**
 * Generates simple addition/subtraction challenges with operands 1–9 and a
 * guaranteed non-negative result. The answer never leaves the server: it is
 * stored in a short-lived transient keyed by an unguessable token.
 */
class Captcha {

	private const TTL = 15 * MINUTE_IN_SECONDS;

	/**
	 * Whether CAPTCHA is enabled in settings.
	 */
	public static function enabled(): bool {
		return (bool) Settings::get( 'scch_captcha', 'enabled', 1 );
	}

	/**
	 * Generate a new challenge.
	 *
	 * @return array{token:string,question:string}
	 */
	public static function generate(): array {
		$operations = (array) Settings::get( 'scch_captcha', 'operations', array( 'add', 'subtract' ) );
		if ( empty( $operations ) ) {
			$operations = array( 'add' );
		}
		$operation = $operations[ wp_rand( 0, count( $operations ) - 1 ) ];

		$a = wp_rand( 1, 9 );
		$b = wp_rand( 1, 9 );

		if ( 'subtract' === $operation ) {
			// Never produce a negative answer.
			if ( $b > $a ) {
				list( $a, $b ) = array( $b, $a );
			}
			$answer   = $a - $b;
			$question = sprintf( '%d − %d = ?', $a, $b );
		} else {
			$answer   = $a + $b;
			$question = sprintf( '%d + %d = ?', $a, $b );
		}

		$token = wp_generate_password( 24, false, false );
		set_transient( 'scch_cap_' . $token, (string) $answer, self::TTL );

		return array(
			'token'    => $token,
			'question' => $question,
		);
	}

	/**
	 * Verify a submitted answer. Single use: the transient is always deleted,
	 * so a failed or replayed token forces a brand-new question.
	 *
	 * @param string $token  Challenge token.
	 * @param string $answer Submitted answer.
	 */
	public static function verify( string $token, string $answer ): bool {
		if ( ! self::enabled() ) {
			return true;
		}

		$token = preg_replace( '/[^a-zA-Z0-9]/', '', $token );
		if ( '' === $token ) {
			return false;
		}

		$expected = get_transient( 'scch_cap_' . $token );
		delete_transient( 'scch_cap_' . $token );

		if ( false === $expected ) {
			return false;
		}

		return trim( $answer ) === (string) $expected;
	}
}
