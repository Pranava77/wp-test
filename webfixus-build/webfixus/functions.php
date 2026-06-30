<?php
/**
 * WEBFIXUS theme bootstrap.
 *
 * @package WEBFIXUS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WFX_VERSION', '1.1.0' );
define( 'WFX_DIR', get_template_directory() );
define( 'WFX_URI', get_template_directory_uri() );

/* -------------------------------------------------------------------------
 * Theme supports
 * ---------------------------------------------------------------------- */
function wfx_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'custom-logo' );
	add_theme_support( 'align-wide' );

	register_nav_menus(
		array(
			'primary' => __( 'Primary Menu', 'webfixus' ),
		)
	);
}
add_action( 'after_setup_theme', 'wfx_setup' );

/* -------------------------------------------------------------------------
 * Front-end assets (fonts + design system + interactions)
 * ---------------------------------------------------------------------- */
function wfx_enqueue_assets() {
	// Google Fonts: Archivo, Space Grotesk, Space Mono.
	wp_enqueue_style(
		'wfx-fonts',
		'https://fonts.googleapis.com/css2?family=Archivo:wght@600;700;800;900&family=Space+Grotesk:wght@400;500;700&family=Space+Mono:wght@400;700&display=swap',
		array(),
		null
	);

	wp_enqueue_style( 'wfx-main', WFX_URI . '/assets/css/webfixus.css', array( 'wfx-fonts' ), WFX_VERSION );

	wp_enqueue_script( 'wfx-main', WFX_URI . '/assets/js/webfixus.js', array(), WFX_VERSION, true );
}
add_action( 'wp_enqueue_scripts', 'wfx_enqueue_assets' );

/**
 * Make the design system visible inside the Elementor editor preview too,
 * so editing the pages looks like the front end.
 */
function wfx_editor_assets() {
	wp_enqueue_style(
		'wfx-fonts',
		'https://fonts.googleapis.com/css2?family=Archivo:wght@600;700;800;900&family=Space+Grotesk:wght@400;500;700&family=Space+Mono:wght@400;700&display=swap',
		array(),
		null
	);
	wp_enqueue_style( 'wfx-main', WFX_URI . '/assets/css/webfixus.css', array( 'wfx-fonts' ), WFX_VERSION );
}
add_action( 'elementor/preview/enqueue_styles', 'wfx_editor_assets' );

/**
 * Add the .wfx body class so our base styles scope cleanly.
 */
function wfx_body_class( $classes ) {
	$classes[] = 'wfx';
	// Reveal the contact success state after a submission (no-JS friendly).
	if ( ! empty( $_GET['sent'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$classes[] = 'wfx-sent';
	}
	return $classes;
}
add_filter( 'body_class', 'wfx_body_class' );

/**
 * Hard-coded nav fallback (used until the WP "primary" menu is assigned).
 */
function wfx_nav_fallback() {
	$items = array(
		'work'  => 'WORK',
		'about' => 'ABOUT',
	);
	echo '<nav class="wfx-nav-wrap"><ul class="wfx-nav">';
	foreach ( $items as $slug => $label ) {
		$page   = get_page_by_path( $slug );
		$url    = $page ? get_permalink( $page ) : home_url( '/' . $slug );
		$active = ( $page && is_page( $page->ID ) ) ? ' class="is-active"' : '';
		printf( '<li><a href="%s"%s>%s</a></li>', esc_url( $url ), $active, esc_html( $label ) );
	}
	$contact = get_page_by_path( 'contact' );
	$curl    = $contact ? get_permalink( $contact ) : home_url( '/contact' );
	printf(
		'<li class="wfx-nav-cta"><a class="wfx-nav-cta" href="%s">CONTACT &rarr;</a></li>',
		esc_url( $curl )
	);
	echo '</ul></nav>';
}

/* -------------------------------------------------------------------------
 * Content width (used by Elementor / embeds)
 * ---------------------------------------------------------------------- */
function wfx_content_width() {
	$GLOBALS['content_width'] = 1120;
}
add_action( 'after_setup_theme', 'wfx_content_width', 0 );

/* -------------------------------------------------------------------------
 * Activation scaffolding (create Elementor pages, menu, front page)
 * ---------------------------------------------------------------------- */
require_once WFX_DIR . '/inc/scaffold.php';

/* -------------------------------------------------------------------------
 * Contact form handler (no plugin required).
 * Form posts to admin-post.php with action=wfx_contact.
 * Honeypot + sanitization; emails site admin; redirects with ?sent=1.
 * ---------------------------------------------------------------------- */
function wfx_handle_contact() {
	$redirect = wp_get_referer();
	if ( ! $redirect ) {
		$contact = get_page_by_path( 'contact' );
		$redirect = $contact ? get_permalink( $contact ) : home_url( '/' );
	}

	// Honeypot: bots fill this hidden field.
	if ( ! empty( $_POST['wfx_hp'] ) ) {
		wp_safe_redirect( add_query_arg( 'sent', '1', $redirect ) );
		exit;
	}

	$name    = isset( $_POST['wfx_name'] ) ? sanitize_text_field( wp_unslash( $_POST['wfx_name'] ) ) : '';
	$email   = isset( $_POST['wfx_email'] ) ? sanitize_email( wp_unslash( $_POST['wfx_email'] ) ) : '';
	$ptype   = isset( $_POST['wfx_type'] ) ? sanitize_text_field( wp_unslash( $_POST['wfx_type'] ) ) : '';
	$brief   = isset( $_POST['wfx_brief'] ) ? sanitize_textarea_field( wp_unslash( $_POST['wfx_brief'] ) ) : '';

	if ( $name && is_email( $email ) ) {
		$to      = get_option( 'admin_email' );
		$subject = sprintf( '[WEBFIXUS] New brief from %s', $name );
		$body    = "Name: {$name}\nEmail: {$email}\nProject type: {$ptype}\n\nBrief:\n{$brief}\n";
		$headers = array( 'Reply-To: ' . $name . ' <' . $email . '>' );
		wp_mail( $to, $subject, $body, $headers );
	}

	wp_safe_redirect( add_query_arg( 'sent', '1', $redirect ) );
	exit;
}
add_action( 'admin_post_nopriv_wfx_contact', 'wfx_handle_contact' );
add_action( 'admin_post_wfx_contact', 'wfx_handle_contact' );

/* -------------------------------------------------------------------------
 * Admin notice if Elementor is missing (pages need it to edit/render).
 * ---------------------------------------------------------------------- */
function wfx_elementor_notice() {
	if ( did_action( 'elementor/loaded' ) ) {
		return;
	}
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}
	echo '<div class="notice notice-warning"><p><strong>WEBFIXUS:</strong> Install &amp; activate the free <em>Elementor</em> plugin to edit the Home, Work, About and Contact pages in the visual editor.</p></div>';
}
add_action( 'admin_notices', 'wfx_elementor_notice' );
