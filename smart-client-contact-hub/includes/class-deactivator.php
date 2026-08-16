<?php
/**
 * Plugin deactivation routines.
 *
 * @package SCCH
 */

namespace SCCH;

defined( 'ABSPATH' ) || exit;

/**
 * Cleans transient state on deactivation. Data and settings are preserved;
 * removal is handled by uninstall.php according to the administrator's choice.
 */
class Deactivator {

	/**
	 * Run deactivation tasks.
	 */
	public static function deactivate(): void {
		global $wpdb;

		// Remove rate-limit and CAPTCHA transients created by the plugin.
		$wpdb->query(
			"DELETE FROM {$wpdb->options}
			 WHERE option_name LIKE '\\_transient\\_scch\\_%'
			    OR option_name LIKE '\\_transient\\_timeout\\_scch\\_%'"
		); // phpcs:ignore WordPress.DB.DirectDatabaseQuery -- targeted transient cleanup.
	}
}
