<?php
/**
 * BB-03 — the plugin must not hijack search template resolution.
 *
 * @package Blackbird_Sandbox
 */

/**
 * Guards the removal of the dead search_template override.
 */
class SearchTemplateTest extends Blackbird_TestCase {

	/**
	 * The override is gone, not merely re-registered on the right hook.
	 *
	 * It was hooked with add_action on a filter, so its return value was
	 * discarded and it never had any effect. Registering it correctly would
	 * have made things worse, not better — see the next test.
	 */
	public function test_plugin_does_not_override_search_template() {
		$this->assertFalse(
			function_exists( 'ucscgiving_style_guide_search_template' ),
			'The dead search_template callback still exists.'
		);
		$this->assertFalse(
			has_filter( 'search_template', 'ucscgiving_style_guide_search_template' ),
			'The callback is still registered on search_template.'
		);
		$this->assertFalse(
			has_action( 'search_template', 'ucscgiving_style_guide_search_template' ),
			'The callback is still registered as an action on search_template.'
		);
	}

	/**
	 * The plugin leaves search template resolution alone.
	 *
	 * Asserted by passing a sentinel through the filter rather than by calling
	 * get_search_template(). The test environment pins WP_DEFAULT_THEME to the
	 * 'default' stub, which ships no templates, so real resolution is empty
	 * here whatever the plugin does. Passing a sentinel through measures only
	 * the plugin's own contribution.
	 *
	 * Red against the previous code: the callback matched this query and
	 * returned locate_template( '' ), which is an empty string.
	 */
	public function test_plugin_does_not_alter_the_search_template() {
		self::factory()->post->create(
			array(
				'post_type'   => self::POST_TYPE,
				'post_title'  => 'Serial comma',
				'post_status' => 'publish',
			)
		);

		$this->go_to( home_url( '/?s=comma&post_type=' . self::POST_TYPE ) );

		$this->assertTrue( is_search(), 'Test setup failed: not a search query.' );
		$this->assertSame(
			self::POST_TYPE,
			get_query_var( 'post_type' ),
			'Test setup failed: the query is not scoped to the Style Guide post type.'
		);

		$sentinel = '/theme/search.php';

		$this->assertSame(
			$sentinel,
			apply_filters( 'search_template', $sentinel ),
			'Something in the plugin is still rewriting the search template.'
		);
	}
}
