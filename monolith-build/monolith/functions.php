<?php
/**
 * MONOLITH theme bootstrap.
 *
 * @package monolith
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MONOLITH_VERSION', '1.0.0' );

/**
 * Theme supports.
 */
function webfixus_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	register_nav_menus( array( 'primary' => __( 'Primary', 'monolith' ) ) );

	// Logo "slot": adds Appearance → Customize → Site Identity → Logo. When a
	// logo is uploaded it replaces the "MONOLITH" wordmark in the header/footer;
	// otherwise the text wordmark shows as a fallback.
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 60,
			'width'       => 220,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);
}
add_action( 'after_setup_theme', 'webfixus_setup' );

/**
 * Style the Primary menu's links to match the theme's hand-built nav.
 *
 * wp_nav_menu() outputs plain <a> tags, but our CSS expects the classes
 * .wfx-nav-link (with .is-active on the current page) and .wfx-nav-cta (the blue
 * button). This filter applies them so a menu created in Appearance → Menus
 * looks identical to the original hardcoded nav. Add the CSS class "cta" to a
 * menu item to render it as the CONTACT-style button.
 */
function webfixus_nav_link_attributes( $atts, $item, $args ) {
	// Only touch our header menu, not any other menu on the site.
	if ( empty( $args->theme_location ) || 'primary' !== $args->theme_location ) {
		return $atts;
	}

	$classes = is_array( $item->classes ) ? $item->classes : array();

	if ( in_array( 'cta', $classes, true ) || in_array( 'wfx-nav-cta', $classes, true ) ) {
		$atts['class'] = 'wfx-nav-cta';
	} else {
		$class = 'wfx-nav-link';
		if ( in_array( 'current-menu-item', $classes, true ) || in_array( 'current_page_item', $classes, true ) ) {
			$class .= ' is-active';
		}
		$atts['class'] = $class;
	}

	return $atts;
}
add_filter( 'nav_menu_link_attributes', 'webfixus_nav_link_attributes', 10, 3 );

/**
 * Fonts + design-system CSS, site-wide.
 */
function webfixus_assets() {
	wp_enqueue_style(
		'webfixus-fonts',
		'https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;700&family=Space+Mono:wght@400;700&family=Archivo:wght@600;700;800;900&display=swap',
		array(),
		null
	);
	// Version the stylesheet by its file modification time so any CSS edit
	// cache-busts automatically (no manual version bump, and — importantly —
	// without touching the scaffold version that would re-create pages).
	$css_path = get_template_directory() . '/assets/css/monolith.css';
	$css_ver  = file_exists( $css_path ) ? filemtime( $css_path ) : MONOLITH_VERSION;
	wp_enqueue_style(
		'webfixus-style',
		get_template_directory_uri() . '/assets/css/monolith.css',
		array(),
		$css_ver
	);

	// Scroll-reveal + sticky-header script (versioned by mtime, loaded in footer).
	$js_path = get_template_directory() . '/assets/js/monolith-anim.js';
	$js_ver  = file_exists( $js_path ) ? filemtime( $js_path ) : MONOLITH_VERSION;
	wp_enqueue_script(
		'monolith-anim',
		get_template_directory_uri() . '/assets/js/monolith-anim.js',
		array(),
		$js_ver,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'webfixus_assets' );

/**
 * Page scaffolding (creates the Elementor pages on activation / version bump).
 */
require_once get_template_directory() . '/inc/scaffold.php';
