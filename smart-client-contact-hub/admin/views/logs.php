<?php
/**
 * Email logs view with resend.
 *
 * @package SCCH
 * @var \SCCH\Admin\Admin $admin
 */

use SCCH\Admin\Admin;
use SCCH\Email_Log_Repository;
use SCCH\Settings;

defined( 'ABSPATH' ) || exit;

$scch_paged  = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- pagination only.
$scch_result = Email_Log_Repository::query( 30, $scch_paged );
$scch_pages  = (int) ceil( $scch_result['total'] / 30 );
?>
<div class="wrap scch-wrap">
	<h1><?php esc_html_e( 'Email Logs', 'smart-client-contact-hub' ); ?></h1>
	<?php Admin::maybe_notice(); ?>

	<?php if ( ! Settings::get( 'scch_general', 'log_enabled', 1 ) ) : ?>
		<div class="notice notice-warning inline"><p><?php esc_html_e( 'Email logging is currently disabled, so new emails are not being recorded.', 'smart-client-contact-hub' ); ?></p></div>
	<?php endif; ?>

	<?php if ( ! empty( $scch_result['items'] ) ) : ?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="scch-clear-logs"
			onsubmit="return confirm('<?php echo esc_js( __( 'Delete ALL email logs permanently? This cannot be undone.', 'smart-client-contact-hub' ) ); ?>');">
			<?php wp_nonce_field( 'scch_clear_logs' ); ?>
			<input type="hidden" name="action" value="scch_clear_logs" />
			<?php submit_button( __( 'Clear All Logs', 'smart-client-contact-hub' ), 'delete', 'submit', false ); ?>
		</form>
	<?php endif; ?>

	<?php if ( empty( $scch_result['items'] ) ) : ?>
		<p><?php esc_html_e( 'No emails logged yet.', 'smart-client-contact-hub' ); ?></p>
	<?php else : ?>
		<table class="widefat striped scch-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Recipient', 'smart-client-contact-hub' ); ?></th>
					<th><?php esc_html_e( 'Subject', 'smart-client-contact-hub' ); ?></th>
					<th><?php esc_html_e( 'Type', 'smart-client-contact-hub' ); ?></th>
					<th><?php esc_html_e( 'Status', 'smart-client-contact-hub' ); ?></th>
					<th><?php esc_html_e( 'Date', 'smart-client-contact-hub' ); ?></th>
					<th><?php esc_html_e( 'Error', 'smart-client-contact-hub' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'smart-client-contact-hub' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $scch_result['items'] as $scch_log ) : ?>
					<tr>
						<td><?php echo esc_html( $scch_log->recipient ); ?></td>
						<td><?php echo esc_html( $scch_log->subject ); ?></td>
						<td><?php echo esc_html( ucfirst( $scch_log->type ) ); ?></td>
						<td><span class="scch-badge scch-badge--<?php echo 'sent' === $scch_log->status ? 'qualified' : 'spam'; ?>"><?php echo esc_html( ucfirst( $scch_log->status ) ); ?></span></td>
						<td><?php echo esc_html( mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $scch_log->created_at ) ); ?></td>
						<td><?php echo esc_html( wp_html_excerpt( (string) $scch_log->error, 80, '…' ) ); ?></td>
						<td>
							<button type="button" class="button button-small scch-resend" data-log="<?php echo (int) $scch_log->id; ?>"><?php esc_html_e( 'Resend', 'smart-client-contact-hub' ); ?></button>
							<button type="button" class="button button-small scch-delete-log" data-log="<?php echo (int) $scch_log->id; ?>"><?php esc_html_e( 'Delete', 'smart-client-contact-hub' ); ?></button>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<?php if ( $scch_pages > 1 ) : ?>
			<div class="tablenav"><div class="tablenav-pages">
				<?php
				echo wp_kses_post(
					paginate_links(
						array(
							'base'    => add_query_arg( 'paged', '%#%' ),
							'format'  => '',
							'current' => $scch_paged,
							'total'   => $scch_pages,
						)
					) ?: ''
				);
				?>
			</div></div>
		<?php endif; ?>
	<?php endif; ?>
</div>
