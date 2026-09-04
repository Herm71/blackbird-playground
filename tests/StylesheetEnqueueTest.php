<?php
/**
 * BB-06 — the stylesheet version must not depend on filemtime() succeeding.
 *
 * @package Blackbird_Sandbox
 */

/**
 * Enqueue guard regression.
 */
class StylesheetEnqueueTest extends WP_UnitTestCase {

	/**
	 * Handle the plugin registers.
	 */
	const HANDLE = 'blackbird-playground-style';

	/**
	 * Path to the stylesheet the plugin versions against.
	 *
	 * @var string
	 */
	private $stylesheet;

	/**
	 * Path the stylesheet is moved to while simulating its absence.
	 *
	 * @var string
	 */
	private $stashed;

	/**
	 * Resolve paths.
	 */
	public function set_up() {
		parent::set_up();
		$this->stylesheet = dirname( __DIR__ ) . '/style.css';
		$this->stashed    = $this->stylesheet . '.phpunit-stashed';
	}

	/**
	 * Always put the stylesheet back, even if an assertion fails.
	 */
	public function tear_down() {
		if ( file_exists( $this->stashed ) ) {
			rename( $this->stashed, $this->stylesheet );
		}
		parent::tear_down();
	}

	/**
	 * The normal case still gets a cache-busting version.
	 */
	public function test_enqueues_with_a_version_when_stylesheet_exists() {
		blackbird_playground_enqueue_styles();

		$registered = wp_styles()->registered;

		$this->assertArrayHasKey( self::HANDLE, $registered, 'Stylesheet was not registered.' );
		$this->assertNotEmpty( $registered[ self::HANDLE ]->ver, 'No cache-busting version was set.' );
	}

	/**
	 * A missing stylesheet must not raise a warning or set a false version.
	 *
	 * phpunit.xml.dist sets failOnWarning, so an unguarded filemtime() on a
	 * missing file fails this test on the warning alone.
	 */
	public function test_missing_stylesheet_does_not_warn_or_set_false_version() {
		$this->assertTrue( rename( $this->stylesheet, $this->stashed ), 'Could not stash style.css.' );

		blackbird_playground_enqueue_styles();

		$registered = wp_styles()->registered;

		$this->assertArrayHasKey( self::HANDLE, $registered, 'Stylesheet was not registered.' );
		$this->assertNotFalse(
			$registered[ self::HANDLE ]->ver,
			'Version is false; filemtime() failed and its return value was used anyway.'
		);
	}
}
