<?php
/**
 * BB-02 — [style-archive] must restore the global $post.
 *
 * @package Blackbird_Sandbox
 */

/**
 * Post-data leakage regression for the archive shortcode.
 */
class ArchiveShortcodePostDataTest extends Blackbird_TestCase {

	/**
	 * Content rendered after the shortcode must still see its own post.
	 *
	 * The shortcode runs a WP_Query loop, which reassigns the global $post on
	 * every iteration. Without a reset, everything rendered after it on the
	 * page draws from the last Style Guide entry instead of the page itself.
	 */
	public function test_global_post_is_restored_after_shortcode() {
		$page_id = self::factory()->post->create(
			array(
				'post_type'    => 'page',
				'post_title'   => 'Editorial Style Guide',
				'post_content' => '[style-archive]',
				'post_status'  => 'publish',
			)
		);

		self::factory()->post->create(
			array(
				'post_type'   => self::POST_TYPE,
				'post_title'  => 'Zebra',
				'post_status' => 'publish',
			)
		);

		$this->go_to( get_permalink( $page_id ) );
		the_post();

		$this->assertSame( $page_id, $GLOBALS['post']->ID, 'Test setup failed: global $post is not the page.' );

		do_shortcode( '[style-archive]' );

		$this->assertSame(
			$page_id,
			$GLOBALS['post']->ID,
			'Global $post was left pointing at a Style Guide entry; content after the shortcode will render the wrong post.'
		);
	}

	/**
	 * Template tags after the shortcode must reflect the page, not the loop.
	 *
	 * States the same defect in the terms an author would notice.
	 */
	public function test_the_title_after_shortcode_is_the_page_title() {
		$page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_title'  => 'Editorial Style Guide',
				'post_status' => 'publish',
			)
		);

		self::factory()->post->create(
			array(
				'post_type'   => self::POST_TYPE,
				'post_title'  => 'Zebra',
				'post_status' => 'publish',
			)
		);

		$this->go_to( get_permalink( $page_id ) );
		the_post();

		do_shortcode( '[style-archive]' );

		$this->assertSame( 'Editorial Style Guide', get_the_title(), 'get_the_title() returned a Style Guide entry.' );
	}
}
