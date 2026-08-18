<?php
/**
 * Follow-up tasks against leads.
 *
 * @package SCCH
 */

namespace SCCH;

defined( 'ABSPATH' ) || exit;

/**
 * Create, complete, reschedule and query follow-up tasks.
 *
 * A lead's next open follow-up is mirrored onto the lead row so lists can
 * show and sort by it without a join; that mirror is refreshed whenever a
 * task is created, completed, rescheduled or deleted.
 */
class Followup_Service {

	/**
	 * Fully-qualified table name.
	 */
	public static function table(): string {
		global $wpdb;
		return $wpdb->prefix . 'client_lead_followups';
	}

	/**
	 * Priority levels.
	 *
	 * @return array<string,string>
	 */
	public static function priorities(): array {
		return array(
			'low'    => __( 'Low', 'smart-client-contact-hub' ),
			'normal' => __( 'Normal', 'smart-client-contact-hub' ),
			'high'   => __( 'High', 'smart-client-contact-hub' ),
		);
	}

	/**
	 * Create a task.
	 *
	 * @param array $data { lead_id, title, notes, due_at, priority, assigned_user }.
	 * @return int Insert ID, 0 on failure.
	 */
	public static function create( array $data ): int {
		global $wpdb;

		$lead_id = absint( $data['lead_id'] ?? 0 );
		$title   = sanitize_text_field( $data['title'] ?? '' );
		$due     = self::normalize_datetime( $data['due_at'] ?? '' );

		if ( $lead_id <= 0 || '' === $title || '' === $due ) {
			return 0;
		}

		$priority = isset( self::priorities()[ $data['priority'] ?? '' ] ) ? $data['priority'] : 'normal';

		$inserted = $wpdb->insert(
			self::table(),
			array(
				'lead_id'       => $lead_id,
				'title'         => $title,
				'notes'         => sanitize_textarea_field( $data['notes'] ?? '' ),
				'due_at'        => $due,
				'priority'      => $priority,
				'assigned_user' => absint( $data['assigned_user'] ?? 0 ),
				'created_by'    => get_current_user_id(),
				'created_at'    => current_time( 'mysql' ),
			),
			array( '%d', '%s', '%s', '%s', '%s', '%d', '%d', '%s' )
		);

		if ( ! $inserted ) {
			return 0;
		}

		$id = (int) $wpdb->insert_id;

		self::sync_next( $lead_id );

		Activity_Service::log(
			$lead_id,
			'followup',
			/* translators: %s: follow-up title. */
			sprintf( __( 'Follow-up scheduled: %s', 'smart-client-contact-hub' ), $title ),
			'',
			array( 'followup_id' => $id, 'due_at' => $due )
		);

		/**
		 * Fires after a follow-up task is created.
		 *
		 * @param int   $id      Follow-up ID.
		 * @param int   $lead_id Lead ID.
		 * @param array $data    Sanitized task data.
		 */
		do_action( 'scch_followup_created', $id, $lead_id, $data );

		return $id;
	}

	/**
	 * Mark a task complete, or reopen it.
	 *
	 * @param int  $id       Task ID.
	 * @param bool $complete True to complete, false to reopen.
	 */
	public static function set_complete( int $id, bool $complete = true ): bool {
		global $wpdb;

		$task = self::find( $id );
		if ( ! $task ) {
			return false;
		}

		$updated = $wpdb->update(
			self::table(),
			array( 'completed_at' => $complete ? current_time( 'mysql' ) : null ),
			array( 'id' => $id ),
			array( '%s' ),
			array( '%d' )
		);

		if ( false === $updated ) {
			return false;
		}

		self::sync_next( (int) $task->lead_id );

		if ( $complete ) {
			Activity_Service::log(
				(int) $task->lead_id,
				'followup',
				/* translators: %s: follow-up title. */
				sprintf( __( 'Follow-up completed: %s', 'smart-client-contact-hub' ), $task->title ),
				'',
				array( 'followup_id' => $id )
			);

			/**
			 * Fires after a follow-up task is completed.
			 *
			 * @param int    $id   Follow-up ID.
			 * @param object $task The task row as it was before completion.
			 */
			do_action( 'scch_followup_completed', $id, $task );
		}

		return true;
	}

	/**
	 * Move a task's due date.
	 *
	 * @param int    $id     Task ID.
	 * @param string $due_at New due datetime.
	 */
	public static function reschedule( int $id, string $due_at ): bool {
		global $wpdb;

		$task = self::find( $id );
		$due  = self::normalize_datetime( $due_at );

		if ( ! $task || '' === $due ) {
			return false;
		}

		$wpdb->update( self::table(), array( 'due_at' => $due ), array( 'id' => $id ), array( '%s' ), array( '%d' ) );
		self::sync_next( (int) $task->lead_id );

		return true;
	}

