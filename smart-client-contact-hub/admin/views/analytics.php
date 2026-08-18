<?php
/**
 * Reports: where leads come from and what converts.
 *
 * @package SCCH
 * @var \SCCH\Admin\Admin $admin
 */

use SCCH\Admin\Admin;
use SCCH\Analytics_Service;
use SCCH\Attribution;
use SCCH\Pipeline_Service;
use SCCH\UI;

defined( 'ABSPATH' ) || exit;

$scch_days = isset( $_GET['period'] ) ? Analytics_Service::clamp_days( absint( $_GET['period'] ) ) : 30; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only period selector.
$scch_base = admin_url( 'admin.php?page=scch-analytics' );

$scch_reports = array(
	'source'       => __( 'Source', 'smart-client-contact-hub' ),
	'channel'      => __( 'Channel', 'smart-client-contact-hub' ),
	'service'      => __( 'Service', 'smart-client-contact-hub' ),
	'utm_campaign' => __( 'Campaign', 'smart-client-contact-hub' ),
	'device'       => __( 'Device', 'smart-client-contact-hub' ),
);

$scch_stage_counts = Analytics_Service::stage_counts();
$scch_funnel_total = max( 1, array_sum( $scch_stage_counts ) );
?>
<div class="wrap scch-wrap scch-ui">

	<div class="ui-head">
		<div>
			<h1 class="ui-title"><?php esc_html_e( 'Reports', 'smart-client-contact-hub' ); ?></h1>
			<p class="ui-lead"><?php esc_html_e( 'Every figure is counted from your stored leads. The plugin does not track page views, so no visitor-level numbers are shown.', 'smart-client-contact-hub' ); ?></p>
		</div>
		<div class="ui-head__actions">
			<label class="screen-reader-text" for="scch-period"><?php esc_html_e( 'Reporting period', 'smart-client-contact-hub' ); ?></label>
			<select id="scch-period" onchange="window.location.href=this.value">
				<?php foreach ( array( 7, 30, 90, 365 ) as $scch_option ) : ?>
					<option value="<?php echo esc_url( add_query_arg( 'period', $scch_option, $scch_base ) ); ?>" <?php selected( $scch_days, $scch_option ); ?>>
						<?php
						echo esc_html(
							365 === $scch_option
								? __( 'Last 12 months', 'smart-client-contact-hub' )
								/* translators: %d: number of days. */
								: sprintf( __( 'Last %d days', 'smart-client-contact-hub' ), $scch_option )
						);
						?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>
	</div>

	<?php Admin::maybe_notice(); ?>

	<div class="ui-card" style="margin-bottom:16px">
		<div class="ui-card__head"><h2 class="ui-card-title"><?php esc_html_e( 'Funnel', 'smart-client-contact-hub' ); ?></h2></div>
		<div class="ui-bars">
			<?php
			foreach ( Pipeline_Service::stages() as $scch_key => $scch_stage ) {
				$scch_count = (int) ( $scch_stage_counts[ $scch_key ] ?? 0 );
				echo UI::bar( // phpcs:ignore WordPress.Security.EscapeOutput
					$scch_stage['label'],
					$scch_count,
					$scch_funnel_total,
					sprintf( '%s%%', number_format_i18n( ( $scch_count / $scch_funnel_total ) * 100, 1 ) )
				);
			}
			?>
		</div>
	</div>

	<div class="ui-grid ui-grid--halves">
		<?php foreach ( $scch_reports as $scch_column => $scch_label ) : ?>
			<?php $scch_rows = Analytics_Service::breakdown( $scch_column, $scch_days, 12 ); ?>
			<div class="ui-card ui-card--flush">
				<div class="ui-card__head"><h2 class="ui-card-title"><?php echo esc_html( $scch_label ); ?></h2></div>
				<?php if ( $scch_rows ) : ?>
					<div class="ui-table-wrap">
						<table class="ui-table ui-table--stack">
							<thead>
								<tr>
									<th><?php echo esc_html( $scch_label ); ?></th>
									<th><?php esc_html_e( 'Leads', 'smart-client-contact-hub' ); ?></th>
									<th><?php esc_html_e( 'Won', 'smart-client-contact-hub' ); ?></th>
									<th><?php esc_html_e( 'Rate', 'smart-client-contact-hub' ); ?></th>
									<th><?php esc_html_e( 'Revenue', 'smart-client-contact-hub' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ( $scch_rows as $scch_row ) : ?>
									<tr>
										<td data-label="<?php echo esc_attr( $scch_label ); ?>">
											<?php echo esc_html( 'source' === $scch_column ? Attribution::label( $scch_row['label'] ) : ucfirst( $scch_row['label'] ) ); ?>
										</td>
										<td class="ui-num" data-label="<?php esc_attr_e( 'Leads', 'smart-client-contact-hub' ); ?>"><?php echo esc_html( number_format_i18n( $scch_row['leads'] ) ); ?></td>
										<td class="ui-num" data-label="<?php esc_attr_e( 'Won', 'smart-client-contact-hub' ); ?>"><?php echo esc_html( number_format_i18n( $scch_row['won'] ) ); ?></td>
										<td class="ui-num" data-label="<?php esc_attr_e( 'Rate', 'smart-client-contact-hub' ); ?>"><?php echo esc_html( number_format_i18n( $scch_row['conversion'], 1 ) ); ?>%</td>
										<td class="ui-num" data-label="<?php esc_attr_e( 'Revenue', 'smart-client-contact-hub' ); ?>"><?php echo esc_html( UI::money( $scch_row['revenue'] ) ); ?></td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				<?php else : ?>
					<?php
					echo UI::empty_state( // phpcs:ignore WordPress.Security.EscapeOutput
						'globe',
						__( 'Nothing to report yet', 'smart-client-contact-hub' ),
						__( 'This report fills in as leads arrive during the selected period.', 'smart-client-contact-hub' )
					);
					?>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>
</div>
