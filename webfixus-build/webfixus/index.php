<?php
/**
 * Fallback template (archives, blog, search, 404).
 *
 * @package WEBFIXUS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<div class="wfx-wrap wfx-pad">
	<?php
	if ( have_posts() ) :
		while ( have_posts() ) :
			the_post();
			?>
			<article <?php post_class(); ?> style="margin-bottom:40px">
				<h2 class="wfx-h2" style="margin-bottom:12px">
					<a href="<?php the_permalink(); ?>" style="color:inherit;text-decoration:none"><?php the_title(); ?></a>
				</h2>
				<div class="wfx-lead"><?php the_excerpt(); ?></div>
			</article>
			<?php
		endwhile;

		the_posts_pagination();
	else :
		?>
		<h1 class="wfx-h1">Nothing here.</h1>
		<p class="wfx-lead" style="margin-top:20px">Try the <a href="<?php echo esc_url( home_url( '/' ) ); ?>">home page</a>.</p>
		<?php
	endif;
	?>
</div>
<?php
get_footer();
