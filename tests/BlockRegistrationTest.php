<?php
/**
 * BB-04 — the compiled birdblocks block must be registered with WordPress.
 *
 * @package Blackbird_Sandbox
 */

/**
 * Block registration regression.
 */
class BlockRegistrationTest extends WP_UnitTestCase {

	/**
	 * Block the plugin registers.
	 */
	const BLOCK_NAME = 'blackbird/birdblocks';

	/**
	 * Registration reads build/, which is gitignored and only exists after a
	 * build. npm run test:php builds first; a bare in-container composer test
	 * may not have.
	 */
	public function set_up() {
		parent::set_up();

		if ( ! file_exists( dirname( __DIR__ ) . '/build/block.json' ) ) {
			$this->markTestSkipped( 'build/block.json is absent. Run npm run build.' );
		}
	}

	/**
	 * Registration has to happen on init, not at file scope.
	 *
	 * register_block_type_from_metadata() needs the block registry and the
	 * script registry, neither of which exists when plugin.php is included.
	 */
	public function test_registration_is_hooked_on_init() {
		$this->assertNotFalse(
			has_action( 'init', 'blackbird_register_blocks' ),
			'blackbird_register_blocks is not hooked on init.'
		);
	}

	/**
	 * Without this the block cannot be inserted in the editor at all.
	 */
	public function test_block_type_is_registered() {
		$this->assertTrue(
			WP_Block_Type_Registry::get_instance()->is_registered( self::BLOCK_NAME ),
			self::BLOCK_NAME . ' is not in the block registry.'
		);
	}

	/**
	 * The registration must come from block.json, not a hand-written array.
	 *
	 * Asserting the metadata round-trips catches a register_block_type() call
	 * that skips the compiled metadata and quietly drops the attribute.
	 */
	public function test_block_carries_its_compiled_metadata() {
		$block_type = WP_Block_Type_Registry::get_instance()->get_registered( self::BLOCK_NAME );

		$this->assertSame( 'Blackbird Blocks', $block_type->title );
		$this->assertSame( 'text', $block_type->category );
		$this->assertArrayHasKey( 'message', $block_type->attributes, 'The message attribute is missing.' );
		$this->assertSame( 'string', $block_type->attributes['message']['type'] );
	}

	/**
	 * The metadata points at file:./ assets. If those handles do not resolve,
	 * the block registers but renders unstyled with no editor script.
	 */
	public function test_build_assets_are_registered() {
		$block_type = WP_Block_Type_Registry::get_instance()->get_registered( self::BLOCK_NAME );

		$this->assertNotEmpty( $block_type->editor_script_handles, 'No editor script handle.' );
		$this->assertTrue(
			wp_script_is( $block_type->editor_script_handles[0], 'registered' ),
			'The editor script handle is not registered; build/index.js was not found.'
		);

		$this->assertNotEmpty( $block_type->style_handles, 'No front-end style handle.' );
		$this->assertTrue(
			wp_style_is( $block_type->style_handles[0], 'registered' ),
			'The front-end style handle is not registered; build/style-index.css was not found.'
		);
	}
}
