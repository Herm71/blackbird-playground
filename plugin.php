<?php
/**
 * Plugin Name: Blackbird's Playground
 * Plugin URI: https://github.com/ucsc/ucsc-custom-functionality.git
 * Description: Sandbox plugin for testing php functions.
 * Version: 1.0.0
 * Author: @Herm71
 * Author URI: https://github.com/Herm71
 * License: GPL2
 *
 * @package blackbird-playground
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU
 * General Public License version 2, as published by the Free Software Foundation.  You may NOT assume
 * that you can use any other version of the GPL.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

/**
 * replace content */


/* Add a paragraph only to Pages. */
function my_added_page_content( $content ) {
		$sample_page = 2;
	if ( is_page( $sample_page ) ) {
				$new_content = '<p>My new content</p>';
				$content     = $new_content;
		return $content;
	}

	return $content;
}
add_filter( 'the_content', 'my_added_page_content' );

/**
 * end replace content */
function ucsc_get_post_format() {
	$post_format = get_post_format();
	// echo $post_format;
	if ( 'link' === $post_format && is_archive() ) {
		echo 'is archive and is link';
	} else {
		echo 'not an archive';
	}
}

function ucsc_has_post_format() {
	global $wp;
	global $post;
	$post_template = get_page_template_slug( $post );
	$current_url   = home_url( add_query_arg( array(), $wp->request ) );
	$current_parse = parse_url( $current_url );
	if ( is_page_template( 'taxonomy-post_format-post-format-link.html' ) ) {
		$post_format_link = get_post_format_link( 'link' );
		$post_parse       = parse_url( $post_format_link );
	}
	// echo 'Post format Link ' . $post_parse['path'] . '<br>';
	// echo 'Current format Link ' . $current_parse['path'] . '<br>';
	echo 'Template Slug ' . $post_template . '<br>';
	var_dump( $post_template );
	// if ( $current_parse['path'] === $post_parse['path'] ) {
	// echo 'Matches';
	// } else {echo 'Nope!';}
}


function ucsc_parse_blocks() {
	global $post;
	$blocks = parse_blocks( $post->post_content );

	echo '<pre>';
	var_dump( $blocks );
	echo '</pre>';

}
// add_action( 'wp_head', 'ucsc_parse_blocks' );

function location() {
	$site_location = home_url();
	echo $site_location;
}
// add_action( 'wp_head', 'which_template' );

function which_template() {
	 global $wp_query;
	 global $template;
	echo '<pre>';
	var_dump( $wp_query->query_vars );
	echo '</pre>';

	if ( is_front_page() && is_home() ) {
		echo '<pre>is_front_page() && is_home()</pre>';
		echo '<p>Default homepage = Show latest blog posts</p>';

	} elseif ( is_front_page() ) {
		// Static homepage
		echo '<pre>is_front_page()</pre>';
		echo '<p>Static Home Page = assign page as Front Page</p>';
	} elseif ( is_home() ) {

		// Blog page
		echo '<pre>is_home()</pre>';
		echo '<p>Static Blog Page = assign page as Blog Page</p>';
	} else {

		echo '<pre>nope</pre>';

	}

}

/**
 * Exclude Category from Blog
 *
 * @author Bill Erickson
 * @link   https://www.billerickson.net/customize-the-wordpress-query/
 * @param  object $query data
 */
function be_exclude_category_from_blog( $query ) {
	if ( $query->is_main_query() && ! is_admin() && $query->is_home() ) {
		$query->set( 'posts_per_page', '1' );
	}
}
// add_action( 'pre_get_posts', 'be_exclude_category_from_blog' );


function just_one( $query ) {
	if ( $query->is_main_query() ) {
		$query->set( 'posts_per_page', 1 );
	}
}
// add_action( 'pre_get_posts', 'just_one' );
// add_shortcode( 'ucsc-link-post', 'basic_loop' );


function basic_loop() {
	$output = '';
	$args   = array(
		'post_type' => 'post',
		'tax_query' => array(
			'taxonomy' => 'post_format',
			'field'    => 'slug',
			'terms'    => array( 'post-format-link' ),
		),
	);

	$linkposts = new WP_Query( $args );
	if ( $linkposts->have_posts() ) :
		$output .= '<ul class="fe-query-results-shortcode-output">';
		while ( $linkposts->have_posts() ) :
			$linkposts->the_post();
			$title   = get_the_title();
			$link    = get_permalink();
			$output .= "<li><a href=\"{$link}\">{$title}</a></li>";
		endwhile;
		$output .= '</ul>';
		wp_reset_postdata();
	else :
		$output .= '<div class="fe-query-results-shortcode-output-none">No results were found</div>';
	endif;

	return $output;
}


/*
* Plugin Name: Author Taxonomy
* Description: A short example showing how to add a taxonomy called Author.
* Version: 1.0
* Author: developer.wordpress.org
* Author URI: https://codex.wordpress.org/User:Aternus
*/

function ucsc_register_taxonomy_author() {
	$labels = array(
		'name'              => _x( 'Guest Authors', 'taxonomy general name' ),
		'singular_name'     => _x( 'Guest Author', 'taxonomy singular name' ),
		'search_items'      => __( 'Search Authors' ),
		'all_items'         => __( 'All Authors' ),
		'parent_item'       => __( 'Parent Author' ),
		'parent_item_colon' => __( 'Parent Author:' ),
		'edit_item'         => __( 'Edit Author' ),
		'update_item'       => __( 'Update Author' ),
		'add_new_item'      => __( 'Add New Author' ),
		'new_item_name'     => __( 'New Author Name' ),
		'menu_name'         => __( 'Author' ),
	);
	$args   = array(
		'hierarchical'      => true, // make it hierarchical (like categories)
		'labels'            => $labels,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'author-two' ),
	);
	register_taxonomy( 'author-two', array( 'post' ), $args );
}
// add_action( 'init', 'ucsc_register_taxonomy_author' );

/**
 *  Filter: link posts
 *  Set link post permalinks to an external URL
 *
 * @param  string $link External link url
 * @param  array  $post Post attributes
 * @return string
 */
function ucsc_link_post_filter2( $url, $post, $leavename = false ) {
	if ( is_single() ) {
		global $post;
		if ( function_exists( 'get_field' ) ) {
			$lp = get_field( 'link_post' );
			if ( $lp ) {
				$lp_fields  = get_field( 'lp-fields' );
				$lp_source  = $lp_fields['lp-source'];
				$lp_url     = $lp_fields['lp-url'];
				$lp_url_esc = esc_url( $lp_url );
			}
			$url = $lp_url_esc;
		}
		return $url;
	}
}
// add_filter( 'post_link', 'ucsc_link_post_filter2', 10, 2 );

/**
 *  Filter: link posts
 *  Set link post permalinks to an external URL
 *
 * @param  string $link External link url
 * @param  array  $post Post attributes
 * @return string
 */
// Exclude post-format from main query
function ucsc_exclude_post_formats( $query ) {
	if ( ! is_admin() && $query->is_main_query() && $query->is_home() ) {
		$tax_query = array(
			'taxonomy' => 'post_format',
			'field'    => 'slug',
			'terms'    => array( 'post-format-link' ),
			'operator' => 'NOT IN',
		);
		$query->set( 'category_name', 'aciform' );
	}
}
// add_action( 'pre_get_posts', 'ucsc_exclude_post_formats' );

// $template = locate_template( 'dynamic-content.php ');

// if( $template ){

// set_query_var('my_variable ', 'Hello Template File' );

// load_template( $template );

// }


