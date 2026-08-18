<?php
/**
 * Lead workspace: who they are, where they are, and what to do next.
 *
 * @package SCCH
 * @var \SCCH\Admin\Admin $admin
 * @var object|null       $lead
 */

use SCCH\Activity_Service;
use SCCH\Admin\Admin;
use SCCH\Attribution;
use SCCH\Followup_Service;
use SCCH\Form_Fields;
use SCCH\Icons;
use SCCH\Lead_Scoring_Service;
use SCCH\Pipeline_Service;
use SCCH\UI;

defined( 'ABSPATH' ) || exit;

$scch_leads_url = admin_url( 'admin.php?page=scch-leads' );
?>
<div class="wrap scch-wrap scch-ui">

	<?php if ( ! $lead ) : ?>
		<div class="ui-head">
			<h1 class="ui-title"><?php esc_html_e( 'Lead', 'smart-client-contact-hub' ); ?></h1>
		</div>
		<?php Admin::maybe_notice(); ?>
		<div class="ui-card">
			<?php
			echo UI::empty_state( // phpcs:ignore WordPress.Security.EscapeOutput
				'question',
				__( 'Lead not found', 'smart-client-contact-hub' ),
				__( 'It may have been deleted.', 'smart-client-contact-hub' ),
				sprintf( '<a class="ui-btn ui-btn--primary" href="%s">%s</a>', esc_url( $scch_leads_url ), esc_html__( 'Back to leads', 'smart-client-contact-hub' ) )
			);
			?>
		</div>
		<?php
		return;
	endif;

	$scch_id       = (int) $lead->id;
	$scch_stages   = Pipeline_Service::stages();
	$scch_timeline = Activity_Service::for_lead( $scch_id );
	$scch_tasks    = Followup_Service::for_lead( $scch_id );
	$scch_types    = Activity_Service::types();
	$scch_owner    = $lead->assigned_user ? get_userdata( (int) $lead->assigned_user ) : null;
	$scch_users    = get_users( array( 'capability' => Admin::CAP, 'fields' => array( 'ID', 'display_name' ), 'number' => 100 ) );

	/*
	 * Answers to custom fields. The stored keys are the source of truth, so a
	 * field deleted from the form still shows the answers it collected; the
	 * current definition is used for the label when there is one.
	 */
	$scch_answers = Form_Fields::decode( $lead->extra_fields ?? null );
	$scch_defs    = Form_Fields::custom();
	?>

	<div class="ui-head">
		<div>
			<a class="ui-meta" href="<?php echo esc_url( $scch_leads_url ); ?>">&larr; <?php esc_html_e( 'All leads', 'smart-client-contact-hub' ); ?></a>
			<h1 class="ui-title" style="margin-top:4px"><?php echo esc_html( $lead->name ); ?></h1>
			<div class="ui-row" style="margin-top:6px">
				<?php echo UI::stage_badge( (string) $lead->status ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<?php echo UI::score( (int) $lead->score ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<span class="ui-meta"><?php echo esc_html( UI::when( $lead->submission_date ) ); ?></span>
			</div>
		</div>

		<div class="ui-head__actions">
			<?php echo UI::quick_actions( $lead ); // phpcs:ignore WordPress.Security.EscapeOutput ?>

			<label class="screen-reader-text" for="scch-lead-stage"><?php esc_html_e( 'Stage', 'smart-client-contact-hub' ); ?></label>
			<select id="scch-lead-stage" class="scch-stage-select" data-lead="<?php echo (int) $scch_id; ?>">
				<?php foreach ( $scch_stages as $scch_key => $scch_stage ) : ?>
					<option value="<?php echo esc_attr( $scch_key ); ?>" <?php selected( $lead->status, $scch_key ); ?>><?php echo esc_html( $scch_stage['label'] ); ?></option>
				<?php endforeach; ?>
			</select>

			<label class="screen-reader-text" for="scch-lead-owner"><?php esc_html_e( 'Assigned to', 'smart-client-contact-hub' ); ?></label>
			<select id="scch-lead-owner" class="scch-assign-select" data-lead="<?php echo (int) $scch_id; ?>">
				<option value="0"><?php esc_html_e( 'Unassigned', 'smart-client-contact-hub' ); ?></option>
				<?php foreach ( $scch_users as $scch_user ) : ?>
					<option value="<?php echo (int) $scch_user->ID; ?>" <?php selected( (int) $lead->assigned_user, (int) $scch_user->ID ); ?>><?php echo esc_html( $scch_user->display_name ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
	</div>

	<?php Admin::maybe_notice(); ?>

	<div class="ui-card" style="margin-bottom:16px">
		<div class="ui-card__head"><h2 class="ui-card-title"><?php esc_html_e( 'Pipeline', 'smart-client-contact-hub' ); ?></h2></div>
		<?php echo UI::rail( (string) $lead->status ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	</div>

	<div class="ui-split">

		<div class="ui-stack">

			<div class="ui-card">
				<div class="ui-card__head">
					<h2 class="ui-card-title"><?php esc_html_e( 'Add a note', 'smart-client-contact-hub' ); ?></h2>
				</div>
				<form class="scch-note-form" data-lead="<?php echo (int) $scch_id; ?>">
					<label class="screen-reader-text" for="scch-note"><?php esc_html_e( 'Note', 'smart-client-contact-hub' ); ?></label>
					<textarea id="scch-note" name="note" rows="3" class="large-text" placeholder="<?php esc_attr_e( 'What happened? Kept here for you and your team.', 'smart-client-contact-hub' ); ?>"></textarea>
					<p style="margin:8px 0 0"><button type="submit" class="ui-btn ui-btn--primary"><?php esc_html_e( 'Save note', 'smart-client-contact-hub' ); ?></button></p>
				</form>
			</div>

			<div class="ui-card">
				<div class="ui-card__head">
					<h2 class="ui-card-title"><?php esc_html_e( 'Activity', 'smart-client-contact-hub' ); ?></h2>
					<span class="ui-meta ui-num"><?php echo esc_html( number_format_i18n( count( $scch_timeline ) ) ); ?></span>
				</div>

				<?php if ( $scch_timeline ) : ?>
					<ul class="ui-timeline">
						<?php
						foreach ( $scch_timeline as $scch_event ) :
							$scch_type = $scch_types[ $scch_event->type ] ?? $scch_types['note'];
							$scch_by   = $scch_event->user_id ? get_userdata( (int) $scch_event->user_id ) : null;
							?>
							<li>
								<span class="ui-timeline__dot" style="--dot:<?php echo esc_attr( $scch_type['color'] ); ?>"><?php echo Icons::svg( $scch_type['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
								<p class="ui-timeline__summary"><?php echo esc_html( $scch_event->summary ); ?></p>
								<?php if ( $scch_event->body ) : ?>
									<p class="ui-timeline__body"><?php echo esc_html( $scch_event->body ); ?></p>
								<?php endif; ?>
								<p class="ui-timeline__when">
									<?php
									echo esc_html( mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $scch_event->created_at ) );
									if ( $scch_by ) {
										echo ' · ' . esc_html( $scch_by->display_name );
									}
									?>
								</p>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php else : ?>
					<?php
					echo UI::empty_state( // phpcs:ignore WordPress.Security.EscapeOutput
						'clock',
						__( 'No activity yet', 'smart-client-contact-hub' ),
						__( 'Notes, stage changes and follow-ups appear here as you work the lead.', 'smart-client-contact-hub' )
					);
					?>
				<?php endif; ?>
			</div>

			<div class="ui-card">
				<div class="ui-card__head"><h2 class="ui-card-title"><?php esc_html_e( 'Their message', 'smart-client-contact-hub' ); ?></h2></div>
				<?php if ( trim( (string) $lead->message ) ) : ?>
					<div style="font-size:13px;line-height:1.6"><?php echo wp_kses_post( wpautop( esc_html( (string) $lead->message ) ) ); ?></div>
				<?php else : ?>
					<p class="ui-meta"><?php esc_html_e( 'They did not leave a message.', 'smart-client-contact-hub' ); ?></p>
				<?php endif; ?>
			</div>

			<?php if ( $scch_answers ) : ?>
				<div class="ui-card">
					<div class="ui-card__head"><h2 class="ui-card-title"><?php esc_html_e( 'Their answers', 'smart-client-contact-hub' ); ?></h2></div>
					<dl class="ui-dl">
						<?php
						foreach ( $scch_answers as $scch_key => $scch_value ) :
							$scch_def = $scch_defs[ $scch_key ] ?? array( 'type' => 'text', 'options' => '', 'label' => '' );
							?>
							<dt><?php echo esc_html( '' !== (string) ( $scch_def['label'] ?? '' ) ? $scch_def['label'] : $scch_key ); ?></dt>
							<dd><?php echo esc_html( Form_Fields::display( $scch_def, $scch_value ) ?: '—' ); ?></dd>
						<?php endforeach; ?>
					</dl>
					<?php if ( array_diff_key( $scch_answers, $scch_defs ) ) : ?>
						<p class="ui-meta"><?php esc_html_e( 'Some of these fields are no longer on the form. Their answers are kept here so nothing is lost.', 'smart-client-contact-hub' ); ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>

		<div class="ui-stack">

			<div class="ui-card">
				<div class="ui-card__head">
					<h2 class="ui-card-title"><?php esc_html_e( 'Follow-ups', 'smart-client-contact-hub' ); ?></h2>
				</div>

				<form class="scch-followup-form" data-lead="<?php echo (int) $scch_id; ?>" style="margin-bottom:14px">
					<p style="margin:0 0 8px">
						<label for="scch-fu-title" style="display:block;font-weight:600;font-size:12px;margin-bottom:3px"><?php esc_html_e( 'Task', 'smart-client-contact-hub' ); ?></label>
						<input type="text" id="scch-fu-title" name="title" class="large-text" placeholder="<?php esc_attr_e( 'Call to discuss the quote', 'smart-client-contact-hub' ); ?>" required />
					</p>
					<p style="margin:0 0 8px">
						<label for="scch-fu-due" style="display:block;font-weight:600;font-size:12px;margin-bottom:3px"><?php esc_html_e( 'Due', 'smart-client-contact-hub' ); ?></label>
						<input type="datetime-local" id="scch-fu-due" name="due_at" required />
					</p>
					<p style="margin:0 0 8px">
						<label for="scch-fu-priority" style="display:block;font-weight:600;font-size:12px;margin-bottom:3px"><?php esc_html_e( 'Priority', 'smart-client-contact-hub' ); ?></label>
						<select id="scch-fu-priority" name="priority">
							<?php foreach ( Followup_Service::priorities() as $scch_pk => $scch_pl ) : ?>
								<option value="<?php echo esc_attr( $scch_pk ); ?>" <?php selected( 'normal', $scch_pk ); ?>><?php echo esc_html( $scch_pl ); ?></option>
							<?php endforeach; ?>
						</select>
					</p>
					<p style="margin:0"><button type="submit" class="ui-btn"><?php esc_html_e( 'Schedule', 'smart-client-contact-hub' ); ?></button></p>
				</form>

				<?php if ( $scch_tasks ) : ?>
					<ul style="list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:10px">
						<?php foreach ( $scch_tasks as $scch_task ) : ?>
							<li data-followup-row="<?php echo (int) $scch_task->id; ?>" style="display:flex;gap:8px;align-items:flex-start;justify-content:space-between">
								<div style="min-width:0">
									<div style="font-size:13px;font-weight:500"><?php echo esc_html( $scch_task->title ); ?></div>
									<div class="ui-meta"><?php echo esc_html( mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $scch_task->due_at ) ); ?></div>
								</div>
								<div class="ui-row" style="flex-wrap:nowrap">
									<?php echo UI::due_badge( (string) $scch_task->due_at, $scch_task->completed_at ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
									<?php if ( ! $scch_task->completed_at ) : ?>
										<button type="button" class="ui-btn scch-followup-done" data-followup="<?php echo (int) $scch_task->id; ?>"><?php esc_html_e( 'Done', 'smart-client-contact-hub' ); ?></button>
									<?php endif; ?>
								</div>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php else : ?>
					<p class="ui-meta"><?php esc_html_e( 'Nothing scheduled.', 'smart-client-contact-hub' ); ?></p>
				<?php endif; ?>
			</div>

			<div class="ui-card">
				<div class="ui-card__head"><h2 class="ui-card-title"><?php esc_html_e( 'Value', 'smart-client-contact-hub' ); ?></h2></div>
				<form class="scch-value-form" data-lead="<?php echo (int) $scch_id; ?>">
					<p style="margin:0 0 8px">
						<label for="scch-est" style="display:block;font-weight:600;font-size:12px;margin-bottom:3px"><?php esc_html_e( 'Estimated value', 'smart-client-contact-hub' ); ?></label>
						<input type="number" step="0.01" min="0" id="scch-est" name="estimated_value" value="<?php echo esc_attr( (float) $lead->estimated_value ?: '' ); ?>" class="regular-text" />
					</p>
					<p style="margin:0 0 8px">
						<label for="scch-rev" style="display:block;font-weight:600;font-size:12px;margin-bottom:3px"><?php esc_html_e( 'Actual revenue', 'smart-client-contact-hub' ); ?></label>
						<input type="number" step="0.01" min="0" id="scch-rev" name="actual_revenue" value="<?php echo esc_attr( (float) $lead->actual_revenue ?: '' ); ?>" class="regular-text" />
					</p>
					<p style="margin:0"><button type="submit" class="ui-btn"><?php esc_html_e( 'Save value', 'smart-client-contact-hub' ); ?></button></p>
					<p class="ui-meta" style="margin-top:6px"><?php esc_html_e( 'Optional. Leave blank if you do not track deal values.', 'smart-client-contact-hub' ); ?></p>
				</form>
			</div>

			<div class="ui-card">
				<div class="ui-card__head"><h2 class="ui-card-title"><?php esc_html_e( 'Lead details', 'smart-client-contact-hub' ); ?></h2></div>
				<dl class="ui-dl">
					<dt><?php esc_html_e( 'Email', 'smart-client-contact-hub' ); ?></dt>
					<dd><a href="mailto:<?php echo esc_attr( $lead->email ); ?>"><?php echo esc_html( $lead->email ); ?></a></dd>

					<dt><?php esc_html_e( 'Phone', 'smart-client-contact-hub' ); ?></dt>
					<dd>
						<?php if ( $lead->phone ) : ?>
							<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', (string) $lead->phone ) ); ?>"><?php echo esc_html( $lead->phone ); ?></a>
						<?php else : ?>—<?php endif; ?>
					</dd>

					<dt><?php esc_html_e( 'Service', 'smart-client-contact-hub' ); ?></dt>
					<dd><?php echo esc_html( $lead->service ?: '—' ); ?></dd>

					<dt><?php esc_html_e( 'Assigned to', 'smart-client-contact-hub' ); ?></dt>
					<dd><?php echo esc_html( $scch_owner ? $scch_owner->display_name : __( 'Unassigned', 'smart-client-contact-hub' ) ); ?></dd>

					<dt><?php esc_html_e( 'Source', 'smart-client-contact-hub' ); ?></dt>
					<dd><?php echo esc_html( Attribution::label( (string) $lead->source ) ); ?></dd>

					<dt><?php esc_html_e( 'Channel', 'smart-client-contact-hub' ); ?></dt>
					<dd><?php echo esc_html( ucfirst( (string) $lead->channel ) ); ?></dd>

					<?php if ( $lead->utm_campaign ) : ?>
						<dt><?php esc_html_e( 'Campaign', 'smart-client-contact-hub' ); ?></dt>
						<dd><?php echo esc_html( $lead->utm_campaign ); ?></dd>
					<?php endif; ?>

					<?php if ( $lead->utm_medium ) : ?>
						<dt><?php esc_html_e( 'Medium', 'smart-client-contact-hub' ); ?></dt>
						<dd><?php echo esc_html( $lead->utm_medium ); ?></dd>
					<?php endif; ?>

					<?php if ( $lead->landing_page ) : ?>
						<dt><?php esc_html_e( 'Landing page', 'smart-client-contact-hub' ); ?></dt>
						<dd><a href="<?php echo esc_url( $lead->landing_page ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( wp_html_excerpt( (string) $lead->landing_page, 48, '…' ) ); ?></a></dd>
					<?php endif; ?>

					<?php if ( $lead->referrer ) : ?>
						<dt><?php esc_html_e( 'Referrer', 'smart-client-contact-hub' ); ?></dt>
						<dd><?php echo esc_html( wp_html_excerpt( (string) $lead->referrer, 48, '…' ) ); ?></dd>
					<?php endif; ?>

					<?php if ( $lead->device ) : ?>
						<dt><?php esc_html_e( 'Device', 'smart-client-contact-hub' ); ?></dt>
						<dd><?php echo esc_html( ucfirst( (string) $lead->device ) ); ?></dd>
					<?php endif; ?>

					<dt><?php esc_html_e( 'Received', 'smart-client-contact-hub' ); ?></dt>
					<dd><?php echo esc_html( mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $lead->submission_date ) ); ?></dd>

					<dt><?php esc_html_e( 'IP address', 'smart-client-contact-hub' ); ?></dt>
					<dd><?php echo esc_html( $lead->ip_address ?: '—' ); ?></dd>
				</dl>

				<p style="margin:14px 0 0">
					<button type="button" class="ui-btn scch-rescore" data-lead="<?php echo (int) $scch_id; ?>"><?php esc_html_e( 'Recalculate score', 'smart-client-contact-hub' ); ?></button>
				</p>
			</div>

			<div class="ui-card">
				<div class="ui-card__head"><h2 class="ui-card-title"><?php esc_html_e( 'Danger zone', 'smart-client-contact-hub' ); ?></h2></div>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
					onsubmit="return confirm('<?php echo esc_js( __( 'Delete this lead permanently, with its notes and follow-ups?', 'smart-client-contact-hub' ) ); ?>');">
					<?php wp_nonce_field( 'scch_lead_action' ); ?>
					<input type="hidden" name="action" value="scch_lead_action" />
					<input type="hidden" name="task" value="delete" />
					<input type="hidden" name="lead_id" value="<?php echo (int) $scch_id; ?>" />
					<button type="submit" class="ui-btn" style="color:#b42318;border-color:#f0c4c0"><?php esc_html_e( 'Delete lead', 'smart-client-contact-hub' ); ?></button>
				</form>
			</div>
		</div>
	</div>
</div>
