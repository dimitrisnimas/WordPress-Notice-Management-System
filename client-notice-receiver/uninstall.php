<?php
/**
 * Remove plugin settings when Client Notice Receiver is uninstalled.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'cnr_api_url' );
delete_option( 'cnr_api_key' );