	/**
	 * Delete a task.
	 *
	 * @param int $id Task ID.
	 */
	public static function delete( int $id ): bool {
		global $wpdb;

		$task = self::find( $id );
		if ( ! $task ) {
			return false;
		}

		$deleted = (bool) $wpdb->delete( self::table(), array( 'id' => $id ), array( '%d' ) );
		self::sync_next( (int) $task->lead_id );

		return $deleted;
	}

	/**
	 * One task.
	 *
	 * @param int $id Task ID.
	 */
	public static function find( int $id ): ?object {
		global $wpdb;
		$table = self::table();
		$row   = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		return $row ?: null;
	}

	/**
	 * Tasks for one lead, soonest first, open before completed.
	 *
	 * @param int $lead_id Lead ID.
	 * @return array<int,object>
	 */
	public static function for_lead( int $lead_id ): array {
		global $wpdb;
		$table = self::table();

		return $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE lead_id = %d ORDER BY completed_at IS NOT NULL, due_at ASC", $lead_id ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		) ?: array();
	}

	/**
	 * Query open tasks across leads.
	 *
	 * @param array $args { scope: due_today|overdue|upcoming|all, assigned_user, limit }.
	 * @return array<int,object>
	 */
	public static function query( array $args = array() ): array {
		global $wpdb;

		$scope = $args['scope'] ?? 'all';
		$limit = max( 1, min( 200, (int) ( $args['limit'] ?? 50 ) ) );
		$table = self::table();
		$leads = Lead_Repository::table();

		$where  = array( 'f.completed_at IS NULL' );
		$params = array();

		$now   = current_time( 'mysql' );
		$today = gmdate( 'Y-m-d', strtotime( $now ) );

		if ( 'due_today' === $scope ) {
			$where[]  = 'f.due_at >= %s AND f.due_at <= %s';
			$params[] = $today . ' 00:00:00';
			$params[] = $today . ' 23:59:59';
		} elseif ( 'overdue' === $scope ) {
			$where[]  = 'f.due_at < %s';
			$params[] = $today . ' 00:00:00';
		} elseif ( 'upcoming' === $scope ) {
			$where[]  = 'f.due_at > %s';
			$params[] = $today . ' 23:59:59';
		}

		if ( ! empty( $args['assigned_user'] ) ) {
			$where[]  = 'f.assigned_user = %d';
			$params[] = absint( $args['assigned_user'] );
		}

		$where_sql = implode( ' AND ', $where );
		$params[]  = $limit;

		$sql = "SELECT f.*, l.name AS lead_name, l.email AS lead_email, l.phone AS lead_phone, l.status AS lead_status
				FROM {$table} f
				LEFT JOIN {$leads} l ON l.id = f.lead_id
				WHERE {$where_sql}
				ORDER BY f.due_at ASC
				LIMIT %d";

		return $wpdb->get_results( $wpdb->prepare( $sql, $params ) ) ?: array(); // phpcs:ignore WordPress.DB.PreparedSQL
	}

	/**
	 * Count of open tasks in a scope.
	 *
	 * @param string $scope due_today|overdue|upcoming|all.
	 */
	public static function count( string $scope = 'all' ): int {
		return count( self::query( array( 'scope' => $scope, 'limit' => 200 ) ) );
	}

	/**
	 * Refresh a lead's next_followup_at mirror.
	 *
	 * @param int $lead_id Lead ID.
	 */
	private static function sync_next( int $lead_id ): void {
		global $wpdb;

		$table = self::table();
		$next  = $wpdb->get_var(
			$wpdb->prepare( "SELECT MIN(due_at) FROM {$table} WHERE lead_id = %d AND completed_at IS NULL", $lead_id ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);

		$wpdb->update(
			Lead_Repository::table(),
			array( 'next_followup_at' => $next ?: null ),
			array( 'id' => $lead_id ),
			array( '%s' ),
			array( '%d' )
		);
	}

	/**
	 * Coerce a datetime-local value into MySQL format, or '' if unusable.
	 *
	 * @param string $value Raw value.
	 */
	private static function normalize_datetime( string $value ): string {
		$value = trim( $value );
		if ( '' === $value ) {
			return '';
		}

		$ts = strtotime( str_replace( 'T', ' ', $value ) );

		return $ts ? gmdate( 'Y-m-d H:i:s', $ts ) : '';
	}

	/**
	 * Remove tasks belonging to deleted leads.
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
