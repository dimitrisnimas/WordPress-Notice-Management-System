<?php
/**
 * Clients list template.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$message      = isset( $_GET['message'] ) ? sanitize_key( wp_unslash( $_GET['message'] ) ) : '';
$message_text = array(
	'saved'   => __( 'Client saved successfully.', 'admin-notice-manager' ),
	'deleted' => __( 'Client deleted successfully.', 'admin-notice-manager' ),
	'invalid' => __( 'Enter a site name and a valid HTTP or HTTPS URL.', 'admin-notice-manager' ),
	'error'   => __( 'The client could not be saved.', 'admin-notice-manager' ),
);
?>

<div class="wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Client Sites', 'admin-notice-manager' ); ?></h1>
	<button type="button" class="page-title-action" id="anm-add-client-btn"><?php esc_html_e( 'Add New', 'admin-notice-manager' ); ?></button>
	<hr class="wp-header-end">

	<?php if ( isset( $message_text[ $message ] ) ) : ?>
		<div class="notice <?php echo in_array( $message, array( 'invalid', 'error' ), true ) ? 'notice-error' : 'notice-success'; ?> is-dismissible">
			<p><?php echo esc_html( $message_text[ $message ] ); ?></p>
		</div>
	<?php endif; ?>

	<form method="get">
		<input type="hidden" name="page" value="anm-clients">
		<?php $list_table->search_box( __( 'Search Clients', 'admin-notice-manager' ), 'anm-clients' ); ?>
		<?php $list_table->display(); ?>
	</form>

	<div id="anm-add-client-modal" class="anm-modal" role="dialog" aria-modal="true" aria-labelledby="anm-add-client-title" hidden>
		<h2 id="anm-add-client-title"><?php esc_html_e( 'Add New Client', 'admin-notice-manager' ); ?></h2>
		<form method="post">
			<?php wp_nonce_field( 'anm_action', 'anm_nonce' ); ?>

			<table class="form-table">
				<tr>
					<th scope="row"><label for="site_name"><?php esc_html_e( 'Site Name', 'admin-notice-manager' ); ?></label></th>
					<td><input type="text" name="site_name" id="site_name" class="regular-text" required></td>
				</tr>
				<tr>
					<th scope="row"><label for="site_url"><?php esc_html_e( 'Site URL', 'admin-notice-manager' ); ?></label></th>
					<td><input type="url" name="site_url" id="site_url" class="regular-text" placeholder="https://client-site.com" required></td>
				</tr>
				<tr>
					<th scope="row"><label for="status"><?php esc_html_e( 'Status', 'admin-notice-manager' ); ?></label></th>
					<td>
						<select name="status" id="status">
							<option value="active"><?php esc_html_e( 'Active', 'admin-notice-manager' ); ?></option>
							<option value="inactive"><?php esc_html_e( 'Inactive', 'admin-notice-manager' ); ?></option>
						</select>
					</td>
				</tr>
			</table>

			<p>
				<button type="submit" name="anm_save_client" class="button button-primary"><?php esc_html_e( 'Add Client', 'admin-notice-manager' ); ?></button>
				<button type="button" class="button" id="anm-cancel-add-client"><?php esc_html_e( 'Cancel', 'admin-notice-manager' ); ?></button>
			</p>
		</form>
	</div>

	<div id="anm-modal-overlay" class="anm-modal-overlay" hidden></div>
</div>
