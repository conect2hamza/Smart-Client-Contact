<?php
/**
 * Pipeline board.
 *
 * @package SCCH
 * @var \SCCH\Admin\Admin $admin
 */

use SCCH\Admin\Admin;
use SCCH\Analytics_Service;
use SCCH\Attribution;
use SCCH\Lead_Repository;
use SCCH\Pipeline_Service;
use SCCH\UI;

defined( 'ABSPATH' ) || exit;

$scch_stages = Pipeline_Service::stages();
$scch_counts = Analytics_Service::stage_counts();
$scch_leads_url = admin_url( 'admin.php?page=scch-leads' );

// A column holds at most this many cards; the rest stay in the list view.
$scch_per_column = 25;

$scch_total = array_sum( $scch_counts );
?>
<div class="wrap scch-wrap scch-ui">

	<div class="ui-head">
		<div>
			<h1 class="ui-title"><?php esc_html_e( 'Pipeline', 'smart-client-contact-hub' ); ?></h1>
			<p class="ui-lead"><?php esc_html_e( 'Drag a card to move a lead, or use the stage menu on the card. Every move is recorded on the lead\'s timeline.', 'smart-client-contact-hub' ); ?></p>
		</div>
		<div class="ui-head__actions">
			<a class="ui-btn" href="<?php echo esc_url( $scch_leads_url ); ?>"><?php esc_html_e( 'List view', 'smart-client-contact-hub' ); ?></a>
		</div>
	</div>

	<?php Admin::maybe_notice(); ?>

	<?php if ( 0 === $scch_total ) : ?>
		<div class="ui-card">
			<?php
			echo UI::empty_state( // phpcs:ignore WordPress.Security.EscapeOutput
				'users',
				__( 'No leads yet', 'smart-client-contact-hub' ),
				__( 'Once visitors contact you, their leads appear here and you can move them through your stages.', 'smart-client-contact-hub' ),
				sprintf( '<a class="ui-btn ui-btn--primary" href="%s" target="_blank" rel="noopener noreferrer">%s</a>', esc_url( home_url( '/' ) ), esc_html__( 'Preview the widget', 'smart-client-contact-hub' ) )
			);
			?>
		</div>
	<?php else : ?>
		<div class="ui-board" id="scch-board">
			<?php
			foreach ( $scch_stages as $scch_key => $scch_stage ) :
				$scch_result = Lead_Repository::query(
					array(
						'status'   => $scch_key,
						'per_page' => $scch_per_column,
						'orderby'  => 'score',
						'order'    => 'DESC',
					)
				);
				?>
				<section class="ui-col" data-stage="<?php echo esc_attr( $scch_key ); ?>" style="<?php echo esc_attr( UI::stage_vars( $scch_stage['color'] ) ); ?>"
					aria-label="<?php echo esc_attr( $scch_stage['label'] ); ?>">
					<header class="ui-col__head">
						<span class="ui-col__dot" aria-hidden="true"></span>
						<span class="ui-col__name"><?php echo esc_html( $scch_stage['label'] ); ?></span>
						<span class="ui-col__count ui-num"><?php echo esc_html( number_format_i18n( $scch_result['total'] ) ); ?></span>
					</header>

					<div class="ui-col__body">
						<?php foreach ( $scch_result['items'] as $scch_lead ) : ?>
							<article class="ui-lead-card" draggable="true" data-lead="<?php echo (int) $scch_lead->id; ?>">
								<a class="ui-lead-card__name" href="<?php echo esc_url( add_query_arg( 'lead', (int) $scch_lead->id, $scch_leads_url ) ); ?>">
									<?php echo esc_html( $scch_lead->name ); ?>
								</a>

								<?php if ( $scch_lead->service ) : ?>
									<span class="ui-meta"><?php echo esc_html( $scch_lead->service ); ?></span>
								<?php endif; ?>

								<div class="ui-lead-card__row">
									<?php echo UI::score( (int) $scch_lead->score ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
									<?php if ( (float) $scch_lead->estimated_value > 0 ) : ?>
										<span class="ui-meta ui-num"><?php echo esc_html( UI::money( (float) $scch_lead->estimated_value ) ); ?></span>
									<?php endif; ?>
								</div>

								<div class="ui-lead-card__row">
									<span class="ui-meta"><?php echo esc_html( Attribution::label( (string) $scch_lead->source ) ); ?></span>
									<span class="ui-meta"><?php echo esc_html( UI::when( $scch_lead->submission_date ) ); ?></span>
								</div>

								<?php if ( $scch_lead->next_followup_at ) : ?>
									<div><?php echo UI::due_badge( (string) $scch_lead->next_followup_at ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
								<?php endif; ?>

								<?php echo UI::quick_actions( $scch_lead ); // phpcs:ignore WordPress.Security.EscapeOutput ?>

								<?php // Dragging is a shortcut; this select is the accessible path. ?>
								<label class="screen-reader-text" for="scch-move-<?php echo (int) $scch_lead->id; ?>">
									<?php
									printf(
										/* translators: %s: lead name. */
										esc_html__( 'Move %s to another stage', 'smart-client-contact-hub' ),
										esc_html( $scch_lead->name )
									);
									?>
								</label>
								<select class="ui-lead-card__move scch-stage-select" id="scch-move-<?php echo (int) $scch_lead->id; ?>" data-lead="<?php echo (int) $scch_lead->id; ?>">
									<?php foreach ( $scch_stages as $scch_ok => $scch_ostage ) : ?>
										<option value="<?php echo esc_attr( $scch_ok ); ?>" <?php selected( $scch_key, $scch_ok ); ?>><?php echo esc_html( $scch_ostage['label'] ); ?></option>
									<?php endforeach; ?>
								</select>
							</article>
						<?php endforeach; ?>

						<?php if ( $scch_result['total'] > $scch_per_column ) : ?>
							<p class="ui-meta" style="padding:4px 6px">
								<?php
								printf(
									/* translators: %d: number of additional leads. */
									esc_html__( '+%d more — open the list view', 'smart-client-contact-hub' ),
									(int) $scch_result['total'] - $scch_per_column
								);
								?>
							</p>
						<?php endif; ?>

						<?php if ( ! $scch_result['items'] ) : ?>
							<p class="ui-meta" style="padding:10px 6px"><?php esc_html_e( 'Nothing here', 'smart-client-contact-hub' ); ?></p>
						<?php endif; ?>
					</div>
				</section>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>
