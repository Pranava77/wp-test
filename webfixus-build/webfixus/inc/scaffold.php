<?php
/**
 * One-time scaffolding on theme activation:
 *  - create Home / Work / About / Contact as Elementor pages from bundled JSON
 *  - set the static front page to Home
 *  - build a primary menu and assign it
 *
 * Idempotent: existing pages/menu are reused, never duplicated.
 *
 * @package WEBFIXUS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Page definitions: slug => [ title, template-json-file ].
 */
function wfx_pages_map() {
	return array(
		'home'    => array( 'Home', 'home.json' ),
		'work'    => array( 'Work', 'work.json' ),
		'about'   => array( 'About', 'about.json' ),
		'contact' => array( 'Contact', 'contact.json' ),
	);
}

/**
 * Write the bundled Elementor template into a page (create or refresh).
 *
 * @param int    $page_id Target page.
 * @param string $file    JSON filename in /templates.
 * @return bool True if the template was applied.
 */
function wfx_apply_template( $page_id, $file ) {
	$path = WFX_DIR . '/templates/' . $file;
	if ( ! file_exists( $path ) ) {
		return false;
	}
	$json    = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	$decoded = json_decode( $json, true );
	if ( ! is_array( $decoded ) ) {
		return false;
	}
	// Elementor stores slashed JSON; update_metadata() unslashes once on save.
	update_post_meta( $page_id, '_elementor_data', wp_slash( wp_json_encode( $decoded ) ) );
	update_post_meta( $page_id, '_elementor_edit_mode', 'builder' );
	update_post_meta( $page_id, '_elementor_template_type', 'wp-page' );
	update_post_meta( $page_id, '_elementor_version', defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '3.0.0' );
	// Full-width Elementor template that keeps the theme header/footer.
	update_post_meta( $page_id, '_wp_page_template', 'elementor_header_footer' );
	update_post_meta( $page_id, '_wfx_tpl_version', WFX_VERSION );
	return true;
}

/**
 * Create a page if missing, or refresh its template when the bundled version
 * changed (so theme updates ship corrected layouts to existing installs).
 *
 * @param string $slug  Page slug.
 * @param string $title Page title.
 * @param string $file  JSON filename in /templates.
 * @return int Page ID (existing or newly created), 0 on failure.
 */
function wfx_create_page( $slug, $title, $file ) {
	$existing = get_page_by_path( $slug );
	if ( $existing instanceof WP_Post ) {
		$page_id = (int) $existing->ID;
		// Refresh only if our template version differs (never clobbers on every load).
		if ( get_post_meta( $page_id, '_wfx_tpl_version', true ) !== WFX_VERSION ) {
			wfx_apply_template( $page_id, $file );
		}
		return $page_id;
	}

	$page_id = wp_insert_post(
		array(
			'post_title'   => $title,
			'post_name'    => $slug,
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_content' => '',
		),
		true
	);

	if ( is_wp_error( $page_id ) || ! $page_id ) {
		return 0;
	}

	wfx_apply_template( $page_id, $file );
	return (int) $page_id;
}

/**
 * Build and assign the primary menu (once).
 *
 * @param array $ids slug => page ID.
 */
function wfx_build_menu( $ids ) {
	$menu_name = 'WEBFIXUS Menu';
	$menu      = wp_get_nav_menu_object( $menu_name );
	$menu_id   = $menu ? (int) $menu->term_id : (int) wp_create_nav_menu( $menu_name );

	if ( is_wp_error( $menu_id ) || ! $menu_id ) {
		return;
	}

	// Only populate if empty (idempotent).
	$items = wp_get_nav_menu_items( $menu_id );
	if ( empty( $items ) ) {
		$order = 1;
		$plan  = array(
			'home'    => array( 'Home', '' ),
			'work'    => array( 'Work', '' ),
			'about'   => array( 'About', '' ),
			'contact' => array( 'Contact', 'wfx-nav-cta' ),
		);
		foreach ( $plan as $slug => $data ) {
			if ( empty( $ids[ $slug ] ) ) {
				continue;
			}
			wp_update_nav_menu_item(
				$menu_id,
				0,
				array(
					'menu-item-title'     => $data[0],
					'menu-item-object'    => 'page',
					'menu-item-object-id' => (int) $ids[ $slug ],
					'menu-item-type'      => 'post_type',
					'menu-item-status'    => 'publish',
					'menu-item-position'  => $order,
					'menu-item-classes'   => $data[1],
				)
			);
			$order++;
		}
	}

	// Assign to the primary location.
	$locations = get_theme_mod( 'nav_menu_locations' );
	if ( ! is_array( $locations ) ) {
		$locations = array();
	}
	$locations['primary'] = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );
}

/**
 * Run scaffolding when the theme is activated.
 */
function wfx_scaffold() {
	$ids = array();
	foreach ( wfx_pages_map() as $slug => $def ) {
		$ids[ $slug ] = wfx_create_page( $slug, $def[0], $def[1] );
	}

	// Static front page → Home.
	if ( ! empty( $ids['home'] ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', (int) $ids['home'] );
	}

	wfx_build_menu( $ids );

	// Let Elementor regenerate cached CSS on next load.
	if ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->files_manager ) ) {
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}

	update_option( 'wfx_scaffolded', WFX_VERSION );
}
add_action( 'after_switch_theme', 'wfx_scaffold' );

/**
 * Catch the case where the theme files are replaced/updated while already the
 * active theme (no after_switch_theme fires). On the next admin load we detect
 * the version bump and refresh the pages/menu once.
 */
function wfx_maybe_scaffold() {
	if ( get_option( 'wfx_scaffolded' ) !== WFX_VERSION ) {
		wfx_scaffold();
	}
}
add_action( 'admin_init', 'wfx_maybe_scaffold' );
