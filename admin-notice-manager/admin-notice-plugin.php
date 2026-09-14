<?php
/**
 * Plugin Name: Admin Notice Manager
 * Plugin URI: https://dimitrisnimas.gr
 * Description: Create and manage dashboard notices for client sites
 * Version: 1.0.0
 * Requires at least: 6.2
 * Requires PHP: 7.4
 * Author: Dimitris Nimas
 * Author URI: https://dimitrisnimas.gr
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: admin-notice-manager
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants
define( 'ANM_VERSION', '1.0.0' );
define( 'ANM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'ANM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Include required files
require_once ANM_PLUGIN_DIR . 'includes/class-anm-database.php';
require_once ANM_PLUGIN_DIR . 'includes/class-anm-admin.php';
require_once ANM_PLUGIN_DIR . 'includes/class-anm-api.php';

/**
 * Main Plugin Class
 */
class Admin_Notice_Manager {

	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->init_hooks();
	}

	private function init_hooks() {
		register_activation_hook( __FILE__, array( $this, 'activate' ) );

		// Initialize components immediately
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}

	public function activate() {

		ANM_Database::create_tables();
	}

	public function init() {
		// Initialize components
		ANM_Admin::get_instance();
		ANM_API::get_instance();
	}

	public function enqueue_admin_assets( $hook ) {
		if ( false === strpos( $hook, 'anm-' ) && 'toplevel_page_anm-notices' !== $hook ) {
			return;
		}

		wp_enqueue_style( 'anm-admin-css', ANM_PLUGIN_URL . 'assets/css/admin-style.css', array(), ANM_VERSION );
		wp_enqueue_script( 'anm-admin-js', ANM_PLUGIN_URL . 'assets/js/admin-script.js', array( 'jquery' ), ANM_VERSION, true );

		wp_localize_script(
			'anm-admin-js',
			'anmData',
			array(
				'copiedText'        => __( 'Copied!', 'admin-notice-manager' ),
				'confirmDeleteText' => __( 'Are you sure you want to delete this item?', 'admin-notice-manager' ),
			)
		);
	}
}

// Initialize the plugin
Admin_Notice_Manager::get_instance();
