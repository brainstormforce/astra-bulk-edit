<?php
/**
 * PHPUnit bootstrap file for Astra Bulk Edit tests.
 *
 * @package Astra_Bulk_Edit
 */

// Composer autoloader.
$autoloader = dirname( __DIR__ ) . '/vendor/autoload.php';
if ( file_exists( $autoloader ) ) {
	require_once $autoloader;
}

// Define test constants.
define( 'ASTRA_BLK_TEST_DIR', __DIR__ );
define( 'ASTRA_BLK_PLUGIN_DIR', dirname( __DIR__ ) );

// Try to load WordPress test environment.
$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

// Check if WordPress test library is available.
if ( file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	// Give access to tests_add_filter() function.
	require_once $_tests_dir . '/includes/functions.php';

	/**
	 * Manually load the plugin being tested.
	 */
	function _manually_load_plugin() {
		// Define plugin constants if not already defined.
		if ( ! defined( 'ASTRA_BLK_VER' ) ) {
			define( 'ASTRA_BLK_VER', '1.0.0' );
		}
		if ( ! defined( 'ASTRA_BLK_FILE' ) ) {
			define( 'ASTRA_BLK_FILE', dirname( __DIR__ ) . '/astra-bulk-edit.php' );
		}
		if ( ! defined( 'ASTRA_BLK_DIR' ) ) {
			define( 'ASTRA_BLK_DIR', dirname( __DIR__ ) . '/' );
		}
		if ( ! defined( 'ASTRA_BLK_URI' ) ) {
			define( 'ASTRA_BLK_URI', plugin_dir_url( ASTRA_BLK_FILE ) );
		}

		require dirname( __DIR__ ) . '/astra-bulk-edit.php';
	}
	tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

	// Start up the WP testing environment.
	require $_tests_dir . '/includes/bootstrap.php';
} else {
	// WordPress test library not available - load standalone test framework.
	// This allows running unit tests without full WordPress environment.

	// Define WordPress constants for standalone testing.
	if ( ! defined( 'ABSPATH' ) ) {
		define( 'ABSPATH', '/tmp/wordpress/' );
	}

	// Define plugin constants.
	if ( ! defined( 'ASTRA_BLK_VER' ) ) {
		define( 'ASTRA_BLK_VER', '1.0.0' );
	}
	if ( ! defined( 'ASTRA_BLK_FILE' ) ) {
		define( 'ASTRA_BLK_FILE', dirname( __DIR__ ) . '/astra-bulk-edit.php' );
	}
	if ( ! defined( 'ASTRA_BLK_DIR' ) ) {
		define( 'ASTRA_BLK_DIR', dirname( __DIR__ ) . '/' );
	}
	if ( ! defined( 'ASTRA_BLK_URI' ) ) {
		define( 'ASTRA_BLK_URI', 'http://example.com/wp-content/plugins/astra-bulk-edit/' );
	}

	// Load test case base class.
	require_once __DIR__ . '/TestCase.php';
}
