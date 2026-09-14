<?php
/**
 * Admin interface for Admin Notice Manager
 * File: includes/class-anm-admin.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ANM_Admin {

	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {

		if ( ! is_admin() ) {
			return;
		}

		require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
		require_once ANM_PLUGIN_DIR . 'includes/class-anm-notices-list-table.php';
		require_once ANM_PLUGIN_DIR . 'includes/class-anm-clients-list-table.php';
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'handle_form_submissions' ) );
		add_action( 'admin_post_anm_delete_notice', array( $this, 'handle_delete_notice' ) );
		add_action( 'admin_post_anm_delete_client', array( $this, 'handle_delete_client' ) );
		add_action( 'admin_post_anm_rotate_client_key', array( $this, 'handle_rotate_client_key' ) );
	}

	public function add_admin_menu() {
		add_menu_page(
			__( 'Notice Manager', 'admin-notice-manager' ),
			__( 'Notices', 'admin-notice-manager' ),
			'manage_options',
			'anm-notices',
			array( $this, 'notices_page' ),
			'dashicons-megaphone',
			30
		);

		add_submenu_page(
			'anm-notices',
			__( 'All Notices', 'admin-notice-manager' ),
			__( 'All Notices', 'admin-notice-manager' ),
			'manage_options',
			'anm-notices',
			array( $this, 'notices_page' )
		);

		add_submenu_page(
			'anm-notices',
			__( 'Add New Notice', 'admin-notice-manager' ),
			__( 'Add New', 'admin-notice-manager' ),
			'manage_options',
			'anm-add-notice',
			array( $this, 'add_notice_page' )
		);

		add_submenu_page(
			'anm-notices',
			__( 'Clients', 'admin-notice-manager' ),
			__( 'Clients', 'admin-notice-manager' ),
			'manage_options',
			'anm-clients',
			array( $this, 'clients_page' )
		);

		add_submenu_page(
			'anm-notices',
			__( 'Settings', 'admin-notice-manager' ),
			__( 'Settings', 'admin-notice-manager' ),
			'manage_options',
			'anm-settings',
			array( $this, 'settings_page' )
		);
	}

	public function notices_page() {

		$action = isset( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : '';
		if ( 'edit' === $action && isset( $_GET['id'] ) ) {
			$this->edit_notice_page();
			return;
		}

		$search     = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
		$list_table = new ANM_Notices_List_Table( $search );
		$list_table->prepare_items();

		include ANM_PLUGIN_DIR . 'templates/notices-list.php';
	}

	public function add_notice_page() {

		$clients = ANM_Database::get_all_clients();

		include ANM_PLUGIN_DIR . 'templates/notice-form.php';
	}

	public function edit_notice_page() {

		$notice_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		$notice    = ANM_Database::get_notice( $notice_id );

		if ( ! $notice ) {
			wp_die( __( 'Notice not found.', 'admin-notice-manager' ) );
		}

		$clients          = ANM_Database::get_all_clients();
		$selected_clients = ANM_Database::get_notice_clients( $notice_id );

		include ANM_PLUGIN_DIR . 'templates/notice-form.php';
	}

	public function clients_page() {

		$action = isset( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : '';
		if ( 'edit' === $action && isset( $_GET['id'] ) ) {
			$this->edit_client_page();
			return;
		}

		$search     = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
		$list_table = new ANM_Clients_List_Table( $search );
		$list_table->prepare_items();

		include ANM_PLUGIN_DIR . 'templates/clients-list.php';
	}

	public function edit_client_page() {

		$client_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		$client    = ANM_Database::get_client( $client_id );

		if ( ! $client ) {
			wp_die( __( 'Client not found.', 'admin-notice-manager' ) );
		}

		include ANM_PLUGIN_DIR . 'templates/client-form.php';
	}

	public function settings_page() {
		include ANM_PLUGIN_DIR . 'templates/settings.php';
	}

	public function handle_form_submissions() {

		if ( ! isset( $_POST['anm_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['anm_nonce'] ) ), 'anm_action' ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to perform this action.', 'admin-notice-manager' ) );
		}

		// Handle notice save
		if ( isset( $_POST['anm_save_notice'] ) ) {
			$this->save_notice();
		}

		// Handle client save
		if ( isset( $_POST['anm_save_client'] ) ) {
			$this->save_client();
		}
	}

	private function save_notice() {

		$notice_id    = isset( $_POST['notice_id'] ) ? absint( $_POST['notice_id'] ) : 0;
		$title        = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
		$content      = isset( $_POST['content'] ) ? wp_kses_post( wp_unslash( $_POST['content'] ) ) : '';
		$content_type = isset( $_POST['content_type'] ) ? sanitize_key( wp_unslash( $_POST['content_type'] ) ) : 'html';
		$notice_type  = isset( $_POST['notice_type'] ) ? sanitize_key( wp_unslash( $_POST['notice_type'] ) ) : 'info';
		$status       = isset( $_POST['status'] ) ? sanitize_key( wp_unslash( $_POST['status'] ) ) : 'inactive';
		if ( '' === $title || '' === trim( wp_strip_all_tags( $content ) ) ) {
			$this->redirect_with_message( 'anm-notices', 'invalid' );
		}

		if ( ! in_array( $content_type, array( 'html', 'text' ), true ) ) {
			$content_type = 'html';
		}

		if ( ! in_array( $notice_type, array( 'info', 'success', 'warning', 'error' ), true ) ) {
			$notice_type = 'info';
		}

		if ( ! in_array( $status, array( 'active', 'inactive' ), true ) ) {
			$status = 'inactive';
		}

		$data = array(
			'title'          => $title,
			'content'        => $content,
			'content_type'   => $content_type,
			'notice_type'    => $notice_type,
			'status'         => $status,
			'is_dismissible' => isset( $_POST['is_dismissible'] ) ? 1 : 0,
		);
		if ( ! empty( $_POST['expiration_date'] ) ) {
			$expiration              = sanitize_text_field( wp_unslash( $_POST['expiration_date'] ) );
			$date                    = date_create_from_format( 'Y-m-d\TH:i', $expiration, wp_timezone() );
			$data['expiration_date'] = $date ? $date->format( 'Y-m-d H:i:s' ) : null;
		} else {
			$data['expiration_date'] = null;
		}

		if ( $notice_id > 0 ) {
			$result = ANM_Database::update_notice( $notice_id, $data );
		} else {
			$notice_id = ANM_Database::insert_notice( $data );
			$result    = (bool) $notice_id;
		}

		if ( false === $result ) {
			$this->redirect_with_message( 'anm-notices', 'error' );
		}

		// Assign to clients
		$client_ids = isset( $_POST['client_ids'] ) && is_array( $_POST['client_ids'] ) ? array_values( array_unique( array_map( 'absint', wp_unslash( $_POST['client_ids'] ) ) ) ) : array();
		ANM_Database::assign_notice_to_clients( $notice_id, $client_ids );
		$this->redirect_with_message( 'anm-notices', 'saved' );
	}

	private function save_client() {
		$client_id = isset( $_POST['client_id'] ) ? absint( $_POST['client_id'] ) : 0;
		$site_name = isset( $_POST['site_name'] ) ? sanitize_text_field( wp_unslash( $_POST['site_name'] ) ) : '';
		$site_url  = isset( $_POST['site_url'] ) ? esc_url_raw( wp_unslash( $_POST['site_url'] ) ) : '';
		$status    = isset( $_POST['status'] ) ? sanitize_key( wp_unslash( $_POST['status'] ) ) : 'inactive';
		if ( '' === $site_name || ! wp_http_validate_url( $site_url ) ) {
			$this->redirect_with_message( 'anm-clients', 'invalid' );
		}

		if ( ! in_array( $status, array( 'active', 'inactive' ), true ) ) {
			$status = 'inactive';
		}

		$data = array(
			'site_name' => $site_name,
			'site_url'  => $site_url,
			'status'    => $status,
		);

		if ( $client_id > 0 ) {
			$result = ANM_Database::update_client( $client_id, $data );
		} else {
			$client_id = ANM_Database::insert_client( $data );
			$result    = (bool) $client_id;
		}

		$this->redirect_with_message( 'anm-clients', false === $result ? 'error' : 'saved' );
	}

	public function handle_delete_notice() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to perform this action.', 'admin-notice-manager' ) );
		}

		$notice_id = isset( $_GET['notice_id'] ) ? absint( $_GET['notice_id'] ) : 0;
		check_admin_referer( 'anm_delete_notice_' . $notice_id );
		ANM_Database::delete_notice( $notice_id );
		$this->redirect_with_message( 'anm-notices', 'deleted' );
	}

	public function handle_delete_client() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to perform this action.', 'admin-notice-manager' ) );
		}

		$client_id = isset( $_GET['client_id'] ) ? absint( $_GET['client_id'] ) : 0;
		check_admin_referer( 'anm_delete_client_' . $client_id );
		ANM_Database::delete_client( $client_id );
		$this->redirect_with_message( 'anm-clients', 'deleted' );
	}

	public function handle_rotate_client_key() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to perform this action.', 'admin-notice-manager' ) );
		}

		$client_id = isset( $_POST['client_id'] ) ? absint( $_POST['client_id'] ) : 0;
		check_admin_referer( 'anm_rotate_client_key_' . $client_id );
		$result = ANM_Database::rotate_client_api_key( $client_id );
		$url    = add_query_arg(
			array(
				'page'    => 'anm-clients',
				'action'  => 'edit',
				'id'      => $client_id,
				'message' => false === $result ? 'key-error' : 'key-rotated',
			),
			admin_url( 'admin.php' )
		);
		wp_safe_redirect( $url );
		exit;
	}

	private function redirect_with_message( $page, $message ) {

		$url = add_query_arg(
			array(
				'page'    => $page,
				'message' => $message,
			),
			admin_url( 'admin.php' )
		);
		wp_safe_redirect( $url );
		exit;
	}
}
