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
// function create_block_birdblocks_block_init() {
// register_block_type( __DIR__ . '/build' );
// }
// add_action( 'init', 'create_block_birdblocks_block_init' );




/**
 * Enqueue search template
 * description: Filters the path of the queried template by type.
 * see: https://developer.wordpress.org/reference/hooks/type_template/
 * see: https://tommcfarlin.com/custom-templates-in-our-wordpress-plugin/
 *
 * @param string $template
 * @return string
 */
// add_action( 'search_template', 'bb_fund_search_template' );

function bb_fund_search_template( $template ) {
	if ( is_search() && 'fund' === get_query_var( 'post_type' ) ) {
		return plugin_dir_path( __FILE__ ) . '/templates/funds-search.php';
		// return locate_template( 'blackbird-plugin-templates//fund-search' );
		// return locate_template( 'wp-custom-template-fund-search-results.html' );
		// return locate_template( '' ); // this will return search results in the archive template
	}

	return $template;
}

// add_action( 'init', 'ucscgiving_register_featured_image_block_binding' );
/**
 * Register Custom Block Binding Source
 *
 * Registers a custom callback that concatenates
 * the Giving BASE url with the Fund Designation Code
 *
 * @return void
 */
// function ucscgiving_register_featured_image_block_binding() {
// 	register_block_bindings_source(
// 		'ucscgiving/featured-image',
// 		array(
// 			'label'              => __( 'Featured Image', 'ucscgiving' ),
// 			'get_value_callback' => 'ucscgiving_featured_image',
// 		)
// 	);
// }


	// Example: Binding the Image block's src attribute to the featured image URL
add_filter( 'block_bindings_source_value', 'function_name', 10, 5 );
/**
 * Filter the value of a block binding source
 *
 * @param mixed    $value The value of the block binding source.
 * @param string   $name The name of the block binding source.
 * @param array    $source_args The arguments passed to the block binding source callback.
 * @param WP_Block $block_instance The block instance.
 * @param string   $attribute_name The name of the attribute.
 * @return mixed
 */
function function_name( $value, $name, $source_args, $block_instance, $attribute_name ) {
	if ( $name === 'post-featured-image' && $attribute_name === 'src' ) {
		$image_url = wp_get_attachment_image_url( get_post_thumbnail_id(), 'full' );
		return $image_url ? $image_url : 'assets/lucy.jpg'; // Placeholder image
	}
		return $value;
}


// add_action( 'init', 'wpse_register_block_bindings' );

function wpse_register_block_bindings() {
    register_block_bindings_source( 'wpse/featured-image', array(
        'label'              => esc_html__( 'Featured Image', 'wpse' ),
        'get_value_callback' => 'wpse_featured_image_bindings'
    ) );
}

function wpse_featured_image_bindings( $args ) {
    if ( ! isset( $args['key'] ) ) {
        return null;
    }

    if ( ! has_post_thumbnail() ) {
        return null;
    }

    $id  = get_post_thumbnail_id();
    $url = get_the_post_thumbnail_url();
    $alt = get_post_meta( $id, '_wp_attachment_image_alt', true );
		$image_url = 'assets/lucy.jpg';

    switch ( $args['key'] ) {
        case 'url':
            return esc_url( $image_url );
        case 'alt':
            return  esc_attr( $alt );
        default:
            return null;
    }
}
