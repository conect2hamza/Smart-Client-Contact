<?php
/**
 * Data access layer for the wp_client_leads table.
 *
 * @package SCCH
 */

namespace SCCH;

defined( 'ABSPATH' ) || exit;

/**
 * All reads/writes against the custom leads table. Every query is prepared.
 */
class Lead_Repository {

	public const STATUSES = array( 'new', 'contacted', 'qualified', 'closed', 'spam' );

	/**
	 * Fully-qualified table name.
	 */
	public static function table(): string {
		global $wpdb;
		return $wpdb->prefix . 'client_leads';
	}

	/**
	 * Insert a lead.
	 *
	 * @param array $data Sanitized lead data.
	 * @return int Insert ID, 0 on failure.
	 */
	public static function insert( array $data ): int {
		global $wpdb;

		$inserted = $wpdb->insert(
			self::table(),
			array(
				'name'            => $data['name'],
				'phone'           => $data['phone'],
				'email'           => $data['email'],
				'service'         => $data['service'],
				'message'         => $data['message'],
				'status'          => 'new',
				'submission_date' => current_time( 'mysql' ),
				'ip_address'      => $data['ip_address'],
				'user_agent'      => $data['user_agent'],
			),
			array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		return $inserted ? (int) $wpdb->insert_id : 0;
	}

	/**
	 * Fetch one lead.
	 *
	 * @param int $id Lead ID.
	 */
	public static function find( int $id ): ?object {
		global $wpdb;
		$table = self::table();
		$row   = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $row ?: null;
	}

	/**
	 * Query leads with search/filter/sort/pagination.
	 *
	 * @param array $args {search, status, orderby, order, per_page, paged}.
	 * @return array{items:array,total:int}
	 */
	public static function query( array $args ): array {
		global $wpdb;

		$defaults = array(
			'search'   => '',
			'status'   => '',
			'orderby'  => 'submission_date',
			'order'    => 'DESC',
			'per_page' => 20,
			'paged'    => 1,
		);
		$args     = wp_parse_args( $args, $defaults );

		$allowed_orderby = array( 'id', 'name', 'email', 'service', 'status', 'submission_date' );
		$orderby         = in_array( $args['orderby'], $allowed_orderby, true ) ? $args['orderby'] : 'submission_date';
		$order           = 'ASC' === strtoupper( $args['order'] ) ? 'ASC' : 'DESC';
		$per_page        = max( 1, min( 200, (int) $args['per_page'] ) );
		$offset          = ( max( 1, (int) $args['paged'] ) - 1 ) * $per_page;

		$where  = array( '1=1' );
		$params = array();

		if ( '' !== $args['search'] ) {
			$like     = '%' . $wpdb->esc_like( $args['search'] ) . '%';
			$where[]  = '(name LIKE %s OR email LIKE %s OR phone LIKE %s OR message LIKE %s)';
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
		}

		if ( '' !== $args['status'] && in_array( $args['status'], self::STATUSES, true ) ) {
			$where[]  = 'status = %s';
			$params[] = $args['status'];
		}

		$table     = self::table();
		$where_sql = implode( ' AND ', $where );

		$count_sql = "SELECT COUNT(*) FROM {$table} WHERE {$where_sql}";
		$total     = (int) ( $params ? $wpdb->get_var( $wpdb->prepare( $count_sql, $params ) ) : $wpdb->get_var( $count_sql ) ); // phpcs:ignore WordPress.DB.PreparedSQL

		$list_sql    = "SELECT * FROM {$table} WHERE {$where_sql} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d";
		$list_params = array_merge( $params, array( $per_page, $offset ) );
		$items       = $wpdb->get_results( $wpdb->prepare( $list_sql, $list_params ) ); // phpcs:ignore WordPress.DB.PreparedSQL

		return array(
			'items' => $items ?: array(),
			'total' => $total,
		);
	}

	/**
	 * All leads matching a status filter (for CSV export).
	 *
	 * @param string $status Optional status.
	 */
	public static function all( string $status = '' ): array {
		global $wpdb;
		$table = self::table();

		if ( '' !== $status && in_array( $status, self::STATUSES, true ) ) {
			return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} WHERE status = %s ORDER BY submission_date DESC", $status ) ) ?: array(); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		}

		return $wpdb->get_results( "SELECT * FROM {$table} ORDER BY submission_date DESC" ) ?: array(); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * Bulk delete by IDs.
	 *
	 * @param int[] $ids Lead IDs.
	 */
	public static function delete( array $ids ): int {
		global $wpdb;
		$ids = array_filter( array_map( 'absint', $ids ) );
		if ( ! $ids ) {
			return 0;
		}
		$table        = self::table();
		$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
		return (int) $wpdb->query( $wpdb->prepare( "DELETE FROM {$table} WHERE id IN ({$placeholders})", $ids ) ); // phpcs:ignore WordPress.DB.PreparedSQL
	}

	/**
	 * Bulk status update.
	 *
	 * @param int[]  $ids    Lead IDs.
	 * @param string $status New status.
	 */
	public static function update_status( array $ids, string $status ): int {
		global $wpdb;
		$ids = array_filter( array_map( 'absint', $ids ) );
		if ( ! $ids || ! in_array( $status, self::STATUSES, true ) ) {
			return 0;
		}
		$table        = self::table();
		$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
		$params       = array_merge( array( $status ), $ids );
		return (int) $wpdb->query( $wpdb->prepare( "UPDATE {$table} SET status = %s WHERE id IN ({$placeholders})", $params ) ); // phpcs:ignore WordPress.DB.PreparedSQL
	}

	/**
	 * Dashboard counters.
	 *
	 * @return array{total:int,new:int,today:int,week:int}
	 */
	public static function stats(): array {
		global $wpdb;
		$table = self::table();

		return array(
			'total' => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" ), // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			'new'   => (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE status = %s", 'new' ) ), // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			'today' => (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE submission_date >= %s", gmdate( 'Y-m-d 00:00:00', strtotime( current_time( 'mysql' ) ) ) ) ), // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			'week'  => (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE submission_date >= %s", gmdate( 'Y-m-d H:i:s', strtotime( current_time( 'mysql' ) ) - WEEK_IN_SECONDS ) ) ), // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);
	}
}
