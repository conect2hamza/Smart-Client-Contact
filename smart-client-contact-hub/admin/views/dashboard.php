<?php
/**
 * Dashboard: what is happening, what needs doing, and where to go next.
 *
 * @package SCCH
 * @var \SCCH\Admin\Admin $admin
 * @var array             $stats
 * @var array             $recent
 */

use SCCH\Activity_Service;
use SCCH\Admin\Admin;
use SCCH\Analytics_Service;
use SCCH\Attribution;
use SCCH\Channels;
use SCCH\Followup_Service;
use SCCH\Icons;
use SCCH\Lead_Scoring_Service;
use SCCH\Settings;
use SCCH\UI;

defined( 'ABSPATH' ) || exit;

$scch_days = isset( $_GET['period'] ) ? Analytics_Service::clamp_days( absint( $_GET['period'] ) ) : 30; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only period selector.
$scch_base = admin_url( 'admin.php?page=' . Admin::MENU );

$scch_kpis      = Analytics_Service::kpis( $scch_days );
$scch_trend     = Analytics_Service::trend( $scch_days );
$scch_sources   = Analytics_Service::breakdown( 'source', $scch_days );
$scch_channels  = Analytics_Service::breakdown( 'channel', $scch_days );
$scch_hot       = Analytics_Service::hot_leads( 5 );
$scch_due       = Followup_Service::query( array( 'scope' => 'due_today', 'limit' => 6 ) );
$scch_overdue   = Followup_Service::query( array( 'scope' => 'overdue', 'limit' => 6 ) );
$scch_activity  = Activity_Service::recent( 8 );
$scch_leads_url = admin_url( 'admin.php?page=scch-leads' );

$scch_user  = wp_get_current_user();
$scch_hour  = (int) current_time( 'G' );
$scch_greet = $scch_hour < 12
	? __( 'Good morning', 'smart-client-contact-hub' )
	: ( $scch_hour < 18 ? __( 'Good afternoon', 'smart-client-contact-hub' ) : __( 'Good evening', 'smart-client-contact-hub' ) );

// Setup progress: the few things that must be true for the plugin to work.
$scch_setup = array(
	array(
		'label' => __( 'Add a contact channel', 'smart-client-contact-hub' ),
		'done'  => count( Channels::all() ) > 0,
		'url'   => admin_url( 'admin.php?page=scch-channels' ),
	),
	array(
		'label' => __( 'Set your notification recipient', 'smart-client-contact-hub' ),
		'done'  => '' !== trim( (string) Settings::get( 'scch_email', 'admin_recipients', '' ) ),
		'url'   => admin_url( 'admin.php?page=scch-notifications' ),
	),
	array(
		'label' => __( 'Define your services', 'smart-client-contact-hub' ),
		'done'  => count( Settings::services() ) > 0,
		'url'   => admin_url( 'admin.php?page=scch-services' ),
	),
	array(
		'label' => __( 'Style the widget', 'smart-client-contact-hub' ),
		'done'  => get_option( 'scch_appearance' ) !== false,
		'url'   => admin_url( 'admin.php?page=scch-appearance' ),
	),
	array(
		'label' => __( 'Receive your first lead', 'smart-client-contact-hub' ),
		'done'  => (int) $stats['total'] > 0,
		'url'   => $scch_leads_url,
	),
);

$scch_done    = count( array_filter( array_column( $scch_setup, 'done' ) ) );
$scch_percent = (int) round( ( $scch_done / max( 1, count( $scch_setup ) ) ) * 100 );

/**
 * A KPI tile from the analytics payload.
 *
 * @param array  $kpis   Analytics payload.
 * @param string $key    Metric key.
 * @param string $label  Tile label.
 * @param string $format plain|percent|money.
 */
