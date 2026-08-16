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
<div class="wrap scch-wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Leads', 'smart-client-contact-hub' ); ?></h1>
	<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=scch_export_csv' ), 'scch_export_csv' ) ); ?>" class="page-title-action"><?php esc_html_e( 'Export CSV', 'smart-client-contact-hub' ); ?></a>
	<hr class="wp-header-end" />
	<?php Admin::maybe_notice(); ?>

	<form method="get">
		<input type="hidden" name="page" value="scch-leads" />
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
