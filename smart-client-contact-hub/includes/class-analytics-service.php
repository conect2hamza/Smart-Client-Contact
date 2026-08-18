<?php
/**
 * Reporting queries for the dashboard and analytics screens.
 *
 * @package SCCH
 */

namespace SCCH;

defined( 'ABSPATH' ) || exit;

/**
 * Every figure here is calculated from stored lead rows.
 *
 * Nothing is estimated or invented: the plugin does not track page views, so
 * no visitor-level metric is reported. Results are cached briefly because the
 * dashboard runs several of these at once on every load.
 */
class Analytics_Service {

	/**
	 * Cache lifetime for a computed report.
	 */
	private const TTL = 5 * MINUTE_IN_SECONDS;

	/**
	 * Cache key prefix, bumped when the shape of a report changes.
	 */
	private const PREFIX = 'scch_rep1_';

	/**
	 * Headline numbers for a period, with the previous period for comparison.
	 *
	 * @param int $days Period length in days.
	 * @return array<string,array{value:float,previous:float,delta:?float}>
	 */
	public static function kpis( int $days = 30 ): array {
		$days = self::clamp_days( $days );

		return self::remember(
			'kpis_' . $days,
			static function () use ( $days ) {
				$now  = self::now();
				$from = self::days_ago( $days );
				$prev = self::days_ago( $days * 2 );

				$current  = self::window_totals( $from, $now );
				$previous = self::window_totals( $prev, $from );

				$out = array();
				foreach ( $current as $key => $value ) {
					$was = $previous[ $key ] ?? 0;
					$out[ $key ] = array(
						'value'    => $value,
						'previous' => $was,
						'delta'    => self::delta( $value, $was ),
					);
				}

				return $out;
			}
		);
	}

