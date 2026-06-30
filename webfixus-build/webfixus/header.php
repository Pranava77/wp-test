<?php
/**
 * Theme header — sticky neo-brutalist bar.
 *
 * @package WEBFIXUS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div class="wfx-site">

	<header class="wfx-header">
		<div class="wfx-header__inner">
			<?php
			if ( has_custom_logo() ) {
				the_custom_logo();
			} else {
				printf(
					'<a class="wfx-logo" href="%s">%s</a>',
					esc_url( home_url( '/' ) ),
					esc_html( get_bloginfo( 'name' ) ? get_bloginfo( 'name' ) : 'WEBFIXUS' )
				);
			}

			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'container'      => 'nav',
						'container_class' => 'wfx-nav-wrap',
						'menu_class'     => 'wfx-nav',
						'depth'          => 1,
						'fallback_cb'    => 'wfx_nav_fallback',
					)
				);
			} else {
				wfx_nav_fallback();
			}
			?>
		</div>
	</header>

	<main class="wfx-main">
