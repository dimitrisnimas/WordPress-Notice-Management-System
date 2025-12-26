<?php
/**
 * Client form template (edit)
 * File: templates/client-form.php
 */

if (!defined('ABSPATH')) {
    exit;
}

$is_edit = isset($client);
$page_title = __('Edit Client', 'admin-notice-manager');
?>

<div class="wrap">
    <h1><?php echo esc_html($page_title); ?></h1>
    
    <form method="post" action="">
        <?php wp_nonce_field('anm_action', 'anm_nonce'); ?>
        
        <input type="hidden" name="client_id" value="<?php echo esc_attr($client->id); ?>">
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="site_name"><?php _e('Site Name', 'admin-notice-manager'); ?> <span class="required">*</span></label>
                </th>
                <td>
                    <input type="text" name="site_name" id="site_name" class="regular-text" value="<?php echo esc_attr($client->site_name); ?>" required>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="site_url"><?php _e('Site URL', 'admin-notice-manager'); ?> <span class="required">*</span></label>
                </th>
                <td>
                    <input type="url" name="site_url" id="site_url" class="regular-text" value="<?php echo esc_attr($client->site_url); ?>" placeholder="https://client-site.com" required>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="status"><?php _e('Status', 'admin-notice-manager'); ?></label>
                </th>
                <td>
                    <select name="status" id="status">
                        <option value="active" <?php selected($client->status, 'active'); ?>><?php _e('Active', 'admin-notice-manager'); ?></option>
                        <option value="inactive" <?php selected($client->status, 'inactive'); ?>><?php _e('Inactive', 'admin-notice-manager'); ?></option>
                    </select>
                    <p class="description"><?php _e('Inactive clients cannot fetch notices even with a valid API key.', 'admin-notice-manager'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label><?php _e('API Key', 'admin-notice-manager'); ?></label>
                </th>
                <td>
                    <div class="anm-api-key-display">
                        <code><?php echo esc_html($client->api_key); ?></code>
                    </div>
                    <button type="button" class="button anm-copy-api-key anm-copy-button" data-api-key="<?php echo esc_attr($client->api_key); ?>">
                        <?php _e('Copy API Key', 'admin-notice-manager'); ?>
                    </button>
                    <p class="description"><?php _e('This unique API key is used by the client site to authenticate with your notice system.', 'admin-notice-manager'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label><?php _e('Client Information', 'admin-notice-manager'); ?></label>
                </th>
                <td>
                    <p>
                        <strong><?php _e('Created:', 'admin-notice-manager'); ?></strong> 
                        <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($client->created_at))); ?>
                    </p>
                    <p>
                        <strong><?php _e('Last Connected:', 'admin-notice-manager'); ?></strong> 
                        <?php 
                        if ($client->last_connected) {
                            echo esc_html(human_time_diff(strtotime($client->last_connected), current_time('timestamp'))) . ' ' . __('ago', 'admin-notice-manager');
                        } else {
                            echo '<em>' . __('Never', 'admin-notice-manager') . '</em>';
                        }
                        ?>
                    </p>
                    <?php
                    $notices_count = ANM_Clients::get_client_notices_count($client->id);
                    ?>
                    <p>
                        <strong><?php _e('Assigned Notices:', 'admin-notice-manager'); ?></strong> 
                        <?php echo esc_html($notices_count); ?>
                    </p>
                </td>
            </tr>
        </table>
        
        <p class="submit">
            <button type="submit" name="anm_save_client" class="button button-primary">
                <?php _e('Update Client', 'admin-notice-manager'); ?>
            </button>
            <a href="<?php echo admin_url('admin.php?page=anm-clients'); ?>" class="button">
                <?php _e('Cancel', 'admin-notice-manager'); ?>
            </a>
        </p>
    </form>
    
    <hr>
    
    <h2><?php _e('Danger Zone', 'admin-notice-manager'); ?></h2>
    
    <form method="post" action="" style="background: #f8d7da; padding: 15px; border-radius: 4px; border-left: 4px solid #d63638;">
        <?php wp_nonce_field('anm_action', 'anm_nonce'); ?>
        <input type="hidden" name="client_id" value="<?php echo esc_attr($client->id); ?>">
        
        <p>
            <strong><?php _e('Delete Client', 'admin-notice-manager'); ?></strong><br>
            <span class="description">
                <?php _e('This will permanently delete this client and remove all notice assignments. This action cannot be undone.', 'admin-notice-manager'); ?>
            </span>
        </p>
        
        <p>
            <button type="submit" name="anm_delete_client" class="button button-secondary" onclick="return confirm('<?php _e('Are you sure you want to delete this client? This will remove all notice assignments and cannot be undone.', 'admin-notice-manager'); ?>');">
                <?php _e('Delete Client', 'admin-notice-manager'); ?>
            </button>
        </p>
    </form>
</div>
