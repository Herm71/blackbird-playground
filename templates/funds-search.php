<?PHP
/*
 * Template Name: Funds Search PHP Template
 * Description: A Page Template that allows us to use PHP code to search for funds
 */

// Example of rendering a Query Loop block
$block_header_part = get_block_template( get_stylesheet() . '//site-header', 'wp_template_part' );
$block_header = $block_header_part && ! empty( $block_header_part->content ) ? do_blocks( $block_header_part->content  ) : '';
$block_content = '<!-- wp:query {"queryId":15,"query":{"perPage":9,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":true},"metadata":{"categories":["posts"],"patternName":"core/query-small-posts","name":"Small image and title"}} -->
<div class="wp-block-query"><!-- wp:post-template -->
<!-- wp:columns {"verticalAlignment":"center"} -->
<div class="wp-block-columns are-vertically-aligned-center"><!-- wp:column {"verticalAlignment":"center","width":"25%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:25%"><!-- wp:post-featured-image {"isLink":true} /--></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"75%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:75%"><!-- wp:post-title {"isLink":true} /-->

<!-- wp:post-excerpt {"excerptLength":100} /--></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->
<!-- /wp:post-template --></div>
<!-- /wp:query -->';

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div class="wp-site-blocks">
<header class="header-region wp-block-template-part">
<?php //echo $block_header;
	block_header_area(); 
?>
</header>
<p>plugin PHP template</p>
<?php echo do_blocks( $block_content ); ?>
<footer class="footer-region wp-block-template-part">
<?php block_footer_area(); ?>
</footer>
</div>
</div>
<?php wp_footer(); ?>

</body>
</html>
