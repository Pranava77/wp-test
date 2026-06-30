<?php
/**
 * WEBFIXUS theme bootstrap.
 *
 * @package webfixus
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WEBFIXUS_VERSION', '1.1.0' );

/**
 * Theme supports.
 */
function webfixus_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	register_nav_menus( array( 'primary' => __( 'Primary', 'webfixus' ) ) );
}
add_action( 'after_setup_theme', 'webfixus_setup' );

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
	wp_enqueue_style(
		'webfixus-style',
		get_template_directory_uri() . '/assets/css/webfixus.css',
		array(),
		WEBFIXUS_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'webfixus_assets' );

/**
 * Page scaffolding (creates the Elementor pages on activation / version bump).
 */
require_once get_template_directory() . '/inc/scaffold.php';
