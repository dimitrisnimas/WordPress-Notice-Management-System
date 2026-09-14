<?php
/**
 * PHPUnit bootstrap file.
 */

$tests_dir   = getenv( 'WP_TESTS_DIR' );
$config_file = getenv( 'WP_TESTS_CONFIG_FILE_PATH' );

if ( ! $tests_dir ) {
	$tests_dir = '/tmp/wordpress-tests-lib';
}

if ( $config_file && ! defined( 'WP_TESTS_CONFIG_FILE_PATH' ) ) {
	define( 'WP_TESTS_CONFIG_FILE_PATH', $config_file );
}

if ( ! file_exists( $tests_dir . '/includes/functions.php' ) ) {
	fwrite( STDERR, "WordPress test suite not found at {$tests_dir}.\n" );
	exit( 1 );
}

require_once $tests_dir . '/includes/functions.php';

tests_add_filter(
	'muplugins_loaded',
	static function () {
		require dirname( __DIR__ ) . '/admin-notice-manager/admin-notice-plugin.php';
		require dirname( __DIR__ ) . '/client-notice-receiver/client-notice-plugin.php';
	}
);

require $tests_dir . '/includes/bootstrap.php';
