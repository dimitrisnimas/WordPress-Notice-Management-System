<?php
/**
 * Client form template (edit)
 * File: templates/client-form.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$is_edit    = isset( $client );
$page_title = __( 'Edit Client', 'admin-notice-manager' );
?>

<div class="wrap">
	<h1><?php echo esc_html( $page_title ); ?></h1>

	<?php
	$message = isset( $_GET['message'] ) ? sanitize_key( wp_unslash( $_GET['message'] ) ) : '';
	if ( 'key-rotated' === $message ) :
		?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'The API key was rotated. Update the client site with the new key below.', 'admin-notice-manager' ); ?></p></div>
		<?php
	elseif ( 'key-error' === $message ) :
		?>
		<div class="notice notice-error"><p><?php esc_html_e( 'The API key could not be rotated.', 'admin-notice-manager' ); ?></p></div>
	<?php endif; ?>

	<form method="post" action="">
		<?php wp_nonce_field( 'anm_action', 'anm_nonce' ); ?>

		<input type="hidden" name="client_id" value="<?php echo esc_attr( $client->id ); ?>">

		<table class="form-table">
			<tr>
				<th scope="row">
					<label for="site_name"><?php _e( 'Site Name', 'admin-notice-manager' ); ?> <span class="required">*</span></label>
				</th>
				<td>
					<input type="text" name="site_name" id="site_name" class="regular-text" value="<?php echo esc_attr( $client->site_name ); ?>" required>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="site_url"><?php _e( 'Site URL', 'admin-notice-manager' ); ?> <span class="required">*</span></label>
				</th>
				<td>
					<input type="url" name="site_url" id="site_url" class="regular-text" value="<?php echo esc_attr( $client->site_url ); ?>" placeholder="https://client-site.com" required>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="status"><?php _e( 'Status', 'admin-notice-manager' ); ?></label>
				</th>
				<td>
					<select name="status" id="status">
						<option value="active" <?php selected( $client->status, 'active' ); ?>><?php _e( 'Active', 'admin-notice-manager' ); ?></option>
						<option value="inactive" <?php selected( $client->status, 'inactive' ); ?>><?php _e( 'Inactive', 'admin-notice-manager' ); ?></option>
					</select>
					<p class="description"><?php _e( 'Inactive clients cannot fetch notices even with a valid API key.', 'admin-notice-manager' ); ?></p>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label><?php _e( 'API Key', 'admin-notice-manager' ); ?></label>
				</th>
				<td>
					<div class="anm-api-key-display">
						<code><?php echo esc_html( $client->api_key ); ?></code>
					</div>
					<button type="button" class="button anm-copy-api-key anm-copy-button" data-api-key="<?php echo esc_attr( $client->api_key ); ?>">
						<?php _e( 'Copy API Key', 'admin-notice-manager' ); ?>
					</button>
					<p class="description"><?php _e( 'This unique API key is used by the client site to authenticate with your notice system.', 'admin-notice-manager' ); ?></p>

				</td>
			</tr>

			<tr>
				<th scope="row">
					<label><?php _e( 'Client Information', 'admin-notice-manager' ); ?></label>
				</th>
				<td>
					<p>
						<strong><?php _e( 'Created:', 'admin-notice-manager' ); ?></strong>
						<?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $client->created_at ) ) ); ?>
					</p>
					<p>
						<strong><?php _e( 'Last Connected:', 'admin-notice-manager' ); ?></strong>
						<?php
						if ( $client->last_connected ) {
							$last_connected = date_create_immutable_from_format( 'Y-m-d H:i:s', $client->last_connected, wp_timezone() );
							if ( $last_connected ) {
								echo esc_html( human_time_diff( $last_connected->getTimestamp(), current_datetime()->getTimestamp() ) ) . ' ' . esc_html__( 'ago', 'admin-notice-manager' );
							}
						} else {
							?>
						<em><?php esc_html_e( 'Never', 'admin-notice-manager' ); ?></em>
							<?php
						}
						?>
					</p>
					<?php
					$notices_count = ANM_Database::get_client_notice_count( $client->id );
					?>
					<p>
						<strong><?php _e( 'Assigned Notices:', 'admin-notice-manager' ); ?></strong>
						<?php echo esc_html( $notices_count ); ?>
					</p>
				</td>
			</tr>
		</table>

		<p class="submit">
			<button type="submit" name="anm_save_client" class="button button-primary">
				<?php _e( 'Update Client', 'admin-notice-manager' ); ?>
			</button>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=anm-clients' ) ); ?>" class="button">
				<?php _e( 'Cancel', 'admin-notice-manager' ); ?>
			</a>
		</p>
	</form>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="anm-key-rotation-form">
		<input type="hidden" name="action" value="anm_rotate_client_key">
		<input type="hidden" name="client_id" value="<?php echo esc_attr( $client->id ); ?>">
		<?php wp_nonce_field( 'anm_rotate_client_key_' . $client->id ); ?>
		<h2><?php esc_html_e( 'API Key Rotation', 'admin-notice-manager' ); ?></h2>
		<p class="description"><?php esc_html_e( 'Rotate only this client’s key. Its existing connection will stop until the new key is saved on that site.', 'admin-notice-manager' ); ?></p>
		<p>
			<button type="submit" class="button" onclick="return confirm('<?php echo esc_js( __( 'Rotate this API key? The client site will stop connecting until it is updated.', 'admin-notice-manager' ) ); ?>');">
				<?php esc_html_e( 'Rotate API Key', 'admin-notice-manager' ); ?>
			</button>
		</p>
	</form>

	<hr>

	<h2><?php _e( 'Danger Zone', 'admin-notice-manager' ); ?></h2>

	<form method="get" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="anm-danger-zone">
		<input type="hidden" name="action" value="anm_delete_client">
		<?php wp_nonce_field( 'anm_delete_client_' . $client->id ); ?>
		<input type="hidden" name="client_id" value="<?php echo esc_attr( $client->id ); ?>">

		<p>
			<strong><?php _e( 'Delete Client', 'admin-notice-manager' ); ?></strong><br>
			<span class="description">
				<?php _e( 'This will permanently delete this client and remove all notice assignments. This action cannot be undone.', 'admin-notice-manager' ); ?>
			</span>
		</p>

		<p>
			<button type="submit" name="anm_delete_client" class="button button-secondary" onclick="return confirm('<?php echo esc_js( __( 'Are you sure you want to delete this client? This will remove all notice assignments and cannot be undone.', 'admin-notice-manager' ) ); ?>');">
				<?php _e( 'Delete Client', 'admin-notice-manager' ); ?>
			</button>
		</p>
	</form>
</div>
