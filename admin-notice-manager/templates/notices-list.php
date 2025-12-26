<?php
/**
 * Notices list template
 * File: templates/notices-list.php
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Dashboard Notices', 'admin-notice-manager'); ?></h1>
    <a href="<?php echo admin_url('admin.php?page=anm-add-notice'); ?>" class="page-title-action"><?php _e('Add New', 'admin-notice-manager'); ?></a>
    <hr class="wp-header-end">
    
    <?php if (isset($_GET['message'])): ?>
        <div class="notice notice-success is-dismissible">
            <p>
                <?php
                if ($_GET['message'] === 'saved') {
                    _e('Notice saved successfully.', 'admin-notice-manager');
                } elseif ($_GET['message'] === 'deleted') {
                    _e('Notice deleted successfully.', 'admin-notice-manager');
                }
                ?>
            </p>
        </div>
    <?php endif; ?>
    
    <?php if (empty($notices)): ?>
        <p><?php _e('No notices found. Create your first notice to get started.', 'admin-notice-manager'); ?></p>
    <?php else: ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th style="width: 50px;"><?php _e('ID', 'admin-notice-manager'); ?></th>
                    <th><?php _e('Title', 'admin-notice-manager'); ?></th>
                    <th><?php _e('Type', 'admin-notice-manager'); ?></th>
                    <th><?php _e('Status', 'admin-notice-manager'); ?></th>
                    <th><?php _e('Clients', 'admin-notice-manager'); ?></th>
                    <th><?php _e('Expiration', 'admin-notice-manager'); ?></th>
                    <th><?php _e('Created', 'admin-notice-manager'); ?></th>
                    <th><?php _e('Actions', 'admin-notice-manager'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($notices as $notice): ?>
                    <?php
                    $client_ids = ANM_Database::get_notice_clients($notice->id);
                    $client_count = count($client_ids);
                    ?>
                    <tr>
                        <td><?php echo esc_html($notice->id); ?></td>
                        <td><strong><?php echo esc_html($notice->title); ?></strong></td>
                        <td>
                            <span class="anm-badge anm-badge-<?php echo esc_attr($notice->notice_type); ?>">
                                <?php echo esc_html(ucfirst($notice->notice_type)); ?>
                            </span>
                        </td>
                        <td>
                            <span class="anm-status anm-status-<?php echo esc_attr($notice->status); ?>">
                                <?php echo esc_html(ucfirst($notice->status)); ?>
                            </span>
                        </td>
                        <td><?php echo esc_html($client_count); ?> <?php _e('client(s)', 'admin-notice-manager'); ?></td>
                        <td>
                            <?php 
                            if ($notice->expiration_date) {
                                echo esc_html(date_i18n(get_option('date_format'), strtotime($notice->expiration_date)));
                            } else {
                                echo '<em>' . __('Never', 'admin-notice-manager') . '</em>';
                            }
                            ?>
                        </td>
                        <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($notice->created_at))); ?></td>
                        <td>
                            <a href="<?php echo admin_url('admin.php?page=anm-notices&action=edit&id=' . $notice->id); ?>" class="button button-small">
                                <?php _e('Edit', 'admin-notice-manager'); ?>
                            </a>
                            <form method="post" style="display: inline;">
                                <?php wp_nonce_field('anm_action', 'anm_nonce'); ?>
                                <input type="hidden" name="notice_id" value="<?php echo esc_attr($notice->id); ?>">
                                <button type="submit" name="anm_delete_notice" class="button button-small button-link-delete" onclick="return confirm('<?php _e('Are you sure you want to delete this notice?', 'admin-notice-manager'); ?>');">
                                    <?php _e('Delete', 'admin-notice-manager'); ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div