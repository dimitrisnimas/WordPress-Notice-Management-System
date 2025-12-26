<?php
/**
 * Clients list template
 * File: templates/clients-list.php
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Client Sites', 'admin-notice-manager'); ?></h1>
    <a href="#" class="page-title-action" id="anm-add-client-btn"><?php _e('Add New', 'admin-notice-manager'); ?></a>
    <hr class="wp-header-end">
    
    <?php if (isset($_GET['message'])): ?>
        <div class="notice notice-success is-dismissible">
            <p>
                <?php
                if ($_GET['message'] === 'saved') {
                    _e('Client saved successfully.', 'admin-notice-manager');
                } elseif ($_GET['message'] === 'deleted') {
                    _e('Client deleted successfully.', 'admin-notice-manager');
                }
                ?>
            </p>
        </div>
    <?php endif; ?>
    
    <?php if (empty($clients)): ?>
        <p><?php _e('No clients found. Add your first client site to get started.', 'admin-notice-manager'); ?></p>
    <?php else: ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th style="width: 50px;"><?php _e('ID', 'admin-notice-manager'); ?></th>
                    <th><?php _e('Site Name', 'admin-notice-manager'); ?></th>
                    <th><?php _e('Site URL', 'admin-notice-manager'); ?></th>
                    <th><?php _e('Status', 'admin-notice-manager'); ?></th>
                    <th><?php _e('Last Connected', 'admin-notice-manager'); ?></th>
                    <th><?php _e('API Key', 'admin-notice-manager'); ?></th>
                    <th><?php _e('Actions', 'admin-notice-manager'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clients as $client): ?>
                    <tr>
                        <td><?php echo esc_html($client->id); ?></td>
                        <td><strong><?php echo esc_html($client->site_name); ?></strong></td>
                        <td>
                            <a href="<?php echo esc_url($client->site_url); ?>" target="_blank">
                                <?php echo esc_html($client->site_url); ?>
                            </a>
                        </td>
                        <td>
                            <span class="anm-status anm-status-<?php echo esc_attr($client->status); ?>">
                                <?php echo esc_html(ucfirst($client->status)); ?>
                            </span>
                        </td>
                        <td>
                            <?php 
                            if ($client->last_connected) {
                                echo esc_html(human_time_diff(strtotime($client->last_connected), current_time('timestamp'))) . ' ' . __('ago', 'admin-notice-manager');
                            } else {
                                echo '<em>' . __('Never', 'admin-notice-manager') . '</em>';
                            }
                            ?>
                        </td>
                        <td>
                            <code style="font-size: 11px;">
                                <?php echo esc_html(substr($client->api_key, 0, 12) . '...'); ?>
                            </code>
                            <button type="button" class="button button-small anm-copy-api-key" data-api-key="<?php echo esc_attr($client->api_key); ?>">
                                <?php _e('Copy', 'admin-notice-manager'); ?>
                            </button>
                        </td>
                        <td>
                            <a href="<?php echo admin_url('admin.php?page=anm-clients&action=edit&id=' . $client->id); ?>" class="button button-small">
                                <?php _e('Edit', 'admin-notice-manager'); ?>
                            </a>
                            <form method="post" style="display: inline;">
                                <?php wp_nonce_field('anm_action', 'anm_nonce'); ?>
                                <input type="hidden" name="client_id" value="<?php echo esc_attr($client->id); ?>">
                                <button type="submit" name="anm_delete_client" class="button button-small button-link-delete" onclick="return confirm('<?php _e('Are you sure you want to delete this client? This will remove all notice assignments.', 'admin-notice-manager'); ?>');">
                                    <?php _e('Delete', 'admin-notice-manager'); ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    
    <!-- Add Client Modal/Form -->
    <div id="anm-add-client-modal" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.3); z-index: 9999; width: 500px; max-width: 90%;">
        <h2><?php _e('Add New Client', 'admin-notice-manager'); ?></h2>
        <form method="post" action="">
            <?php wp_nonce_field('anm_action', 'anm_nonce'); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="site_name"><?php _e('Site Name', 'admin-notice-manager'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="site_name" id="site_name" class="regular-text" required>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="site_url"><?php _e('Site URL', 'admin-notice-manager'); ?></label>
                    </th>
                    <td>
                        <input type="url" name="site_url" id="site_url" class="regular-text" placeholder="https://client-site.com" required>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="status"><?php _e('Status', 'admin-notice-manager'); ?></label>
                    </th>
                    <td>
                        <select name="status" id="status">
                            <option value="active"><?php _e('Active', 'admin-notice-manager'); ?></option>
                            <option value="inactive"><?php _e('Inactive', 'admin-notice-manager'); ?></option>
                        </select>
                    </td>
                </tr>
            </table>
            
            <p>
                <button type="submit" name="anm_save_client" class="button button-primary">
                    <?php _e('Add Client', 'admin-notice-manager'); ?>
                </button>
                <button type="button" class="button" id="anm-cancel-add-client">
                    <?php _e('Cancel', 'admin-notice-manager'); ?>
                </button>
            </p>
        </form>
    </div>
    
    <div id="anm-modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9998;"></div>
    
    <script>
    jQuery(document).ready(function($) {
        $('#anm-add-client-btn').on('click', function(e) {
            e.preventDefault();
            $('#anm-add-client-modal, #anm-modal-overlay').fadeIn(200);
        });
        
        $('#anm-cancel-add-client, #anm-modal-overlay').on('click', function() {
            $('#anm-add-client-modal, #anm-modal-overlay').fadeOut(200);
        });
    });
    </script>
</div>
