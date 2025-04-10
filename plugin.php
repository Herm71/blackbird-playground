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
 * Enqueue the Blackbird Playground stylesheet.
 */
function blackbird_playground_enqueue_styles() {
    // Get the plugin directory URL
    $plugin_url = plugin_dir_url(__FILE__);

    // Enqueue the compiled CSS file
    wp_enqueue_style(
        'blackbird-playground-style', // Handle
        $plugin_url . 'style.css', // Path to the compiled CSS file
        array(), // Dependencies
        filemtime(plugin_dir_path(__FILE__) . 'style.css') // Version based on file modification time
    );
}
add_action('wp_enqueue_scripts', 'blackbird_playground_enqueue_styles');
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
 * Add Subtitle meta field
 * Single Blog Post
 *
 * @param  string $block_content Block content to be rendered.
 * @param  array  $block         Block attributes.
 * @return string
 */
function ucsc_test( $block_content = '', $block = array() ) {

	if ( 'media_coverage' === $post->post_type ) {

		if (
			// Check if the block is a post title block
			isset( $block['blockName'] ) &&
			'core/post-title' === $block['blockName']
		) {
			$html = str_replace(
				$block_content,
				$block_content . '<p>Hello World</p>',
				$block_content
			);
			return $html;
		}
	}
	return $block_content;
}
// add_filter('render_block', 'ucsc_test', 10, 2);

// add_action('wp_head', 'ucsc_test_head');
function ucsc_test_head() {
	 require 'wp-load.php';
	$block_types = WP_Block_Type_Registry::get_instance()->get_all_registered();
	$keys        = array();
	foreach ( $block_types as $key ) {
		$keys[] = $key->name;
	}
	print_r( $keys );

}

add_filter( 'the_title', 'ucscgiving_filter_media_coverage_title' );
/**
 * Add SVG icon to the title of media coverage posts
 *
 * @param string $title The post title.
 * @return string The modified post title.
 */ 

function ucscgiving_filter_media_coverage_title( $title ) {
	global $id, $post;
	if ( is_admin() ) {
		return $title;
	}
		// Check if the post is a media coverage post
	if ( $id && $post && $post->post_type === 'media_coverage' ) {
		// Add the SVG icon to the title
		$title = $title . ' <svg width="16" height="16" fill="none" stroke="currentColor" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg">
						<path d="M4 3h9v9M3 13 13 3"></path></svg>';
	}
	return $title;
}

