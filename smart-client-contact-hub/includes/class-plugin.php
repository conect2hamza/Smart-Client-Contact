<?php
/**
 * Core plugin orchestrator.
 *
 * @package SCCH
 */

namespace SCCH;

defined( 'ABSPATH' ) || exit;

/**
 * Singleton that wires together the admin, frontend, and AJAX layers.
 */
final class Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var Plugin|null
	 */
	private static ?Plugin $instance = null;

	/**
	 * Retrieve the singleton.
	 */
	public static function instance(): Plugin {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Private constructor — use instance().
	 */
	private function __construct() {}

	/**
	 * Register all hooks.
	 */
	public function run(): void {
		$this->maybe_upgrade();

		( new Ajax() )->register();

		if ( is_admin() ) {
			( new Admin\Admin() )->register();
			( new Admin\Export() )->register();
		}

		( new Frontend\Frontend() )->register();
	}

	/**
	 * Re-run table creation if the plugin was updated in place.
	 */
	private function maybe_upgrade(): void {
		if ( SCCH_VERSION !== get_option( 'scch_version' ) ) {
			Activator::activate();
		}
	}
}
