<?php
/**
 * Proves the test harness itself works.
 *
 * This is not a test of plugin behaviour. It asserts the three things every
 * later test depends on: WordPress boots, the database is reachable, and
 * plugin.php was actually loaded. When a Phase 1 regression test fails, this
 * file passing is what tells you the failure is real and not scaffolding.
 *
 * @package Blackbird_Sandbox
 */

/**
 * Harness smoke tests.
 */
class SmokeTest extends WP_UnitTestCase {

	/**
	 * WordPress is loaded, not just PHPUnit.
	 */
	public function test_wordpress_is_loaded() {
		$this->assertTrue( defined( 'ABSPATH' ), 'ABSPATH is not defined; WordPress did not boot.' );
		$this->assertTrue( function_exists( 'add_filter' ), 'WordPress plugin API is unavailable.' );
	}

	/**
	 * The database is reachable and writable.
	 *
	 * Round-trips a post through the factory. Without this, a failing test
	 * could mean a broken database rather than broken code.
	 */
	public function test_database_round_trip() {
		$post_id = self::factory()->post->create(
			array(
				'post_title'  => 'Harness smoke post',
				'post_status' => 'publish',
			)
		);

		$this->assertIsInt( $post_id );
		$this->assertGreaterThan( 0, $post_id );
		$this->assertSame( 'Harness smoke post', get_post( $post_id )->post_title );
	}

	/**
	 * plugin.php was loaded by the bootstrap.
	 *
	 * Asserted through the shortcode tags rather than the plugin's constants
	 * or function names: the tags are a public contract carried in existing
	 * post content, while the constants and prefixes are due to be renamed by
	 * BB-08, BB-09, and BB-10.
	 */
	public function test_plugin_is_loaded() {
		$this->assertTrue( shortcode_exists( 'style-definition' ), '[style-definition] is not registered.' );
		$this->assertTrue( shortcode_exists( 'style-archive' ), '[style-archive] is not registered.' );
	}
}
