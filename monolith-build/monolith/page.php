<?php
/**
 * Page template. Elementor hooks into the_content(), so its layout renders here
 * between the shared theme header and footer.
 *
 * @package monolith
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	the_content();
endwhile;

get_footer();
