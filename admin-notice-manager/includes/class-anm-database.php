<?php
/**
 * Database operations for Admin Notice Manager
 * File: includes/class-anm-database.php
 */

if (!defined('ABSPATH')) {
    exit;
}

class ANM_Database {
    
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Notices table
        $notices_table = $wpdb->prefix . 'anm_notices';
        $notices_sql = "CREATE TABLE $notices_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            title varchar(255) NOT NULL,
            content longtext NOT NULL,
            content_type varchar(20) NOT NULL DEFAULT 'html',
            notice_type varchar(20) NOT NULL DEFAULT 'info',
            status varchar(20) NOT NULL DEFAULT 'active',
            expiration_date datetime DEFAULT NULL,
            is_dismissable tinyint(1) NOT NULL DEFAULT 1,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY status (status),
            KEY expiration_date (expiration_date)
        ) $charset_collate;";
        
        // Clients table
        $clients_table = $wpdb->prefix . 'anm_clients';
        $clients_sql = "CREATE TABLE $clients_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            site_name varchar(255) NOT NULL,
            site_url varchar(255) NOT NULL,
            api_key varchar(64) NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'active',
            last_connected datetime DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY api_key (api_key),
            KEY status (status)
        ) $charset_collate;";
        
        // Notice-Client relationship table
        $relationships_table = $wpdb->prefix . 'anm_notice_clients';
        $relationships_sql = "CREATE TABLE $relationships_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            notice_id bigint(20) unsigned NOT NULL,
            client_id bigint(20) unsigned NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY notice_client (notice_id, client_id),
            KEY notice_id (notice_id),
            KEY client_id (client_id)
        ) $charset_collate;";
        
        // Dismissal tracking table
        $dismissals_table = $wpdb->prefix . 'anm_dismissals';
        $dismissals_sql = "CREATE TABLE $dismissals_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            notice_id bigint(20) unsigned NOT NULL,
            client_id bigint(20) unsigned NOT NULL,
            user_id bigint(20) unsigned NOT NULL,
            dismissed_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY notice_client_user (notice_id, client_id, user_id),
            KEY notice_id (notice_id),
            KEY client_id (client_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($notices_sql);
        dbDelta($clients_sql);
        dbDelta($relationships_sql);
        dbDelta($dismissals_sql);
    }
    
    public static function create_default_api_key() {
        $api_key = get_option('anm_master_api_key');
        if (!$api_key) {
            $api_key = self::generate_api_key();
            update_option('anm_master_api_key', $api_key);
        }
    }
    
    public static function generate_api_key() {
        return bin2hex(random_bytes(32));
    }
    
    // Notice CRUD operations
    public static function insert_notice($data) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'anm_notices';
        
        $defaults = array(
            'title' => '',
            'content' => '',
            'content_type' => 'html',
            'notice_type' => 'info',
            'status' => 'active',
            'expiration_date' => null,
            'is_dismissable' => 1
        );
        
        $data = wp_parse_args($data, $defaults);
        
        $result = $wpdb->insert($table, $data);
        
        return $result ? $wpdb->insert_id : false;
    }
    
    public static function update_notice($id, $data) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'anm_notices';
        
        return $wpdb->update($table, $data, array('id' => $id));
    }
    
    public static function delete_notice($id) {
        global $wpdb;
        
        $notices_table = $wpdb->prefix . 'anm_notices';
        $relationships_table = $wpdb->prefix . 'anm_notice_clients';
        $dismissals_table = $wpdb->prefix . 'anm_dismissals';
        
        // Delete relationships
        $wpdb->delete($relationships_table, array('notice_id' => $id));
        
        // Delete dismissals
        $wpdb->delete($dismissals_table, array('notice_id' => $id));
        
        // Delete notice
        return $wpdb->delete($notices_table, array('id' => $id));
    }
    
    public static function get_notice($id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'anm_notices';
        
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id));
    }
    
    public static function get_all_notices($status = 'all') {
        global $wpdb;
        
        $table = $wpdb->prefix . 'anm_notices';
        
        if ($status === 'all') {
            return $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC");
        }
        
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE status = %s ORDER BY created_at DESC", $status));
    }
    
    // Client CRUD operations
    public static function insert_client($data) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'anm_clients';
        
        $data['api_key'] = self::generate_api_key();
        
        $result = $wpdb->insert($table, $data);
        
        return $result ? $wpdb->insert_id : false;
    }
    
    public static function update_client($id, $data) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'anm_clients';
        
        return $wpdb->update($table, $data, array('id' => $id));
    }
    
    public static function delete_client($id) {
        global $wpdb;
        
        $clients_table = $wpdb->prefix . 'anm_clients';
        $relationships_table = $wpdb->prefix . 'anm_notice_clients';
        
        // Delete relationships
        $wpdb->delete($relationships_table, array('client_id' => $id));
        
        // Delete client
        return $wpdb->delete($clients_table, array('id' => $id));
    }
    
    public static function get_client($id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'anm_clients';
        
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id));
    }
    
    public static function get_client_by_api_key($api_key) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'anm_clients';
        
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE api_key = %s AND status = 'active'", $api_key));
    }
    
    public static function get_all_clients($status = 'all') {
        global $wpdb;
        
        $table = $wpdb->prefix . 'anm_clients';
        
        if ($status === 'all') {
            return $wpdb->get_results("SELECT * FROM $table ORDER BY site_name ASC");
        }
        
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE status = %s ORDER BY site_name ASC", $status));
    }
    
    // Relationship operations
    public static function assign_notice_to_clients($notice_id, $client_ids) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'anm_notice_clients';
        
        // Clear existing assignments
        $wpdb->delete($table, array('notice_id' => $notice_id));
        
        // Add new assignments
        foreach ($client_ids as $client_id) {
            $wpdb->insert($table, array(
                'notice_id' => $notice_id,
                'client_id' => $client_id
            ));
        }
        
        return true;
    }
    
    public static function get_notice_clients($notice_id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'anm_notice_clients';
        
        return $wpdb->get_col($wpdb->prepare("SELECT client_id FROM $table WHERE notice_id = %d", $notice_id));
    }
    
    public static function get_notices_for_client($client_id) {
        global $wpdb;
        
        $notices_table = $wpdb->prefix . 'anm_notices';
        $relationships_table = $wpdb->prefix . 'anm_notice_clients';
        
        $query = "SELECT n.* FROM $notices_table n
                  INNER JOIN $relationships_table r ON n.id = r.notice_id
                  WHERE r.client_id = %d 
                  AND n.status = 'active'
                  AND (n.expiration_date IS NULL OR n.expiration_date > NOW())
                  ORDER BY n.created_at DESC";
        
        return $wpdb->get_results($wpdb->prepare($query, $client_id));
    }
    
    // Dismissal operations
    public static function dismiss_notice($notice_id, $client_id, $user_id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'anm_dismissals';
        
        return $wpdb->replace($table, array(
            'notice_id' => $notice_id,
            'client_id' => $client_id,
            'user_id' => $user_id
        ));
    }
    
    public static function is_notice_dismissed($notice_id, $client_id, $user_id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'anm_dismissals';
        
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE notice_id = %d AND client_id = %d AND user_id = %d",
            $notice_id, $client_id, $user_id
        ));
        
        return $count > 0;
    }
    
    public static function update_client_last_connected($client_id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'anm_clients';
        
        return $wpdb->update($table, array('last_connected' => current_time('mysql')), array('id' => $client_id));
    }
}
