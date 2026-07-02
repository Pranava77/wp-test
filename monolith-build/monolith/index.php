<?php
/**
 * Blog index / archives / search — the "Posts page".
 * Renders posts as neo-brutalist cards in a grid, matching the MONOLITH design.
 * (Also the fallback for category/tag/date archives and search results.)
 *
 * @package monolith
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// Adapt the page header to the context (blog home / search / archive).
if ( is_search() ) {
	$wfx_kicker = 'SEARCH RESULTS';
	$wfx_title  = '“' . get_search_query() . '”';
} elseif ( is_archive() ) {
	$wfx_kicker = 'ARCHIVE';
	$wfx_title  = wp_strip_all_tags( get_the_archive_title() );
} else {
	$wfx_posts_page = (int) get_option( 'page_for_posts' );
	$wfx_kicker     = 'MONOLITH · WORDS';
	$wfx_title      = $wfx_posts_page ? get_the_title( $wfx_posts_page ) : 'The Journal';
}
?>
<header class="wfx-blog-head">
	<div class="wfx-blog-head-inner">
		<p class="wfx-blog-kicker"><?php echo esc_html( $wfx_kicker ); ?></p>
		<h1 class="wfx-blog-title"><?php echo esc_html( $wfx_title ); ?></h1>
	</div>
</header>

<section class="wfx-blog-body">
	<?php if ( have_posts() ) : ?>
		<div class="wfx-post-grid">
			<?php
			while ( have_posts() ) :
				the_post();
				$wfx_cats = get_the_category();
				?>
				<article <?php post_class( 'wfx-post-card' ); ?>>
					<a class="wfx-post-cardlink" href="<?php the_permalink(); ?>">
						<?php if ( has_post_thumbnail() ) : ?>
							<div class="wfx-post-thumb">
								<?php the_post_thumbnail( 'large', array( 'loading' => 'lazy' ) ); ?>
							</div>
						<?php else : ?>
							<div class="wfx-post-thumb wfx-post-thumb--empty" aria-hidden="true"></div>
						<?php endif; ?>

						<div class="wfx-post-meta">
							<?php if ( $wfx_cats ) : ?>
								<span class="wfx-post-cat"><?php echo esc_html( $wfx_cats[0]->name ); ?></span>
							<?php endif; ?>

							<h2 class="wfx-post-title"><?php the_title(); ?></h2>

							<p class="wfx-post-excerpt">
								<?php echo esc_html( wp_trim_words( get_the_excerpt(), 22, '…' ) ); ?>
							</p>

							<span class="wfx-post-foot">
								<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
								<span class="wfx-post-more">READ →</span>
							</span>
						</div>
					</a>
				</article>
			<?php endwhile; ?>
		</div>

		<?php
		the_posts_pagination(
			array(
				'mid_size'  => 1,
				'prev_text' => '← PREV',
				'next_text' => 'NEXT →',
			)
		);
		?>
	<?php else : ?>
		<div class="wfx-blog-empty">
			<h2 class="wfx-post-title">NOTHING PUBLISHED YET.</h2>
			<p>New words are on the way. Head back
				<a class="wfx-a wfx-underline" href="<?php echo esc_url( home_url( '/' ) ); ?>">home</a>.</p>
		</div>
	<?php endif; ?>
</section>
<?php
get_footer();
