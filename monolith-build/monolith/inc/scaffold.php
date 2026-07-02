<?php
/**
 * Page scaffolding.
 *
 * On theme activation (and again whenever the bundled content version changes)
 * this creates / refreshes the four Elementor pages — Home, Work, About,
 * Contact — from the JSON in inc/pages/, sets Home as the front page, and
 * switches on pretty permalinks so /work/, /about/, /contact/ resolve.
 *
 * The pages are stored as normal Elementor builder pages, so the user can open
 * any of them in Elementor and edit freely. The same markup is also written to
 * post_content as a graceful fallback if Elementor is ever deactivated.
 *
 * @package monolith
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Bump this when the bundled page JSON changes so re-uploads refresh the pages.
 */
define( 'MONOLITH_SCAFFOLD_VERSION', '1.1.0' );

/**
 * Ordered map of slug => page title.
 */
function webfixus_pages() {
	return array(
		'home'    => 'Home',
		'work'    => 'Work',
		'about'   => 'About',
		'contact' => 'Contact',
	);
}

/**
 * Pull the concatenated HTML-widget markup out of a page's section JSON.
 * Used for the no-Elementor fallback stored in post_content.
 */
function webfixus_extract_html( $sections ) {
	$out = '';
	if ( ! is_array( $sections ) ) {
		return $out;
	}
	foreach ( $sections as $node ) {
		if ( isset( $node['widgetType'] ) && 'html' === $node['widgetType'] && isset( $node['settings']['html'] ) ) {
			$out .= $node['settings']['html'] . "\n";
		}
		if ( ! empty( $node['elements'] ) ) {
			$out .= webfixus_extract_html( $node['elements'] );
		}
	}
	return $out;
}

/**
 * Create or update a single page from its JSON file.
 */
function webfixus_upsert_page( $slug, $title ) {
	$file = get_template_directory() . '/inc/pages/' . $slug . '.json';
	if ( ! file_exists( $file ) ) {
		return 0;
	}

	$raw      = file_get_contents( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	$sections = json_decode( $raw, true );
	if ( null === $sections ) {
		return 0;
	}

	$fallback = webfixus_extract_html( $sections );

	// Find an existing page by slug (survives the user renaming the title).
	$existing = get_page_by_path( $slug );

	$postarr = array(
		'post_title'   => $title,
		'post_name'    => $slug,
		'post_status'  => 'publish',
		'post_type'    => 'page',
		'post_content' => $fallback,
	);

	if ( $existing ) {
		$postarr['ID'] = $existing->ID;
		$page_id       = wp_update_post( $postarr );
	} else {
		$page_id = wp_insert_post( $postarr );
	}

	if ( ! $page_id || is_wp_error( $page_id ) ) {
		return 0;
	}

	// Elementor builder meta. _elementor_data is stored slashed, matching how
	// Elementor itself persists it.
	update_post_meta( $page_id, '_elementor_data', wp_slash( wp_json_encode( $sections ) ) );
	update_post_meta( $page_id, '_elementor_edit_mode', 'builder' );
	update_post_meta( $page_id, '_elementor_template_type', 'wp-page' );
	update_post_meta( $page_id, '_elementor_version', defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '3.0.0' );
	update_post_meta( $page_id, '_wp_page_template', 'default' );

	// Force Elementor to regenerate this page's CSS on next view.
	delete_post_meta( $page_id, '_elementor_css' );

	return (int) $page_id;
}

/**
 * Run the full scaffold: pages, front page, permalinks.
 */
function webfixus_scaffold() {
	$ids = array();
	foreach ( webfixus_pages() as $slug => $title ) {
		$id = webfixus_upsert_page( $slug, $title );
		if ( $id ) {
			$ids[ $slug ] = $id;
		}
	}

	// Home as the static front page.
	if ( ! empty( $ids['home'] ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $ids['home'] );
	}

	// Pretty permalinks so /work/, /about/, /contact/ resolve.
	if ( '' === get_option( 'permalink_structure' ) ) {
		update_option( 'permalink_structure', '/%postname%/' );
	}
	flush_rewrite_rules();

	update_option( 'webfixus_scaffold_version', MONOLITH_SCAFFOLD_VERSION );
}

/**
 * Run once when the theme is activated.
 */
add_action( 'after_switch_theme', 'webfixus_scaffold' );

/**
 * Refresh on re-upload: if the bundled version differs from what ran last, or a
 * page was deleted, re-run the scaffold. Cheap guard, admin-side only.
 */
function webfixus_maybe_refresh() {
	if ( ! is_admin() ) {
		return;
	}
	$done = get_option( 'webfixus_scaffold_version' );
	$home = get_page_by_path( 'home' );
	if ( MONOLITH_SCAFFOLD_VERSION !== $done || ! $home ) {
		webfixus_scaffold();
	}
}
add_action( 'admin_init', 'webfixus_maybe_refresh' );

/**
 * Nudge the user if Elementor isn't active (pages still render via the
 * post_content fallback, but the editor experience needs the plugin).
 */
function webfixus_elementor_notice() {
	if ( did_action( 'elementor/loaded' ) ) {
		return;
	}
	echo '<div class="notice notice-warning"><p><strong>MONOLITH:</strong> install &amp; activate the free <em>Elementor</em> plugin to edit your pages visually. Your pages already display without it.</p></div>';
}
add_action( 'admin_notices', 'webfixus_elementor_notice' );
