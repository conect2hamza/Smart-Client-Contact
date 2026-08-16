<?php
/**
 * Email composition and delivery via wp_mail().
 *
 * @package SCCH
 */

namespace SCCH;

defined( 'ABSPATH' ) || exit;

/**
 * Builds branded HTML emails from the admin-configurable templates and sends
 * them through wp_mail(). Because delivery goes through wp_mail(), any SMTP
 * provider configured at the WordPress level (Gmail, Brevo, Mailgun, SES,
 * SendGrid, Outlook, Postmark, …) is supported automatically.
 */
class Email_Manager {

	/**
	 * Last wp_mail failure message captured via the wp_mail_failed hook.
	 *
	 * @var string
	 */
	private string $last_error = '';

	/**
	 * Send both notification emails for a lead.
	 *
	 * @param int   $lead_id Lead ID.
	 * @param array $lead    Sanitized lead data.
	 */
	public function send_lead_notifications( int $lead_id, array $lead ): void {
		$email = Settings::group( 'scch_email' );
		$data  = $this->placeholders( $lead_id, $lead, $email );

		if ( ! empty( $email['admin_enabled'] ) && ! empty( $email['admin_recipients'] ) ) {
			$this->dispatch(
				$lead_id,
				'admin',
				$email['admin_recipients'],
				$this->replace( $email['admin_subject'], $data ),
				$this->render_html(
					$this->replace( $email['admin_heading'], $data ),
					$this->replace( $email['admin_body'], $data ),
					$this->replace( $email['admin_footer'], $data ),
					$email,
					array(
						'label' => $email['button_label'],
						'url'   => $data['{view_lead_link}'],
					)
				),
				$this->admin_headers( $email, $lead )
			);
		}

		if ( ! empty( $email['customer_enabled'] ) && is_email( $lead['email'] ) ) {
			$this->dispatch(
				$lead_id,
				'customer',
				$lead['email'],
				$this->replace( $email['customer_subject'], $data ),
				$this->render_html(
					$this->replace( $email['customer_heading'], $data ),
					$this->replace( $email['customer_body'], $data ),
					$this->replace( $email['customer_footer'], $data ),
					$email
				),
				$this->base_headers( $email )
			);
		}
	}

	/**
	 * Send a test email to the current administrator.
	 *
	 * @param string $recipient Target address.
	 * @return array{sent:bool,error:string}
	 */
	public function send_test( string $recipient ): array {
		$email = Settings::group( 'scch_email' );
		$lead  = array(
			'name'    => 'Test Customer',
			'phone'   => '+1 555 000 1234',
			'email'   => $recipient,
			'service' => 'Test Service',
			'message' => 'This is a test email from Smart Client Contact Hub.',
		);
		$data  = $this->placeholders( 0, $lead, $email );

		$sent = $this->dispatch(
			0,
			'test',
			$recipient,
			'[Test] ' . $this->replace( $email['admin_subject'], $data ),
			$this->render_html(
				$this->replace( $email['admin_heading'], $data ),
				$this->replace( $email['admin_body'], $data ),
				$this->replace( $email['admin_footer'], $data ),
				$email
			),
			$this->base_headers( $email )
		);

		return array(
			'sent'  => $sent,
			'error' => $this->last_error,
		);
	}

	/**
	 * Re-send a previously logged email using current templates and lead data.
	 *
	 * @param int $log_id Email log row ID.
	 */
	public function resend( int $log_id ): bool {
		$log = Email_Log_Repository::find( $log_id );
		if ( ! $log ) {
			return false;
		}

		$lead_row = Lead_Repository::find( (int) $log->lead_id );
		if ( ! $lead_row ) {
			// Test emails have no lead; just resend a fresh test.
			$result = $this->send_test( $log->recipient );
			return $result['sent'];
		}

		$lead = array(
			'name'    => $lead_row->name,
			'phone'   => $lead_row->phone,
			'email'   => $lead_row->email,
			'service' => $lead_row->service,
			'message' => $lead_row->message,
			'date'    => $lead_row->submission_date,
		);

		$email = Settings::group( 'scch_email' );
		$data  = $this->placeholders( (int) $lead_row->id, $lead, $email );

		if ( 'customer' === $log->type ) {
			return $this->dispatch(
				(int) $lead_row->id,
				'customer',
				$lead_row->email,
				$this->replace( $email['customer_subject'], $data ),
				$this->render_html( $this->replace( $email['customer_heading'], $data ), $this->replace( $email['customer_body'], $data ), $this->replace( $email['customer_footer'], $data ), $email ),
				$this->base_headers( $email )
			);
		}

		return $this->dispatch(
			(int) $lead_row->id,
			'admin',
			$log->recipient,
			$this->replace( $email['admin_subject'], $data ),
			$this->render_html(
				$this->replace( $email['admin_heading'], $data ),
				$this->replace( $email['admin_body'], $data ),
				$this->replace( $email['admin_footer'], $data ),
				$email,
				array( 'label' => $email['button_label'], 'url' => $data['{view_lead_link}'] )
			),
			$this->admin_headers( $email, $lead )
		);
	}

