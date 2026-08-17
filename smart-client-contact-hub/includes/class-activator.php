<?php
/**
 * Plugin activation routines.
 *
 * @package SCCH
 */

namespace SCCH;

defined( 'ABSPATH' ) || exit;

/**
 * Creates database tables and seeds default options on activation.
 */
class Activator {

	/**
	 * Run activation tasks.
	 */
	public static function activate(): void {
		self::create_tables();
		self::seed_defaults();

		// Builds the channel list from the pre-1.0.5 Contact Settings the
		// first time, so an upgrading site keeps exactly the buttons it had.
		Channels::maybe_migrate();

		update_option( 'scch_version', SCCH_VERSION );
	}

	/**
	 * Create the custom leads and email log tables via dbDelta.
	 */
	private static function create_tables(): void {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset = $wpdb->get_charset_collate();

		$leads = $wpdb->prefix . 'client_leads';
		$sql   = "CREATE TABLE {$leads} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			name VARCHAR(80) NOT NULL DEFAULT '',
			phone VARCHAR(40) NOT NULL DEFAULT '',
			email VARCHAR(190) NOT NULL DEFAULT '',
			service VARCHAR(190) NOT NULL DEFAULT '',
			message TEXT NULL,
			status VARCHAR(20) NOT NULL DEFAULT 'new',
			submission_date DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00',
			ip_address VARCHAR(45) NOT NULL DEFAULT '',
			user_agent VARCHAR(255) NOT NULL DEFAULT '',
			PRIMARY KEY  (id),
			KEY status (status),
			KEY email (email(100)),
			KEY submission_date (submission_date)
		) {$charset};";
		dbDelta( $sql );

		$logs = $wpdb->prefix . 'client_leads_email_log';
		$sql  = "CREATE TABLE {$logs} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			lead_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			recipient VARCHAR(190) NOT NULL DEFAULT '',
			subject VARCHAR(255) NOT NULL DEFAULT '',
			type VARCHAR(20) NOT NULL DEFAULT 'admin',
			status VARCHAR(20) NOT NULL DEFAULT 'sent',
			error TEXT NULL,
			created_at DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00',
			PRIMARY KEY  (id),
			KEY lead_id (lead_id),
			KEY status (status),
			KEY created_at (created_at)
		) {$charset};";
		dbDelta( $sql );
	}

	/**
	 * Seed default option values without overwriting existing settings.
	 */
	private static function seed_defaults(): void {
		foreach ( Settings::defaults() as $option => $values ) {
			if ( false === get_option( $option ) ) {
				add_option( $option, $values );
			}
		}

		if ( false === get_option( 'scch_services' ) ) {
			add_option(
				'scch_services',
				array(
					array( 'id' => 'seo', 'label' => __( 'SEO & Content Strategy', 'smart-client-contact-hub' ) ),
					array( 'id' => 'web-design', 'label' => __( 'Web Design & Development', 'smart-client-contact-hub' ) ),
					array( 'id' => 'ppc', 'label' => __( 'Paid Advertising (PPC)', 'smart-client-contact-hub' ) ),
					array( 'id' => 'other', 'label' => __( 'Something Else', 'smart-client-contact-hub' ) ),
				)
			);
		}
	}
}
