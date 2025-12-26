<?php
/**
 * Clients management for Admin Notice Manager
 * File: includes/class-anm-clients.php
 */

if (!defined('ABSPATH')) {
    exit;
}

class ANM_Clients {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Add any client-specific hooks here if needed
    }
    
    /**
     * Get client connection status
     */
    public static function get_client_status($client_id) {
        $client = ANM_Database::get_client($client_id);
        
        if (!$client) {
            return 'unknown';
        }
        
        if ($client->status !== 'active') {
            return 'inactive';
        }
        
        // Check if client has connected recently (within last 24 hours)
        if (!$client->last_connected) {
            return 'never_connected';
        }
        
        $last_connected_timestamp = strtotime($client->last_connected);
        $hours_since_connection = (current_time('timestamp') - $last_connected_timestamp) / 3600;
        
        if ($hours_since_connection < 24) {
            return 'connected';
        } elseif ($hours_since_connection < 168) { // 7 days
            return 'recently_connected';
        } else {
            return 'disconnected';
        }
    }
    
    /**
     * Get notices count for a client
     */
    public static function get_client_notices_count($client_id) {
        $notices = ANM_Database::get_notices_for_client($client_id);
        return count($notices);
    }
    
    /**
     * Validate client credentials
     */
    public static function validate_client($api_key) {
        $client = ANM_Database::get_client_by_api_key($api_key);
        
        if (!$client) {
            return array(
                'valid' => false,
                'message' => __('Invalid API key.', 'admin-notice-manager')
            );
        }
        
        if ($client->status !== 'active') {
            return array(
                'valid' => false,
                'message' => __('Client is inactive.', 'admin-notice-manager')
            );
        }
        
        return array(
            'valid' => true,
            'client' => $client
        );
    }
}
