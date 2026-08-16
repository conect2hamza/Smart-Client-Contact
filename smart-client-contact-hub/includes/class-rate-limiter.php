<?php
/**
 * Transient-backed submission rate limiter.
 *
 * @package SCCH
 */

namespace SCCH;

defined( 'ABSPATH' ) || exit;

/**
 * Limits form submissions per client IP inside a rolling window.
 */
class Rate_Limiter {

	/**
	 * Cache group used for the object-cache counting path.
	 */
	private const CACHE_GROUP = 'scch_rate_limit';

	/**
	 * Headroom multiplier for the challenge bucket.
	 *
	 * A visitor requests a challenge every time they open the form view, and
	 * again on every manual refresh, so this bucket has to tolerate far more
	 * traffic than the submission bucket before it starts refusing.
	 */
	private const CHALLENGE_HEADROOM = 6;

	/**
	 * Whether the current client may perform an action in the given bucket.
	 *
	 * Buckets are counted independently: exhausting challenge requests never
	 * consumes a visitor's submission allowance, and vice versa.
	 *
	 * @param string $bucket 'submit' | 'challenge'.
	 */
	public static function allowed( string $bucket = 'submit' ): bool {
		$max    = max( 1, (int) Settings::get( 'scch_general', 'rate_limit_max', 5 ) );
		$window = max( 1, (int) Settings::get( 'scch_general', 'rate_limit_window', 10 ) );

		if ( 'challenge' === $bucket ) {
			$max *= self::CHALLENGE_HEADROOM;
		}

		$key = self::key( $bucket );
		$ttl = $window * MINUTE_IN_SECONDS;

		// When a persistent object cache (Redis, Memcached, …) is active,
		// wp_cache_incr() is atomic and closes the check-then-act race that a
		// bare get_transient()/set_transient() pair has under concurrent
		// requests. Sites without a persistent cache backend (the WordPress
		// default) fall back to the transient-based count below.
		if ( wp_using_ext_object_cache() ) {
			wp_cache_add( $key, 0, self::CACHE_GROUP, $ttl );
			$count = wp_cache_incr( $key, 1, self::CACHE_GROUP );

			if ( false !== $count ) {
				return $count <= $max;
			}
			// Backend didn't support incr (unusual); fall through below.
		}

		return self::allowed_via_transient( $key, $max, $ttl );
	}

	/**
	 * Transient-backed fallback counter. Not atomic, but the practical
	 * exposure is a handful of extra spam-form submissions in a burst, not a
	 * bypass of validation, CAPTCHA, or the honeypot.
	 *
	 * @param string $key Transient key.
	 * @param int    $max Max allowed submissions in the window.
	 * @param int    $ttl Window length in seconds.
	 */
	private static function allowed_via_transient( string $key, int $max, int $ttl ): bool {
		$count = (int) get_transient( $key );

		if ( $count >= $max ) {
			return false;
		}

		set_transient( $key, $count + 1, $ttl );

		return true;
	}

	/**
	 * Transient key for the current client IP within a bucket.
	 *
	 * @param string $bucket Counter bucket.
	 */
	private static function key( string $bucket ): string {
		return 'scch_rl_' . $bucket . '_' . md5( self::client_ip() . wp_salt( 'nonce' ) );
	}

	/**
	 * Best-effort client IP. REMOTE_ADDR only — proxy headers are spoofable
	 * and must not be trusted for rate limiting.
	 *
	 * When REMOTE_ADDR is absent or malformed this returns an empty string,
	 * and every such request shares a single counter. That is the intended
	 * failure mode: unattributable traffic is limited collectively rather
	 * than exempted. In practice it only occurs on misconfigured proxies —
	 * see the note on the CAPTCHA screen.
	 */
	public static function client_ip(): string {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		return filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : '';
	}
}
