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
 * Text Domain:       blackbird-playground
 *
 * @package           create-block
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'BLACKBIRD_PLUGIN_VERSION' ) ) {
	define( 'BLACKBIRD_PLUGIN_VERSION', '0.1.0' ); // Keep in step with the Version header above.
}

if ( ! defined( 'BLACKBIRD_PLUGIN_DIR' ) ) {
	define( 'BLACKBIRD_PLUGIN_DIR', plugin_dir_path( __FILE__ ) ); // Path to plugin directory.
}
/**
 * Enqueue the Blackbird Playground stylesheet.
 */
function blackbird_enqueue_styles() {
    // Get the plugin directory URL
    $plugin_url = plugin_dir_url(__FILE__);

    $stylesheet_path = plugin_dir_path( __FILE__ ) . 'style.css';

    // Version on file modification time so the browser picks up rebuilds. Fall
    // back to the plugin version when the file is absent from a build, rather
    // than passing filemtime()'s false through as a version.
    $version = file_exists( $stylesheet_path ) ? filemtime( $stylesheet_path ) : BLACKBIRD_PLUGIN_VERSION;

    // Enqueue the compiled CSS file
    wp_enqueue_style(
        'blackbird-playground-style', // Handle
        $plugin_url . 'style.css', // Path to the compiled CSS file
        array(), // Dependencies
        $version
    );
}
add_action('wp_enqueue_scripts', 'blackbird_enqueue_styles');

/**
 * ACF JSON Save Point
 *
 * @param [type] $path
 * @return $path
 * @package Blackbird_Sandbox
 */
function blackbird_acf_json_save_point( $path ) {
	$path = BLACKBIRD_PLUGIN_DIR . 'acf-json';
	return $path;
}
// Set plugin directory for saving ACF JSON files.
add_filter( 'acf/settings/save_json', 'blackbird_acf_json_save_point' );

/**
 * ACF JSON Load Point
 *
 * @param [type] $paths
 * @return $paths
 * @package Blackbird_Sandbox
 */
function blackbird_acf_json_load_point( $paths ) {
	unset( $paths[0] );
	$paths[] = BLACKBIRD_PLUGIN_DIR . 'acf-json';
	return $paths;
}
// Set plugin directory for loading ACF JSON files.
add_filter( 'acf/settings/load_json', 'blackbird_acf_json_load_point' );

/**
 * Register the A-Z Editorial Style Guide shortcode.
 *
 * @return string
 */
// This shortcode outputs the A-Z Editorial Style Guide definitions.

add_shortcode( 'style-definition','blackbird_style_guide_single_loop' );

function blackbird_style_guide_single_loop(){

	$finaldefs = '';

	if( have_rows('style_definitions') ):while( have_rows('style_definitions') ): the_row();
		$azItem = get_sub_field('editorial_style_item');
		$azDef = get_sub_field('editorial_style_definition');		
		$finaldefs .= '<p><b>' . esc_html( $azItem ) . ':</b></p>' . wp_kses_post( $azDef ) . '<hr>';
		endwhile;
	endif;

return $finaldefs;
}

/**
 * Register the A-Z Editorial Style Guide archive shortcode.
 *
 * @return string
 */
// This shortcode outputs the A-Z Editorial Style Guide archive loop.
// It retrieves all posts of the 'a_z_style_guide' post type, ordered by title in ascending order, and displays each post's title along with its style definitions.
add_shortcode( 'style-archive','blackbird_style_guide_archive_loop' );

function blackbird_style_guide_archive_loop() {
	$finalloop = '';

	// Call Post
	$args = array (
	'post_type' => 'a_z_style_guide',
	'orderby' => 'title',
	'order' => 'ASC',
	'posts_per_page' => -1,
	);

	$azDir = new \WP_Query( $args );

	if ($azDir->have_posts()) :
		while ($azDir->have_posts()) :
			$azDir->the_post();
			$azTitle = get_the_title();
			$finalloop .= '<h2>'.$azTitle.'</h2>';
			if( have_rows('style_definitions') ):
				while( have_rows('style_definitions') ):
					the_row();
					// vars
					$azItem = get_sub_field('editorial_style_item');
					$azDef = get_sub_field('editorial_style_definition');
					$finalloop .= '<p><b>' . esc_html( $azItem ) . ':</b></p>' . wp_kses_post( $azDef ) . '<hr>';
				endwhile;
			endif;
		endwhile;
	endif;

	wp_reset_postdata();

	return $finalloop;
}

/**
 * Register Search block variation for Fund post type
 * description: Registers a custom block variation for the Fund post type
 *
 * @param mixed         $variations
 * @param WP_Block_Type $block_type The block type being filtered.
 * @return mixed
 */
function blackbird_create_style_guide_search_variation( $variations, $block_type ) {
	if ( 'core/search' !== $block_type->name ) {
			return $variations;
	}

		$variations[] = array(
			'name'        => 'styleguide-search',
			'title'       => __( 'Style Guide Search', 'blackbird-playground' ),
			'description' => __( 'Search only Style Guide posts', 'blackbird-playground' ),
			'attributes'  => array(
				'query'       => array(
					'post_type' => 'a_z_style_guide',
				),
				'placeholder' => __( 'Search Style Guide', 'blackbird-playground' ),
				'buttonText'  => __( 'Search Style Guide', 'blackbird-playground' ),
				'label'       => __( 'Search Style Guide', 'blackbird-playground' ),
			),
		);

		return $variations;
}

add_filter( 'get_block_type_variations', 'blackbird_create_style_guide_search_variation', 10, 2 );

function blackbird_filter_style_guide_search( $query ) {
    if ( ! is_admin() && $query->is_main_query() && is_search() && 'a_z_style_guide' === get_query_var( 'post_type' ) ) {
        // Only proceed if there's a search term
        if ( ! empty( $query->query_vars['s'] ) ) {
            global $wpdb;
            
            $search_term = $query->query_vars['s'];
            
            // Get posts that have matching sub-field values
            $sql = $wpdb->prepare("
                SELECT DISTINCT p.ID 
                FROM {$wpdb->posts} p
                INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                WHERE p.post_type = 'a_z_style_guide'
                AND p.post_status = 'publish'
                AND (
                    (pm.meta_key LIKE %s AND pm.meta_value LIKE %s)
                    OR 
                    (pm.meta_key LIKE %s AND pm.meta_value LIKE %s)
                )
                ORDER BY p.post_title ASC
            ", 
                'style_definitions_%_editorial_style_item',
                '%' . $wpdb->esc_like($search_term) . '%',
                'style_definitions_%_editorial_style_definition', 
                '%' . $wpdb->esc_like($search_term) . '%'
            );
            
            $post_ids = $wpdb->get_col($sql);
            
            if (!empty($post_ids)) {
                $query->set('post__in', $post_ids);
                $query->set('orderby', 'post__in'); // Maintain the order from the SQL query
                $query->set('s', ''); // Remove the default search to avoid conflicts
            } else {
                // If no matches found, return no posts
                $query->set('post__in', array(0));
                $query->set('s', '');
            }
        }
    }
}
add_action( 'pre_get_posts', 'blackbird_filter_style_guide_search' );