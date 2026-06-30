<?php
/**
 * Fallback template (blog index / archives / anything without a more specific
 * template). The site's real content lives on the Elementor pages.
 *
 * @package webfixus
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<section class="wfx-wrap wfx-pad" style="max-width:1120px;margin:0 auto;padding:64px 32px 80px">
	<?php if ( have_posts() ) : ?>
		<?php while ( have_posts() ) : the_post(); ?>
			<article style="margin-bottom:40px;border-bottom:3px solid #14110c;padding-bottom:24px">
				<h2 class="wfx-h2" style="font-family:'Archivo',sans-serif;font-weight:900;letter-spacing:-.02em;margin:0 0 12px">
					<a class="wfx-a" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
				</h2>
				<div style="font-size:16px;line-height:1.55;color:#3a352c"><?php the_excerpt(); ?></div>
			</article>
		<?php endwhile; ?>
	<?php else : ?>
		<h1 class="wfx-h1" style="font-family:'Archivo',sans-serif;font-weight:900;letter-spacing:-.03em">NOTHING HERE YET.</h1>
		<p style="font-size:18px;color:#3a352c;margin-top:14px">Head back <a class="wfx-a" style="border-bottom:3px solid #2b4cff" href="<?php echo esc_url( home_url( '/' ) ); ?>">home</a>.</p>
	<?php endif; ?>
</section>
<?php
get_footer();
