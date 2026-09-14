<?php
/**
 * Notice form template (add/edit)
 * File: templates/notice-form.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$is_edit    = isset( $notice );
$page_title = $is_edit ? __( 'Edit Notice', 'admin-notice-manager' ) : __( 'Add New Notice', 'admin-notice-manager' );
$expiration = $is_edit && $notice->expiration_date
	? date_create_immutable_from_format( 'Y-m-d H:i:s', $notice->expiration_date, wp_timezone() )
	: false;
?>

<div class="wrap">
	<h1><?php echo esc_html( $page_title ); ?></h1>

	<form method="post" action="">
		<?php wp_nonce_field( 'anm_action', 'anm_nonce' ); ?>

		<?php if ( $is_edit ) : ?>
			<input type="hidden" name="notice_id" value="<?php echo esc_attr( $notice->id ); ?>">
		<?php endif; ?>

		<table class="form-table">
			<tr>
				<th scope="row">
					<label for="title"><?php _e( 'Title', 'admin-notice-manager' ); ?> <span class="required">*</span></label>
				</th>
				<td>
					<input type="text" name="title" id="title" class="regular-text" value="<?php echo $is_edit ? esc_attr( $notice->title ) : ''; ?>" required>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="content"><?php _e( 'Content', 'admin-notice-manager' ); ?> <span class="required">*</span></label>
				</th>
				<td>
					<?php
					$content = $is_edit ? $notice->content : '';
					wp_editor(
						$content,
						'content',
						array(
							'textarea_name' => 'content',
							'textarea_rows' => 10,
							'media_buttons' => true,
							'teeny'         => false,
						)
					);
					?>
					<p class="description"><?php _e( 'You can add text, HTML, images, and other media.', 'admin-notice-manager' ); ?></p>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="content_type"><?php _e( 'Content Type', 'admin-notice-manager' ); ?></label>
				</th>
				<td>
					<select name="content_type" id="content_type">
						<option value="html" <?php selected( $is_edit ? $notice->content_type : 'html', 'html' ); ?>><?php _e( 'HTML', 'admin-notice-manager' ); ?></option>
						<option value="text" <?php selected( $is_edit ? $notice->content_type : 'html', 'text' ); ?>><?php _e( 'Plain Text', 'admin-notice-manager' ); ?></option>
					</select>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="notice_type"><?php _e( 'Notice Type', 'admin-notice-manager' ); ?></label>
				</th>
				<td>
					<select name="notice_type" id="notice_type">
						<option value="info" <?php selected( $is_edit ? $notice->notice_type : 'info', 'info' ); ?>><?php _e( 'Info', 'admin-notice-manager' ); ?></option>
						<option value="success" <?php selected( $is_edit ? $notice->notice_type : 'info', 'success' ); ?>><?php _e( 'Success', 'admin-notice-manager' ); ?></option>
						<option value="warning" <?php selected( $is_edit ? $notice->notice_type : 'info', 'warning' ); ?>><?php _e( 'Warning', 'admin-notice-manager' ); ?></option>
						<option value="error" <?php selected( $is_edit ? $notice->notice_type : 'info', 'error' ); ?>><?php _e( 'Error', 'admin-notice-manager' ); ?></option>
					</select>
					<p class="description"><?php _e( 'This determines the visual style of the notice.', 'admin-notice-manager' ); ?></p>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="status"><?php _e( 'Status', 'admin-notice-manager' ); ?></label>
				</th>
				<td>
					<select name="status" id="status">
						<option value="active" <?php selected( $is_edit ? $notice->status : 'active', 'active' ); ?>><?php _e( 'Active', 'admin-notice-manager' ); ?></option>
						<option value="inactive" <?php selected( $is_edit ? $notice->status : 'active', 'inactive' ); ?>><?php _e( 'Inactive', 'admin-notice-manager' ); ?></option>
					</select>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="expiration_date"><?php _e( 'Expiration Date', 'admin-notice-manager' ); ?></label>
				</th>
				<td>
					<input type="datetime-local" name="expiration_date" id="expiration_date" value="<?php echo $expiration ? esc_attr( $expiration->format( 'Y-m-d\TH:i' ) ) : ''; ?>">
					<p class="description"><?php _e( 'Leave blank for no expiration.', 'admin-notice-manager' ); ?></p>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="is_dismissible"><?php _e( 'Dismissible', 'admin-notice-manager' ); ?></label>
				</th>
				<td>
					<label>
						<input type="checkbox" name="is_dismissible" id="is_dismissible" value="1" <?php checked( ! $is_edit || $notice->is_dismissible ); ?>>
						<?php _e( 'Allow users to dismiss this notice', 'admin-notice-manager' ); ?>
					</label>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label><?php _e( 'Assign to Clients', 'admin-notice-manager' ); ?></label>
				</th>
				<td>
					<?php if ( empty( $clients ) ) : ?>
						<p class="description"><?php _e( 'No clients found. Add a client first, or save this notice without an assignment.', 'admin-notice-manager' ); ?></p>
					<?php else : ?>
						<div class="anm-client-selector">
							<label class="anm-select-all"><input type="checkbox" id="anm-select-all-clients"> <?php esc_html_e( 'Select all', 'admin-notice-manager' ); ?></label>
							<?php foreach ( $clients as $client ) : ?>
								<label>
									<input type="checkbox" name="client_ids[]" value="<?php echo esc_attr( $client->id ); ?>" <?php checked( $is_edit && in_array( $client->id, $selected_clients, true ) ); ?>>
									<?php echo esc_html( $client->site_name ); ?> (<?php echo esc_html( $client->site_url ); ?>) — <?php echo esc_html( ucfirst( $client->status ) ); ?>
								</label>
							<?php endforeach; ?>
						</div>
						<p class="description"><?php _e( 'Select the clients that should receive this notice. You can leave it unassigned.', 'admin-notice-manager' ); ?></p>
					<?php endif; ?>
				</td>
			</tr>
		</table>

		<p class="submit">
			<button type="submit" name="anm_save_notice" class="button button-primary">
				<?php echo $is_edit ? __( 'Update Notice', 'admin-notice-manager' ) : __( 'Create Notice', 'admin-notice-manager' ); ?>
			</button>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=anm-notices' ) ); ?>" class="button">
				<?php _e( 'Cancel', 'admin-notice-manager' ); ?>
			</a>
		</p>
	</form>
</div>