	/**
	 * Totals for one window.
	 *
	 * @param string $from Inclusive start, MySQL datetime.
	 * @param string $to   Exclusive end, MySQL datetime.
	 * @return array<string,float>
	 */
	private static function window_totals( string $from, string $to ): array {
		global $wpdb;

		$table = Lead_Repository::table();
		$won   = Pipeline_Service::of_type( 'won' );
		$junk  = Pipeline_Service::of_type( 'junk' );
		$open  = Pipeline_Service::of_type( 'open' );

		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT
					COUNT(*) AS total,
					SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) AS fresh,
					SUM(CASE WHEN score >= 70 THEN 1 ELSE 0 END) AS hot,
					SUM(estimated_value) AS pipeline_all,
					SUM(actual_revenue) AS revenue
				FROM {$table}
				WHERE submission_date >= %s AND submission_date < %s",
				$from,
				$to
			), // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			ARRAY_A
		);

		$counted   = static fn( array $statuses ): int => self::count_in( $statuses, $from, $to );
		$total     = (int) ( $row['total'] ?? 0 );
		$qualified = $counted( array( 'qualified', 'proposal', 'negotiation' ) );
		$won_count = $counted( $won );
		$real      = max( 0, $total - $counted( $junk ) );

		return array(
			'total'      => (float) $total,
			'new'        => (float) ( $row['fresh'] ?? 0 ),
			'hot'        => (float) ( $row['hot'] ?? 0 ),
			'qualified'  => (float) $qualified,
			'won'        => (float) $won_count,
			'conversion' => $real > 0 ? round( ( $won_count / $real ) * 100, 1 ) : 0.0,
			'pipeline'   => (float) self::sum_value( $open, $from, $to ),
			'revenue'    => (float) ( $row['revenue'] ?? 0 ),
		);
	}

	/**
	 * Count leads whose status is in a set, within a window.
	 *
	 * @param string[] $statuses Status keys.
	 * @param string   $from     Window start.
	 * @param string   $to       Window end.
	 */
	private static function count_in( array $statuses, string $from, string $to ): int {
		global $wpdb;

		if ( ! $statuses ) {
			return 0;
		}

		$table  = Lead_Repository::table();
		$holes  = implode( ',', array_fill( 0, count( $statuses ), '%s' ) );
		$params = array_merge( $statuses, array( $from, $to ) );

		return (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE status IN ({$holes}) AND submission_date >= %s AND submission_date < %s",
				$params
			) // phpcs:ignore WordPress.DB.PreparedSQL
		);
	}

	/**
	 * Sum estimated value for a set of statuses within a window.
	 *
	 * @param string[] $statuses Status keys.
	 * @param string   $from     Window start.
	 * @param string   $to       Window end.
	 */
	private static function sum_value( array $statuses, string $from, string $to ): float {
		global $wpdb;

		if ( ! $statuses ) {
			return 0.0;
		}

		$table  = Lead_Repository::table();
		$holes  = implode( ',', array_fill( 0, count( $statuses ), '%s' ) );
		$params = array_merge( $statuses, array( $from, $to ) );

		return (float) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COALESCE(SUM(estimated_value),0) FROM {$table} WHERE status IN ({$holes}) AND submission_date >= %s AND submission_date < %s",
				$params
			) // phpcs:ignore WordPress.DB.PreparedSQL
		);
	}

	/**
	 * Leads per day over a period, zero-filled so the chart has no gaps.
	 *
	 * @param int $days Period length.
	 * @return array<int,array{date:string,count:int}>
	 */
	public static function trend( int $days = 30 ): array {
		$days = self::clamp_days( $days );

		return self::remember(
			'trend_' . $days,
			static function () use ( $days ) {
				global $wpdb;

				$table = Lead_Repository::table();
				$from  = self::days_ago( $days - 1 );

				$rows = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT DATE(submission_date) AS day, COUNT(*) AS count
						 FROM {$table}
						 WHERE submission_date >= %s
						 GROUP BY DATE(submission_date)",
						gmdate( 'Y-m-d 00:00:00', strtotime( $from ) )
					), // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					ARRAY_A
				) ?: array();

				$counts = array();
				foreach ( $rows as $row ) {
					$counts[ $row['day'] ] = (int) $row['count'];
				}

				$series = array();
				$cursor = strtotime( gmdate( 'Y-m-d', strtotime( $from ) ) );

				for ( $i = 0; $i < $days; $i++ ) {
					$day      = gmdate( 'Y-m-d', $cursor + ( $i * DAY_IN_SECONDS ) );
					$series[] = array(
						'date'  => $day,
						'count' => $counts[ $day ] ?? 0,
					);
				}

				return $series;
			}
		);
	}

	/**
	 * Lead counts grouped by a column, with won counts and conversion.
	 *
	 * @param string $column source|channel|service|utm_campaign.
	 * @param int    $days   Period length.
	 * @param int    $limit  Maximum groups.
	 * @return array<int,array>
	 */
	public static function breakdown( string $column, int $days = 30, int $limit = 8 ): array {
		$allowed = array( 'source', 'channel', 'service', 'utm_campaign', 'utm_source', 'device' );

		if ( ! in_array( $column, $allowed, true ) ) {
			return array();
		}

		$days = self::clamp_days( $days );

		return self::remember(
			'break_' . $column . '_' . $days . '_' . $limit,
			static function () use ( $column, $days, $limit ) {
				global $wpdb;

				$table = Lead_Repository::table();
				$from  = self::days_ago( $days );
				$won   = Pipeline_Service::of_type( 'won' );
				$holes = $won ? implode( ',', array_fill( 0, count( $won ), '%s' ) ) : "''";

				// $column is whitelisted above; $holes is placeholders only.
				$sql = "SELECT
							{$column} AS label,
							COUNT(*) AS leads,
							SUM(CASE WHEN status IN ({$holes}) THEN 1 ELSE 0 END) AS won,
							COALESCE(SUM(actual_revenue),0) AS revenue
						FROM {$table}
						WHERE submission_date >= %s
						GROUP BY {$column}
						ORDER BY leads DESC
						LIMIT %d";

				$params = array_merge( $won, array( $from, max( 1, min( 50, $limit ) ) ) );
				$rows   = $wpdb->get_results( $wpdb->prepare( $sql, $params ), ARRAY_A ) ?: array(); // phpcs:ignore WordPress.DB.PreparedSQL

				$out = array();
				foreach ( $rows as $row ) {
					$leads = (int) $row['leads'];
					$out[] = array(
						'label'      => '' === trim( (string) $row['label'] ) ? __( 'Unknown', 'smart-client-contact-hub' ) : (string) $row['label'],
						'leads'      => $leads,
						'won'        => (int) $row['won'],
						'revenue'    => (float) $row['revenue'],
						'conversion' => $leads > 0 ? round( ( (int) $row['won'] / $leads ) * 100, 1 ) : 0.0,
					);
				}

				return $out;
			}
		);
	}

	/**
	 * Lead counts per pipeline stage, for the funnel and the board header.
	 *
	 * @return array<string,int>
	 */
	public static function stage_counts(): array {
		return self::remember(
			'stages',
			static function () {
				global $wpdb;

				$table = Lead_Repository::table();
				$rows  = $wpdb->get_results( "SELECT status, COUNT(*) AS count FROM {$table} GROUP BY status", ARRAY_A ) ?: array(); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

				$out = array();
				foreach ( array_keys( Pipeline_Service::stages() ) as $stage ) {
					$out[ $stage ] = 0;
				}
				foreach ( $rows as $row ) {
					$status = (string) $row['status'];
					// Legacy "closed" leads count toward Won.
					if ( 'closed' === $status ) {
						$status = 'won';
					}
					if ( isset( $out[ $status ] ) ) {
						$out[ $status ] += (int) $row['count'];
					}
				}

				return $out;
			}
		);
	}

	/**
	 * Highest-scoring open leads.
	 *
	 * @param int $limit Maximum rows.
	 * @return array<int,object>
	 */
	public static function hot_leads( int $limit = 5 ): array {
		global $wpdb;

		$table = Lead_Repository::table();
		$open  = Pipeline_Service::of_type( 'open' );

		if ( ! $open ) {
			return array();
		}

		$holes  = implode( ',', array_fill( 0, count( $open ), '%s' ) );
		$params = array_merge( $open, array( max( 1, min( 50, $limit ) ) ) );

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE status IN ({$holes}) ORDER BY score DESC, submission_date DESC LIMIT %d",
				$params
			) // phpcs:ignore WordPress.DB.PreparedSQL
		) ?: array();
	}

	/**
	 * Percentage change between two figures, or null when there is no base.
	 *
	 * @param float $current  Current value.
	 * @param float $previous Previous value.
	 */
	public static function delta( float $current, float $previous ): ?float {
		if ( $previous <= 0.0 ) {
			return $current > 0.0 ? null : 0.0;
		}

		return round( ( ( $current - $previous ) / $previous ) * 100, 1 );
	}

	/**
	 * Drop every cached report. Called whenever lead data changes.
	 */
	public static function flush(): void {
		global $wpdb;

		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
				$wpdb->esc_like( '_transient_' . self::PREFIX ) . '%',
				$wpdb->esc_like( '_transient_timeout_' . self::PREFIX ) . '%'
			) // phpcs:ignore WordPress.DB.DirectDatabaseQuery -- targeted cache cleanup.
		);
	}

	/**
	 * Run a report, caching the result briefly.
	 *
	 * @param string   $key     Cache key suffix.
	 * @param callable $compute Producer.
	 * @return mixed
	 */
	private static function remember( string $key, callable $compute ) {
		$full   = self::PREFIX . $key;
		$cached = get_transient( $full );

		if ( false !== $cached ) {
			return $cached;
		}

		$value = $compute();
		set_transient( $full, $value, self::TTL );

		return $value;
	}

	/**
	 * Current site time as a MySQL datetime.
	 */
	private static function now(): string {
		return current_time( 'mysql' );
	}

	/**
	 * A MySQL datetime N days before now, at the start of that day.
	 *
	 * @param int $days Days back.
	 */
	private static function days_ago( int $days ): string {
		return gmdate( 'Y-m-d 00:00:00', strtotime( self::now() ) - ( $days * DAY_IN_SECONDS ) );
	}

	/**
	 * Keep a period within the range the UI offers.
	 *
	 * @param int $days Requested days.
	 */
	public static function clamp_days( int $days ): int {
		return max( 1, min( 365, $days ) );
	}
}
