<?php
/**
 * Shared base for tests that exercise the Style Guide shortcodes.
 *
 * @package Blackbird_Sandbox
 */

/**
 * Registers the post type the plugin assumes and resets the ACF stub.
 */
abstract class Blackbird_TestCase extends WP_UnitTestCase {

	/**
	 * Post type the plugin queries.
	 *
	 * Normally registered by ACF from acf-json/post_type_685d7e97c87c6.json.
	 * ACF is absent here, so the tests register it themselves.
	 */
	const POST_TYPE = 'a_z_style_guide';

	/**
	 * Register the post type and clear stub state.
	 */
	public function set_up() {
		parent::set_up();

		register_post_type(
			self::POST_TYPE,
			array(
				'public'      => true,
				'has_archive' => true,
				'label'       => 'A-Z Style Guide',
			)
		);

		Blackbird_ACF_Stub::reset();
	}

	/**
	 * Unregister and clear.
	 */
	public function tear_down() {
		unregister_post_type( self::POST_TYPE );
		Blackbird_ACF_Stub::reset();

		parent::tear_down();
	}
}
