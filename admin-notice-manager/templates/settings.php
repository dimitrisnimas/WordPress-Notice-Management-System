<?php
/**
 * Settings page template
 * File: templates/settings.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$api_url        = untrailingslashit( rest_url( 'anm/v1' ) );
$total_notices  = count( ANM_Database::get_all_notices() );
$active_notices = count( ANM_Database::get_all_notices( 'active' ) );
$total_clients  = count( ANM_Database::get_all_clients() );
$active_clients = count( ANM_Database::get_all_clients( 'active' ) );
?>

<div class="wrap">
	<h1><?php _e( 'Notice Manager Settings', 'admin-notice-manager' ); ?></h1>


	<div class="anm-client-stats">
		<h3><?php _e( 'Statistics', 'admin-notice-manager' ); ?></h3>

		<div class="anm-stat-box">
			<span class="anm-stat-number"><?php echo esc_html( $active_notices ); ?></span>
			<span class="anm-stat-label"><?php _e( 'Active Notices', 'admin-notice-manager' ); ?></span>
		</div>

		<div class="anm-stat-box">
			<span class="anm-stat-number"><?php echo esc_html( $total_notices ); ?></span>
			<span class="anm-stat-label"><?php _e( 'Total Notices', 'admin-notice-manager' ); ?></span>
		</div>

		<div class="anm-stat-box">
			<span class="anm-stat-number"><?php echo esc_html( $active_clients ); ?></span>
			<span class="anm-stat-label"><?php _e( 'Active Clients', 'admin-notice-manager' ); ?></span>
		</div>

		<div class="anm-stat-box">
			<span class="anm-stat-number"><?php echo esc_html( $total_clients ); ?></span>
			<span class="anm-stat-label"><?php _e( 'Total Clients', 'admin-notice-manager' ); ?></span>
		</div>
	</div>

	<h2><?php _e( 'API Configuration', 'admin-notice-manager' ); ?></h2>

	<p><?php _e( 'Provide the following information to your client sites to connect them to this admin site:', 'admin-notice-manager' ); ?></p>

	<table class="form-table">
		<tr>
			<th scope="row">
				<label><?php _e( 'API URL', 'admin-notice-manager' ); ?></label>
			</th>
			<td>
				<input type="text" value="<?php echo esc_attr( $api_url ); ?>" class="large-text" readonly>
				<button type="button" class="button anm-copy-api-key" data-api-key="<?php echo esc_attr( $api_url ); ?>">
					<?php _e( 'Copy', 'admin-notice-manager' ); ?>
				</button>
				<p class="description">
					<?php _e( 'This is the base URL that client sites will use to connect to your notice system.', 'admin-notice-manager' ); ?>
				</p>
			</td>
		</tr>

		<tr>
			<th scope="row">
				<label><?php _e( 'Individual Client API Keys', 'admin-notice-manager' ); ?></label>
			</th>
			<td>
				<p class="description">
					<?php _e( 'Each client site has a unique API key. You can find and copy these keys from the Clients page.', 'admin-notice-manager' ); ?>
				</p>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=anm-clients' ) ); ?>" class="button">
					<?php _e( 'Manage Clients', 'admin-notice-manager' ); ?>
				</a>
			</td>
		</tr>
	</table>

	<hr>

	<h2><?php _e( 'Setup Instructions for Client Sites', 'admin-notice-manager' ); ?></h2>

	<ol>
		<li><?php _e( 'Install and activate the "Client Notice Receiver" plugin on the client site.', 'admin-notice-manager' ); ?></li>
		<li><?php _e( 'Navigate to Settings → Notice Receiver on the client site.', 'admin-notice-manager' ); ?></li>
		<li><?php /* translators: %s: REST API URL. */ printf( __( 'Enter the API URL: %s', 'admin-notice-manager' ), '<code>' . esc_html( $api_url ) . '</code>' ); ?></li>
		<li><?php _e( 'Enter the unique API Key for that client (found on the Clients page).', 'admin-notice-manager' ); ?></li>
		<li><?php _e( 'Click "Save Changes" and then test the connection.', 'admin-notice-manager' ); ?></li>
		<li><?php _e( 'The client site will now receive notices assigned to it!', 'admin-notice-manager' ); ?></li>
	</ol>

	<hr>

	<h2><?php _e( 'Security Notes', 'admin-notice-manager' ); ?></h2>

	<ul>
		<li><?php _e( 'Each client site has a unique API key for authentication.', 'admin-notice-manager' ); ?></li>
		<li><?php _e( 'API keys should be kept secure and not shared publicly.', 'admin-notice-manager' ); ?></li>
		<li><?php _e( 'You can deactivate a client at any time to prevent them from receiving notices.', 'admin-notice-manager' ); ?></li>
		<li><?php _e( 'Inactive clients cannot fetch notices even with a valid API key.', 'admin-notice-manager' ); ?></li>
	</ul>

</div>
