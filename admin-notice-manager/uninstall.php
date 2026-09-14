<?php
/**
 * Remove plugin data when Admin Notice Manager is uninstalled.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

$tables = array(
	$wpdb->prefix . 'anm_dismissals',
	$wpdb->prefix . 'anm_notice_clients',
	$wpdb->prefix . 'anm_clients',
	$wpdb->prefix . 'anm_notices',
);

foreach ( $tables as $table ) {
	$wpdb->query( $wpdb->prepare( 'DROP TABLE IF EXISTS %i', $table ) );
}

delete_option( 'anm_master_api_key' );
