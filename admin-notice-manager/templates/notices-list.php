<?php
/**
 * Notices list template.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$message      = isset( $_GET['message'] ) ? sanitize_key( wp_unslash( $_GET['message'] ) ) : '';
$message_text = array(
	'saved'   => __( 'Notice saved successfully.', 'admin-notice-manager' ),
	'deleted' => __( 'Notice deleted successfully.', 'admin-notice-manager' ),
	'invalid' => __( 'A title and content are required.', 'admin-notice-manager' ),
	'error'   => __( 'The notice could not be saved.', 'admin-notice-manager' ),
);
?>

<div class="wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Dashboard Notices', 'admin-notice-manager' ); ?></h1>
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=anm-add-notice' ) ); ?>" class="page-title-action"><?php esc_html_e( 'Add New', 'admin-notice-manager' ); ?></a>
	<hr class="wp-header-end">

	<?php if ( isset( $message_text[ $message ] ) ) : ?>
		<div class="notice <?php echo in_array( $message, array( 'invalid', 'error' ), true ) ? 'notice-error' : 'notice-success'; ?> is-dismissible">
			<p><?php echo esc_html( $message_text[ $message ] ); ?></p>
		</div>
	<?php endif; ?>

	<form method="get">
		<input type="hidden" name="page" value="anm-notices">
		<?php $list_table->search_box( __( 'Search Notices', 'admin-notice-manager' ), 'anm-notices' ); ?>
		<?php $list_table->display(); ?>
	</form>
</div>