	/**
	 * Placeholder map for template substitution.
	 *
	 * @param int   $lead_id Lead ID (0 for tests).
	 * @param array $lead    Lead data.
	 * @param array $email   Email settings group.
	 * @return array<string,string>
	 */
	private function placeholders( int $lead_id, array $lead, array $email ): array {
		$view_link = $lead_id
			? admin_url( 'admin.php?page=scch-leads&lead=' . $lead_id )
			: admin_url( 'admin.php?page=scch-leads' );

		return array(
			'{customer_name}'   => $lead['name'] ?? '',
			'{customer_email}'  => $lead['email'] ?? '',
			'{customer_phone}'  => $lead['phone'] ?? '',
			'{service}'         => $lead['service'] ?? '',
			'{message}'         => $lead['message'] ?? '',
			'{submission_date}' => $lead['date'] ?? current_time( 'mysql' ),
			'{website_name}'    => get_bloginfo( 'name' ),
			'{business_name}'   => $email['business_name'],
			'{business_contact}' => $email['business_contact'],
			'{response_time}'   => $email['response_time'],
			'{view_lead_link}'  => $view_link,
		);
	}

	/**
	 * Replace placeholders in a template string.
	 *
	 * @param string $template Template text.
	 * @param array  $data     Placeholder map.
	 */
	private function replace( string $template, array $data ): string {
		return strtr( $template, $data );
	}

	/**
	 * Render the branded HTML wrapper around a plain-text body.
	 *
	 * @param string     $heading Heading text.
	 * @param string     $body    Body text (newlines become paragraphs).
	 * @param string     $footer  Footer text.
	 * @param array      $email   Email settings group.
	 * @param array|null $button  Optional {label,url}.
	 */
	private function render_html( string $heading, string $body, string $footer, array $email, ?array $button = null ): string {
		$template = SCCH_PATH . 'templates/email.php';

		ob_start();
		$context = array(
			'heading'     => $heading,
			'body'        => $body,
			'footer'      => $footer,
			'logo_url'    => $email['logo_url'],
			'brand_color' => $email['brand_color'],
			'signature'   => $email['signature'],
			'button'      => $button,
		);
		include $template;
		return (string) ob_get_clean();
	}

	/**
	 * Base headers: content type, sender, and configured CC/BCC.
	 *
	 * @param array $email Email settings group.
	 * @return string[]
	 */
	private function base_headers( array $email ): array {
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );

		if ( is_email( $email['sender_email'] ) ) {
			$name      = $email['sender_name'] ? $email['sender_name'] : get_bloginfo( 'name' );
			$headers[] = sprintf( 'From: %s <%s>', $name, $email['sender_email'] );
		}

		return $headers;
	}

	/**
	 * Headers for the administrator notification: CC, BCC, Reply-To customer.
	 *
	 * @param array $email Email settings group.
	 * @param array $lead  Lead data.
	 * @return string[]
	 */
	private function admin_headers( array $email, array $lead ): array {
		$headers = $this->base_headers( $email );

		foreach ( explode( ',', (string) $email['cc'] ) as $cc ) {
			$cc = trim( $cc );
			if ( is_email( $cc ) ) {
				$headers[] = 'Cc: ' . $cc;
			}
		}
		foreach ( explode( ',', (string) $email['bcc'] ) as $bcc ) {
			$bcc = trim( $bcc );
			if ( is_email( $bcc ) ) {
				$headers[] = 'Bcc: ' . $bcc;
			}
		}

		if ( ! empty( $email['reply_to_customer'] ) && is_email( $lead['email'] ?? '' ) ) {
			$headers[] = sprintf( 'Reply-To: %s <%s>', $lead['name'] ?? '', $lead['email'] );
		}

		return $headers;
	}

	/**
	 * Send and log a single email.
	 *
	 * @param int      $lead_id   Lead ID.
	 * @param string   $type      Log type.
	 * @param string   $recipient Recipient(s), comma-separated.
	 * @param string   $subject   Subject.
	 * @param string   $html      HTML body.
	 * @param string[] $headers   Headers.
	 */
	private function dispatch( int $lead_id, string $type, string $recipient, string $subject, string $html, array $headers ): bool {
		$this->last_error = '';

		$capture = function ( $error ) {
			if ( is_wp_error( $error ) ) {
				$this->last_error = $error->get_error_message();
			}
		};
		add_action( 'wp_mail_failed', $capture );

		$recipients = array_filter( array_map( 'trim', explode( ',', $recipient ) ), 'is_email' );
		$sent       = ! empty( $recipients ) && wp_mail( $recipients, $subject, $html, $headers );

		remove_action( 'wp_mail_failed', $capture );

		Email_Log_Repository::log( $lead_id, implode( ', ', $recipients ), $subject, $type, $sent, $this->last_error );

		return $sent;
	}
}
