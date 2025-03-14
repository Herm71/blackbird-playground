<?php
/**
 * Plugin Name:       Blackbird Sandbox
 * Description:       bb testing plugin
 * Version:           0.1.0
 * Requires at least: 6.1
 * Requires PHP:      7.0
 * Author:            @Herm71
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       birdblocks
 *
 * @package           create-block
 */

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function create_block_birdblocks_block_init() {
	register_block_type( __DIR__ . '/build' );
}
// add_action( 'init', 'create_block_birdblocks_block_init' );

add_action( 'init', 'bb_register_block_template' );

function bb_register_block_template() {
		register_block_template( 'devblog-plugin-templates//my-template', [
    'title'     => __( 'Example', 'devblog-plugin-templates' ),
    'description' => __( 'An example block template from a plugin.', 'devblog-plugin-templates' ),
    'content'   => '
        <!-- wp:template-part {"slug":"header","area":"header","tagName":"header"} /-->
        <!-- wp:group {"tagName":"main"} -->
        <main class="wp-block-group">
            <!-- wp:group {"layout":{"type":"constrained"}} -->
            <div class="wp-block-group">
                <!-- wp:paragraph -->
                <p>This is a plugin-registered template.</p>
                <!-- /wp:paragraph -->
            </div>
            <!-- /wp:group -->
        </main>
        <!-- /wp:group -->
        <!-- wp:template-part {"slug":"footer","area":"footer","tagName":"footer"} /-->'
] );
}


