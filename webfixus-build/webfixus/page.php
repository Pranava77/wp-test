<?php
/**
 * Generic page template (Work, About, Contact, and any other page).
 * Renders Elementor content full-width.
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
	<article <?php post_class( 'wfx-page' ); ?>>
		<?php
		the_content();

		// Page bodies are built in Elementor; show a gentle hint only to editors
		// on a page that has no content yet.
		if ( '' === trim( get_the_content() ) && current_user_can( 'edit_pages' ) ) {
			echo '<div class="wfx-wrap wfx-pad"><p class="wfx-lead">This page is empty. Edit it with Elementor to add content.</p></div>';
		}
		?>
	</article>
	<?php
endwhile;

get_footer();
