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
		register_block_template(
			'devblog-plugin-templates//my-template',
			array(
				'title'       => __( 'Example', 'devblog-plugin-templates' ),
				'description' => __( 'An example block template from a plugin.', 'devblog-plugin-templates' ),
				'content'     => '
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
        <!-- wp:template-part {"slug":"footer","area":"footer","tagName":"footer"} /-->',
			)
		);
}

add_filter( 'get_block_type_variations', 'modify_block_type_variations', 10, 2 );
add_filter( 'get_block_type_variations', 'modify_block_type_variations2', 10, 2 );

function modify_block_type_variations( $variations, $block_type ) {
	if ( 'core/search' !== $block_type->name ) {
			return $variations;
	}

		$variations[] = array(
			'name'        => 'fund-search',
			'title'       => __( 'Fund Search', 'birdblocks' ),
			'description' => __( 'Search only for fund posts', 'birdblocks' ),
			'attributes'  => array(
				'query' => array(
					'post_type' => 'fund',
				),
			),
		);

		return $variations;
}

function modify_block_type_variations2( $variations, $block_type ) {
	if ( 'core/search' !== $block_type->name ) {
			return $variations;
	} elseif ( 'core/search' === $block_type->name ) {
			$variations[] = array(
				'name'        => 'key-search',
				'title'       => __( 'Keyword Search', 'birdblocks' ),
				'description' => __( 'Search only in keyword taxonomy', 'birdblocks' ),
				'attributes'  => array(
					'query' => array(
						'taxonomy' => 'keyword',
					),
				),
			);
	}

		return $variations;
}

// namespace birdblocks;

function render_search_template( $template ) {
   global $wp_query;
   $post_type = get_query_var( 'post_type' );

   if ( ! empty( $wp_query->is_search ) && $post_type == 'fund') {
      return locate_template( 'wp-custom-template-fund-search-results.html' );  //  redirect to custom-post-type-search.php
   }

   return $template;
}

// add_filter( 'template_include', __NAMESPACE__ . '\\render_search_template' );