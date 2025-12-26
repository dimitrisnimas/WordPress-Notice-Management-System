<?php
/**
 * Notice form template (add/edit)
 * File: templates/notice-form.php
 */

if (!defined('ABSPATH')) {
    exit;
}

$is_edit = isset($notice);
$page_title = $is_edit ? __('Edit Notice', 'admin-notice-manager') : __('Add New Notice', 'admin-notice-manager');
?>

<div class="wrap">
    <h1><?php echo esc_html($page_title); ?></h1>
    
    <form method="post" action="">
        <?php wp_nonce_field('anm_action', 'anm_nonce'); ?>
        
        <?php if ($is_edit): ?>
            <input type="hidden" name="notice_id" value="<?php echo esc_attr($notice->id); ?>">
        <?php endif; ?>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="title"><?php _e('Title', 'admin-notice-manager'); ?> <span class="required">*</span></label>
                </th>
                <td>
                    <input type="text" name="title" id="title" class="regular-text" value="<?php echo $is_edit ? esc_attr($notice->title) : ''; ?>" required>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="content"><?php _e('Content', 'admin-notice-manager'); ?> <span class="required">*</span></label>
                </th>
                <td>
                    <?php
                    $content = $is_edit ? $notice->content : '';
                    wp_editor($content, 'content', array(
                        'textarea_name' => 'content',
                        'textarea_rows' => 10,
                        'media_buttons' => true,
                        'teeny' => false
                    ));
                    ?>
                    <p class="description"><?php _e('You can add text, HTML, images, and other media.', 'admin-notice-manager'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="content_type"><?php _e('Content Type', 'admin-notice-manager'); ?></label>
                </th>
                <td>
                    <select name="content_type" id="content_type">
                        <option value="html" <?php echo ($is_edit && $notice->content_type === 'html') ? 'selected' : ''; ?>><?php _e('HTML', 'admin-notice-manager'); ?></option>
                        <option value="text" <?php echo ($is_edit && $notice->content_type === 'text') ? 'selected' : ''; ?>><?php _e('Plain Text', 'admin-notice-manager'); ?></option>
                    </select>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="notice_type"><?php _e('Notice Type', 'admin-notice-manager'); ?></label>
                </th>
                <td>
                    <select name="notice_type" id="notice_type">
                        <option value="info" <?php echo ($is_edit && $notice->notice_type === 'info') ? 'selected' : ''; ?>><?php _e('Info', 'admin-notice-manager'); ?></option>
                        <option value="success" <?php echo ($is_edit && $notice->notice_type === 'success') ? 'selected' : ''; ?>><?php _e('Success', 'admin-notice-manager'); ?></option>
                        <option value="warning" <?php echo ($is_edit && $notice->notice_type === 'warning') ? 'selected' : ''; ?>><?php _e('Warning', 'admin-notice-manager'); ?></option>
                        <option value="error" <?php echo ($is_edit && $notice->notice_type === 'error') ? 'selected' : ''; ?>><?php _e('Error', 'admin-notice-manager'); ?></option>
                    </select>
                    <p class="description"><?php _e('This determines the visual style of the notice.', 'admin-notice-manager'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="status"><?php _e('Status', 'admin-notice-manager'); ?></label>
                </th>
                <td>
                    <select name="status" id="status">
                        <option value="active" <?php echo ($is_edit && $notice->status === 'active') ? 'selected' : ''; ?>><?php _e('Active', 'admin-notice-manager'); ?></option>
                        <option value="inactive" <?php echo ($is_edit && $notice->status === 'inactive') ? 'selected' : ''; ?>><?php _e('Inactive', 'admin-notice-manager'); ?></option>
                    </select>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="expiration_date"><?php _e('Expiration Date', 'admin-notice-manager'); ?></label>
                </th>
                <td>
                    <input type="datetime-local" name="expiration_date" id="expiration_date" value="<?php echo $is_edit && $notice->expiration_date ? esc_attr(date('Y-m-d\TH:i', strtotime($notice->expiration_date))) : ''; ?>">
                    <p class="description"><?php _e('Leave blank for no expiration.', 'admin-notice-manager'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="is_dismissable"><?php _e('Dismissable', 'admin-notice-manager'); ?></label>
                </th>
                <td>
                    <label>
                        <input type="checkbox" name="is_dismissable" id="is_dismissable" value="1" <?php echo (!$is_edit || $notice->is_dismissable) ? 'checked' : ''; ?>>
                        <?php _e('Allow users to dismiss this notice', 'admin-notice-manager'); ?>
                    </label>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label><?php _e('Assign to Clients', 'admin-notice-manager'); ?> <span class="required">*</span></label>
                </th>
                <td>
                    <?php if (empty($clients)): ?>
                        <p class="description"><?php _e('No active clients found. Please add clients first.', 'admin-notice-manager'); ?></p>
                    <?php else: ?>
                        <div style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; background: #f9f9f9;">
                            <?php foreach ($clients as $client): ?>
                                <label style="display: block; margin-bottom: 5px;">
                                    <input type="checkbox" name="client_ids[]" value="<?php echo esc_attr($client->id); ?>" <?php echo ($is_edit && in_array($client->id, $selected_clients)) ? 'checked' : ''; ?>>
                                    <?php echo esc_html($client->site_name); ?> (<?php echo esc_html($client->site_url); ?>)
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <p class="description"><?php _e('Select one or more clients to receive this notice.', 'admin-notice-manager'); ?></p>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
        
        <p class="submit">
            <button type="submit" name="anm_save_notice" class="button button-primary">
                <?php echo $is_edit ? __('Update Notice', 'admin-notice-manager') : __('Create Notice', 'admin-notice-manager'); ?>
            </button>
            <a href="<?php echo admin_url('admin.php?page=anm-notices'); ?>" class="button">
                <?php _e('Cancel', 'admin-notice-manager'); ?>
            </a>
        </p>
    </form>
</div>