$scch_tile = static function ( array $kpis, string $key, string $label, string $format = 'plain' ) : string {
	$metric = $kpis[ $key ] ?? array( 'value' => 0, 'delta' => null );
	$value  = (float) $metric['value'];

	if ( 'percent' === $format ) {
		$text = number_format_i18n( $value, 1 ) . '%';
	} elseif ( 'money' === $format ) {
		$text = UI::money( $value );
	} else {
		$text = number_format_i18n( $value );
	}

	return UI::kpi( $label, $text, UI::delta( $metric['delta'] ) );
};
?>
<div class="wrap scch-wrap scch-ui">

	<div class="ui-head">
		<div>
			<h1 class="ui-title">
				<?php
				printf(
					/* translators: 1: greeting, 2: user display name. */
					esc_html__( '%1$s, %2$s', 'smart-client-contact-hub' ),
					esc_html( $scch_greet ),
					esc_html( $scch_user->display_name )
				);
				?>
			</h1>
			<p class="ui-lead">
				<?php
				if ( $scch_overdue ) {
					printf(
						/* translators: %d: number of overdue follow-ups. */
						esc_html( _n( '%d follow-up is overdue. Everything else is on track.', '%d follow-ups are overdue. Everything else is on track.', count( $scch_overdue ), 'smart-client-contact-hub' ) ),
						count( $scch_overdue )
					);
				} elseif ( $scch_due ) {
					printf(
						/* translators: %d: number of follow-ups due today. */
						esc_html( _n( '%d follow-up is due today.', '%d follow-ups are due today.', count( $scch_due ), 'smart-client-contact-hub' ) ),
						count( $scch_due )
					);
				} else {
					esc_html_e( 'Nothing is overdue. Here is how the last period went.', 'smart-client-contact-hub' );
				}
				?>
			</p>
		</div>

		<div class="ui-head__actions">
			<label class="screen-reader-text" for="scch-period"><?php esc_html_e( 'Reporting period', 'smart-client-contact-hub' ); ?></label>
			<select id="scch-period" onchange="window.location.href=this.value">
				<?php
				foreach ( array( 7, 30, 90, 365 ) as $scch_option ) :
					$scch_label = 365 === $scch_option
						? __( 'Last 12 months', 'smart-client-contact-hub' )
						/* translators: %d: number of days. */
						: sprintf( __( 'Last %d days', 'smart-client-contact-hub' ), $scch_option );
					?>
					<option value="<?php echo esc_url( add_query_arg( 'period', $scch_option, $scch_base ) ); ?>" <?php selected( $scch_days, $scch_option ); ?>>
						<?php echo esc_html( $scch_label ); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<a class="ui-btn" href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'View widget', 'smart-client-contact-hub' ); ?></a>
			<a class="ui-btn ui-btn--primary" href="<?php echo esc_url( $scch_leads_url ); ?>"><?php esc_html_e( 'Open leads', 'smart-client-contact-hub' ); ?></a>
		</div>
	</div>

	<?php Admin::maybe_notice(); ?>

	<?php if ( $scch_done < count( $scch_setup ) ) : ?>
		<div class="ui-card" style="margin-bottom:16px">
			<div class="ui-card__head">
				<h2 class="ui-card-title"><?php esc_html_e( 'Finish setting up', 'smart-client-contact-hub' ); ?></h2>
				<span class="ui-meta ui-num">
					<?php
					printf(
						/* translators: 1: completed steps, 2: total steps. */
						esc_html__( '%1$d of %2$d done', 'smart-client-contact-hub' ),
						(int) $scch_done,
						count( $scch_setup )
					);
					?>
				</span>
			</div>
			<div class="ui-meter" role="img" aria-label="<?php echo esc_attr( sprintf( /* translators: %d: percentage complete. */ __( 'Setup %d%% complete', 'smart-client-contact-hub' ), $scch_percent ) ); ?>">
				<span class="ui-meter__fill" style="width:<?php echo (int) $scch_percent; ?>%"></span>
			</div>
			<ul style="margin:12px 0 0;display:flex;flex-wrap:wrap;gap:8px 20px;list-style:none;padding:0">
				<?php foreach ( $scch_setup as $scch_step ) : ?>
					<li style="font-size:13px">
						<?php if ( $scch_step['done'] ) : ?>
							<span class="ui-badge ui-badge--ok">✓</span> <?php echo esc_html( $scch_step['label'] ); ?>
						<?php else : ?>
							<a href="<?php echo esc_url( $scch_step['url'] ); ?>"><?php echo esc_html( $scch_step['label'] ); ?></a>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>

	<div class="ui-kpis">
		<?php
		// phpcs:disable WordPress.Security.EscapeOutput -- UI builders escape internally.
		echo $scch_tile( $scch_kpis, 'total', __( 'Leads', 'smart-client-contact-hub' ) );
		echo $scch_tile( $scch_kpis, 'new', __( 'New', 'smart-client-contact-hub' ) );
		echo $scch_tile( $scch_kpis, 'hot', __( 'Hot leads', 'smart-client-contact-hub' ) );
		echo $scch_tile( $scch_kpis, 'qualified', __( 'Qualified', 'smart-client-contact-hub' ) );
		echo $scch_tile( $scch_kpis, 'won', __( 'Won', 'smart-client-contact-hub' ) );
		echo $scch_tile( $scch_kpis, 'conversion', __( 'Conversion', 'smart-client-contact-hub' ), 'percent' );
		echo $scch_tile( $scch_kpis, 'pipeline', __( 'Pipeline value', 'smart-client-contact-hub' ), 'money' );
		echo $scch_tile( $scch_kpis, 'revenue', __( 'Revenue', 'smart-client-contact-hub' ), 'money' );
		// phpcs:enable WordPress.Security.EscapeOutput
		?>
	</div>

	<div class="ui-grid ui-grid--halves" style="margin-bottom:16px">

		<div class="ui-card">
			<div class="ui-card__head">
				<h2 class="ui-card-title"><?php esc_html_e( 'Leads over time', 'smart-client-contact-hub' ); ?></h2>
				<span class="ui-meta">
					<?php
					printf(
						/* translators: %d: number of days. */
						esc_html__( 'Last %d days', 'smart-client-contact-hub' ),
						(int) $scch_days
					);
					?>
				</span>
			</div>
			<?php if ( array_sum( array_column( $scch_trend, 'count' ) ) > 0 ) : ?>
				<div class="ui-trend-wrap"><?php echo UI::trend_chart( $scch_trend ); // phpcs:ignore WordPress.Security.EscapeOutput -- built and escaped in UI. ?></div>
			<?php else : ?>
				<?php
				echo UI::empty_state( // phpcs:ignore WordPress.Security.EscapeOutput
					'sparkles',
					__( 'No leads in this period', 'smart-client-contact-hub' ),
					__( 'Once visitors start contacting you, their leads will chart here.', 'smart-client-contact-hub' )
				);
				?>
			<?php endif; ?>
		</div>

		<div class="ui-card">
			<div class="ui-card__head">
				<h2 class="ui-card-title"><?php esc_html_e( 'Where leads come from', 'smart-client-contact-hub' ); ?></h2>
				<a class="ui-meta" href="<?php echo esc_url( admin_url( 'admin.php?page=scch-analytics' ) ); ?>"><?php esc_html_e( 'All reports', 'smart-client-contact-hub' ); ?></a>
			</div>
			<?php if ( $scch_sources ) : ?>
				<div class="ui-bars">
					<?php
					$scch_max = max( array_column( $scch_sources, 'leads' ) );
					foreach ( $scch_sources as $scch_row ) {
						echo UI::bar( // phpcs:ignore WordPress.Security.EscapeOutput
							Attribution::label( $scch_row['label'] ),
							$scch_row['leads'],
							$scch_max,
							$scch_row['won'] > 0 ? sprintf( '%s%% won', number_format_i18n( $scch_row['conversion'], 1 ) ) : ''
						);
					}
					?>
				</div>
			<?php else : ?>
				<?php
				echo UI::empty_state( // phpcs:ignore WordPress.Security.EscapeOutput
					'globe',
					__( 'No sources yet', 'smart-client-contact-hub' ),
					__( 'Source, referrer and UTM campaign are recorded on every new lead from now on.', 'smart-client-contact-hub' )
				);
				?>
			<?php endif; ?>
		</div>
	</div>

	<div class="ui-grid ui-grid--halves" style="margin-bottom:16px">

		<div class="ui-card ui-card--flush">
			<div class="ui-card__head">
				<h2 class="ui-card-title"><?php esc_html_e( 'Hot leads', 'smart-client-contact-hub' ); ?></h2>
				<a class="ui-meta" href="<?php echo esc_url( add_query_arg( 'band', 'hot', $scch_leads_url ) ); ?>"><?php esc_html_e( 'See all', 'smart-client-contact-hub' ); ?></a>
			</div>
			<?php if ( $scch_hot ) : ?>
				<div class="ui-table-wrap">
					<table class="ui-table ui-table--stack">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Lead', 'smart-client-contact-hub' ); ?></th>
								<th><?php esc_html_e( 'Score', 'smart-client-contact-hub' ); ?></th>
								<th><?php esc_html_e( 'Stage', 'smart-client-contact-hub' ); ?></th>
								<th><?php esc_html_e( 'Actions', 'smart-client-contact-hub' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $scch_hot as $scch_lead ) : ?>
								<tr>
									<td data-label="<?php esc_attr_e( 'Lead', 'smart-client-contact-hub' ); ?>">
										<a href="<?php echo esc_url( add_query_arg( 'lead', (int) $scch_lead->id, $scch_leads_url ) ); ?>"><strong><?php echo esc_html( $scch_lead->name ); ?></strong></a>
										<div class="ui-meta"><?php echo esc_html( $scch_lead->service ?: Attribution::label( (string) $scch_lead->source ) ); ?></div>
									</td>
									<td data-label="<?php esc_attr_e( 'Score', 'smart-client-contact-hub' ); ?>"><?php echo UI::score( (int) $scch_lead->score ); // phpcs:ignore WordPress.Security.EscapeOutput ?></td>
									<td data-label="<?php esc_attr_e( 'Stage', 'smart-client-contact-hub' ); ?>"><?php echo UI::stage_badge( (string) $scch_lead->status ); // phpcs:ignore WordPress.Security.EscapeOutput ?></td>
									<td data-label="<?php esc_attr_e( 'Actions', 'smart-client-contact-hub' ); ?>"><?php echo UI::quick_actions( $scch_lead ); // phpcs:ignore WordPress.Security.EscapeOutput ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php else : ?>
				<?php
				echo UI::empty_state( // phpcs:ignore WordPress.Security.EscapeOutput
					'bolt',
					__( 'No hot leads right now', 'smart-client-contact-hub' ),
					__( 'Leads scoring 70 or above appear here so you can call them first.', 'smart-client-contact-hub' ),
					sprintf( '<a class="ui-btn" href="%s">%s</a>', esc_url( admin_url( 'admin.php?page=scch-scoring' ) ), esc_html__( 'Tune scoring', 'smart-client-contact-hub' ) )
				);
				?>
			<?php endif; ?>
		</div>

		<div class="ui-card ui-card--flush">
			<div class="ui-card__head">
				<h2 class="ui-card-title"><?php esc_html_e( 'Follow-ups', 'smart-client-contact-hub' ); ?></h2>
				<a class="ui-meta" href="<?php echo esc_url( admin_url( 'admin.php?page=scch-followups' ) ); ?>"><?php esc_html_e( 'See all', 'smart-client-contact-hub' ); ?></a>
			</div>
			<?php
			$scch_tasks = array_merge( $scch_overdue, $scch_due );
			if ( $scch_tasks ) :
				?>
				<div class="ui-table-wrap">
					<table class="ui-table ui-table--stack">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Task', 'smart-client-contact-hub' ); ?></th>
								<th><?php esc_html_e( 'Lead', 'smart-client-contact-hub' ); ?></th>
								<th><?php esc_html_e( 'Due', 'smart-client-contact-hub' ); ?></th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( array_slice( $scch_tasks, 0, 6 ) as $scch_task ) : ?>
								<tr data-followup="<?php echo (int) $scch_task->id; ?>">
									<td data-label="<?php esc_attr_e( 'Task', 'smart-client-contact-hub' ); ?>"><?php echo esc_html( $scch_task->title ); ?></td>
									<td data-label="<?php esc_attr_e( 'Lead', 'smart-client-contact-hub' ); ?>">
										<a href="<?php echo esc_url( add_query_arg( 'lead', (int) $scch_task->lead_id, $scch_leads_url ) ); ?>"><?php echo esc_html( $scch_task->lead_name ?: __( '(deleted)', 'smart-client-contact-hub' ) ); ?></a>
									</td>
									<td data-label="<?php esc_attr_e( 'Due', 'smart-client-contact-hub' ); ?>"><?php echo UI::due_badge( (string) $scch_task->due_at, $scch_task->completed_at ); // phpcs:ignore WordPress.Security.EscapeOutput ?></td>
									<td>
										<button type="button" class="ui-btn scch-followup-done" data-followup="<?php echo (int) $scch_task->id; ?>"><?php esc_html_e( 'Done', 'smart-client-contact-hub' ); ?></button>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php else : ?>
				<?php
				echo UI::empty_state( // phpcs:ignore WordPress.Security.EscapeOutput
					'check',
					__( "You're all caught up", 'smart-client-contact-hub' ),
					__( 'No follow-ups are due today.', 'smart-client-contact-hub' )
				);
				?>
			<?php endif; ?>
		</div>
	</div>

	<div class="ui-grid ui-grid--halves">

		<div class="ui-card">
			<div class="ui-card__head">
				<h2 class="ui-card-title"><?php esc_html_e( 'Channels', 'smart-client-contact-hub' ); ?></h2>
			</div>
			<?php if ( $scch_channels ) : ?>
				<div class="ui-bars">
					<?php
					$scch_cmax = max( array_column( $scch_channels, 'leads' ) );
					foreach ( $scch_channels as $scch_row ) {
						echo UI::bar( // phpcs:ignore WordPress.Security.EscapeOutput
							ucfirst( $scch_row['label'] ),
							$scch_row['leads'],
							$scch_cmax,
							$scch_row['won'] > 0 ? sprintf( '%s%% won', number_format_i18n( $scch_row['conversion'], 1 ) ) : ''
						);
					}
					?>
				</div>
				<p class="ui-meta" style="margin-top:12px">
					<?php esc_html_e( 'Only the lead form records a channel today. Click-through channels such as WhatsApp open the visitor\'s own app, which the plugin cannot observe.', 'smart-client-contact-hub' ); ?>
				</p>
			<?php else : ?>
				<?php
				echo UI::empty_state( // phpcs:ignore WordPress.Security.EscapeOutput
					'chat-dots',
					__( 'No channel data yet', 'smart-client-contact-hub' ),
					__( 'Leads are grouped by the channel they arrived through.', 'smart-client-contact-hub' )
				);
				?>
			<?php endif; ?>
		</div>

		<div class="ui-card">
			<div class="ui-card__head">
				<h2 class="ui-card-title"><?php esc_html_e( 'Recent activity', 'smart-client-contact-hub' ); ?></h2>
			</div>
			<?php if ( $scch_activity ) : ?>
				<ul class="ui-timeline">
					<?php
					$scch_types = Activity_Service::types();
					foreach ( $scch_activity as $scch_event ) :
						$scch_type = $scch_types[ $scch_event->type ] ?? $scch_types['note'];
						?>
						<li>
							<span class="ui-timeline__dot" style="--dot:<?php echo esc_attr( $scch_type['color'] ); ?>"><?php echo Icons::svg( $scch_type['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
							<p class="ui-timeline__summary">
								<?php echo esc_html( $scch_event->summary ); ?>
								<?php if ( $scch_event->lead_name ) : ?>
									— <a href="<?php echo esc_url( add_query_arg( 'lead', (int) $scch_event->lead_id, $scch_leads_url ) ); ?>"><?php echo esc_html( $scch_event->lead_name ); ?></a>
								<?php endif; ?>
							</p>
							<p class="ui-timeline__when"><?php echo esc_html( UI::when( $scch_event->created_at ) ); ?></p>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php else : ?>
				<?php
				echo UI::empty_state( // phpcs:ignore WordPress.Security.EscapeOutput
					'clock',
					__( 'Nothing has happened yet', 'smart-client-contact-hub' ),
					__( 'Lead arrivals, stage changes, notes and follow-ups all show up here.', 'smart-client-contact-hub' )
				);
				?>
			<?php endif; ?>
		</div>
	</div>
</div>
