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

add_filter( 'get_block_type_variations', 'modify_block_type_variations', 10, 2 );

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


add_filter( 'get_block_type_variations', 'modify_block_type_variations2', 10, 2 );

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


/**
 * Enqueue search template
 */
add_action( 'search_template', 'bb_fund_search_template' );

function bb_fund_search_template( $template ) {
	if ( is_search() && 'fund' === get_query_var( 'post_type' ) ) {
		// return plugin_dir_path( __FILE__ ) . '/templates/funds-search.php';
		// return locate_template( 'blackbird-plugin-templates//fund-search' );  //  redirect to custom-post-type-search.php
		return locate_template( 'wp-custom-template-fund-search-results' );  //  redirect to custom-post-type-search.php
	}

	return $template;
}