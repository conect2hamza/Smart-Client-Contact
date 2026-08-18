<?php
/**
 * Front-end AJAX endpoints.
 *
 * @package SCCH
 */

namespace SCCH;

defined( 'ABSPATH' ) || exit;

/**
 * Handles lead submission and CAPTCHA refresh over admin-ajax for both
 * logged-in and anonymous visitors. Every request is nonce-verified,
 * rate-limited, validated server-side, and sanitized before storage.
 */
class Ajax {

	/**
	 * Field ceilings, matched to the column widths declared in Activator.
	 *
	 * Values longer than the column are rejected with a field error. Left to
	 * the database they would either abort the INSERT under MySQL strict mode
	 * — losing the lead and showing the visitor a generic failure — or be
	 * silently truncated to something unusable.
	 */
	private const MAX_NAME  = 80;  // name VARCHAR(80).
	private const MAX_PHONE = 40;  // phone VARCHAR(40).
	private const MAX_EMAIL = 190; // email VARCHAR(190).

	/**
	 * Register hooks.
	 */
	public function register(): void {
		add_action( 'wp_ajax_scch_submit_lead', array( $this, 'submit_lead' ) );
		add_action( 'wp_ajax_nopriv_scch_submit_lead', array( $this, 'submit_lead' ) );
		add_action( 'wp_ajax_scch_refresh_captcha', array( $this, 'refresh_captcha' ) );
		add_action( 'wp_ajax_nopriv_scch_refresh_captcha', array( $this, 'refresh_captcha' ) );
	}

	/**
	 * Issue a fresh submission nonce and, when enabled, a CAPTCHA challenge.
	 *
	 * The widget markup ships with neither, because it is printed into pages
	 * that a full-page cache may serve to thousands of visitors: a token
	 * baked into cached HTML is single-use for the first visitor and broken
	 * for everyone after, and a nonce baked into cached HTML expires roughly
	 * a day later. Both are therefore fetched here, per visitor, when the
	 * form is actually opened.
	 *
	 * Deliberately not nonce-verified. The response contains nothing that is
	 * not already public to anyone who can load the page, and requiring the
	 * cached nonce here would reintroduce the very expiry problem this
	 * endpoint exists to solve. Abuse is bounded by the rate limiter, which
	 * also stops the transient writes this endpoint performs from being used
	 * to inflate the options table.
	 */
	public function refresh_captcha(): void {
		if ( ! Rate_Limiter::allowed( 'challenge' ) ) {
			wp_send_json_error(
				array( 'message' => __( 'Too many attempts. Please wait a few minutes and try again.', 'smart-client-contact-hub' ) ),
				429
			);
		}

		$payload = array( 'nonce' => wp_create_nonce( 'scch_frontend' ) );

		if ( Captcha::enabled() ) {
			$payload = array_merge( $payload, Captcha::generate() );
		}

		wp_send_json_success( $payload );
	}

