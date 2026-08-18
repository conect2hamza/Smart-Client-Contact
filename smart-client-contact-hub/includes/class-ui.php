<?php
/**
 * Rendering helpers for the admin design system.
 *
 * @package SCCH
 */

namespace SCCH;

defined( 'ABSPATH' ) || exit;

/**
 * Small, escaped builders for the components in admin-ui.css.
 *
 * Views call these instead of hand-writing markup, so a badge or an empty
 * state looks the same everywhere and escaping happens in one place. Every
 * method returns a string that is safe to echo.
 */
class UI {

	/**
	 * Stage badge, colored from the pipeline definition.
	 *
	 * @param string $status Stage key.
	 */
	public static function stage_badge( string $status ): string {
		$stage = Pipeline_Service::stage( $status );

		return sprintf(
			'<span class="ui-badge ui-badge--stage" style="%s">%s</span>',
			esc_attr( self::stage_vars( $stage['color'] ) ),
			esc_html( $stage['label'] )
		);
	}

	/**
	 * CSS custom properties for a stage color, with a soft tint and a border.
	 *
	 * @param string $hex Stage color.
	 */
	public static function stage_vars( string $hex ): string {
		$hex = sanitize_hex_color( $hex ) ?: '#64748b';

		return sprintf(
			'--stage:%1$s;--stage-soft:%2$s;--stage-line:%3$s',
			$hex,
			self::tint( $hex, 0.12 ),
			self::tint( $hex, 0.28 )
		);
	}

	/**
	 * An rgba() tint of a hex color.
	 *
	 * @param string $hex   Hex color.
	 * @param float  $alpha 0-1.
	 */
	public static function tint( string $hex, float $alpha ): string {
		$hex = ltrim( sanitize_hex_color( $hex ) ?: '#64748b', '#' );

		if ( 3 === strlen( $hex ) ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}

		return sprintf(
			'rgba(%d,%d,%d,%s)',
			hexdec( substr( $hex, 0, 2 ) ),
			hexdec( substr( $hex, 2, 2 ) ),
			hexdec( substr( $hex, 4, 2 ) ),
			rtrim( rtrim( number_format( max( 0, min( 1, $alpha ) ), 2, '.', '' ), '0' ), '.' ) ?: '0'
		);
	}

	/**
	 * Score chip plus band, so the value never depends on color alone.
	 *
	 * @param int  $score 0-100.
	 * @param bool $meter Whether to draw the bar underneath.
	 */
	public static function score( int $score, bool $meter = false ): string {
		$score = max( 0, min( 100, $score ) );
		$band  = Lead_Scoring_Service::band( $score );

		$out = sprintf(
			'<span class="ui-score"><span class="ui-num">%1$d</span><span class="ui-score__out">/100</span> <span class="ui-badge ui-badge--%2$s">%3$s</span></span>',
			$score,
			esc_attr( $band ),
			esc_html( Lead_Scoring_Service::band_label( $band ) )
		);

		if ( $meter ) {
			$out .= sprintf(
				'<span class="ui-meter" role="img" aria-label="%1$s"><span class="ui-meter__fill is-%2$s" style="width:%3$d%%"></span></span>',
				/* translators: %d: score out of 100. */
				esc_attr( sprintf( __( 'Lead score %d out of 100', 'smart-client-contact-hub' ), $score ) ),
				esc_attr( $band ),
				$score
			);
		}

		return $out;
	}

	/**
	 * Percentage change indicator against the previous period.
	 *
	 * @param float|null $delta   Percentage, or null when there is no base.
	 * @param bool       $inverse True when a fall is the good direction.
	 */
	public static function delta( ?float $delta, bool $inverse = false ): string {
		if ( null === $delta ) {
			return '<span class="ui-kpi__delta">' . esc_html__( 'No earlier data', 'smart-client-contact-hub' ) . '</span>';
		}

		if ( 0.0 === $delta ) {
			return '<span class="ui-kpi__delta">' . esc_html__( 'No change', 'smart-client-contact-hub' ) . '</span>';
		}

		$up    = $delta > 0;
		$good  = $inverse ? ! $up : $up;
		$arrow = $up ? '↑' : '↓';

		return sprintf(
			'<span class="ui-kpi__delta %1$s"><span aria-hidden="true">%2$s</span> %3$s</span>',
			$good ? 'is-up' : 'is-down',
			$arrow,
			esc_html(
				sprintf(
					/* translators: %s: percentage change, already signed. */
					__( '%s vs previous period', 'smart-client-contact-hub' ),
					number_format_i18n( abs( $delta ), 1 ) . '%'
				)
			)
		);
	}

