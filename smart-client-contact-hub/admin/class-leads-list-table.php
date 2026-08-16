<?php
/**
 * Leads list table.
 *
 * @package SCCH
 */

namespace SCCH\Admin;

use SCCH\Lead_Repository;

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
			'cb'              => '<input type="checkbox" />',
			'id'              => __( 'ID', 'smart-client-contact-hub' ),
			'name'            => __( 'Customer Name', 'smart-client-contact-hub' ),
			'phone'           => __( 'Phone', 'smart-client-contact-hub' ),
			'email'           => __( 'Email', 'smart-client-contact-hub' ),
			'service'         => __( 'Service', 'smart-client-contact-hub' ),
			'message'         => __( 'Message', 'smart-client-contact-hub' ),
			'status'          => __( 'Status', 'smart-client-contact-hub' ),
			'submission_date' => __( 'Date', 'smart-client-contact-hub' ),
		);
	}

	/**
	 * Sortable columns.
	 *
	 * @return array<string,array{0:string,1:bool}>
	 */
	protected function get_sortable_columns(): array {
		return array(
			'id'              => array( 'id', false ),
			'name'            => array( 'name', false ),
			'email'           => array( 'email', false ),
			'service'         => array( 'service', false ),
			'status'          => array( 'status', false ),
			'submission_date' => array( 'submission_date', true ),
		);
	}

	/**
	 * Bulk actions.
	 *
	 * @return array<string,string>
	 */
	protected function get_bulk_actions(): array {
		$actions = array( 'delete' => __( 'Delete', 'smart-client-contact-hub' ) );
		foreach ( Lead_Repository::STATUSES as $status ) {
			/* translators: %s: lead status. */
			$actions[ 'status_' . $status ] = sprintf( __( 'Mark as %s', 'smart-client-contact-hub' ), ucfirst( $status ) );
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

		foreach ( Lead_Repository::STATUSES as $status ) {
			$views[ $status ] = sprintf(
				'<a href="%s"%s>%s</a>',
				esc_url( add_query_arg( 'status', $status, $base ) ),
				$current === $status ? ' class="current"' : '',
				esc_html( ucfirst( $status ) )
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
			'search'   => isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : '',
			'status'   => isset( $_GET['status'] ) ? sanitize_key( wp_unslash( $_GET['status'] ) ) : '',
			'orderby'  => isset( $_GET['orderby'] ) ? sanitize_key( wp_unslash( $_GET['orderby'] ) ) : 'submission_date',
			'order'    => isset( $_GET['order'] ) ? sanitize_key( wp_unslash( $_GET['order'] ) ) : 'desc',
			'per_page' => $per_page,
			'paged'    => $this->get_pagenum(),
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
			Lead_Repository::delete( $ids );
			return;
		}

		if ( str_starts_with( $action, 'status_' ) ) {
			Lead_Repository::update_status( $ids, substr( $action, 7 ) );
		}
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
	 * Name column with row actions.
	 *
	 * @param object $item Lead row.
	 */
	public function column_name( $item ): string {
		$view = admin_url( 'admin.php?page=scch-leads&lead=' . (int) $item->id );

		$actions = array(
			'view' => sprintf( '<a href="%s">%s</a>', esc_url( $view ), esc_html__( 'View', 'smart-client-contact-hub' ) ),
		);

		return sprintf(
			'<strong><a href="%s">%s</a></strong>%s',
			esc_url( $view ),
			esc_html( $item->name ),
			$this->row_actions( $actions )
		);
	}

	/**
	 * Status column with a colored badge.
	 *
	 * @param object $item Lead row.
	 */
	public function column_status( $item ): string {
		return sprintf( '<span class="scch-badge scch-badge--%1$s">%2$s</span>', esc_attr( $item->status ), esc_html( ucfirst( $item->status ) ) );
	}

	/**
	 * Message column, truncated.
	 *
	 * @param object $item Lead row.
	 */
	public function column_message( $item ): string {
		return esc_html( wp_html_excerpt( (string) $item->message, 60, '…' ) );
	}

	/**
	 * Email column as a mailto link.
	 *
	 * @param object $item Lead row.
	 */
	public function column_email( $item ): string {
		return sprintf( '<a href="mailto:%1$s">%1$s</a>', esc_attr( $item->email ) );
	}

	/**
	 * Default column renderer.
	 *
	 * @param object $item        Lead row.
	 * @param string $column_name Column key.
	 */
	public function column_default( $item, $column_name ): string {
		switch ( $column_name ) {
			case 'submission_date':
				return esc_html( mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $item->submission_date ) );
			default:
				return esc_html( (string) ( $item->{$column_name} ?? '' ) );
		}
	}

	/**
	 * Empty-state text.
	 */
	public function no_items(): void {
		esc_html_e( 'No leads yet. New submissions from the floating widget will appear here.', 'smart-client-contact-hub' );
	}
}
