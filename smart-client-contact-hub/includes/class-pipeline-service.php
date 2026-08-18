<?php
/**
 * Pipeline stage definitions and transitions.
 *
 * @package SCCH
 */

namespace SCCH;

defined( 'ABSPATH' ) || exit;

/**
 * The stages a lead moves through, and the rules for moving it.
 *
 * The pre-1.1 statuses (new, contacted, qualified, closed, spam) are all
 * still stages, so every stored lead keeps a valid stage without migration.
 * "closed" is retained as a hidden legacy alias of "won" for old records.
 */
class Pipeline_Service {

	/**
	 * Option holding administrator stage customizations.
	 */
	public const OPTION = 'scch_pipeline_stages';

	/**
	 * Stages as shipped. Keys are stored in the lead's status column.
	 *
	 * @return array<string,array{label:string,color:string,type:string}>
	 */
	public static function defaults(): array {
		return array(
			'new'        => array(
				'label' => __( 'New', 'smart-client-contact-hub' ),
				'color' => '#2563eb',
				'type'  => 'open',
			),
			'contacted'  => array(
				'label' => __( 'Contacted', 'smart-client-contact-hub' ),
				'color' => '#0891b2',
				'type'  => 'open',
			),
			'qualified'  => array(
				'label' => __( 'Qualified', 'smart-client-contact-hub' ),
				'color' => '#7c3aed',
				'type'  => 'open',
			),
			'proposal'   => array(
				'label' => __( 'Proposal Sent', 'smart-client-contact-hub' ),
				'color' => '#c026d3',
				'type'  => 'open',
			),
			'negotiation' => array(
				'label' => __( 'Negotiation', 'smart-client-contact-hub' ),
				'color' => '#d97706',
				'type'  => 'open',
			),
			'won'        => array(
				'label' => __( 'Won', 'smart-client-contact-hub' ),
				'color' => '#16a34a',
				'type'  => 'won',
			),
			'lost'       => array(
				'label' => __( 'Lost', 'smart-client-contact-hub' ),
				'color' => '#64748b',
				'type'  => 'lost',
			),
			'spam'       => array(
				'label' => __( 'Spam', 'smart-client-contact-hub' ),
				'color' => '#dc2626',
				'type'  => 'junk',
			),
		);
	}

	/**
	 * Legacy statuses kept valid but not offered as pipeline columns.
	 *
	 * @return array<string,array>
	 */
	public static function legacy(): array {
		return array(
			'closed' => array(
				'label' => __( 'Closed (legacy)', 'smart-client-contact-hub' ),
				'color' => '#16a34a',
				'type'  => 'won',
			),
		);
	}

	/**
	 * Active stages, honoring administrator customization.
	 *
	 * @return array<string,array>
	 */
	public static function stages(): array {
		$stored = get_option( self::OPTION, null );

		if ( ! is_array( $stored ) || ! $stored ) {
			return self::defaults();
		}

		$defaults = self::defaults();
		$out      = array();

		foreach ( $stored as $key => $stage ) {
			$key = sanitize_key( $key );
			if ( '' === $key || ! is_array( $stage ) ) {
				continue;
			}
			$out[ $key ] = array(
				'label' => sanitize_text_field( $stage['label'] ?? ( $defaults[ $key ]['label'] ?? $key ) ),
				'color' => sanitize_hex_color( $stage['color'] ?? '' ) ?: ( $defaults[ $key ]['color'] ?? '#64748b' ),
				'type'  => in_array( $stage['type'] ?? '', array( 'open', 'won', 'lost', 'junk' ), true )
					? $stage['type']
					: ( $defaults[ $key ]['type'] ?? 'open' ),
			);
		}

		return $out ? $out : $defaults;
	}

	/**
	 * Every status value that may legitimately be stored.
	 *
	 * @return string[]
	 */
	public static function valid_statuses(): array {
		return array_merge( array_keys( self::stages() ), array_keys( self::legacy() ) );
	}

	/**
	 * Whether a status is one the plugin recognizes.
	 *
	 * @param string $status Status key.
	 */
	public static function is_valid( string $status ): bool {
		return in_array( $status, self::valid_statuses(), true );
	}

	/**
	 * One stage's definition, falling back to a neutral shape.
	 *
	 * @param string $status Status key.
	 * @return array{label:string,color:string,type:string}
	 */
	public static function stage( string $status ): array {
		$all = self::stages() + self::legacy();

		return $all[ $status ] ?? array(
			'label' => ucfirst( str_replace( '_', ' ', $status ) ),
			'color' => '#64748b',
			'type'  => 'open',
		);
	}

	/**
	 * Stage keys of a given type.
	 *
	 * @param string $type open|won|lost|junk.
	 * @return string[]
	 */
	public static function of_type( string $type ): array {
		$keys = array();
		foreach ( self::stages() + self::legacy() as $key => $stage ) {
			if ( $stage['type'] === $type ) {
				$keys[] = $key;
			}
		}
		return $keys;
	}

	/**
	 * The open stages, in order, for the progress bar on a lead.
	 *
	 * @return array<string,array>
	 */
	public static function progression(): array {
		$out = array();
		foreach ( self::stages() as $key => $stage ) {
			if ( 'open' === $stage['type'] || 'won' === $stage['type'] ) {
				$out[ $key ] = $stage;
			}
		}
		return $out;
	}

	/**
	 * How far through the progression a status sits, 0-based. -1 when the
	 * status is not part of it (lost, spam).
	 *
	 * @param string $status Status key.
	 */
	public static function position( string $status ): int {
		$keys = array_keys( self::progression() );
		$idx  = array_search( $status, $keys, true );

		if ( false !== $idx ) {
			return (int) $idx;
		}

		// A legacy "closed" lead sits where "won" sits.
		if ( 'closed' === $status ) {
			$won = array_search( 'won', $keys, true );
			return false === $won ? -1 : (int) $won;
		}

		return -1;
	}
}