	/**
	 * A KPI tile.
	 *
	 * @param string      $label Tile label.
	 * @param string      $value Formatted value.
	 * @param string|null $delta Rendered delta markup, or null.
	 */
	public static function kpi( string $label, string $value, ?string $delta = null ): string {
		return sprintf(
			'<div class="ui-kpi"><span class="ui-kpi__label">%1$s</span><span class="ui-kpi__value">%2$s</span>%3$s</div>',
			esc_html( $label ),
			esc_html( $value ),
			$delta ?? ''
		);
	}

	/**
	 * Empty state.
	 *
	 * @param string $icon    Icon id from the library.
	 * @param string $title   Headline.
	 * @param string $body    Supporting sentence.
	 * @param string $action  Optional button markup.
	 */
	public static function empty_state( string $icon, string $title, string $body, string $action = '' ): string {
		return sprintf(
			'<div class="ui-empty"><div class="ui-empty__icon">%1$s</div><h3>%2$s</h3><p>%3$s</p>%4$s</div>',
			Icons::svg( $icon ),
			esc_html( $title ),
			esc_html( $body ),
			$action
		);
	}

	/**
	 * Horizontal bar row for a breakdown list.
	 *
	 * @param string $label Row label.
	 * @param int    $value Row value.
	 * @param int    $max   Largest value in the set, for the bar width.
	 * @param string $note  Right-hand note, e.g. a conversion rate.
	 */
	public static function bar( string $label, int $value, int $max, string $note = '' ): string {
		$width = $max > 0 ? (int) round( ( $value / $max ) * 100 ) : 0;

		return sprintf(
			'<div class="ui-bar"><span class="ui-bar__label">%1$s</span><span class="ui-bar__value">%2$s</span><span class="ui-bar__track"><span class="ui-bar__fill" style="width:%3$d%%"></span></span></div>',
			esc_html( $label ),
			esc_html( '' !== $note ? $value . ' · ' . $note : (string) $value ),
			$width
		);
	}

	/**
	 * Quick contact links for a lead: call, WhatsApp, email, SMS.
	 *
	 * @param object $lead Lead row.
	 */
	public static function quick_actions( object $lead ): string {
		$digits = preg_replace( '/[^0-9+]/', '', (string) ( $lead->phone ?? '' ) );
		$plain  = ltrim( (string) $digits, '+' );
		$email  = (string) ( $lead->email ?? '' );

		$links = array(
			array( 'phone', 'tel:' . $digits, __( 'Call', 'smart-client-contact-hub' ), '' !== $digits ),
			array( 'whatsapp', 'https://wa.me/' . $plain, __( 'WhatsApp', 'smart-client-contact-hub' ), '' !== $plain ),
			array( 'mail', 'mailto:' . $email, __( 'Email', 'smart-client-contact-hub' ), is_email( $email ) ),
			array( 'sms', 'sms:' . $digits, __( 'Text', 'smart-client-contact-hub' ), '' !== $digits ),
		);

		$out = '<span class="ui-quick">';

		foreach ( $links as $link ) {
			list( $icon, $href, $label, $usable ) = $link;

			$out .= sprintf(
				'<a class="ui-iconlink" href="%1$s" title="%2$s"%3$s%4$s>%5$s<span class="screen-reader-text">%2$s</span></a>',
				$usable ? esc_url( $href ) : '#',
				esc_attr( $label ),
				$usable ? '' : ' aria-disabled="true" tabindex="-1"',
				'whatsapp' === $icon && $usable ? ' target="_blank" rel="noopener noreferrer"' : '',
				Icons::svg( $icon )
			);
		}

		return $out . '</span>';
	}

	/**
	 * Pipeline progress rail for one lead.
	 *
	 * @param string $status Current stage.
	 */
	public static function rail( string $status ): string {
		$stages  = Pipeline_Service::progression();
		$current = Pipeline_Service::position( $status );
		$type    = Pipeline_Service::stage( $status )['type'];

		$out = '<ul class="ui-rail">';
		$i   = 0;

		foreach ( $stages as $key => $stage ) {
			$class = '';
			if ( 'lost' === $type || 'junk' === $type ) {
				$class = 0 === $i ? 'is-lost' : '';
			} elseif ( $current >= 0 && $i < $current ) {
				$class = 'is-done';
			} elseif ( $i === $current ) {
				$class = 'is-current';
			}

			$out .= sprintf(
				'<li class="%1$s"><span class="ui-rail__bar"></span>%2$s</li>',
				esc_attr( $class ),
				esc_html( $stage['label'] )
			);
			$i++;
		}

		$out .= '</ul>';

		if ( 'lost' === $type || 'junk' === $type ) {
			$out .= '<p class="ui-meta">' . esc_html(
				sprintf(
					/* translators: %s: stage label. */
					__( 'This lead is marked %s.', 'smart-client-contact-hub' ),
					Pipeline_Service::stage( $status )['label']
				)
			) . '</p>';
		}

		return $out;
	}

