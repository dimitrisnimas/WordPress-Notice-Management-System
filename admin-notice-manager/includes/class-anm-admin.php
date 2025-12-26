<?php
/**
 * Admin interface for Admin Notice Manager
 * File: includes/class-anm-admin.php
 */

if (!defined('ABSPATH')) {
    exit;
}

class ANM_Admin {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'handle_form_submissions'));
    }
    
    public function add_admin_menu() {
        add_menu_page(
            __('Notice Manager', 'admin-notice-manager'),
            __('Notices', 'admin-notice-manager'),
            'manage_options',
            'anm-notices',
            array($this, 'notices_page'),
            'dashicons-megaphone',
            30
        );
        
        add_submenu_page(
            'anm-notices',
            __('All Notices', 'admin-notice-manager'),
            __('All Notices', 'admin-notice-manager'),
            'manage_options',
            'anm-notices',
            array($this, 'notices_page')
        );
        
        add_submenu_page(
            'anm-notices',
            __('Add New Notice', 'admin-notice-manager'),
            __('Add New', 'admin-notice-manager'),
            'manage_options',
            'anm-add-notice',
            array($this, 'add_notice_page')
        );
        
        add_submenu_page(
            'anm-notices',
            __('Clients', 'admin-notice-manager'),
            __('Clients', 'admin-notice-manager'),
            'manage_options',
            'anm-clients',
            array($this, 'clients_page')
        );
        
        add_submenu_page(
            'anm-notices',
            __('Settings', 'admin-notice-manager'),
            __('Settings', 'admin-notice-manager'),
            'manage_options',
            'anm-settings',
            array($this, 'settings_page')
        );
    }
    
    public function notices_page() {
        if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
            $this->edit_notice_page();
            return;
        }
        
        $notices = ANM_Database::get_all_notices();
        
        include ANM_PLUGIN_DIR . 'templates/notices-list.php';
    }
    
    public function add_notice_page() {
        $clients = ANM_Database::get_all_clients('active');
        
        include ANM_PLUGIN_DIR . 'templates/notice-form.php';
    }
    
    public function edit_notice_page() {
        $notice_id = intval($_GET['id']);
        $notice = ANM_Database::get_notice($notice_id);
        
        if (!$notice) {
            wp_die(__('Notice not found.', 'admin-notice-manager'));
        }
        
        $clients = ANM_Database::get_all_clients('active');
        $selected_clients = ANM_Database::get_notice_clients($notice_id);
        
        include ANM_PLUGIN_DIR . 'templates/notice-form.php';
    }
    
    public function clients_page() {
        if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
            $this->edit_client_page();
            return;
        }
        
        $clients = ANM_Database::get_all_clients();
        
        include ANM_PLUGIN_DIR . 'templates/clients-list.php';
    }
    
    public function edit_client_page() {
        $client_id = intval($_GET['id']);
        $client = ANM_Database::get_client($client_id);
        
        if (!$client) {
            wp_die(__('Client not found.', 'admin-notice-manager'));
        }
        
        include ANM_PLUGIN_DIR . 'templates/client-form.php';
    }
    
    public function settings_page() {
        include ANM_PLUGIN_DIR . 'templates/settings.php';
    }
    
    public function handle_form_submissions() {
        if (!isset($_POST['anm_nonce']) || !wp_verify_nonce($_POST['anm_nonce'], 'anm_action')) {
            return;
        }
        
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Handle notice save
        if (isset($_POST['anm_save_notice'])) {
            $this->save_notice();
        }
        
        // Handle notice delete
        if (isset($_POST['anm_delete_notice'])) {
            $this->delete_notice();
        }
        
        // Handle client save
        if (isset($_POST['anm_save_client'])) {
            $this->save_client();
        }
        
        // Handle client delete
        if (isset($_POST['anm_delete_client'])) {
            $this->delete_client();
        }
    }
    
    private function save_notice() {
        $notice_id = isset($_POST['notice_id']) ? intval($_POST['notice_id']) : 0;
        
        $data = array(
            'title' => sanitize_text_field($_POST['title']),
            'content' => wp_kses_post($_POST['content']),
            'content_type' => sanitize_text_field($_POST['content_type']),
            'notice_type' => sanitize_text_field($_POST['notice_type']),
            'status' => sanitize_text_field($_POST['status']),
            'is_dismissable' => isset($_POST['is_dismissable']) ? 1 : 0
        );
        
        if (!empty($_POST['expiration_date'])) {
            $data['expiration_date'] = sanitize_text_field($_POST['expiration_date']);
        } else {
            $data['expiration_date'] = null;
        }
        
        if ($notice_id > 0) {
            ANM_Database::update_notice($notice_id, $data);
        } else {
            $notice_id = ANM_Database::insert_notice($data);
        }
        
        // Assign to clients
        if (isset($_POST['client_ids']) && is_array($_POST['client_ids'])) {
            $client_ids = array_map('intval', $_POST['client_ids']);
            ANM_Database::assign_notice_to_clients($notice_id, $client_ids);
        }
        
        wp_redirect(admin_url('admin.php?page=anm-notices&message=saved'));
        exit;
    }
    
    private function delete_notice() {
        $notice_id = intval($_POST['notice_id']);
        
        ANM_Database::delete_notice($notice_id);
        
        wp_redirect(admin_url('admin.php?page=anm-notices&message=deleted'));
        exit;
    }
    
    private function save_client() {
        $client_id = isset($_POST['client_id']) ? intval($_POST['client_id']) : 0;
        
        $data = array(
            'site_name' => sanitize_text_field($_POST['site_name']),
            'site_url' => esc_url_raw($_POST['site_url']),
            'status' => sanitize_text_field($_POST['status'])
        );
        
        if ($client_id > 0) {
            ANM_Database::update_client($client_id, $data);
        } else {
            $client_id = ANM_Database::insert_client($data);
        }
        
        wp_redirect(admin_url('admin.php?page=anm-clients&message=saved'));
        exit;
    }
    
    private function delete_client() {
        $client_id = intval($_POST['client_id']);
        
        ANM_Database::delete_client($client_id);
        
        wp_redirect(admin_url('admin.php?page=anm-clients&message=deleted'));
        exit;
    }
}
