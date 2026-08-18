<?php
/**
 * Leads list table.
 *
 * @package SCCH
 */

namespace SCCH\Admin;

use SCCH\Attribution;
use SCCH\Lead_Repository;
use SCCH\Lead_Service;
use SCCH\Pipeline_Service;
use SCCH\UI;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( '\WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Native WordPress table with search, status filter, sortable columns,
 * pagination, and nonce-verified bulk actions.
 */
class Leads_List_Table extends \WP_List_Table {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'lead',
				'plural'   => 'leads',
				'ajax'     => false,
			)
		);
	}

	/**
	 * Columns.
	 *
	 * @return array<string,string>
	 */
	public function get_columns(): array {
		return array(
			'cb'               => '<input type="checkbox" />',
			'name'             => __( 'Lead', 'smart-client-contact-hub' ),
			'score'            => __( 'Score', 'smart-client-contact-hub' ),
			'status'           => __( 'Stage', 'smart-client-contact-hub' ),
			'service'          => __( 'Service', 'smart-client-contact-hub' ),
			'source'           => __( 'Source', 'smart-client-contact-hub' ),
			'next_followup_at' => __( 'Follow-up', 'smart-client-contact-hub' ),
			'submission_date'  => __( 'Received', 'smart-client-contact-hub' ),
			'actions'          => __( 'Contact', 'smart-client-contact-hub' ),
		);
	}

	/**
	 * Sortable columns.
	 *
	 * @return array<string,array{0:string,1:bool}>
	 */
	protected function get_sortable_columns(): array {
		return array(
			'name'             => array( 'name', false ),
			'score'            => array( 'score', false ),
			'status'           => array( 'status', false ),
			'service'          => array( 'service', false ),
			'source'           => array( 'source', false ),
			'next_followup_at' => array( 'next_followup_at', false ),
			'submission_date'  => array( 'submission_date', true ),
		);
	}

	/**
	 * Bulk actions.
	 *
	 * @return array<string,string>
	 */
	protected function get_bulk_actions(): array {
		$actions = array( 'delete' => __( 'Delete', 'smart-client-contact-hub' ) );
		foreach ( Pipeline_Service::stages() as $key => $stage ) {
			/* translators: %s: pipeline stage label. */
			$actions[ 'status_' . $key ] = sprintf( __( 'Move to %s', 'smart-client-contact-hub' ), $stage['label'] );
		}
		return $actions;
	}

	/**
	 * Status filter links above the table.
	 *
	 * @return array<string,string>
	 */
	protected function get_views(): array {
		$current = isset( $_GET['status'] ) ? sanitize_key( wp_unslash( $_GET['status'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read filter.
		$base    = admin_url( 'admin.php?page=scch-leads' );
		$views   = array(
			'' => sprintf(
				'<a href="%s"%s>%s</a>',
				esc_url( $base ),
				'' === $current ? ' class="current"' : '',
				esc_html__( 'All', 'smart-client-contact-hub' )
			),
		);

		foreach ( Pipeline_Service::stages() as $key => $stage ) {
			$views[ $key ] = sprintf(
				'<a href="%s"%s>%s</a>',
				esc_url( add_query_arg( 'status', $key, $base ) ),
				$current === $key ? ' class="current"' : '',
				esc_html( $stage['label'] )
			);
		}

		return $views;
	}

	/**
	 * Handle bulk actions, then load the current page of rows.
	 */
	public function prepare_items(): void {
		$this->process_bulk_action();

		$per_page = $this->get_items_per_page( 'scch_leads_per_page', 20 );
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only list filters.
		$args = array(
			'search'        => isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : '',
			'status'        => isset( $_GET['status'] ) ? sanitize_key( wp_unslash( $_GET['status'] ) ) : '',
			'service'       => isset( $_GET['service'] ) ? sanitize_text_field( wp_unslash( $_GET['service'] ) ) : '',
			'source'        => isset( $_GET['source'] ) ? sanitize_text_field( wp_unslash( $_GET['source'] ) ) : '',
			'channel'       => isset( $_GET['channel'] ) ? sanitize_key( wp_unslash( $_GET['channel'] ) ) : '',
			'band'          => isset( $_GET['band'] ) ? sanitize_key( wp_unslash( $_GET['band'] ) ) : '',
			'assigned_user' => isset( $_GET['assigned_user'] ) ? sanitize_text_field( wp_unslash( $_GET['assigned_user'] ) ) : '',
			'since'         => $this->since_from_request(),
			'orderby'       => isset( $_GET['orderby'] ) ? sanitize_key( wp_unslash( $_GET['orderby'] ) ) : 'submission_date',
			'order'         => isset( $_GET['order'] ) ? sanitize_key( wp_unslash( $_GET['order'] ) ) : 'desc',
			'per_page'      => $per_page,
			'paged'         => $this->get_pagenum(),
		);
		// phpcs:enable

		$result = Lead_Repository::query( $args );

		$this->items = $result['items'];
		$this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns(), 'id' );
		$this->set_pagination_args(
			array(
				'total_items' => $result['total'],
				'per_page'    => $per_page,
				'total_pages' => (int) ceil( $result['total'] / $per_page ),
			)
		);
	}

	/**
	 * Run the selected bulk action against checked rows.
	 */
	private function process_bulk_action(): void {
		$action = $this->current_action();
		if ( ! $action ) {
			return;
		}

		check_admin_referer( 'bulk-' . $this->_args['plural'] );

		if ( ! current_user_can( Admin::CAP ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'smart-client-contact-hub' ) );
		}

		$ids = isset( $_REQUEST['lead'] ) ? array_map( 'absint', (array) $_REQUEST['lead'] ) : array();
		if ( ! $ids ) {
			return;
		}

		if ( 'delete' === $action ) {
			Lead_Service::delete( $ids );
			return;
		}

		if ( str_starts_with( $action, 'status_' ) ) {
			$status = substr( $action, 7 );

			// Routed one at a time so each move is logged and hooked.
			foreach ( $ids as $id ) {
				Lead_Service::change_status( (int) $id, $status );
			}
		}
	}

	/**
	 * Translate the date filter into a cut-off datetime.
	 */
	private function since_from_request(): string {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only filter.
		$range = isset( $_GET['range'] ) ? sanitize_key( wp_unslash( $_GET['range'] ) ) : '';
		$days  = array( '7' => 7, '30' => 30, '90' => 90 );

		if ( ! isset( $days[ $range ] ) ) {
			return '';
		}

		return gmdate( 'Y-m-d 00:00:00', strtotime( current_time( 'mysql' ) ) - ( $days[ $range ] * DAY_IN_SECONDS ) );
	}

	/**
	 * Filter controls above the table.
	 *
	 * @param string $which top|bottom.
	 */
	protected function extra_tablenav( $which ) {
		if ( 'top' !== $which ) {
			return;
		}

		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only filters.
		$selected = array(
			'band'    => isset( $_GET['band'] ) ? sanitize_key( wp_unslash( $_GET['band'] ) ) : '',
			'source'  => isset( $_GET['source'] ) ? sanitize_text_field( wp_unslash( $_GET['source'] ) ) : '',
			'service' => isset( $_GET['service'] ) ? sanitize_text_field( wp_unslash( $_GET['service'] ) ) : '',
			'range'   => isset( $_GET['range'] ) ? sanitize_key( wp_unslash( $_GET['range'] ) ) : '',
			'assigned_user' => isset( $_GET['assigned_user'] ) ? sanitize_text_field( wp_unslash( $_GET['assigned_user'] ) ) : '',
		);
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		$menus = array(
			'band'  => array(
				'label'   => __( 'Any score', 'smart-client-contact-hub' ),
				'options' => array(
					'hot'  => __( 'Hot (70+)', 'smart-client-contact-hub' ),
					'warm' => __( 'Warm (40–69)', 'smart-client-contact-hub' ),
					'cold' => __( 'Cold (under 40)', 'smart-client-contact-hub' ),
				),
			),
			'range' => array(
				'label'   => __( 'Any time', 'smart-client-contact-hub' ),
				'options' => array(
					'7'  => __( 'Last 7 days', 'smart-client-contact-hub' ),
					'30' => __( 'Last 30 days', 'smart-client-contact-hub' ),
					'90' => __( 'Last 90 days', 'smart-client-contact-hub' ),
				),
			),
			'source' => array(
				'label'   => __( 'Any source', 'smart-client-contact-hub' ),
				'options' => $this->distinct_options( 'source' ),
			),
			'service' => array(
				'label'   => __( 'Any service', 'smart-client-contact-hub' ),
				'options' => $this->distinct_options( 'service' ),
			),
		);

		echo '<div class="alignleft actions scch-lead-filters">';

		foreach ( $menus as $name => $menu ) {
			if ( ! $menu['options'] ) {
				continue;
			}

			printf( '<label class="screen-reader-text" for="scch-filter-%1$s">%2$s</label>', esc_attr( $name ), esc_html( $menu['label'] ) );
			printf( '<select name="%1$s" id="scch-filter-%1$s">', esc_attr( $name ) );
			printf( '<option value="">%s</option>', esc_html( $menu['label'] ) );

			foreach ( $menu['options'] as $value => $label ) {
				printf(
					'<option value="%1$s"%2$s>%3$s</option>',
					esc_attr( $value ),
					selected( $selected[ $name ], (string) $value, false ),
					esc_html( $label )
				);
			}

			echo '</select>';
		}

		submit_button( __( 'Filter', 'smart-client-contact-hub' ), '', 'filter_action', false );
		echo '</div>';
	}

	/**
	 * Distinct non-empty values of a column, for a filter menu.
	 *
	 * @param string $column Whitelisted column name.
	 * @return array<string,string>
	 */
	private function distinct_options( string $column ): array {
		global $wpdb;

		if ( ! in_array( $column, array( 'source', 'service', 'channel' ), true ) ) {
			return array();
		}

		$table = Lead_Repository::table();
		$rows  = $wpdb->get_col( "SELECT DISTINCT {$column} FROM {$table} WHERE {$column} <> '' ORDER BY {$column} ASC LIMIT 50" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- column is whitelisted above.

		$out = array();
		foreach ( (array) $rows as $value ) {
			$out[ $value ] = 'source' === $column ? Attribution::label( (string) $value ) : (string) $value;
		}

		return $out;
	}

	/**
	 * Checkbox column.
	 *
	 * @param object $item Lead row.
	 */
	public function column_cb( $item ): string {
		return sprintf( '<input type="checkbox" name="lead[]" value="%d" />', (int) $item->id );
	}

	/**
	 * Lead column: name, contact details, and row actions.
	 *
	 * @param object $item Lead row.
	 */
	public function column_name( $item ): string {
		$view = admin_url( 'admin.php?page=scch-leads&lead=' . (int) $item->id );

		$secondary = array_filter( array( $item->email, $item->phone ) );

		return sprintf(
			'<strong><a href="%1$s">%2$s</a></strong><div class="ui-meta">%3$s</div>%4$s',
			esc_url( $view ),
			esc_html( $item->name ),
			esc_html( implode( ' · ', $secondary ) ),
			$this->row_actions(
				array(
					'view' => sprintf( '<a href="%s">%s</a>', esc_url( $view ), esc_html__( 'Open', 'smart-client-contact-hub' ) ),
				)
			)
		);
	}

	/**
	 * Score column.
	 *
	 * @param object $item Lead row.
	 */
	public function column_score( $item ): string {
		return UI::score( (int) $item->score );
	}

	/**
	 * Stage column.
	 *
	 * @param object $item Lead row.
	 */
	public function column_status( $item ): string {
		return UI::stage_badge( (string) $item->status );
	}

	/**
	 * Source column.
	 *
	 * @param object $item Lead row.
	 */
	public function column_source( $item ): string {
		$out = esc_html( Attribution::label( (string) $item->source ) );

		if ( $item->utm_campaign ) {
			$out .= '<div class="ui-meta">' . esc_html( $item->utm_campaign ) . '</div>';
		}

		return $out;
	}

	/**
	 * Next follow-up column.
	 *
	 * @param object $item Lead row.
	 */
	public function column_next_followup_at( $item ): string {
		if ( ! $item->next_followup_at ) {
			return '<span class="ui-meta">—</span>';
		}

		return UI::due_badge( (string) $item->next_followup_at );
	}

	/**
	 * Quick contact actions.
	 *
	 * @param object $item Lead row.
	 */
	public function column_actions( $item ): string {
		return UI::quick_actions( $item );
	}

	/**
	 * Default column renderer.
	 *
	 * @param object $item        Lead row.
	 * @param string $column_name Column key.
	 */
	public function column_default( $item, $column_name ): string {
		if ( 'submission_date' === $column_name ) {
			return sprintf(
				'<span title="%1$s">%2$s</span>',
				esc_attr( mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $item->submission_date ) ),
				esc_html( UI::when( $item->submission_date ) )
			);
		}

		return esc_html( (string) ( $item->{$column_name} ?? '' ) );
	}

	/**
	 * Empty-state text.
	 */
	public function no_items(): void {
		echo UI::empty_state( // phpcs:ignore WordPress.Security.EscapeOutput -- built and escaped in UI.
			'users',
			__( 'No leads here', 'smart-client-contact-hub' ),
			__( 'Once visitors contact you, their leads appear here. Try clearing your filters if you expected to see some.', 'smart-client-contact-hub' ),
			sprintf(
				'<a class="ui-btn" href="%s">%s</a>',
				esc_url( admin_url( 'admin.php?page=scch-leads' ) ),
				esc_html__( 'Clear filters', 'smart-client-contact-hub' )
			)
		);
	}
}
