<?php
/**
 * REST API endpoints for Admin Notice Manager
 * File: includes/class-anm-api.php
 */

if (!defined('ABSPATH')) {
    exit;
}

class ANM_API {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('rest_api_init', array($this, 'register_routes'));
    }
    
    public function register_routes() {
        register_rest_route('anm/v1', '/notices', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_notices'),
            'permission_callback' => array($this, 'check_api_key')
        ));
        
        register_rest_route('anm/v1', '/dismiss/(?P<id>\d+)', array(
            'methods' => 'POST',
            'callback' => array($this, 'dismiss_notice'),
            'permission_callback' => array($this, 'check_api_key')
        ));
        
        register_rest_route('anm/v1', '/ping', array(
            'methods' => 'GET',
            'callback' => array($this, 'ping'),
            'permission_callback' => array($this, 'check_api_key')
        ));
    }
    
    public function check_api_key($request) {
        $api_key = $request->get_header('X-ANM-API-Key');
        
        if (empty($api_key)) {
            return new WP_Error('no_api_key', __('API key is required.', 'admin-notice-manager'), array('status' => 401));
        }
        
        $client = ANM_Database::get_client_by_api_key($api_key);
        
        if (!$client) {
            return new WP_Error('invalid_api_key', __('Invalid API key.', 'admin-notice-manager'), array('status' => 403));
        }
        
        // Store client info in request
        $request->set_param('anm_client', $client);
        
        // Update last connected timestamp
        ANM_Database::update_client_last_connected($client->id);
        
        return true;
    }
    
    public function get_notices($request) {
        $client = $request->get_param('anm_client');
        $user_id = $request->get_param('user_id');
        
        $notices = ANM_Database::get_notices_for_client($client->id);
        
        $response_notices = array();
        
        foreach ($notices as $notice) {
            // Check if dismissed by user
            $is_dismissed = false;
            if ($user_id && $notice->is_dismissable) {
                $is_dismissed = ANM_Database::is_notice_dismissed($notice->id, $client->id, $user_id);
            }
            
            // Skip dismissed notices
            if ($is_dismissed) {
                continue;
            }
            
            $response_notices[] = array(
                'id' => $notice->id,
                'title' => $notice->title,
                'content' => $notice->content,
                'content_type' => $notice->content_type,
                'notice_type' => $notice->notice_type,
                'is_dismissable' => (bool) $notice->is_dismissable,
                'created_at' => $notice->created_at
            );
        }
        
        return rest_ensure_response(array(
            'success' => true,
            'notices' => $response_notices,
            'count' => count($response_notices)
        ));
    }
    
    public function dismiss_notice($request) {
        $client = $request->get_param('anm_client');
        $notice_id = intval($request['id']);
        $user_id = intval($request->get_param('user_id'));
        
        if (!$user_id) {
            return new WP_Error('no_user_id', __('User ID is required.', 'admin-notice-manager'), array('status' => 400));
        }
        
        $result = ANM_Database::dismiss_notice($notice_id, $client->id, $user_id);
        
        if ($result) {
            return rest_ensure_response(array(
                'success' => true,
                'message' => __('Notice dismissed successfully.', 'admin-notice-manager')
            ));
        }
        
        return new WP_Error('dismiss_failed', __('Failed to dismiss notice.', 'admin-notice-manager'), array('status' => 500));
    }
    
    public function ping($request) {
        $client = $request->get_param('anm_client');
        
        return rest_ensure_response(array(
            'success' => true,
            'message' => __('Connection successful.', 'admin-notice-manager'),
            'client' => array(
                'site_name' => $client->site_name,
                'site_url' => $client->site_url
            )
        ));
    }
}
