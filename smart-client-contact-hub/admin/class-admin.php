<?php
/**
 * Admin dashboard: menus, settings persistence, lead actions, admin AJAX.
 *
 * @package SCCH
 */

namespace SCCH\Admin;

use SCCH\Activity_Service;
use SCCH\Analytics_Service;
use SCCH\Channels;
use SCCH\Design_Tokens;
use SCCH\Icons;
use SCCH\Email_Log_Repository;
use SCCH\Email_Manager;
use SCCH\Followup_Service;
use SCCH\Lead_Repository;
use SCCH\Lead_Scoring_Service;
use SCCH\Lead_Service;
use SCCH\Pipeline_Service;
use SCCH\Settings;
use SCCH\UI;

defined( 'ABSPATH' ) || exit;

/**
 * Registers every admin screen and processes all admin-side writes. Every
 * write path is capability-checked and nonce-verified.
 */
class Admin {

	/**
	 * Capability required for every plugin screen and action.
	 */
	public const CAP = 'manage_options';

	/**
	 * Slug of the top-level menu.
	 */
	public const MENU = 'scch-dashboard';

	/**
	 * Screen hook suffixes returned by add_submenu_page(), used to scope assets.
	 *
	 * @var string[]
	 */
	private array $hooks = array();

	/**
	 * Screen hook for the Leads submenu, or '' when the current user cannot
	 * see it. add_submenu_page() returns false without the capability, and
	 * admin_menu fires for every logged-in user who reaches wp-admin — so
	 * this must never be assumed present.
	 *
	 * @var string
	 */
	private string $leads_hook = '';

