<?php
/**
 * Site header — shared sticky nav (Elementor Free has no Theme Builder, so the
 * header/footer live in the theme template and appear on every page).
 *
 * @package monolith
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Active-state helper: highlights the current page's nav link.
 */
function webfixus_is( $slug ) {
	return is_page( $slug ) ? ' is-active' : '';
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'wfx-page' ); ?>>
<?php wp_body_open(); ?>

<header class="wfx-header">
	<div class="wfx-header-inner">
		<?php if ( has_custom_logo() ) : ?>
			<span class="wfx-logo wfx-logo--img"><?php the_custom_logo(); ?></span>
		<?php else : ?>
			<a class="wfx-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">MONOLITH</a>
		<?php endif; ?>
		<nav class="wfx-nav">
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				// Editable from Appearance → Menus. Assign a menu to the
				// "Primary" location and it renders here. To make a link the
				// blue CTA button, give that menu item the CSS class "cta"
				// (Menus screen → Screen Options → tick "CSS Classes").
				wp_nav_menu( array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'wfx-nav-list',
					'depth'          => 1,
					'fallback_cb'    => false,
				) );
			} else {
				// Fallback until you create + assign a Primary menu. These are
				// the original links so the header is never empty.
				?>
				<a class="wfx-nav-link<?php echo esc_attr( webfixus_is( 'work' ) ); ?>" href="<?php echo esc_url( home_url( '/work/' ) ); ?>">WORK</a>
				<a class="wfx-nav-link<?php echo esc_attr( webfixus_is( 'about' ) ); ?>" href="<?php echo esc_url( home_url( '/about/' ) ); ?>">ABOUT</a>
				<a class="wfx-nav-cta" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">CONTACT →</a>
				<?php
			}
			?>
		</nav>
	</div>
</header>

<main>
