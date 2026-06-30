<?php
/**
 * Front page (Home). Renders the page's Elementor content full-width.
 *
 * @package WEBFIXUS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article class="wfx-page wfx-page--home">
		<?php the_content(); ?>
	</article>
	<?php
endwhile;

get_footer();
