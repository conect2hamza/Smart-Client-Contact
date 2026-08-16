<?php
/**
 * Uninstall routine.
 *
 * Runs only when the plugin is deleted from the Plugins screen. Honors the
 * administrator's choices saved on the Help screen:
 *   - delete_settings: remove every scch_* option
 *   - delete_leads:    drop the leads and email log tables
 * If neither is checked, all data is kept.
 *
 * @package SCCH
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

$scch_uninstall = get_option( 'scch_uninstall', array() );
$scch_uninstall = is_array( $scch_uninstall ) ? $scch_uninstall : array();

global $wpdb;

if ( ! empty( $scch_uninstall['delete_leads'] ) ) {
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}client_leads" );           // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}client_leads_email_log" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
}

if ( ! empty( $scch_uninstall['delete_settings'] ) ) {
	$scch_options = array(
		'scch_appearance',
		'scch_contact',
		'scch_form',
		'scch_captcha',
		'scch_email',
		'scch_general',
		'scch_uninstall',
		'scch_services',
		'scch_version',
		'scch_flush_needed',
	);
	foreach ( $scch_options as $scch_option ) {
		delete_option( $scch_option );
	}
}

// Transients (CAPTCHA challenges and rate-limit counters) are always removed:
// they are ephemeral by design and useless without the plugin.
$wpdb->query(
	"DELETE FROM {$wpdb->options}
	 WHERE option_name LIKE '\_transient\_scch\_%'
	    OR option_name LIKE '\_transient\_timeout\_scch\_%'"
); // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.NotPrepared