	/**
	 * Register hooks.
	 */
	public function register(): void {
		add_action( 'admin_menu', array( $this, 'menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
		add_action( 'admin_post_scch_save_settings', array( $this, 'save_settings' ) );
		add_action( 'admin_post_scch_save_services', array( $this, 'save_services' ) );
		add_action( 'admin_post_scch_save_channels', array( $this, 'save_channels' ) );
		add_action( 'admin_post_scch_reset_appearance', array( $this, 'reset_appearance' ) );
		add_action( 'admin_post_scch_lead_action', array( $this, 'handle_lead_action' ) );
		add_action( 'wp_ajax_scch_test_email', array( $this, 'ajax_test_email' ) );
		add_action( 'wp_ajax_scch_resend_email', array( $this, 'ajax_resend_email' ) );
		add_action( 'wp_ajax_scch_delete_email_log', array( $this, 'ajax_delete_email_log' ) );
		add_action( 'wp_ajax_scch_crm_action', array( $this, 'ajax_crm_action' ) );
		add_action( 'admin_post_scch_save_scoring', array( $this, 'save_scoring' ) );
		add_action( 'admin_post_scch_clear_logs', array( $this, 'clear_logs' ) );
		add_filter( 'plugin_action_links_' . SCCH_BASENAME, array( $this, 'action_links' ) );
	}

	/**
	 * Add a Settings shortcut on the Plugins screen row.
	 *
	 * @param string[] $links Existing action links.
	 * @return string[]
	 */
	public function action_links( array $links ): array {
		array_unshift(
			$links,
			sprintf( '<a href="%s">%s</a>', esc_url( admin_url( 'admin.php?page=' . self::MENU ) ), esc_html__( 'Settings', 'smart-client-contact-hub' ) )
		);
		return $links;
	}

	/**
	 * Register the top-level menu and all submenus.
	 */
	public function menu(): void {
		$icon = 'data:image/svg+xml;base64,' . base64_encode( '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#a7aaad"><path d="M12 3C6.5 3 2 6.9 2 11.7c0 2.6 1.3 4.9 3.4 6.5-.2 1.3-.8 2.4-1.7 3.3 1.9-.1 3.6-.8 4.9-1.9 1.1.3 2.2.5 3.4.5 5.5 0 10-3.9 10-8.7S17.5 3 12 3z"/></svg>' );

		add_menu_page(
			__( 'Smart Client Contact Hub', 'smart-client-contact-hub' ),
			__( 'Contact Hub', 'smart-client-contact-hub' ),
			self::CAP,
			self::MENU,
			array( $this, 'render_dashboard' ),
			$icon,
			58
		);

		/*
		 * Grouped into the product's five areas rather than one flat list of
		 * screens. Separators are non-linking headings, so the grouping reads
		 * without WordPress needing nested menus.
		 */
		$pages = array(
			array( self::MENU, __( 'Dashboard', 'smart-client-contact-hub' ), array( $this, 'render_dashboard' ) ),

			array( '', __( 'CRM', 'smart-client-contact-hub' ), null ),
			array( 'scch-leads', __( 'Leads', 'smart-client-contact-hub' ), array( $this, 'render_leads' ) ),
			array( 'scch-pipeline', __( 'Pipeline', 'smart-client-contact-hub' ), $this->view_renderer( 'pipeline' ) ),
			array( 'scch-followups', __( 'Follow-ups', 'smart-client-contact-hub' ), $this->view_renderer( 'followups' ) ),

			array( '', __( 'Contact Hub', 'smart-client-contact-hub' ), null ),
			array( 'scch-appearance', __( 'Widget', 'smart-client-contact-hub' ), $this->view_renderer( 'appearance' ) ),
			array( 'scch-channels', __( 'Channels', 'smart-client-contact-hub' ), $this->view_renderer( 'channels' ) ),
			array( 'scch-form-builder', __( 'Forms', 'smart-client-contact-hub' ), $this->view_renderer( 'form-builder' ) ),
			array( 'scch-services', __( 'Services', 'smart-client-contact-hub' ), $this->view_renderer( 'services' ) ),
			array( 'scch-triggers', __( 'Triggers', 'smart-client-contact-hub' ), $this->view_renderer( 'triggers' ) ),
			array( 'scch-contact', __( 'Panel Wording', 'smart-client-contact-hub' ), $this->view_renderer( 'contact' ) ),

			array( '', __( 'Analytics', 'smart-client-contact-hub' ), null ),
			array( 'scch-analytics', __( 'Reports', 'smart-client-contact-hub' ), $this->view_renderer( 'analytics' ) ),

			array( '', __( 'Settings', 'smart-client-contact-hub' ), null ),
			array( 'scch-notifications', __( 'Notifications', 'smart-client-contact-hub' ), $this->view_renderer( 'notifications' ) ),
			array( 'scch-email-templates', __( 'Email Templates', 'smart-client-contact-hub' ), $this->view_renderer( 'email-templates' ) ),
			array( 'scch-scoring', __( 'Lead Scoring', 'smart-client-contact-hub' ), $this->view_renderer( 'scoring' ) ),
			array( 'scch-captcha', __( 'Security', 'smart-client-contact-hub' ), $this->view_renderer( 'captcha' ) ),
			array( 'scch-export', __( 'Export', 'smart-client-contact-hub' ), $this->view_renderer( 'export' ) ),
			array( 'scch-logs', __( 'Logs', 'smart-client-contact-hub' ), $this->view_renderer( 'logs' ) ),
			array( 'scch-help', __( 'Help', 'smart-client-contact-hub' ), $this->view_renderer( 'help' ) ),
		);

		foreach ( $pages as $page ) {
			// A separator: a disabled heading that groups the entries under it.
			if ( '' === $page[0] ) {
				$this->separator( $page[1] );
				continue;
			}

			$hook = add_submenu_page( self::MENU, $page[1] . ' — Smart Client Contact Hub', $page[1], self::CAP, $page[0], $page[2] );
			if ( ! $hook ) {
				continue;
			}
			$this->hooks[] = $hook;
			if ( 'scch-leads' === $page[0] ) {
				$this->leads_hook = $hook;
			}
		}

		// The leads screen needs WP_List_Table screen options.
		if ( '' !== $this->leads_hook ) {
			add_action( 'load-' . $this->leads_hook, array( $this, 'leads_screen_options' ) );
		}
	}

	/**
	 * Add a non-linking heading to the submenu.
	 *
	 * WordPress has no first-class separator, so this registers an item with
	 * an impossible capability: it renders as a dimmed label and can never be
	 * clicked or reached directly.
	 *
	 * @param string $label Heading text.
	 */
	private function separator( string $label ): void {
		global $submenu;

		if ( ! current_user_can( self::CAP ) ) {
			return;
		}

		$submenu[ self::MENU ][] = array( // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			'<span class="scch-menu-sep">' . esc_html( $label ) . '</span>',
			'do_not_allow',
			'#scch-sep-' . sanitize_key( $label ),
		);
	}

	/**
	 * Per-page rows screen option on the Leads screen.
	 */
	public function leads_screen_options(): void {
		add_screen_option(
			'per_page',
			array(
				'label'   => __( 'Leads per page', 'smart-client-contact-hub' ),
				'default' => 20,
				'option'  => 'scch_leads_per_page',
			)
		);
	}

	/**
	 * Enqueue admin CSS/JS only on plugin screens.
	 *
	 * @param string $hook Current screen hook.
	 */
	public function assets( string $hook ): void {
		if ( ! in_array( $hook, $this->hooks, true ) && 'toplevel_page_' . self::MENU !== $hook ) {
			return;
		}

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_media();
		wp_enqueue_style( 'scch-admin-ui', SCCH_URL . 'assets/css/admin-ui.css', array(), SCCH_VERSION );
		wp_enqueue_style( 'scch-admin', SCCH_URL . 'assets/css/admin.css', array( 'scch-admin-ui' ), SCCH_VERSION );
		wp_enqueue_script( 'scch-admin', SCCH_URL . 'assets/js/admin.js', array( 'jquery', 'wp-color-picker' ), SCCH_VERSION, true );
		wp_localize_script(
			'scch-admin',
			'scchAdmin',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'scch_admin' ),
				'i18n'    => array(
					'sending'      => __( 'Sending…', 'smart-client-contact-hub' ),
					'sent'         => __( 'Email sent successfully.', 'smart-client-contact-hub' ),
					'failed'       => __( 'Sending failed:', 'smart-client-contact-hub' ),
					'resent'       => __( 'Email re-sent.', 'smart-client-contact-hub' ),
					'confirmDel'   => __( 'Remove this service?', 'smart-client-contact-hub' ),
					'confirmDeleteChannel' => __( 'Remove this channel? Switch it off instead if you only want to hide it.', 'smart-client-contact-hub' ),
					'confirmDeleteLog' => __( 'Delete this log entry? This cannot be undone.', 'smart-client-contact-hub' ),
					'deleting'     => __( 'Deleting…', 'smart-client-contact-hub' ),
					'deleteFailed' => __( 'Delete failed:', 'smart-client-contact-hub' ),
					'chooseImage'  => __( 'Choose image', 'smart-client-contact-hub' ),
					'useThisImage' => __( 'Use this image', 'smart-client-contact-hub' ),
				),
			)
		);
	}

