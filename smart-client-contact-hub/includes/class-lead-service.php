<?php
/**
 * Lead mutations: stage, assignment, value, score, notes, deletion.
 *
 * @package SCCH
 */

namespace SCCH;

defined( 'ABSPATH' ) || exit;

/**
 * The one place a lead is changed after it is created.
 *
 * Every mutation writes a timeline entry, fires a documented action, and
 * invalidates the report cache, so the audit trail and the dashboard can
 * never drift from the data.
 */
class Lead_Service {

	/**
	 * Move a lead to a new stage.
	 *
	 * @param int    $lead_id Lead ID.
	 * @param string $status  Target stage.
	 */
	public static function change_status( int $lead_id, string $status ): bool {
		global $wpdb;

		$lead = Lead_Repository::find( $lead_id );

		if ( ! $lead || ! Pipeline_Service::is_valid( $status ) ) {
			return false;
		}

		$from = (string) $lead->status;

		if ( $from === $status ) {
			return true;
		}

		$updated = $wpdb->update(
			Lead_Repository::table(),
			array( 'status' => $status ),
			array( 'id' => $lead_id ),
			array( '%s' ),
			array( '%d' )
		);

		if ( false === $updated ) {
			return false;
		}

		Activity_Service::log(
			$lead_id,
			'status',
			sprintf(
				/* translators: 1: previous stage, 2: new stage. */
				__( 'Stage changed from %1$s to %2$s', 'smart-client-contact-hub' ),
				Pipeline_Service::stage( $from )['label'],
				Pipeline_Service::stage( $status )['label']
			),
			'',
			array( 'from' => $from, 'to' => $status )
		);

		self::changed( $lead_id );

		/**
		 * Fires when a lead moves between pipeline stages.
		 *
		 * @param int    $lead_id Lead ID.
		 * @param string $status  New stage key.
		 * @param string $from    Previous stage key.
		 */
		do_action( 'scch_lead_status_changed', $lead_id, $status, $from );

		if ( 'won' === Pipeline_Service::stage( $status )['type'] && 'won' !== Pipeline_Service::stage( $from )['type'] ) {
			/**
			 * Fires the first time a lead reaches a winning stage.
			 *
			 * @param int    $lead_id Lead ID.
			 * @param object $lead    The lead row as it was before the change.
			 */
			do_action( 'scch_lead_won', $lead_id, $lead );
		}

		return true;
	}

	/**
	 * Assign a lead to a user, or clear the assignment with 0.
	 *
	 * @param int $lead_id Lead ID.
	 * @param int $user_id User ID, or 0.
	 */
	public static function assign( int $lead_id, int $user_id ): bool {
		global $wpdb;

		$lead = Lead_Repository::find( $lead_id );
		if ( ! $lead ) {
			return false;
		}

		if ( $user_id > 0 && ! get_userdata( $user_id ) ) {
			return false;
		}

		$wpdb->update(
			Lead_Repository::table(),
			array( 'assigned_user' => $user_id ),
			array( 'id' => $lead_id ),
			array( '%d' ),
			array( '%d' )
		);

		$who = $user_id > 0 ? get_userdata( $user_id )->display_name : __( 'nobody', 'smart-client-contact-hub' );

		Activity_Service::log(
			$lead_id,
			'assigned',
			/* translators: %s: user display name. */
			sprintf( __( 'Assigned to %s', 'smart-client-contact-hub' ), $who ),
			'',
			array( 'user_id' => $user_id )
		);

		self::changed( $lead_id );

		/**
		 * Fires after a lead's assignee changes.
		 *
		 * @param int $lead_id Lead ID.
		 * @param int $user_id New assignee, 0 when cleared.
		 */
		do_action( 'scch_lead_assigned', $lead_id, $user_id );

		return true;
	}

