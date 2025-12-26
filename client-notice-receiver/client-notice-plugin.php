<?php
/**
 * Plugin Name: Client Notice Receiver
 * Plugin URI: https://dimitrisnimas.gr
 * Description: Receives and displays dashboard notices from the admin site
 * Version: 1.0.0
 * Author: Dimitris Nimas
 * Author URI: https://dimitrisnimas.gr
 * License: GPL v2 or later
 * Text Domain: client-notice-receiver
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('CNR_VERSION', '1.0.0');
define('CNR_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CNR_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Main Plugin Class
 */
class Client_Notice_Receiver {
    
    private static $instance = null;
    private $api_url;
    private $api_key;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->api_url = get_option('cnr_api_url', '');
        $this->api_key = get_option('cnr_api_key', '');
        
        $this->init_hooks();
    }
    
    private function init_hooks() {
        register_activation_hook(__FILE__, array($this, 'activate'));
        
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_notices', array($this, 'display_notices'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('wp_ajax_cnr_dismiss_notice', array($this, 'ajax_dismiss_notice'));
        add_action('admin_init', array($this, 'check_configuration'));
    }
    
    public function activate() {
        // Nothing specific needed on activation
    }
    
    public function check_configuration() {
        // Show admin notice if not configured
        if (empty($this->api_url) || empty($this->api_key)) {
            add_action('admin_notices', array($this, 'show_config_notice'));
        }
    }
    
    public function show_config_notice() {
        $settings_url = admin_url('options-general.php?page=cnr-settings');
        ?>
        <div class="notice notice-warning">
            <p>
                <strong><?php _e('Client Notice Receiver:', 'client-notice-receiver'); ?></strong>
                <?php printf(__('Please <a href="%s">configure the plugin</a> to receive notices from the admin site.', 'client-notice-receiver'), esc_url($settings_url)); ?>
            </p>
        </div>
        <?php
    }
    
    public function add_settings_page() {
        add_options_page(
            __('Notice Receiver Settings', 'client-notice-receiver'),
            __('Notice Receiver', 'client-notice-receiver'),
            'manage_options',
            'cnr-settings',
            array($this, 'settings_page')
        );
    }
    
    public function register_settings() {
        register_setting('cnr_settings', 'cnr_api_url', array(
            'sanitize_callback' => 'esc_url_raw'
        ));
        
        register_setting('cnr_settings', 'cnr_api_key', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
    }
    
    public function settings_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Client Notice Receiver Settings', 'client-notice-receiver'); ?></h1>
            
            <form method="post" action="options.php">
                <?php settings_fields('cnr_settings'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="cnr_api_url"><?php _e('Admin Site API URL', 'client-notice-receiver'); ?></label>
                        </th>
                        <td>
                            <input type="url" name="cnr_api_url" id="cnr_api_url" value="<?php echo esc_attr($this->api_url); ?>" class="regular-text" placeholder="https://admin-site.com/wp-json/anm/v1">
                            <p class="description"><?php _e('Enter the API URL from your admin site (e.g., https://admin-site.com/wp-json/anm/v1)', 'client-notice-receiver'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="cnr_api_key"><?php _e('API Key', 'client-notice-receiver'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="cnr_api_key" id="cnr_api_key" value="<?php echo esc_attr($this->api_key); ?>" class="regular-text">
                            <p class="description"><?php _e('Enter the API key provided by your admin site.', 'client-notice-receiver'); ?></p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
            
            <?php if (!empty($this->api_url) && !empty($this->api_key)): ?>
                <hr>
                <h2><?php _e('Test Connection', 'client-notice-receiver'); ?></h2>
                <button type="button" class="button" id="cnr-test-connection"><?php _e('Test API Connection', 'client-notice-receiver'); ?></button>
                <div id="cnr-test-result" style="margin-top: 10px;"></div>
                
                <script>
                jQuery(document).ready(function($) {
                    $('#cnr-test-connection').on('click', function() {
                        var button = $(this);
                        var resultDiv = $('#cnr-test-result');
                        
                        button.prop('disabled', true).text('<?php _e('Testing...', 'client-notice-receiver'); ?>');
                        resultDiv.html('');
                        
                        $.ajax({
                            url: '<?php echo esc_js($this->api_url); ?>/ping',
                            method: 'GET',
                            headers: {
                                'X-ANM-API-Key': '<?php echo esc_js($this->api_key); ?>'
                            },
                            success: function(response) {
                                resultDiv.html('<div class="notice notice-success inline"><p><strong><?php _e('Success!', 'client-notice-receiver'); ?></strong> ' + response.message + '</p></div>');
                            },
                            error: function(xhr) {
                                var errorMsg = xhr.responseJSON ? xhr.responseJSON.message : '<?php _e('Connection failed. Please check your settings.', 'client-notice-receiver'); ?>';
                                resultDiv.html('<div class="notice notice-error inline"><p><strong><?php _e('Error:', 'client-notice-receiver'); ?></strong> ' + errorMsg + '</p></div>');
                            },
                            complete: function() {
                                button.prop('disabled', false).text('<?php _e('Test API Connection', 'client-notice-receiver'); ?>');
                            }
                        });
                    });
                });
                </script>
            <?php endif; ?>
        </div>
        <?php
    }
    
    public function display_notices() {
        // Only show on dashboard and main admin pages
        $screen = get_current_screen();
        if (!$screen || $screen->id !== 'dashboard') {
            return;
        }
        
        if (empty($this->api_url) || empty($this->api_key)) {
            return;
        }
        
        $notices = $this->fetch_notices();
        
        if (empty($notices)) {
            return;
        }
        
        foreach ($notices as $notice) {
            $this->render_notice($notice);
        }
    }
    
    private function fetch_notices() {
        $user_id = get_current_user_id();
        
        $response = wp_remote_get($this->api_url . '/notices?user_id=' . $user_id, array(
            'headers' => array(
                'X-ANM-API-Key' => $this->api_key
            ),
            'timeout' => 10
        ));
        
        if (is_wp_error($response)) {
            error_log('CNR: Failed to fetch notices - ' . $response->get_error_message());
            return array();
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (!isset($data['success']) || !$data['success']) {
            return array();
        }
        
        return isset($data['notices']) ? $data['notices'] : array();
    }
    
    private function render_notice($notice) {
        $notice_class = 'notice notice-' . esc_attr($notice['notice_type']);
        $dismissable_class = $notice['is_dismissable'] ? ' is-dismissible' : '';
        
        ?>
        <div class="<?php echo $notice_class . $dismissable_class; ?> cnr-notice" data-notice-id="<?php echo esc_attr($notice['id']); ?>" style="position: relative;">
            <?php if (!empty($notice['title'])): ?>
                <h3 style="margin-top: 0;"><?php echo esc_html($notice['title']); ?></h3>
            <?php endif; ?>
            
            <div class="cnr-notice-content">
                <?php
                if ($notice['content_type'] === 'html') {
                    echo wp_kses_post($notice['content']);
                } else {
                    echo '<p>' . esc_html($notice['content']) . '</p>';
                }
                ?>
            </div>
            
            <?php if ($notice['is_dismissable']): ?>
                <button type="button" class="notice-dismiss cnr-dismiss-notice">
                    <span class="screen-reader-text"><?php _e('Dismiss this notice.', 'client-notice-receiver'); ?></span>
                </button>
            <?php endif; ?>
        </div>
        <?php
    }
    
    public function enqueue_assets() {
        wp_enqueue_style('cnr-style', CNR_PLUGIN_URL . 'assets/css/style.css', array(), CNR_VERSION);
        wp_enqueue_script('cnr-script', CNR_PLUGIN_URL . 'assets/js/script.js', array('jquery'), CNR_VERSION, true);
        
        wp_localize_script('cnr-script', 'cnrData', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('cnr_nonce'),
            'apiUrl' => $this->api_url,
            'apiKey' => $this->api_key,
            'userId' => get_current_user_id()
        ));
    }
    
    public function ajax_dismiss_notice() {
        check_ajax_referer('cnr_nonce', 'nonce');
        
        $notice_id = isset($_POST['notice_id']) ? intval($_POST['notice_id']) : 0;
        $user_id = get_current_user_id();
        
        if (!$notice_id || !$user_id) {
            wp_send_json_error(array('message' => __('Invalid request.', 'client-notice-receiver')));
        }
        
        // Send dismissal to admin site
        $response = wp_remote_post($this->api_url . '/dismiss/' . $notice_id, array(
            'headers' => array(
                'X-ANM-API-Key' => $this->api_key,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode(array('user_id' => $user_id)),
            'timeout' => 10
        ));
        
        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => $response->get_error_message()));
        }
        
        wp_send_json_success(array('message' => __('Notice dismissed.', 'client-notice-receiver')));
    }
}

// Initialize the plugin
function cnr_init() {
    return Client_Notice_Receiver::get_instance();
}
add_action('plugins_loaded', 'cnr_init');