	/**
	 * Returns a closure that renders a named view.
	 *
	 * @param string $view View file basename without extension.
	 */
	private function view_renderer( string $view ): callable {
		return function () use ( $view ): void {
			$this->render_view( $view );
		};
	}

	/**
	 * Include a view file, exposing $admin to it.
	 *
	 * @param string $view View file basename.
	 * @param array  $data Extra variables for the view.
	 */
	public function render_view( string $view, array $data = array() ): void {
		if ( ! current_user_can( self::CAP ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'smart-client-contact-hub' ) );
		}

		$file = SCCH_PATH . 'admin/views/' . $view . '.php';
		if ( ! is_readable( $file ) ) {
			return;
		}

		$admin = $this;
		// phpcs:ignore WordPress.PHP.DontExtract.extract_extract -- controlled, view-scoped data.
		extract( $data, EXTR_SKIP );
		include $file;
	}

	/**
	 * Dashboard screen.
	 */
	public function render_dashboard(): void {
		$this->render_view(
			'dashboard',
			array(
				'stats'  => Lead_Repository::stats(),
				'recent' => Lead_Repository::query( array( 'per_page' => 5 ) )['items'],
			)
		);
	}

	/**
	 * Leads screen: list table or single lead detail.
	 */
	public function render_leads(): void {
		$lead_id = isset( $_GET['lead'] ) ? absint( $_GET['lead'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only view routing.

		if ( $lead_id ) {
			$lead = Lead_Repository::find( $lead_id );
			$this->render_view( 'lead-detail', array( 'lead' => $lead ) );
			return;
		}

		$table = new Leads_List_Table();
		$table->prepare_items();
		$this->render_view( 'leads', array( 'table' => $table ) );
	}

	/**
	 * Print the shared settings-saved notice when redirected back.
	 */
	public static function maybe_notice(): void {
		if ( ! isset( $_GET['scch_notice'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display only.
			return;
		}
		$notice = sanitize_key( wp_unslash( $_GET['scch_notice'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$map = array(
			'saved'          => array( 'success', __( 'Settings saved.', 'smart-client-contact-hub' ) ),
			'lead_updated'   => array( 'success', __( 'Lead updated.', 'smart-client-contact-hub' ) ),
			'lead_deleted'   => array( 'success', __( 'Lead deleted.', 'smart-client-contact-hub' ) ),
			'logs_cleared'   => array( 'success', __( 'All email logs deleted.', 'smart-client-contact-hub' ) ),
			'appearance_reset' => array( 'success', __( 'Appearance settings restored to their defaults.', 'smart-client-contact-hub' ) ),
			'error'          => array( 'error', __( 'The request could not be completed.', 'smart-client-contact-hub' ) ),
		);

		if ( isset( $map[ $notice ] ) ) {
			printf(
				'<div class="notice notice-%1$s is-dismissible"><p>%2$s</p></div>',
				esc_attr( $map[ $notice ][0] ),
				esc_html( $map[ $notice ][1] )
			);
		}
	}

	/**
	 * Persist one settings group posted from a settings view.
	 */
	public function save_settings(): void {
		if ( ! current_user_can( self::CAP ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'smart-client-contact-hub' ) );
		}
		check_admin_referer( 'scch_save_settings' );

		$group = isset( $_POST['scch_group'] ) ? sanitize_key( wp_unslash( $_POST['scch_group'] ) ) : '';
		$known = array_keys( Settings::defaults() );

		if ( ! in_array( $group, $known, true ) ) {
			$this->redirect_back( 'error' );
		}

		$raw   = isset( $_POST[ $group ] ) && is_array( $_POST[ $group ] ) ? wp_unslash( $_POST[ $group ] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- sanitized per group below.
		$clean = $this->sanitize_group( $group, $raw );

		update_option( $group, $clean );
		Settings::flush_cache();

		$this->redirect_back( 'saved' );
	}

	/**
	 * Persist the services list (create/edit/delete/sort in one atomic save).
	 */
	public function save_services(): void {
		if ( ! current_user_can( self::CAP ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'smart-client-contact-hub' ) );
		}
		check_admin_referer( 'scch_save_services' );

		$rows  = isset( $_POST['services'] ) && is_array( $_POST['services'] ) ? wp_unslash( $_POST['services'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- sanitized below.
		$clean = array();
		$seen  = array();

		foreach ( $rows as $row ) {
			$label = sanitize_text_field( $row['label'] ?? '' );
			if ( '' === $label ) {
				continue;
			}
			$id = sanitize_title( $row['id'] ?? '' );
			if ( '' === $id ) {
				$id = sanitize_title( $label );
			}
			while ( in_array( $id, $seen, true ) ) {
				$id .= '-' . wp_rand( 10, 99 );
			}
			$seen[]  = $id;
			$clean[] = array(
				'id'    => $id,
				'label' => $label,
			);
		}

		update_option( 'scch_services', $clean );
		Settings::flush_cache();

		$this->redirect_back( 'saved' );
	}

	/**
	 * Persist the contact channel list. Row order is the submitted order.
	 */
	public function save_channels(): void {
		if ( ! current_user_can( self::CAP ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'smart-client-contact-hub' ) );
		}
		check_admin_referer( 'scch_save_channels' );

		$rows = isset( $_POST['channels'] ) && is_array( $_POST['channels'] ) ? wp_unslash( $_POST['channels'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- sanitized in Channels::sanitize().

		update_option( Channels::OPTION, Channels::sanitize( $rows ) );
		Settings::flush_cache();

		$this->redirect_back( 'saved' );
	}

	/**
	 * Persist lead scoring rules.
	 */
	public function save_scoring(): void {
		if ( ! current_user_can( self::CAP ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'smart-client-contact-hub' ) );
		}
		check_admin_referer( 'scch_save_scoring' );

		$rules = isset( $_POST['rules'] ) && is_array( $_POST['rules'] ) ? wp_unslash( $_POST['rules'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- sanitized in the service.

		Lead_Scoring_Service::save( $rules );
		Analytics_Service::flush();

		$this->redirect_back( 'saved' );
	}

	/**
	 * Every CRM mutation, behind one capability check and one nonce.
	 *
	 * Routing by a whitelisted task keeps the surface small: an unknown task
	 * is refused rather than falling through to anything.
	 */
	public function ajax_crm_action(): void {
		check_ajax_referer( 'scch_admin', 'nonce' );

		if ( ! current_user_can( self::CAP ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'smart-client-contact-hub' ) ), 403 );
		}

		$task    = isset( $_POST['task'] ) ? sanitize_key( wp_unslash( $_POST['task'] ) ) : '';
		$lead_id = isset( $_POST['lead_id'] ) ? absint( $_POST['lead_id'] ) : 0;

		$lead_tasks = array( 'status', 'assign', 'note', 'value', 'followup', 'rescore' );

		if ( in_array( $task, $lead_tasks, true ) && ! Lead_Repository::find( $lead_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Lead not found.', 'smart-client-contact-hub' ) ), 404 );
		}

		switch ( $task ) {
			case 'status':
				$status = isset( $_POST['status'] ) ? sanitize_key( wp_unslash( $_POST['status'] ) ) : '';

				if ( ! Lead_Service::change_status( $lead_id, $status ) ) {
					wp_send_json_error( array( 'message' => __( 'That stage is not valid.', 'smart-client-contact-hub' ) ), 400 );
				}

				wp_send_json_success(
					array(
						'badge'   => UI::stage_badge( $status ),
						'stage'   => $status,
						'message' => __( 'Stage updated', 'smart-client-contact-hub' ),
					)
				);
				break;

			case 'assign':
				$user_id = isset( $_POST['user_id'] ) ? absint( $_POST['user_id'] ) : 0;

				if ( ! Lead_Service::assign( $lead_id, $user_id ) ) {
					wp_send_json_error( array( 'message' => __( 'That user does not exist.', 'smart-client-contact-hub' ) ), 400 );
				}

				wp_send_json_success( array( 'message' => __( 'Lead assigned', 'smart-client-contact-hub' ) ) );
				break;

			case 'note':
				$note = isset( $_POST['note'] ) ? wp_unslash( $_POST['note'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- sanitized in the service.

				if ( ! Lead_Service::add_note( $lead_id, $note ) ) {
					wp_send_json_error( array( 'message' => __( 'Write something first.', 'smart-client-contact-hub' ) ), 400 );
				}

				wp_send_json_success( array( 'message' => __( 'Note added', 'smart-client-contact-hub' ), 'reload' => true ) );
				break;

			case 'value':
				$estimated = isset( $_POST['estimated_value'] ) ? (float) wp_unslash( $_POST['estimated_value'] ) : null;
				$revenue   = isset( $_POST['actual_revenue'] ) ? (float) wp_unslash( $_POST['actual_revenue'] ) : null;

				Lead_Service::set_value( $lead_id, $estimated, $revenue );

				wp_send_json_success( array( 'message' => __( 'Changes saved', 'smart-client-contact-hub' ) ) );
				break;

			case 'followup':
				$id = Followup_Service::create(
					array(
						'lead_id'       => $lead_id,
						'title'         => isset( $_POST['title'] ) ? wp_unslash( $_POST['title'] ) : '', // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- sanitized in the service.
						'notes'         => isset( $_POST['notes'] ) ? wp_unslash( $_POST['notes'] ) : '', // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- sanitized in the service.
						'due_at'        => isset( $_POST['due_at'] ) ? sanitize_text_field( wp_unslash( $_POST['due_at'] ) ) : '',
						'priority'      => isset( $_POST['priority'] ) ? sanitize_key( wp_unslash( $_POST['priority'] ) ) : 'normal',
						'assigned_user' => isset( $_POST['assigned_user'] ) ? absint( $_POST['assigned_user'] ) : 0,
					)
				);

				if ( ! $id ) {
					wp_send_json_error( array( 'message' => __( 'A title and a due date are both required.', 'smart-client-contact-hub' ) ), 400 );
				}

				wp_send_json_success( array( 'message' => __( 'Follow-up created', 'smart-client-contact-hub' ), 'reload' => true ) );
				break;

			case 'rescore':
				$score = Lead_Service::rescore( $lead_id );
				wp_send_json_success( array( 'message' => __( 'Changes saved', 'smart-client-contact-hub' ), 'score' => $score, 'reload' => true ) );
				break;

			case 'followup_done':
			case 'followup_delete':
				$followup_id = isset( $_POST['followup_id'] ) ? absint( $_POST['followup_id'] ) : 0;

				if ( ! Followup_Service::find( $followup_id ) ) {
					wp_send_json_error( array( 'message' => __( 'Follow-up not found.', 'smart-client-contact-hub' ) ), 404 );
				}

				$done = 'followup_done' === $task
					? Followup_Service::set_complete( $followup_id, true )
					: Followup_Service::delete( $followup_id );

				if ( ! $done ) {
					wp_send_json_error( array( 'message' => __( 'That did not work. Please try again.', 'smart-client-contact-hub' ) ), 500 );
				}

				wp_send_json_success(
					array(
						'message' => 'followup_done' === $task
							? __( 'Follow-up completed', 'smart-client-contact-hub' )
							: __( 'Changes saved', 'smart-client-contact-hub' ),
					)
				);
				break;
		}

		wp_send_json_error( array( 'message' => __( 'Unknown action.', 'smart-client-contact-hub' ) ), 400 );
	}

	/**
	 * Restore every Appearance value to its shipped default.
	 */
	public function reset_appearance(): void {
		if ( ! current_user_can( self::CAP ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'smart-client-contact-hub' ) );
		}
		check_admin_referer( 'scch_reset_appearance' );

		update_option( 'scch_appearance', Design_Tokens::defaults() );
		Settings::flush_cache();

		wp_safe_redirect( add_query_arg( 'scch_notice', 'appearance_reset', admin_url( 'admin.php?page=scch-appearance' ) ) );
		exit;
	}

	/**
	 * Single-lead actions from the detail screen: status change or delete.
	 */
	public function handle_lead_action(): void {
		if ( ! current_user_can( self::CAP ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'smart-client-contact-hub' ) );
		}
		check_admin_referer( 'scch_lead_action' );

		$lead_id = isset( $_POST['lead_id'] ) ? absint( $_POST['lead_id'] ) : 0;
		$task    = isset( $_POST['task'] ) ? sanitize_key( wp_unslash( $_POST['task'] ) ) : '';

		if ( ! $lead_id ) {
			$this->redirect_back( 'error' );
		}

		if ( 'delete' === $task ) {
			Lead_Service::delete( array( $lead_id ) );
			wp_safe_redirect( add_query_arg( 'scch_notice', 'lead_deleted', admin_url( 'admin.php?page=scch-leads' ) ) );
			exit;
		}

		if ( 'status' === $task ) {
			$status = isset( $_POST['status'] ) ? sanitize_key( wp_unslash( $_POST['status'] ) ) : '';
			Lead_Service::change_status( $lead_id, $status );
			wp_safe_redirect( add_query_arg( array( 'page' => 'scch-leads', 'lead' => $lead_id, 'scch_notice' => 'lead_updated' ), admin_url( 'admin.php' ) ) );
			exit;
		}

		$this->redirect_back( 'error' );
	}

	/**
	 * AJAX: send a test email to the given address.
	 */
	public function ajax_test_email(): void {
		check_ajax_referer( 'scch_admin', 'nonce' );
		if ( ! current_user_can( self::CAP ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'smart-client-contact-hub' ) ), 403 );
		}

		$recipient = isset( $_POST['recipient'] ) ? sanitize_email( wp_unslash( $_POST['recipient'] ) ) : '';
		if ( ! is_email( $recipient ) ) {
			wp_send_json_error( array( 'message' => __( 'Please enter a valid email address.', 'smart-client-contact-hub' ) ), 400 );
		}

		$result = ( new Email_Manager() )->send_test( $recipient );

		if ( $result['sent'] ) {
			wp_send_json_success();
		}

		wp_send_json_error(
			array( 'message' => $result['error'] ? $result['error'] : __( 'wp_mail() returned false. Check your SMTP configuration.', 'smart-client-contact-hub' ) ),
			500
		);
	}

	/**
	 * AJAX: resend a logged email.
	 */
	public function ajax_resend_email(): void {
		check_ajax_referer( 'scch_admin', 'nonce' );
		if ( ! current_user_can( self::CAP ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'smart-client-contact-hub' ) ), 403 );
		}

		$log_id = isset( $_POST['log_id'] ) ? absint( $_POST['log_id'] ) : 0;
		if ( ! $log_id || ! Email_Log_Repository::find( $log_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Log entry not found.', 'smart-client-contact-hub' ) ), 404 );
		}

		if ( ( new Email_Manager() )->resend( $log_id ) ) {
			wp_send_json_success();
		}

		wp_send_json_error( array( 'message' => __( 'Resend failed. Check the newest log entry for details.', 'smart-client-contact-hub' ) ), 500 );
	}

	/**
	 * AJAX: delete a single logged email entry.
	 */
	public function ajax_delete_email_log(): void {
		check_ajax_referer( 'scch_admin', 'nonce' );
		if ( ! current_user_can( self::CAP ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'smart-client-contact-hub' ) ), 403 );
		}

		$log_id = isset( $_POST['log_id'] ) ? absint( $_POST['log_id'] ) : 0;
		if ( ! $log_id || ! Email_Log_Repository::find( $log_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Log entry not found.', 'smart-client-contact-hub' ) ), 404 );
		}

		if ( Email_Log_Repository::delete( $log_id ) ) {
			wp_send_json_success();
		}

		wp_send_json_error( array( 'message' => __( 'Could not delete this log entry.', 'smart-client-contact-hub' ) ), 500 );
	}

	/**
	 * Delete every email log entry.
	 */
	public function clear_logs(): void {
		if ( ! current_user_can( self::CAP ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'smart-client-contact-hub' ) );
		}
		check_admin_referer( 'scch_clear_logs' );

		Email_Log_Repository::delete_all();

		wp_safe_redirect( add_query_arg( 'scch_notice', 'logs_cleared', admin_url( 'admin.php?page=scch-logs' ) ) );
		exit;
	}

	/**
	 * Redirect back to the referring settings page with a notice code.
	 *
	 * @param string $notice Notice key.
	 */
	private function redirect_back( string $notice ): void {
		$referer = wp_get_referer();
		$target  = $referer ? $referer : admin_url( 'admin.php?page=' . self::MENU );
		wp_safe_redirect( add_query_arg( 'scch_notice', $notice, remove_query_arg( 'scch_notice', $target ) ) );
		exit;
	}

	/**
	 * Sanitize a posted settings group against its defaults.
	 *
	 * @param string $group Option group name.
	 * @param array  $raw   Unslashed raw input.
	 * @return array Clean values merged over defaults.
	 */
	private function sanitize_group( string $group, array $raw ): array {
		$current = Settings::group( $group );

		switch ( $group ) {
			case 'scch_appearance':
				return $this->sanitize_tokens( $raw );

			case 'scch_contact':
				// Merged per key rather than rebuilt. Since 1.0.5 the channel
				// fields live on the Channels screen and are not posted from
				// here; rebuilding would wipe the values the one-time channel
				// migration reads.
				$clean = $current;

				foreach ( array( 'panel_title', 'panel_intro', 'cta_strategy', 'cta_call', 'cta_text', 'sms_body' ) as $key ) {
					if ( array_key_exists( $key, $raw ) ) {
						$clean[ $key ] = sanitize_text_field( $raw[ $key ] );
					}
				}

				foreach ( array( 'phone_number', 'sms_number' ) as $key ) {
					if ( array_key_exists( $key, $raw ) ) {
						$clean[ $key ] = preg_replace( '/[^0-9+]/', '', (string) $raw[ $key ] );
					}
				}

				if ( array_key_exists( 'channels', $raw ) ) {
					$channels          = array_values( array_intersect( array( 'form', 'call', 'sms' ), (array) $raw['channels'] ) );
					$clean['channels'] = $channels ? $channels : array( 'form' );
				}

				return $clean;

			case 'scch_form':
				$fields = array();
				$order  = 1;
				$keys   = array( 'name', 'phone', 'email', 'service', 'message' );
				$posted = is_array( $raw['fields'] ?? null ) ? $raw['fields'] : array();

				// Preserve the submitted row order as the display order.
				foreach ( array_keys( $posted ) as $key ) {
					if ( ! in_array( $key, $keys, true ) ) {
						continue;
					}
					$row            = $posted[ $key ];
					$fields[ $key ] = array(
						'enabled'     => empty( $row['enabled'] ) ? 0 : 1,
						'required'    => empty( $row['required'] ) ? 0 : 1,
						'hide_label'  => empty( $row['hide_label'] ) ? 0 : 1,
						'label'       => sanitize_text_field( $row['label'] ?? '' ),
						'placeholder' => sanitize_text_field( $row['placeholder'] ?? '' ),
						'order'       => $order++,
					);
				}
				// Any field missing from the post keeps its stored config.
				foreach ( $keys as $key ) {
					if ( ! isset( $fields[ $key ] ) ) {
						$fields[ $key ]          = $current['fields'][ $key ];
						$fields[ $key ]['order'] = $order++;
					}
				}
				// Email stays enabled+required: confirmations and reply-to depend on it.
				$fields['email']['enabled']  = 1;
				$fields['email']['required'] = 1;

				return array(
					'form_title'      => sanitize_text_field( $raw['form_title'] ?? $current['form_title'] ),
					'success_message' => sanitize_text_field( $raw['success_message'] ?? $current['success_message'] ),
					'error_message'   => sanitize_text_field( $raw['error_message'] ?? $current['error_message'] ),
					'redirect_url'    => esc_url_raw( $raw['redirect_url'] ?? '' ),
					'submit_label'    => sanitize_text_field( $raw['submit_label'] ?? $current['submit_label'] ),
					'fields'          => $fields,
				);

			case 'scch_captcha':
				$ops = array_values( array_intersect( array( 'add', 'subtract' ), (array) ( $raw['operations'] ?? array() ) ) );
				return array(
					'enabled'    => empty( $raw['enabled'] ) ? 0 : 1,
					'operations' => $ops ? $ops : array( 'add' ),
					'label'      => sanitize_text_field( $raw['label'] ?? $current['label'] ),
				);

			case 'scch_triggers':
				$lines = preg_split( '/[\r\n]+/', (string) ( $raw['selectors'] ?? '' ) );
				$clean = array();
				foreach ( $lines as $line ) {
					$line = trim( sanitize_text_field( $line ) );
					// Selectors never legitimately contain these; strips markup/JS vectors.
					$line = str_replace( array( '<', '>', '{', '}', ';', '"', "'" ), '', $line );
					if ( '' !== $line && strlen( $line ) <= 200 ) {
						$clean[ $line ] = true; // Keyed for automatic de-duplication.
					}
				}
				$clean = array_slice( array_keys( $clean ), 0, 100 );
				return array(
					'enabled'   => empty( $raw['enabled'] ) ? 0 : 1,
					'selectors' => implode( "\n", $clean ),
				);

			case 'scch_email':
				// This group is edited on two screens (Notifications and Email
				// Templates), so merge per key: a key absent from the post is
				// not managed by the submitting form and keeps its stored value.
				// Checkboxes on the owning screen post a hidden "0" fallback.
				$sanitizers = array(
					'admin_enabled'     => fn( $v ) => empty( $v ) ? 0 : 1,
					'admin_recipients'  => fn( $v ) => $this->email_list( (string) $v ),
					'cc'                => fn( $v ) => $this->email_list( (string) $v ),
					'bcc'               => fn( $v ) => $this->email_list( (string) $v ),
					'reply_to_customer' => fn( $v ) => empty( $v ) ? 0 : 1,
					'sender_name'       => 'sanitize_text_field',
					'sender_email'      => 'sanitize_email',
					'customer_enabled'  => fn( $v ) => empty( $v ) ? 0 : 1,
					'response_time'     => 'sanitize_text_field',
					'business_name'     => 'sanitize_text_field',
					'business_contact'  => 'sanitize_text_field',
					'logo_url'          => 'esc_url_raw',
					'brand_color'       => fn( $v ) => $this->color( (string) $v, '#2563eb' ),
					'signature'         => 'sanitize_text_field',
					'admin_subject'     => 'sanitize_text_field',
					'admin_heading'     => 'sanitize_text_field',
					'admin_body'        => 'sanitize_textarea_field',
					'admin_footer'      => 'sanitize_text_field',
					'customer_subject'  => 'sanitize_text_field',
					'customer_heading'  => 'sanitize_text_field',
					'customer_body'     => 'sanitize_textarea_field',
					'customer_footer'   => 'sanitize_text_field',
					'button_label'      => 'sanitize_text_field',
				);

				$clean = array();
				foreach ( $sanitizers as $key => $sanitize ) {
					$clean[ $key ] = array_key_exists( $key, $raw )
						? call_user_func( $sanitize, $raw[ $key ] )
						: $current[ $key ];
				}
				return $clean;

			case 'scch_general':
				// Merged per key: this group is edited from more than one
				// screen, so an absent key keeps its stored value.
				$clean = $current;

				foreach ( array( 'rate_limit_max' => array( 1, 100, 5 ), 'rate_limit_window' => array( 1, 1440, 10 ) ) as $key => $range ) {
					if ( array_key_exists( $key, $raw ) ) {
						$clean[ $key ] = min( $range[1], max( $range[0], absint( $raw[ $key ] ) ) );
					}
				}

				foreach ( array( 'log_enabled', 'crm_enabled' ) as $flag ) {
					if ( array_key_exists( $flag, $raw ) ) {
						$clean[ $flag ] = empty( $raw[ $flag ] ) ? 0 : 1;
					}
				}

				if ( array_key_exists( 'currency_symbol', $raw ) ) {
					$clean['currency_symbol'] = mb_substr( sanitize_text_field( $raw['currency_symbol'] ), 0, 5 );
				}

				return $clean;

			case 'scch_uninstall':
				return array(
					'delete_settings' => empty( $raw['delete_settings'] ) ? 0 : 1,
					'delete_leads'    => empty( $raw['delete_leads'] ) ? 0 : 1,
				);
		}

		return $current;
	}

	/**
	 * Validate a hex color, optionally allowing the keyword "transparent".
	 *
	 * @param string $value             Raw value.
	 * @param string $fallback          Fallback color.
	 * @param bool   $allow_transparent Whether "transparent" is valid.
	 */
	private function color( string $value, string $fallback, bool $allow_transparent = false ): string {
		if ( $allow_transparent && 'transparent' === strtolower( trim( $value ) ) ) {
			return 'transparent';
		}
		$hex = sanitize_hex_color( $value );
		return $hex ? $hex : $fallback;
	}

	/**
	 * Sanitize the Appearance group against the Design_Tokens schema.
	 *
	 * Every value is coerced by its declared type and clamped to its declared
	 * range, so a hand-crafted POST cannot introduce a value the CSS layer is
	 * not prepared to emit. Keys absent from the schema are discarded.
	 *
	 * @param array $raw Unslashed raw input.
	 * @return array<string,mixed>
	 */
	private function sanitize_tokens( array $raw ): array {
		$clean = array();

		foreach ( Design_Tokens::fields() as $key => $field ) {
			$value   = $raw[ $key ] ?? null;
			$default = $field['default'];

			switch ( $field['type'] ) {
				case 'toggle':
					// Absent means unchecked, which is a real value here: the
					// whole group is rebuilt on every save.
					$clean[ $key ] = empty( $value ) ? 0 : 1;
					break;

				case 'select':
					$clean[ $key ] = isset( $field['options'][ (string) $value ] ) ? (string) $value : $default;
					break;

				case 'icon':
					$clean[ $key ] = Icons::exists( (string) $value ) ? (string) $value : (string) $default;
					break;

				case 'color':
					$clean[ $key ] = $this->token_color( $value, $field );
					break;

				case 'px':
				case 'pct':
				case 'num':
					$number        = null === $value || '' === $value ? (int) $default : (int) $value;
					$clean[ $key ] = max( (int) ( $field['min'] ?? 0 ), min( (int) ( $field['max'] ?? PHP_INT_MAX ), $number ) );
					break;

				case 'dec':
					$number        = null === $value || '' === $value ? (float) $default : (float) $value;
					$number        = max( (float) ( $field['min'] ?? 0 ), min( (float) ( $field['max'] ?? 100 ), $number ) );
					$clean[ $key ] = rtrim( rtrim( number_format( $number, 2, '.', '' ), '0' ), '.' ) ?: '0';
					break;

				case 'font':
					$clean[ $key ] = ! empty( $field['empty'] ) && '' === trim( (string) $value )
						? ''
						: $this->font_family( (string) $value );
					break;

				case 'url':
					$clean[ $key ] = esc_url_raw( (string) $value );
					break;

				default:
					$clean[ $key ] = sanitize_text_field( (string) $value );
			}
		}

		return $clean;
	}

	/**
	 * Resolve one color token, honoring its empty and transparent allowances.
	 *
	 * @param mixed $value Raw value.
	 * @param array $field Field definition.
	 */
	private function token_color( $value, array $field ): string {
		$value = trim( (string) $value );

		if ( ! empty( $field['transparent'] ) && 'transparent' === strtolower( $value ) ) {
			return 'transparent';
		}

		if ( '' === $value ) {
			// An empty value is meaningful for optional tokens: no custom
			// property is emitted, so the stylesheet's fallback applies.
			return ! empty( $field['empty'] ) ? '' : (string) $field['default'];
		}

		$hex = sanitize_hex_color( $value );

		if ( $hex ) {
			return $hex;
		}

		return ! empty( $field['empty'] ) ? '' : (string) $field['default'];
	}

	/**
	 * Sanitize a CSS font-family stack.
	 *
	 * This value is printed into a stylesheet on every public page, where
	 * esc_attr() is not sufficient escaping: it leaves ; { } untouched, so a
	 * value could close the declaration and inject arbitrary CSS site-wide.
	 * Only the characters a font stack legitimately needs survive.
	 *
	 * @param string $value Raw value.
	 */
	private function font_family( string $value ): string {
		$clean = preg_replace( '/[^A-Za-z0-9 ,\'"\-]/', '', sanitize_text_field( $value ) );
		$clean = trim( (string) $clean );
		return '' === $clean ? 'inherit' : substr( $clean, 0, 200 );
	}

	/**
	 * Sanitize a comma-separated list of email addresses.
	 *
	 * @param string $value Raw list.
	 */
	private function email_list( string $value ): string {
		$emails = array_filter( array_map( 'sanitize_email', array_map( 'trim', explode( ',', $value ) ) ), 'is_email' );
		return implode( ', ', $emails );
	}
}
