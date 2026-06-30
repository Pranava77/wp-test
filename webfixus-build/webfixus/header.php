<?php
/**
 * Site header — shared sticky nav (Elementor Free has no Theme Builder, so the
 * header/footer live in the theme template and appear on every page).
 *
 * @package webfixus
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
		<a class="wfx-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">WEBFIXUS</a>
		<nav class="wfx-nav">
			<a class="wfx-nav-link<?php echo esc_attr( webfixus_is( 'work' ) ); ?>" href="<?php echo esc_url( home_url( '/work/' ) ); ?>">WORK</a>
			<a class="wfx-nav-link<?php echo esc_attr( webfixus_is( 'about' ) ); ?>" href="<?php echo esc_url( home_url( '/about/' ) ); ?>">ABOUT</a>
			<a class="wfx-nav-cta" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">CONTACT →</a>
		</nav>
	</div>
</header>

<main>
