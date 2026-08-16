<?php
/**
 * Data access layer for the email log table.
 *
 * @package SCCH
 */

namespace SCCH;

defined( 'ABSPATH' ) || exit;

/**
 * Stores every outgoing email attempt so administrators can audit and resend.
 */
class Email_Log_Repository {

	/**
	 * Fully-qualified table name.
	 */
	public static function table(): string {
		global $wpdb;
		return $wpdb->prefix . 'client_leads_email_log';
	}

	/**
	 * Record an email attempt.
	 *
	 * @param int    $lead_id   Related lead.
	 * @param string $recipient Recipient address(es).
	 * @param string $subject   Subject line.
	 * @param string $type      'admin' | 'customer' | 'test'.
	 * @param bool   $sent      Result of wp_mail().
	 * @param string $error     Error detail if any.
	 */
	public static function log( int $lead_id, string $recipient, string $subject, string $type, bool $sent, string $error = '' ): void {
		if ( ! Settings::get( 'scch_general', 'log_enabled', 1 ) ) {
			return;
		}

		global $wpdb;
		$wpdb->insert(
			self::table(),
			array(
				'lead_id'    => $lead_id,
				'recipient'  => $recipient,
				'subject'    => $subject,
				'type'       => $type,
				'status'     => $sent ? 'sent' : 'failed',
				'error'      => $error,
				'created_at' => current_time( 'mysql' ),
			),
			array( '%d', '%s', '%s', '%s', '%s', '%s', '%s' )
		);
	}

	/**
	 * Paged log listing.
	 *
	 * @param int $per_page Rows per page.
	 * @param int $paged    Page number.
	 * @return array{items:array,total:int}
	 */
	public static function query( int $per_page = 30, int $paged = 1 ): array {
		global $wpdb;
		$table    = self::table();
		$per_page = max( 1, min( 200, $per_page ) );
		$offset   = ( max( 1, $paged ) - 1 ) * $per_page;

		return array(
			'items' => $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} ORDER BY created_at DESC LIMIT %d OFFSET %d", $per_page, $offset ) ) ?: array(), // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			'total' => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" ), // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		);
	}

	/**
	 * Fetch a single log row.
	 *
	 * @param int $id Log ID.
	 */
	public static function find( int $id ): ?object {
		global $wpdb;
		$table = self::table();
		$row   = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $row ?: null;
	}

	/**
	 * Delete a single log entry.
	 *
	 * @param int $id Log ID.
	 * @return bool True if a row was deleted.
	 */
	public static function delete( int $id ): bool {
		global $wpdb;
		$table = self::table();
		return (bool) $wpdb->delete( $table, array( 'id' => $id ), array( '%d' ) );
	}

	/**
	 * Delete every log entry.
	 *
	 * @return int Number of rows deleted.
	 */
	public static function delete_all(): int {
		global $wpdb;
		$table = self::table();
		return (int) $wpdb->query( "DELETE FROM {$table}" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}
}
