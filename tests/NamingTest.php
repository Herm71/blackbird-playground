<?php
/**
 * BB-08, BB-09, BB-10 — one prefix, no inherited names, no unguarded defines.
 *
 * @package Blackbird_Sandbox
 */

/**
 * Naming and constant-guard regressions.
 */
class NamingTest extends WP_UnitTestCase {

	/**
	 * Names inherited from unrelated plugins, all of which must be gone.
	 *
	 * @return array[]
	 */
	public function inherited_function_names() {
		return array(
			'ucsccomms ACF save point' => array( 'ucsccomms_acf_json_save_point' ),
			'ucsccomms ACF load point' => array( 'ucsccomms_acf_json_load_point' ),
			'ucscgiving variation'     => array( 'ucscgiving_create_style_guide_search_variation' ),
			'bb_ single loop'          => array( 'bb_a_z_style_guide_single_loop' ),
			'bb_ archive loop'         => array( 'bb_a_z_styles_archive_loop' ),
			'unprefixed query filter'  => array( 'custom_filter_posts' ),
			'old enqueue name'         => array( 'blackbird_playground_enqueue_styles' ),
		);
	}

	/**
	 * @dataProvider inherited_function_names
	 *
	 * @param string $name Function name that must no longer exist.
	 */
	public function test_inherited_function_names_are_gone( $name ) {
		$this->assertFalse( function_exists( $name ), "$name still exists." );
	}

	/**
	 * The replacements exist and are prefixed consistently.
	 *
	 * @return array[]
	 */
	public function replacement_function_names() {
		return array(
			array( 'blackbird_enqueue_styles' ),
			array( 'blackbird_acf_json_save_point' ),
			array( 'blackbird_acf_json_load_point' ),
			array( 'blackbird_style_guide_single_loop' ),
			array( 'blackbird_style_guide_archive_loop' ),
			array( 'blackbird_create_style_guide_search_variation' ),
			array( 'blackbird_filter_style_guide_search' ),
		);
	}

	/**
	 * @dataProvider replacement_function_names
	 *
	 * @param string $name Function name that must exist.
	 */
	public function test_replacement_functions_exist( $name ) {
		$this->assertTrue( function_exists( $name ), "$name is missing." );
		$this->assertStringStartsWith( 'blackbird_', $name );
	}

	/**
	 * Renaming must not silently unhook anything.
	 */
	public function test_replacements_are_still_hooked() {
		$this->assertNotFalse( has_action( 'wp_enqueue_scripts', 'blackbird_enqueue_styles' ) );
		$this->assertNotFalse( has_filter( 'acf/settings/save_json', 'blackbird_acf_json_save_point' ) );
		$this->assertNotFalse( has_filter( 'acf/settings/load_json', 'blackbird_acf_json_load_point' ) );
		$this->assertNotFalse( has_filter( 'get_block_type_variations', 'blackbird_create_style_guide_search_variation' ) );
		$this->assertNotFalse( has_action( 'pre_get_posts', 'blackbird_filter_style_guide_search' ) );
	}

	/**
	 * The shortcode tags are a public contract and must survive the renames.
	 *
	 * They appear in saved post content; renaming a callback is free, renaming
	 * a tag breaks every page already using it.
	 */
	public function test_shortcode_tags_are_unchanged() {
		$this->assertTrue( shortcode_exists( 'style-definition' ) );
		$this->assertTrue( shortcode_exists( 'style-archive' ) );
	}

	/**
	 * Constants are renamed, guarded, and the unused one is gone.
	 */
	public function test_constants() {
		$this->assertTrue( defined( 'BLACKBIRD_PLUGIN_DIR' ), 'BLACKBIRD_PLUGIN_DIR is not defined.' );
		$this->assertFalse( defined( 'UCSCCOMMS_PLUGIN_DIR' ), 'UCSCCOMMS_PLUGIN_DIR still exists.' );
		$this->assertFalse( defined( 'UCSCCOMMS_PLUGIN_BASE' ), 'UCSCCOMMS_PLUGIN_BASE still exists.' );
		$this->assertSame( trailingslashit( dirname( __DIR__ ) ), BLACKBIRD_PLUGIN_DIR );
	}

	/**
	 * Every define() is paired with a guard for that same constant.
	 *
	 * A source-level assertion rather than a behavioural one. The redefinition
	 * notice it protects against only fires when another plugin defines the
	 * same name first, which cannot be staged from inside a suite that has
	 * already loaded plugin.php once.
	 */
	public function test_defines_are_guarded() {
		$source = file_get_contents( dirname( __DIR__ ) . '/plugin.php' );

		preg_match_all( "/define\\(\\s*'([A-Z0-9_]+)'/", $source, $defined );

		$this->assertNotEmpty( $defined[1], 'No define() calls found; this test is not checking anything.' );

		foreach ( $defined[1] as $constant ) {
			$this->assertMatchesRegularExpression(
				"/if\\s*\\(\\s*!\\s*defined\\(\\s*'" . preg_quote( $constant, '/' ) . "'\\s*\\)\\s*\\)/",
				$source,
				"define( '$constant' ) is not wrapped in a defined() guard."
			);
		}
	}

	/**
	 * The ACF JSON paths still resolve after the constant rename.
	 */
	public function test_acf_json_paths_still_resolve() {
		$expected = BLACKBIRD_PLUGIN_DIR . 'acf-json';

		$this->assertSame( $expected, blackbird_acf_json_save_point( '/somewhere/else' ) );
		$this->assertContains( $expected, blackbird_acf_json_load_point( array( '/default/path' ) ) );
	}
}
