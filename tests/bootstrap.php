<?php
/**
 * PHPUnit bootstrap for the Blackbird Sandbox test suite.
 *
 * Runs inside the wp-env `tests-cli` container, where the WordPress PHPUnit
 * library is mounted at /wordpress-phpunit and WP_TESTS_DIR points at it.
 *
 * The guards below fail fast with setup instructions rather than letting a
 * misconfigured run produce a database connection error. They are a backstop,
 * not the only one: a host missing PHPUnit's own required extensions is
 * rejected by PHPUnit before this file is reached.
 *
 * @package Blackbird_Sandbox
 */

$blackbird_autoload = dirname( __DIR__ ) . '/vendor/autoload.php';

if ( ! file_exists( $blackbird_autoload ) ) {
	echo PHP_EOL;
	echo 'Composer dependencies are not installed.' . PHP_EOL;
	echo PHP_EOL;
	echo '    npm run test:php:install' . PHP_EOL;
	echo PHP_EOL;
	exit( 1 );
}

// Must load before the WordPress test bootstrap, which looks for the Yoast
// PHPUnit Polyfills and aborts if they are not already discoverable.
require_once $blackbird_autoload;

$blackbird_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $blackbird_tests_dir ) {
	$blackbird_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! file_exists( $blackbird_tests_dir . '/includes/functions.php' ) ) {
	echo PHP_EOL;
	echo 'Could not find the WordPress test library at ' . $blackbird_tests_dir . PHP_EOL;
	echo PHP_EOL;
	echo 'This suite needs a real WordPress and database. Run it through wp-env:' . PHP_EOL;
	echo PHP_EOL;
	echo '    npm run env:start' . PHP_EOL;
	echo '    npm run test:php' . PHP_EOL;
	echo PHP_EOL;
	exit( 1 );
}

require_once $blackbird_tests_dir . '/includes/functions.php';

/**
 * Load the plugin under test before WordPress finishes booting.
 *
 * muplugins_loaded fires early enough that the plugin's own add_action and
 * add_filter calls land before the hooks they target.
 */
function blackbird_manually_load_plugin() {
	require dirname( __DIR__ ) . '/plugin.php';
}
tests_add_filter( 'muplugins_loaded', 'blackbird_manually_load_plugin' );

require $blackbird_tests_dir . '/includes/bootstrap.php';
