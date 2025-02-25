<?php
/**
 * Plugin Name:       Birdblocks
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
// 	register_block_type( __DIR__ . '/build' );
// }
// add_action( 'init', 'create_block_birdblocks_block_init' );


add_action( 'init', 'ucsc_register_text_meta' );

function ucsc_register_text_meta() {
	$fields = array(
		'code' => 'Code',
		'form' => 'Form',
		'aq_code' => 'AQ_Code',
		'id' => 'ID',
		'button_text' => 'Fund button text',
	);
	foreach ($fields as $slug => $label) {
		register_post_meta(
			'fund',
			$slug,
			array(
				'show_in_rest'      => true,
				'single'            => true,
				'type'              => 'string',
				'sanitize_callback' => 'wp_strip_all_tags',
				'label'							=> _($label),
			)
		);
	}
}

// add_action( 'init', 'ucsc_register_url_meta' );

function ucsc_register_url_meta() {
	$fields = array(
		'fund_link' => 'Fund link',
	);
	foreach ($fields as $slug => $label) {
		register_post_meta(
			'fund',
			$slug,
			array(
				'show_in_rest'      => true,
				'single'            => true,
				'type'              => 'string',
				'sanitize_callback' => 'esc_url_raw',
				'label'							=> _($label),
			)
		);
	}
}

// add_action( 'wp_head', 'ucsc_test' );

function ucsc_test() {
	$baseurl = 'https://give.ucsc.edu/campaigns/38026/donations/new?designation=';
	$aqcode = get_post_meta( get_the_ID(), 'aq_code', true );
	$fundurl = $baseurl.$aqcode;
	echo $fundurl;
}

add_action( 'init', 'ucsc_register_block_bindings' );

function ucsc_register_block_bindings() {
	register_block_bindings_source( 'ucsc/fund-url', array(
		'label'              => __( 'Fund URL', 'ucsc' ),
		'get_value_callback' => 'ucsc_fund_url',
		// 'uses_context'       => [ 'postId', 'postType' ]
	) );
}

function ucsc_fund_url() {
	$baseurl = 'https://give.ucsc.edu/campaigns/38026/donations/new?designation=';
	$aqcode = get_post_meta( get_the_ID(), 'aq_code', true );
	return $baseurl.$aqcode;
}