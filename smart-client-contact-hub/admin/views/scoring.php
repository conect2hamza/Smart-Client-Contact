<?php
/**
 * Lead scoring rules.
 *
 * @package SCCH
 * @var \SCCH\Admin\Admin $admin
 */

use SCCH\Admin\Admin;
use SCCH\Lead_Scoring_Service;
use SCCH\UI;

defined( 'ABSPATH' ) || exit;

$scch_rules = Lead_Scoring_Service::config();
$scch_max   = 0;
foreach ( $scch_rules as $scch_rule ) {
	if ( ! empty( $scch_rule['enabled'] ) ) {
		$scch_max += (int) $scch_rule['weight'];
	}
}
?>
<div class="wrap scch-wrap scch-ui">

	<div class="ui-head">
		<div>
			<h1 class="ui-title"><?php esc_html_e( 'Lead Scoring', 'smart-client-contact-hub' ); ?></h1>
			<p class="ui-lead">
				<?php esc_html_e( 'Each rule a lead satisfies adds its weight. The total is capped at 100, so you can weight rules freely without breaking the bands.', 'smart-client-contact-hub' ); ?>
			</p>
		</div>
	</div>

	<?php Admin::maybe_notice(); ?>

	<div class="ui-kpis">
		<?php
		// phpcs:disable WordPress.Security.EscapeOutput -- UI builders escape internally.
		echo UI::kpi( __( 'Cold', 'smart-client-contact-hub' ), '0 – 39' );
		echo UI::kpi( __( 'Warm', 'smart-client-contact-hub' ), '40 – 69' );
		echo UI::kpi( __( 'Hot', 'smart-client-contact-hub' ), '70 – 100' );
		echo UI::kpi( __( 'Your maximum', 'smart-client-contact-hub' ), number_format_i18n( min( 100, $scch_max ) ) . ' / 100' );
		// phpcs:enable WordPress.Security.EscapeOutput
		?>
	</div>

	<?php if ( $scch_max < 70 ) : ?>
		<div class="notice notice-warning inline" style="margin:0 0 16px">
			<p>
				<?php esc_html_e( 'With the current weights no lead can reach 70, so nothing will ever be marked Hot. Raise a weight or enable more rules.', 'smart-client-contact-hub' ); ?>
			</p>
		</div>
	<?php endif; ?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="scch-dirty-watch">
		<?php wp_nonce_field( 'scch_save_scoring' ); ?>
		<input type="hidden" name="action" value="scch_save_scoring" />

		<div class="ui-card ui-card--flush">
			<div class="ui-table-wrap">
				<table class="ui-table ui-table--stack">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Rule', 'smart-client-contact-hub' ); ?></th>
							<th><?php esc_html_e( 'Active', 'smart-client-contact-hub' ); ?></th>
							<th><?php esc_html_e( 'Points', 'smart-client-contact-hub' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $scch_rules as $scch_key => $scch_rule ) : ?>
							<tr>
								<td data-label="<?php esc_attr_e( 'Rule', 'smart-client-contact-hub' ); ?>">
									<strong><?php echo esc_html( $scch_rule['label'] ); ?></strong>
									<div class="ui-meta"><?php echo esc_html( $scch_rule['help'] ); ?></div>
								</td>
								<td data-label="<?php esc_attr_e( 'Active', 'smart-client-contact-hub' ); ?>">
									<input type="hidden" name="rules[<?php echo esc_attr( $scch_key ); ?>][enabled]" value="0" />
									<label>
										<input type="checkbox" name="rules[<?php echo esc_attr( $scch_key ); ?>][enabled]" value="1" <?php checked( (int) $scch_rule['enabled'], 1 ); ?> />
										<span class="screen-reader-text"><?php echo esc_html( $scch_rule['label'] ); ?></span>
									</label>
								</td>
								<td data-label="<?php esc_attr_e( 'Points', 'smart-client-contact-hub' ); ?>">
									<label class="screen-reader-text" for="scch-w-<?php echo esc_attr( $scch_key ); ?>">
										<?php
										printf(
											/* translators: %s: rule label. */
											esc_html__( 'Points for: %s', 'smart-client-contact-hub' ),
											esc_html( $scch_rule['label'] )
										);
										?>
									</label>
									<input type="number" min="0" max="100" class="small-text" id="scch-w-<?php echo esc_attr( $scch_key ); ?>"
										name="rules[<?php echo esc_attr( $scch_key ); ?>][weight]" value="<?php echo esc_attr( (string) $scch_rule['weight'] ); ?>" />
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>

		<div class="scch-savebar is-clean">
			<span class="scch-savebar__note"><?php esc_html_e( 'All changes saved', 'smart-client-contact-hub' ); ?></span>
			<button type="submit" class="ui-btn ui-btn--primary"><?php esc_html_e( 'Save scoring rules', 'smart-client-contact-hub' ); ?></button>
		</div>
	</form>

	<p class="ui-meta" style="margin-top:14px">
		<?php esc_html_e( 'Changing the rules affects new leads immediately. Existing leads keep their score until you recalculate one from its page.', 'smart-client-contact-hub' ); ?>
	</p>
</div>
