<?php
/**
 * Plugin Name:       Smart Client Contact Hub
 * Plugin URI:        https://hamzadezinr.com/smart-client-contact-hub
 * Description:       Premium floating contact widget with lead capture, built-in math CAPTCHA, email notifications, and full lead management — 100% standalone, no third-party plugins required.
 * Version:           1.2.3
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            Hamza Dezinr
 * Author URI:        https://hamzadezinr.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       smart-client-contact-hub
 * Domain Path:       /languages
 *
 * @package SCCH
 */

namespace SCCH;

defined( 'ABSPATH' ) || exit;

define( 'SCCH_VERSION', '1.2.3' );
define( 'SCCH_FILE', __FILE__ );
define( 'SCCH_PATH', plugin_dir_path( __FILE__ ) );
define( 'SCCH_URL', plugin_dir_url( __FILE__ ) );
define( 'SCCH_BASENAME', plugin_basename( __FILE__ ) );

/**
 * PSR-4 inspired autoloader for the SCCH namespace.
 *
 * Maps SCCH\Admin\Leads_List_Table  -> admin/class-leads-list-table.php
 * Maps SCCH\Frontend\Frontend       -> frontend/class-frontend.php
 * Maps SCCH\Captcha                 -> includes/class-captcha.php
 */
spl_autoload_register(
	static function ( string $class ): void {
		if ( 0 !== strpos( $class, __NAMESPACE__ . '\\' ) ) {
			return;
		}

		$relative = substr( $class, strlen( __NAMESPACE__ ) + 1 );
		$parts    = explode( '\\', $relative );
		$file     = 'class-' . str_replace( '_', '-', strtolower( array_pop( $parts ) ) ) . '.php';
		$subdir   = $parts ? strtolower( implode( '/', $parts ) ) . '/' : 'includes/';

		// Root-namespace classes live in /includes.
		if ( 'includes/' !== $subdir && ! in_array( $parts[0], array( 'Admin', 'Frontend' ), true ) ) {
			$subdir = 'includes/';
		}

		$path = SCCH_PATH . $subdir . $file;

		if ( is_readable( $path ) ) {
			require_once $path;
		}
	}
);

register_activation_hook( __FILE__, array( Activator::class, 'activate' ) );
register_deactivation_hook( __FILE__, array( Deactivator::class, 'deactivate' ) );

/**
 * Boot the plugin once all plugins are loaded.
 */
add_action(
	'plugins_loaded',
	static function (): void {
		Plugin::instance()->run();
	}
);

// Translations load on init, not plugins_loaded: WordPress 6.7+ warns when a
// text domain is loaded before init.
add_action(
	'init',
	static function (): void {
		load_plugin_textdomain( 'smart-client-contact-hub', false, dirname( SCCH_BASENAME ) . '/languages' );
	},
	5
);
