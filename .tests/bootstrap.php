<?php
/**
 * Bootstrap the plugin unit testing environment.
 */

$test_root = getenv( 'WP_TESTS_DIR' );
if ( empty( $test_root ) || getenv( 'DEV_LIB_PATH' ) ) {
	$test_root = dirname( __FILE__ ) . '/lib';
}

require $test_root . '/includes/functions.php';

require $test_root . '/includes/bootstrap.php';
