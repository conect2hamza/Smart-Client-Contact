<?php
/**
 * Follow-ups: everything owed to a lead, grouped by urgency.
 *
 * @package SCCH
 * @var \SCCH\Admin\Admin $admin
 */

use SCCH\Admin\Admin;
use SCCH\Followup_Service;
use SCCH\UI;

defined( 'ABSPATH' ) || exit;

$scch_leads_url = admin_url( 'admin.php?page=scch-leads' );

$scch_groups = array(
	'overdue'  => array(
		'label' => __( 'Overdue', 'smart-client-contact-hub' ),
		'tasks' => Followup_Service::query( array( 'scope' => 'overdue', 'limit' => 100 ) ),
	),
	'due_today' => array(
		'label' => __( 'Due today', 'smart-client-contact-hub' ),
		'tasks' => Followup_Service::query( array( 'scope' => 'due_today', 'limit' => 100 ) ),
	),
	'upcoming' => array(
		'label' => __( 'Upcoming', 'smart-client-contact-hub' ),
		'tasks' => Followup_Service::query( array( 'scope' => 'upcoming', 'limit' => 100 ) ),
	),
);

$scch_total = array_sum( array_map( static fn( $g ) => count( $g['tasks'] ), $scch_groups ) );
?>
<div class="wrap scch-wrap scch-ui">

	<div class="ui-head">
		<div>
			<h1 class="ui-title"><?php esc_html_e( 'Follow-ups', 'smart-client-contact-hub' ); ?></h1>
			<p class="ui-lead"><?php esc_html_e( 'Open tasks against your leads. Schedule a follow-up from any lead\'s page.', 'smart-client-contact-hub' ); ?></p>
		</div>
	</div>

	<?php Admin::maybe_notice(); ?>

	<?php if ( 0 === $scch_total ) : ?>
		<div class="ui-card">
			<?php
			echo UI::empty_state( // phpcs:ignore WordPress.Security.EscapeOutput
				'check',
				__( "You're all caught up", 'smart-client-contact-hub' ),
				__( 'No follow-ups are open. Open a lead and schedule one to keep the conversation moving.', 'smart-client-contact-hub' ),
				sprintf( '<a class="ui-btn ui-btn--primary" href="%s">%s</a>', esc_url( $scch_leads_url ), esc_html__( 'Go to leads', 'smart-client-contact-hub' ) )
			);
			?>
		</div>
	<?php else : ?>
		<div class="ui-stack">
			<?php foreach ( $scch_groups as $scch_key => $scch_group ) : ?>
				<?php if ( ! $scch_group['tasks'] ) { continue; } ?>
				<div class="ui-card ui-card--flush">
					<div class="ui-card__head">
						<h2 class="ui-card-title"><?php echo esc_html( $scch_group['label'] ); ?></h2>
						<span class="ui-meta ui-num"><?php echo esc_html( number_format_i18n( count( $scch_group['tasks'] ) ) ); ?></span>
					</div>
					<div class="ui-table-wrap">
						<table class="ui-table ui-table--stack">
							<thead>
								<tr>
									<th><?php esc_html_e( 'Task', 'smart-client-contact-hub' ); ?></th>
									<th><?php esc_html_e( 'Lead', 'smart-client-contact-hub' ); ?></th>
									<th><?php esc_html_e( 'Due', 'smart-client-contact-hub' ); ?></th>
									<th><?php esc_html_e( 'Priority', 'smart-client-contact-hub' ); ?></th>
									<th><?php esc_html_e( 'Assigned', 'smart-client-contact-hub' ); ?></th>
									<th><?php esc_html_e( 'Actions', 'smart-client-contact-hub' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php
								foreach ( $scch_group['tasks'] as $scch_task ) :
									$scch_owner = $scch_task->assigned_user ? get_userdata( (int) $scch_task->assigned_user ) : null;
									?>
									<tr data-followup-row="<?php echo (int) $scch_task->id; ?>">
										<td data-label="<?php esc_attr_e( 'Task', 'smart-client-contact-hub' ); ?>">
											<strong><?php echo esc_html( $scch_task->title ); ?></strong>
											<?php if ( $scch_task->notes ) : ?>
												<div class="ui-meta"><?php echo esc_html( wp_html_excerpt( (string) $scch_task->notes, 90, '…' ) ); ?></div>
											<?php endif; ?>
										</td>
										<td data-label="<?php esc_attr_e( 'Lead', 'smart-client-contact-hub' ); ?>">
											<?php if ( $scch_task->lead_name ) : ?>
												<a href="<?php echo esc_url( add_query_arg( 'lead', (int) $scch_task->lead_id, $scch_leads_url ) ); ?>"><?php echo esc_html( $scch_task->lead_name ); ?></a>
											<?php else : ?>
												<span class="ui-meta"><?php esc_html_e( '(lead deleted)', 'smart-client-contact-hub' ); ?></span>
											<?php endif; ?>
										</td>
										<td data-label="<?php esc_attr_e( 'Due', 'smart-client-contact-hub' ); ?>">
											<?php echo UI::due_badge( (string) $scch_task->due_at, $scch_task->completed_at ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
											<div class="ui-meta"><?php echo esc_html( mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $scch_task->due_at ) ); ?></div>
										</td>
										<td data-label="<?php esc_attr_e( 'Priority', 'smart-client-contact-hub' ); ?>">
											<?php echo esc_html( Followup_Service::priorities()[ $scch_task->priority ] ?? $scch_task->priority ); ?>
										</td>
										<td data-label="<?php esc_attr_e( 'Assigned', 'smart-client-contact-hub' ); ?>">
											<?php echo esc_html( $scch_owner ? $scch_owner->display_name : __( 'Unassigned', 'smart-client-contact-hub' ) ); ?>
										</td>
										<td data-label="<?php esc_attr_e( 'Actions', 'smart-client-contact-hub' ); ?>">
											<button type="button" class="ui-btn scch-followup-done" data-followup="<?php echo (int) $scch_task->id; ?>"><?php esc_html_e( 'Complete', 'smart-client-contact-hub' ); ?></button>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>