	/**
	 * Set the money fields.
	 *
	 * @param int        $lead_id   Lead ID.
	 * @param float|null $estimated Estimated value, null to leave alone.
	 * @param float|null $revenue   Actual revenue, null to leave alone.
	 */
	public static function set_value( int $lead_id, ?float $estimated = null, ?float $revenue = null ): bool {
		global $wpdb;

		$lead = Lead_Repository::find( $lead_id );
		if ( ! $lead ) {
			return false;
		}

		$data    = array();
		$formats = array();

		if ( null !== $estimated ) {
			$data['estimated_value'] = max( 0, round( $estimated, 2 ) );
			$formats[]               = '%f';
		}
		if ( null !== $revenue ) {
			$data['actual_revenue'] = max( 0, round( $revenue, 2 ) );
			$formats[]              = '%f';
		}

		if ( ! $data ) {
			return true;
		}

		$wpdb->update( Lead_Repository::table(), $data, array( 'id' => $lead_id ), $formats, array( '%d' ) );

		Activity_Service::log( $lead_id, 'value', __( 'Value updated', 'smart-client-contact-hub' ), '', $data );

		// The estimated value feeds a scoring rule, so re-run it.
		self::rescore( $lead_id );
		self::changed( $lead_id );

		return true;
	}

	/**
	 * Recompute and store a lead's score.
	 *
	 * @param int $lead_id Lead ID.
	 * @return int The new score.
	 */
	public static function rescore( int $lead_id ): int {
		global $wpdb;

		$lead = Lead_Repository::find( $lead_id );
		if ( ! $lead ) {
			return 0;
		}

		$result = Lead_Scoring_Service::evaluate(
			array(
				'phone'           => $lead->phone,
				'email'           => $lead->email,
				'service'         => $lead->service,
				'message'         => $lead->message,
				'channel'         => $lead->channel,
				'utm_campaign'    => $lead->utm_campaign,
				'estimated_value' => $lead->estimated_value,
				'known_contact'   => self::has_earlier_lead( (string) $lead->email, $lead_id ),
			)
		);

		if ( (int) $lead->score !== $result['score'] ) {
			$wpdb->update(
				Lead_Repository::table(),
				array( 'score' => $result['score'] ),
				array( 'id' => $lead_id ),
				array( '%d' ),
				array( '%d' )
			);

			/**
			 * Fires after a lead's score is recalculated.
			 *
			 * @param int   $lead_id Lead ID.
			 * @param int   $score   New score, 0-100.
			 * @param array $matched Rule key => weight for matching rules.
			 */
			do_action( 'scch_lead_scored', $lead_id, $result['score'], $result['matched'] );
		}

		return $result['score'];
	}

	/**
	 * Add a timestamped note.
	 *
	 * @param int    $lead_id Lead ID.
	 * @param string $note    Note text.
	 */
	public static function add_note( int $lead_id, string $note ): int {
		$note = trim( sanitize_textarea_field( $note ) );

		if ( '' === $note || ! Lead_Repository::find( $lead_id ) ) {
			return 0;
		}

		$id = Activity_Service::log( $lead_id, 'note', __( 'Note added', 'smart-client-contact-hub' ), $note );

		self::changed( $lead_id );

		return $id;
	}

	/**
	 * Delete leads and everything attached to them.
	 *
	 * @param int[] $ids Lead IDs.
	 */
	public static function delete( array $ids ): int {
		$ids = array_filter( array_map( 'absint', $ids ) );

		if ( ! $ids ) {
			return 0;
		}

		Activity_Service::delete_for_leads( $ids );
		Followup_Service::delete_for_leads( $ids );

		$deleted = Lead_Repository::delete( $ids );

		Analytics_Service::flush();

		return $deleted;
	}

	/**
	 * Whether an earlier lead exists with the same email.
	 *
	 * @param string $email   Email address.
	 * @param int    $exclude Lead ID to ignore.
	 */
	public static function has_earlier_lead( string $email, int $exclude = 0 ): bool {
		global $wpdb;

		if ( ! is_email( $email ) ) {
			return false;
		}

		$table = Lead_Repository::table();

		return (bool) $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE email = %s AND id <> %d", $email, $exclude ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);
	}

	/**
	 * Housekeeping after any change: refresh reports and fire the generic hook.
	 *
	 * @param int $lead_id Lead ID.
	 */
	private static function changed( int $lead_id ): void {
		Analytics_Service::flush();

		/**
		 * Fires after any change to a lead.
		 *
		 * @param int $lead_id Lead ID.
		 */
		do_action( 'scch_lead_updated', $lead_id );
	}
}
