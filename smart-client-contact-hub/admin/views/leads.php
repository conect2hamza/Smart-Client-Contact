<?php
/**
 * Leads list view.
 *
 * @package SCCH
 * @var \SCCH\Admin\Admin            $admin
 * @var \SCCH\Admin\Leads_List_Table $table
 */

use SCCH\Admin\Admin;

defined( 'ABSPATH' ) || exit;
?>
<div class="wrap scch-wrap scch-ui">
	<div class="ui-head">
		<div>
			<h1 class="ui-title"><?php esc_html_e( 'Leads', 'smart-client-contact-hub' ); ?></h1>
			<p class="ui-lead"><?php esc_html_e( 'Everyone who has contacted you. Filter by score, source or stage, then open a lead to work it.', 'smart-client-contact-hub' ); ?></p>
		</div>
		<div class="ui-head__actions">
			<a class="ui-btn" href="<?php echo esc_url( admin_url( 'admin.php?page=scch-pipeline' ) ); ?>"><?php esc_html_e( 'Pipeline view', 'smart-client-contact-hub' ); ?></a>
			<a class="ui-btn" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=scch_export_csv' ), 'scch_export_csv' ) ); ?>"><?php esc_html_e( 'Export CSV', 'smart-client-contact-hub' ); ?></a>
		</div>
	</div>
	<?php Admin::maybe_notice(); ?>

	<form method="get">
		<input type="hidden" name="page" value="scch-leads" />
		<?php
		// Carry active filters through search, sorting and pagination.
		foreach ( array( 'status', 'band', 'source', 'service', 'range', 'assigned_user' ) as $scch_filter ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only filters.
			$scch_value = isset( $_GET[ $scch_filter ] ) ? sanitize_text_field( wp_unslash( $_GET[ $scch_filter ] ) ) : '';
			if ( '' !== $scch_value && ! in_array( $scch_filter, array( 'band', 'source', 'service', 'range' ), true ) ) {
				printf( '<input type="hidden" name="%s" value="%s" />', esc_attr( $scch_filter ), esc_attr( $scch_value ) );
			}
		}
		?>
		<?php
		// No bulk nonce is printed here: WP_List_Table::display() emits
		// wp_nonce_field( 'bulk-leads' ) from display_tablenav( 'top' ), which
		// is the field process_bulk_action() verifies. Adding a second one
		// only duplicates the _wpnonce input.
		$table->views();
		$table->search_box( __( 'Search leads', 'smart-client-contact-hub' ), 'scch-lead-search' );
		$table->display();
		?>
	</form>
</div>