	/**
	 * Validate and store a lead, then fire notifications.
	 */
	public function submit_lead(): void {
		check_ajax_referer( 'scch_frontend', 'nonce' );

		// The form channel can be switched off in Contact Settings. Honor that
		// here too, so "disabled" means the endpoint refuses rather than
		// merely hiding the button.
		if ( ! Channels::form_enabled() ) {
			wp_send_json_error(
				array( 'message' => __( 'This form is not accepting submissions.', 'smart-client-contact-hub' ) ),
				403
			);
		}

		if ( ! Rate_Limiter::allowed() ) {
			wp_send_json_error(
				array( 'message' => __( 'Too many attempts. Please wait a few minutes and try again.', 'smart-client-contact-hub' ) ),
				429
			);
		}

		// Honeypot: bots that fill the hidden field are silently accepted but discarded.
		if ( '' !== ( isset( $_POST['scch_website'] ) ? sanitize_text_field( wp_unslash( $_POST['scch_website'] ) ) : '' ) ) {
			wp_send_json_success( array( 'message' => Settings::get( 'scch_form', 'success_message' ) ) );
		}

		$fields = Settings::form_fields();
		$errors = array();
		$lead   = array(
			'name'    => '',
			'phone'   => '',
			'email'   => '',
			'service' => '',
			'message' => '',
		);

		// Name.
		if ( isset( $fields['name'] ) ) {
			$lead['name'] = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
			$length       = mb_strlen( $lead['name'] );
			if ( ! empty( $fields['name']['required'] ) && '' === $lead['name'] ) {
				$errors['name'] = __( 'Name is required.', 'smart-client-contact-hub' );
			} elseif ( '' !== $lead['name'] && ( $length < 3 || $length > self::MAX_NAME ) ) {
				$errors['name'] = __( 'Name must be between 3 and 80 characters.', 'smart-client-contact-hub' );
			}
		}

		// Phone.
		if ( isset( $fields['phone'] ) ) {
			$raw           = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
			$lead['phone'] = preg_replace( '/[^0-9+\-\s().]/', '', $raw );
			if ( ! empty( $fields['phone']['required'] ) && '' === trim( (string) $lead['phone'] ) ) {
				$errors['phone'] = __( 'Phone number is required.', 'smart-client-contact-hub' );
			} elseif ( '' !== $lead['phone'] && strlen( preg_replace( '/\D/', '', $lead['phone'] ) ) < 6 ) {
				$errors['phone'] = __( 'Please enter a valid phone number.', 'smart-client-contact-hub' );
			} elseif ( mb_strlen( $lead['phone'] ) > self::MAX_PHONE ) {
				$errors['phone'] = __( 'Please enter a valid phone number.', 'smart-client-contact-hub' );
			}
		}

		// Email.
		if ( isset( $fields['email'] ) ) {
			$lead['email'] = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
			if ( ! empty( $fields['email']['required'] ) && '' === $lead['email'] ) {
				$errors['email'] = __( 'Email address is required.', 'smart-client-contact-hub' );
			} elseif ( '' !== $lead['email'] && ( ! is_email( $lead['email'] ) || mb_strlen( $lead['email'] ) > self::MAX_EMAIL ) ) {
				$errors['email'] = __( 'Please enter a valid email address.', 'smart-client-contact-hub' );
			}
		}

		// Service — must be one of the configured services.
		if ( isset( $fields['service'] ) ) {
			$service_id = isset( $_POST['service'] ) ? sanitize_text_field( wp_unslash( $_POST['service'] ) ) : '';
			$match      = '';
			foreach ( Settings::services() as $service ) {
				if ( $service['id'] === $service_id ) {
					$match = $service['label'];
					break;
				}
			}
			$lead['service'] = $match;
			if ( ! empty( $fields['service']['required'] ) && '' === $match ) {
				$errors['service'] = __( 'Please select a service.', 'smart-client-contact-hub' );
			}
		}

		// Message.
		if ( isset( $fields['message'] ) ) {
			$lead['message'] = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';
			if ( ! empty( $fields['message']['required'] ) && '' === trim( $lead['message'] ) ) {
				$errors['message'] = __( 'Message is required.', 'smart-client-contact-hub' );
			} elseif ( mb_strlen( $lead['message'] ) > 1000 ) {
				$errors['message'] = __( 'Message must be 1000 characters or fewer.', 'smart-client-contact-hub' );
			}
		}

		// CAPTCHA — always regenerate on failure.
		if ( Captcha::enabled() ) {
			$token  = isset( $_POST['captcha_token'] ) ? sanitize_text_field( wp_unslash( $_POST['captcha_token'] ) ) : '';
			$answer = isset( $_POST['captcha_answer'] ) ? sanitize_text_field( wp_unslash( $_POST['captcha_answer'] ) ) : '';
			if ( ! Captcha::verify( $token, $answer ) ) {
				$errors['captcha'] = __( 'Incorrect answer. Please try the new question.', 'smart-client-contact-hub' );
			}
		}

		if ( $errors ) {
			$payload = array(
				'message' => Settings::get( 'scch_form', 'error_message' ),
				'errors'  => $errors,
			);
			if ( Captcha::enabled() ) {
				$payload['captcha'] = Captcha::generate();
			}
			wp_send_json_error( $payload, 422 );
		}

		$lead['ip_address'] = Rate_Limiter::client_ip();
		$lead['user_agent'] = isset( $_SERVER['HTTP_USER_AGENT'] )
			? substr( sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ), 0, 255 )
			: '';

		// Where this lead came from. Read once, from the page they submitted
		// on; the plugin does not track visitors across the site.
		$lead = array_merge( $lead, Attribution::from_request( wp_unslash( $_POST ) ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- sanitized field by field in Attribution.

		$lead['channel'] = 'form';

		// Score before insert so the stored row is complete from the start.
		$scoring       = Lead_Scoring_Service::evaluate(
			array_merge(
				$lead,
				array(
					'returning'     => ! empty( $_POST['returning'] ),
					'known_contact' => Lead_Service::has_earlier_lead( $lead['email'] ),
				)
			)
		);
		$lead['score'] = $scoring['score'];

		$lead_id = Lead_Repository::insert( $lead );

		if ( ! $lead_id ) {
			wp_send_json_error(
				array( 'message' => __( 'We could not save your request. Please try again.', 'smart-client-contact-hub' ) ),
				500
			);
		}

		/**
		 * Fires after a lead is stored, before notifications are sent.
		 *
		 * @param int   $lead_id Lead ID.
		 * @param array $lead    Lead data.
		 */
		do_action( 'scch_lead_created', $lead_id, $lead );

		Activity_Service::log(
			$lead_id,
			'created',
			sprintf(
				/* translators: %s: source label. */
				__( 'Lead captured from %s', 'smart-client-contact-hub' ),
				Attribution::label( $lead['source'] )
			),
			'',
			array(
				'score'   => $scoring['score'],
				'band'    => $scoring['band'],
				'matched' => $scoring['matched'],
			)
		);

		if ( $scoring['matched'] ) {
			/** This action is documented in includes/class-lead-service.php */
			do_action( 'scch_lead_scored', $lead_id, $scoring['score'], $scoring['matched'] );
		}

		Analytics_Service::flush();

		( new Email_Manager() )->send_lead_notifications( $lead_id, $lead );

		wp_send_json_success(
			array(
				'message'  => Settings::get( 'scch_form', 'success_message' ),
				'redirect' => esc_url_raw( (string) Settings::get( 'scch_form', 'redirect_url', '' ) ),
			)
		);
	}
}