	/**
	 * Money, using the configured currency symbol.
	 *
	 * @param float $amount Amount.
	 */
	public static function money( float $amount ): string {
		$symbol = (string) Settings::get( 'scch_general', 'currency_symbol', '$' );

		return $symbol . number_format_i18n( $amount, ( (float) (int) $amount === $amount ) ? 0 : 2 );
	}

	/**
	 * A short relative time, falling back to a date for anything older.
	 *
	 * @param string|null $datetime MySQL datetime.
	 */
	public static function when( ?string $datetime ): string {
		if ( ! $datetime || '0000-00-00 00:00:00' === $datetime ) {
			return '—';
		}

		$ts  = strtotime( $datetime );
		$now = strtotime( current_time( 'mysql' ) );

		if ( ! $ts ) {
			return '—';
		}

		$diff = $now - $ts;

		if ( $diff >= 0 && $diff < DAY_IN_SECONDS ) {
			/* translators: %s: human-readable time difference, e.g. "3 hours". */
			return sprintf( __( '%s ago', 'smart-client-contact-hub' ), human_time_diff( $ts, $now ) );
		}

		if ( $diff < 0 && abs( $diff ) < WEEK_IN_SECONDS ) {
			/* translators: %s: human-readable time difference, e.g. "2 days". */
			return sprintf( __( 'in %s', 'smart-client-contact-hub' ), human_time_diff( $now, $ts ) );
		}

		return date_i18n( get_option( 'date_format' ), $ts );
	}

	/**
	 * Due-state badge for a follow-up.
	 *
	 * @param string      $due_at       MySQL datetime.
	 * @param string|null $completed_at MySQL datetime, or null.
	 */
	public static function due_badge( string $due_at, ?string $completed_at = null ): string {
		if ( $completed_at ) {
			return '<span class="ui-badge ui-badge--ok">' . esc_html__( 'Done', 'smart-client-contact-hub' ) . '</span>';
		}

		$due   = strtotime( $due_at );
		$now   = strtotime( current_time( 'mysql' ) );
		$today = gmdate( 'Y-m-d', $now );

		if ( ! $due ) {
			return '';
		}

		if ( $due < strtotime( $today . ' 00:00:00' ) ) {
			return '<span class="ui-badge ui-badge--late">' . esc_html__( 'Overdue', 'smart-client-contact-hub' ) . '</span>';
		}

		if ( gmdate( 'Y-m-d', $due ) === $today ) {
			return '<span class="ui-badge ui-badge--due">' . esc_html__( 'Today', 'smart-client-contact-hub' ) . '</span>';
		}

		return '<span class="ui-badge ui-badge--cold">' . esc_html( self::when( $due_at ) ) . '</span>';
	}

	/**
	 * A sparkline-style area chart, drawn as inline SVG.
	 *
	 * No charting library: the series is small and this keeps the admin
	 * bundle free of a dependency for one graph.
	 *
	 * @param array<int,array{date:string,count:int}> $series Zero-filled series.
	 */
	public static function trend_chart( array $series ): string {
		$count = count( $series );

		if ( $count < 2 ) {
			return '';
		}

		$values = array_map( static fn( $p ) => (int) $p['count'], $series );
		$max    = max( 1, max( $values ) );
		$w      = 100;
		$h      = 34;
		$step   = $w / ( $count - 1 );

		$points = array();
		foreach ( $values as $i => $value ) {
			$points[] = sprintf( '%.2f,%.2f', $i * $step, $h - ( ( $value / $max ) * ( $h - 3 ) ) - 1.5 );
		}

		$line = implode( ' ', $points );
		$area = sprintf( '0,%1$.2f %2$s %3$.2f,%1$.2f', $h, $line, $w );

		return sprintf(
			'<svg class="ui-trend" viewBox="0 0 %1$d %2$d" preserveAspectRatio="none" role="img" aria-label="%3$s" focusable="false">
				<polygon points="%4$s" fill="rgba(37,99,235,.12)"/>
				<polyline points="%5$s" fill="none" stroke="#2563eb" stroke-width="1.5" stroke-linejoin="round" stroke-linecap="round" vector-effect="non-scaling-stroke"/>
			</svg>',
			$w,
			$h,
			esc_attr(
				sprintf(
					/* translators: 1: number of days, 2: total leads. */
					__( 'Leads per day over the last %1$d days, %2$d in total', 'smart-client-contact-hub' ),
					$count,
					array_sum( $values )
				)
			),
			esc_attr( $area ),
			esc_attr( $line )
		);
	}
}
