<?php
/**
 * Theme footer.
 *
 * @package WEBFIXUS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$wfx_email = sanitize_email( get_option( 'admin_email' ) );
if ( ! $wfx_email ) {
	$wfx_email = 'hello@webfixus.studio';
}
$wfx_home    = get_page_by_path( 'home' );
$wfx_work    = get_page_by_path( 'work' );
$wfx_about   = get_page_by_path( 'about' );
$wfx_contact = get_page_by_path( 'contact' );
?>
	</main><!-- .wfx-main -->

	<footer class="wfx-footer">
		<div class="wfx-footer__inner">
			<div class="wfx-footer__top">
				<div class="wfx-footer__brand">
					<div class="wfx-footer__logo"><?php echo esc_html( get_bloginfo( 'name' ) ? get_bloginfo( 'name' ) : 'WEBFIXUS' ); ?></div>
					<p class="wfx-footer__tag">Freelance illustration with a pulse. Editorial, character, brand &amp; murals &mdash; drawn by hand, delivered fast.</p>
				</div>
				<div class="wfx-footer__cols">
					<div>
						<div class="wfx-footer__h">PAGES</div>
						<ul class="wfx-footer__list">
							<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a></li>
							<li><a href="<?php echo esc_url( $wfx_work ? get_permalink( $wfx_work ) : home_url( '/work' ) ); ?>">Work</a></li>
							<li><a href="<?php echo esc_url( $wfx_about ? get_permalink( $wfx_about ) : home_url( '/about' ) ); ?>">About</a></li>
							<li><a href="<?php echo esc_url( $wfx_contact ? get_permalink( $wfx_contact ) : home_url( '/contact' ) ); ?>">Contact</a></li>
						</ul>
					</div>
					<div>
						<div class="wfx-footer__h">ELSEWHERE</div>
						<ul class="wfx-footer__list">
							<li><a href="#">Instagram</a></li>
							<li><a href="#">Behance</a></li>
							<li><a href="#">Dribbble</a></li>
						</ul>
					</div>
					<div>
						<div class="wfx-footer__h">SAY HI</div>
						<div class="wfx-footer__email"><?php echo esc_html( $wfx_email ); ?></div>
					</div>
				</div>
			</div>
			<div class="wfx-footer__bottom">
				<span>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( strtoupper( get_bloginfo( 'name' ) ? get_bloginfo( 'name' ) : 'WEBFIXUS' ) ); ?> &mdash; ALL RIGHTS RESERVED</span>
				<span>BROOKLYN, NY</span>
			</div>
		</div>
	</footer>

</div><!-- .wfx-site -->
<?php wp_footer(); ?>
</body>
</html>
