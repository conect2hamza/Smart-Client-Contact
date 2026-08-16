<?php
/**
 * Export view: CSV downloads filtered by status.
 *
 * @package SCCH
 * @var \SCCH\Admin\Admin $admin
 */

use SCCH\Admin\Admin;
use SCCH\Lead_Repository;

defined( 'ABSPATH' ) || exit;
?>
<div class="wrap scch-wrap">
	<h1><?php esc_html_e( 'Export', 'smart-client-contact-hub' ); ?></h1>
	<?php Admin::maybe_notice(); ?>

	<p><?php esc_html_e( 'Download leads as a CSV file (UTF-8, Excel-compatible). Cells are protected against spreadsheet formula injection.', 'smart-client-contact-hub' ); ?></p>

	<table class="widefat striped scch-table" style="max-width:560px">
		<tbody>
			<tr>
				<td><strong><?php esc_html_e( 'All leads', 'smart-client-contact-hub' ); ?></strong></td>
				<td><a class="button button-primary" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=scch_export_csv' ), 'scch_export_csv' ) ); ?>"><?php esc_html_e( 'Download CSV', 'smart-client-contact-hub' ); ?></a></td>
			</tr>
			<?php foreach ( Lead_Repository::STATUSES as $scch_status ) : ?>
				<tr>
					<td>
						<?php
						/* translators: %s: lead status. */
						printf( esc_html__( 'Leads with status: %s', 'smart-client-contact-hub' ), esc_html( ucfirst( $scch_status ) ) );
						?>
					</td>
					<td><a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=scch_export_csv&status=' . $scch_status ), 'scch_export_csv' ) ); ?>"><?php esc_html_e( 'Download CSV', 'smart-client-contact-hub' ); ?></a></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
