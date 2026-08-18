<?php
/**
 * Lead activity timeline.
 *
 * @package SCCH
 */

namespace SCCH;

defined( 'ABSPATH' ) || exit;

/**
 * Append-only record of what happened to a lead, and who did it.
 *
 * Entries are written by the plugin itself (a lead arriving, a stage change,
 * an email going out) and by administrators adding notes. Nothing here is
 * ever edited in place, so the timeline stays a trustworthy audit trail.
 */
class Activity_Service {

	/**
	 * Fully-qualified table name.
	 */
	public static function table(): string {
		global $wpdb;
		return $wpdb->prefix . 'client_lead_activity';
	}

	/**
	 * Event types and how they present.
	 *
	 * @return array<string,array{label:string,icon:string,color:string}>
	 */
	public static function types(): array {
		return array(
			'created'   => array( 'label' => __( 'Lead created', 'smart-client-contact-hub' ), 'icon' => 'sparkles', 'color' => '#2563eb' ),
			'status'    => array( 'label' => __( 'Stage changed', 'smart-client-contact-hub' ), 'icon' => 'check', 'color' => '#7c3aed' ),
			'note'      => array( 'label' => __( 'Note', 'smart-client-contact-hub' ), 'icon' => 'document', 'color' => '#64748b' ),
			'email'     => array( 'label' => __( 'Email', 'smart-client-contact-hub' ), 'icon' => 'mail', 'color' => '#0891b2' ),
			'followup'  => array( 'label' => __( 'Follow-up', 'smart-client-contact-hub' ), 'icon' => 'clock', 'color' => '#d97706' ),
			'assigned'  => array( 'label' => __( 'Assignment', 'smart-client-contact-hub' ), 'icon' => 'user', 'color' => '#0891b2' ),
			'score'     => array( 'label' => __( 'Score', 'smart-client-contact-hub' ), 'icon' => 'bolt', 'color' => '#d97706' ),
			'value'     => array( 'label' => __( 'Value', 'smart-client-contact-hub' ), 'icon' => 'briefcase', 'color' => '#16a34a' ),
		);
	}

	/**
	 * Record an event.
	 *
	 * @param int    $lead_id Lead ID.
	 * @param string $type    Event type.
	 * @param string $summary One-line description.
	 * @param string $body    Optional longer text (a note's content).
	 * @param array  $meta    Optional structured detail.
	 * @return int Insert ID, 0 on failure.
	 */
	public static function log( int $lead_id, string $type, string $summary, string $body = '', array $meta = array() ): int {
		global $wpdb;

		if ( $lead_id <= 0 ) {
			return 0;
		}

		$types = self::types();
		$type  = isset( $types[ $type ] ) ? $type : 'note';
		$now   = current_time( 'mysql' );

		$inserted = $wpdb->insert(
			self::table(),
			array(
				'lead_id'    => $lead_id,
				'type'       => $type,
				'summary'    => mb_substr( $summary, 0, 255 ),
				'body'       => $body,
				'meta'       => $meta ? wp_json_encode( $meta ) : null,
				'user_id'    => get_current_user_id(),
				'created_at' => $now,
			),
			array( '%d', '%s', '%s', '%s', '%s', '%d', '%s' )
		);

		if ( ! $inserted ) {
			return 0;
		}

		// Keep the denormalized column in step so lead lists can sort on it
		// without joining the timeline.
		$wpdb->update(
			Lead_Repository::table(),
			array( 'last_activity_at' => $now ),
			array( 'id' => $lead_id ),
			array( '%s' ),
			array( '%d' )
		);

		return (int) $wpdb->insert_id;
	}

	/**
	 * Timeline for one lead, newest first.
	 *
	 * @param int $lead_id Lead ID.
	 * @param int $limit   Maximum rows.
	 * @return array<int,object>
	 */
	public static function for_lead( int $lead_id, int $limit = 100 ): array {
		global $wpdb;
		$table = self::table();
		$limit = max( 1, min( 500, $limit ) );

		return $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE lead_id = %d ORDER BY created_at DESC, id DESC LIMIT %d", $lead_id, $limit ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		) ?: array();
	}

	/**
	 * Most recent activity across all leads, for the dashboard.
	 *
	 * @param int $limit Maximum rows.
	 * @return array<int,object>
	 */
	public static function recent( int $limit = 12 ): array {
		global $wpdb;
		$table = self::table();
		$leads = Lead_Repository::table();
		$limit = max( 1, min( 100, $limit ) );

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT a.*, l.name AS lead_name
				 FROM {$table} a
				 LEFT JOIN {$leads} l ON l.id = a.lead_id
				 ORDER BY a.created_at DESC, a.id DESC
				 LIMIT %d",
				$limit
			) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		) ?: array();
	}

	/**
	 * Remove a lead's timeline. Called when the lead itself is deleted.
	 *
	 * @param int[] $lead_ids Lead IDs.
	 */
	public static function delete_for_leads( array $lead_ids ): int {
		global $wpdb;

		$ids = array_filter( array_map( 'absint', $lead_ids ) );
		if ( ! $ids ) {
			return 0;
		}

		$table        = self::table();
		$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );

		return (int) $wpdb->query( $wpdb->prepare( "DELETE FROM {$table} WHERE lead_id IN ({$placeholders})", $ids ) ); // phpcs:ignore WordPress.DB.PreparedSQL
	}
}
