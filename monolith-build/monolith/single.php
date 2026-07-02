<?php
/**
 * Single post view — styled to match the MONOLITH neo-brutalist design.
 *
 * @package monolith
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	$wfx_cats = get_the_category();
	?>
	<article <?php post_class( 'wfx-single' ); ?>>

		<header class="wfx-single-head">
			<p class="wfx-single-meta">
				<?php if ( $wfx_cats ) : ?>
					<span class="wfx-post-cat"><?php echo esc_html( $wfx_cats[0]->name ); ?></span>
				<?php endif; ?>
				<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
			</p>
			<h1 class="wfx-single-title"><?php the_title(); ?></h1>
		</header>

		<?php if ( has_post_thumbnail() ) : ?>
			<div class="wfx-single-hero">
				<?php the_post_thumbnail( 'large' ); ?>
			</div>
		<?php endif; ?>

		<div class="wfx-single-content wfx-post-content">
			<?php the_content(); ?>
			<?php
			wp_link_pages(
				array(
					'before' => '<div class="wfx-page-links">' . esc_html__( 'Pages:', 'monolith' ) . ' ',
					'after'  => '</div>',
				)
			);
			?>
		</div>

		<footer class="wfx-single-foot">
			<?php the_tags( '<span class="wfx-tags">#', ' #', '</span>' ); ?>
			<a class="wfx-back" href="<?php echo esc_url( get_option( 'page_for_posts' ) ? get_permalink( (int) get_option( 'page_for_posts' ) ) : home_url( '/' ) ); ?>">← ALL POSTS</a>
		</footer>

		<?php
		$wfx_prev = get_previous_post();
		$wfx_next = get_next_post();
		if ( $wfx_prev || $wfx_next ) :
			?>
			<nav class="wfx-postnav" aria-label="More posts">
				<span class="wfx-postnav-prev">
					<?php if ( $wfx_prev ) : ?>
						<a href="<?php echo esc_url( get_permalink( $wfx_prev ) ); ?>">← <?php echo esc_html( get_the_title( $wfx_prev ) ); ?></a>
					<?php endif; ?>
				</span>
				<span class="wfx-postnav-next">
					<?php if ( $wfx_next ) : ?>
						<a href="<?php echo esc_url( get_permalink( $wfx_next ) ); ?>"><?php echo esc_html( get_the_title( $wfx_next ) ); ?> →</a>
					<?php endif; ?>
				</span>
			</nav>
		<?php endif; ?>

	</article>
	<?php
endwhile;

get_footer();
