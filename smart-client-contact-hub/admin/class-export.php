<?php
/**
 * CSV export of leads.
 *
 * @package SCCH
 */

namespace SCCH\Admin;

use SCCH\Lead_Repository;

defined( 'ABSPATH' ) || exit;

/**
 * Streams a CSV of leads through admin-post. Capability-checked and
 * nonce-verified; output is escaped against CSV formula injection.
 */
class Export {

	/**
	 * Rows fetched per query while streaming. Matches the ceiling enforced by
	 * Lead_Repository::query().
	 */
	private const BATCH = 200;

	/**
	 * Register hooks.
	 */
	public function register(): void {
		add_action( 'admin_post_scch_export_csv', array( $this, 'export' ) );
	}

	/**
	 * Build and stream the CSV file.
	 */
	public function export(): void {
		if ( ! current_user_can( Admin::CAP ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'smart-client-contact-hub' ) );
		}
		check_admin_referer( 'scch_export_csv' );

		$status = isset( $_GET['status'] ) ? sanitize_key( wp_unslash( $_GET['status'] ) ) : '';

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=leads-' . gmdate( 'Y-m-d-His' ) . '.csv' );

		$out = fopen( 'php://output', 'w' );

		// UTF-8 BOM so Excel opens the file correctly.
		fwrite( $out, "\xEF\xBB\xBF" ); // phpcs:ignore WordPress.WP.AlternativeFunctions

		fputcsv(
			$out,
			array(
				__( 'ID', 'smart-client-contact-hub' ),
				__( 'Name', 'smart-client-contact-hub' ),
				__( 'Phone', 'smart-client-contact-hub' ),
				__( 'Email', 'smart-client-contact-hub' ),
				__( 'Service', 'smart-client-contact-hub' ),
				__( 'Message', 'smart-client-contact-hub' ),
				__( 'Status', 'smart-client-contact-hub' ),
				__( 'Submission Date', 'smart-client-contact-hub' ),
				__( 'IP Address', 'smart-client-contact-hub' ),
				__( 'User Agent', 'smart-client-contact-hub' ),
			)
		);

		// Streamed in pages rather than loaded at once: a single query for a
		// large leads table would exhaust memory_limit before writing a byte.
		$paged = 1;

		do {
			$batch = Lead_Repository::query(
				array(
					'status'   => $status,
					'per_page' => self::BATCH,
					'paged'    => $paged,
					'orderby'  => 'submission_date',
					'order'    => 'DESC',
				)
			);

			foreach ( $batch['items'] as $lead ) {
				fputcsv(
					$out,
					array(
						(int) $lead->id,
						$this->cell( $lead->name ),
						$this->cell( $lead->phone ),
						$this->cell( $lead->email ),
						$this->cell( $lead->service ),
						$this->cell( $lead->message ),
						$this->cell( $lead->status ),
						$this->cell( $lead->submission_date ),
						$this->cell( $lead->ip_address ),
						$this->cell( $lead->user_agent ),
					)
				);
			}

			flush();
			++$paged;
		} while ( count( $batch['items'] ) === self::BATCH );

		fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		exit;
	}

	/**
	 * Neutralize CSV formula injection (=, +, -, @, tab, CR prefixes).
	 *
	 * @param string|null $value Raw cell value.
	 */
	private function cell( ?string $value ): string {
		$value = (string) $value;
		if ( '' !== $value && in_array( $value[0], array( '=', '+', '-', '@', "\t", "\r" ), true ) ) {
			$value = "'" . $value;
		}
		return $value;
	}
}
