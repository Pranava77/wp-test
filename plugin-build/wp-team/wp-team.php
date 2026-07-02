<?php
/**
 * ============================================================================
 *  WP TEAM — a "Team Members" plugin you manage from wp-admin and drop on any
 *  page with the [team_section] shortcode.
 * ============================================================================
 *  Builds directly on WP Learn Kit. The big new idea here is a CUSTOM POST TYPE
 *  (CPT): instead of inventing our own database tables, we tell WordPress to
 *  treat "team members" like a new kind of post. We instantly get an admin
 *  editor, a list table, featured images, and revisions — for free.
 *
 *  Each team member uses built-in WordPress fields + a few custom ones:
 *    - Photo   -> the post's FEATURED IMAGE
 *    - Name    -> the post TITLE
 *    - Bio     -> the post CONTENT (the main editor)
 *    - Role / Email / LinkedIn -> custom fields in a META BOX (Subsystem C)
 *
 * Plugin Name:       WP Team
 * Description:       Add team members (photo, name, role, bio, links) and show them anywhere with [team_section].
 * Version:           1.3.1
 * Requires at least: 5.0
 * Requires PHP:      7.2
 * Author:            You
 * License:           GPL-2.0-or-later
 * Text Domain:       wp-team
 * ============================================================================
 */

// Defensive boilerplate (same reasoning as WP Learn Kit): don't run if loaded
// outside of WordPress.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Reusable constants. "WT_" (WP Team) is our unique prefix/namespace.
define( 'WT_VERSION', '1.3.1' );
define( 'WT_URL', plugin_dir_url( __FILE__ ) );
define( 'WT_CPT', 'team_member' ); // the internal name of our custom post type

/**
 * ============================================================================
 *  SUBSYSTEM A — REGISTER THE CUSTOM POST TYPE ("Team Member")
 * ============================================================================
 *  WHAT:  register_post_type() creates a brand-new content type. After this
 *         runs, a "Team" menu appears in wp-admin with Add/Edit/List screens.
 *  WHY:   It's the standard, theme-independent way to store structured content.
 *  WHEN:  Must run on the 'init' hook (WordPress requires CPTs be registered there).
 */
function wt_register_cpt() {
	$labels = array(
		'name'               => 'Team',
		'singular_name'      => 'Team Member',
		'add_new'            => 'Add New',
		'add_new_item'       => 'Add New Team Member',
		'edit_item'          => 'Edit Team Member',
		'new_item'           => 'New Team Member',
		'view_item'          => 'View Team Member',
		'search_items'       => 'Search Team',
		'not_found'          => 'No team members yet',
		'all_items'          => 'All Team Members',
		'menu_name'          => 'Team',
	);

	register_post_type(
		WT_CPT,
		array(
			'labels'       => $labels,
			'public'       => true,           // viewable + queryable on the front end
			'has_archive'  => false,          // we show them via shortcode, not an archive page
			'menu_icon'    => 'dashicons-groups',
			'menu_position'=> 25,
			'show_in_rest' => true,           // enables the modern block editor for this CPT
			// 'supports' lists which built-in editor panels this CPT gets:
			//   title          -> the member's NAME
			//   editor         -> the member's BIO (main content area)
			//   thumbnail      -> the "Featured image" box = the member's PHOTO
			//   excerpt        -> optional short summary
			//   page-attributes-> the numeric "Order" field, so you can sort members
			'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ),
		)
	);
}
add_action( 'init', 'wt_register_cpt' );

/**
 *  Use the CLASSIC editor for team members (not the Gutenberg block editor), so
 *  this screen matches the WP Gallery editing UI: a compact title + meta-box
 *  layout. The member bio (post content) still edits fine as a classic editor.
 *  WordPress would otherwise load the block editor because the CPT supports
 *  'editor' + 'show_in_rest'; this filter opts our type out.
 */
function wt_use_classic_editor( $use_block_editor, $post_type ) {
	if ( WT_CPT === $post_type ) {
		return false;
	}
	return $use_block_editor;
}
add_filter( 'use_block_editor_for_post_type', 'wt_use_classic_editor', 10, 2 );

/**
 *  Activation hook: register the CPT once, then flush rewrite rules so the
 *  new URLs work immediately. (Registering inside the activation callback too
 *  is required because 'init' hasn't fired yet at activation time.)
 */
function wt_on_activate() {
	wt_register_cpt();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'wt_on_activate' );

// Mirror on deactivate so leftover CPT routes are cleared.
function wt_on_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'wt_on_deactivate' );

/**
 * ============================================================================
 *  SUBSYSTEM C — A META BOX FOR THE EXTRA FIELDS (Role / Email / LinkedIn)
 * ============================================================================
 *  WHAT:  Title/bio/photo are built in, but "role" etc. aren't. A meta box is
 *         a custom panel on the edit screen where we render our own inputs and
 *         save them as POST META (extra key/value data attached to the post).
 *  WHY:   This is how you extend a post with structured fields of your own.
 *  WHEN:  'add_meta_boxes' builds the edit screen; 'save_post' fires on save.
 */

// The list of meta fields we manage: meta_key => human label.
function wt_fields() {
	return array(
		'_wt_role'     => 'Role / Job title',
		'_wt_email'    => 'Email',
		'_wt_linkedin' => 'LinkedIn URL',
	);
}

// C1) Add the panel to the Team Member edit screen.
function wt_add_meta_box() {
	add_meta_box(
		'wt_details',                 // unique id
		'Team Member Details',        // panel title
		'wt_render_meta_box',         // callback that prints the fields
		WT_CPT,                       // show only on our CPT
		'normal',                     // context (main column)
		'high'                        // priority (near the top)
	);
}
add_action( 'add_meta_boxes', 'wt_add_meta_box' );

// C2) Print the input fields, pre-filled with any saved values.
function wt_render_meta_box( $post ) {
	// A nonce = a one-time security token proving the save request really came
	// from this form (CSRF protection). We verify it on save in C3.
	wp_nonce_field( 'wt_save_details', 'wt_details_nonce' );

	echo '<p style="margin:0 0 6px;color:#666;">The photo is set via the “Featured image” box. The bio is the main editor above.</p>';

	foreach ( wt_fields() as $key => $label ) {
		$value = get_post_meta( $post->ID, $key, true ); // read saved value
		printf(
			'<p><label for="%1$s" style="display:block;font-weight:600;">%2$s</label>
			 <input type="text" id="%1$s" name="%1$s" value="%3$s" class="widefat" /></p>',
			esc_attr( $key ),
			esc_html( $label ),
			esc_attr( $value )
		);
	}
}

// C3) Save the values when the post is saved.
function wt_save_meta( $post_id ) {
	// Guard clauses — skip if this isn't a real, authorised user save:
	// 1) nonce missing or invalid -> not our trusted form.
	if ( ! isset( $_POST['wt_details_nonce'] ) ||
	     ! wp_verify_nonce( $_POST['wt_details_nonce'], 'wt_save_details' ) ) {
		return;
	}
	// 2) autosave -> WordPress will call the real save later.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	// 3) the current user must actually be allowed to edit this post.
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	foreach ( wt_fields() as $key => $label ) {
		if ( ! isset( $_POST[ $key ] ) ) {
			continue;
		}
		$raw = wp_unslash( $_POST[ $key ] );
		// Sanitise per field type before storing. NEVER trust raw input.
		if ( '_wt_email' === $key ) {
			$value = sanitize_email( $raw );
		} elseif ( '_wt_linkedin' === $key ) {
			$value = esc_url_raw( $raw );
		} else {
			$value = sanitize_text_field( $raw );
		}
		update_post_meta( $post_id, $key, $value );
	}
}
add_action( 'save_post_' . WT_CPT, 'wt_save_meta' ); // note: CPT-specific save hook

/**
 * ============================================================================
 *  SUBSYSTEM H — "HOW TO DISPLAY" HELP (so the shortcode is easy to find)
 * ============================================================================
 *  WHAT:  A side panel on the edit screen, a hint banner above the Team list,
 *         and a "Shortcode" column in that list — all showing [team_section].
 *  WHY:   Nothing in wp-admin told you the shortcode's name. This plugin has ONE
 *         shortcode for the WHOLE team — [team_section] renders every member as
 *         a grid; a row is a single member, not its own separate section.
 */

// H1) A "How to display" box in the sidebar of the edit screen.
function wt_add_help_meta_box() {
	add_meta_box(
		'wt_help',
		'How to display the team',
		'wt_render_help_meta_box',
		WT_CPT,
		'side',   // right-hand column
		'high'
	);
}
add_action( 'add_meta_boxes', 'wt_add_help_meta_box' );

// A tiny helper: a read-only field that selects itself on click (easy copy).
function wt_copy_field( $code ) {
	printf(
		'<input type="text" readonly onclick="this.select();" value="%s" class="widefat code" style="margin-bottom:6px;font-family:monospace;" />',
		esc_attr( $code )
	);
}

function wt_render_help_meta_box( $post ) {
	echo '<p style="margin-top:0;">Paste this on any page/post, or in an Elementor <strong>Shortcode</strong> widget:</p>';
	wt_copy_field( '[team_section]' );

	echo '<p style="color:#666;margin:10px 0 4px;">Handy variations:</p>';
	wt_copy_field( '[team_section columns="4"]' );
	wt_copy_field( '[team_section columns="3" limit="6"]' );

	echo '<p style="color:#666;margin:10px 0 0;font-size:11px;">Tip: click a field to select it, then copy.</p>';
}

// H2) A one-line reminder banner at the top of the Team list table.
function wt_list_hint() {
	$screen = get_current_screen();
	if ( $screen && 'edit-' . WT_CPT === $screen->id ) {
		echo '<div class="notice notice-info" style="margin-top:12px;"><p>'
			. 'Show the whole team on a page with the shortcode <code>[team_section]</code> '
			. '(options: <code>columns</code>, <code>limit</code>).'
			. '</p></div>';
	}
}
add_action( 'admin_notices', 'wt_list_hint' );

// H3) A "Shortcode" column in the Team list table, so it shows on every row.
//     NOTE: one shortcode renders the whole team — a row is a single member,
//     not its own section, so the shortcode is the same on every row.
function wt_add_shortcode_column( $columns ) {
	$new = array();
	foreach ( $columns as $key => $label ) {
		if ( 'date' === $key ) {
			$new['wt_shortcode'] = 'Shortcode';
		}
		$new[ $key ] = $label;
	}
	if ( ! isset( $new['wt_shortcode'] ) ) {
		$new['wt_shortcode'] = 'Shortcode';
	}
	return $new;
}
add_filter( 'manage_' . WT_CPT . '_posts_columns', 'wt_add_shortcode_column' );

function wt_render_shortcode_column( $column, $post_id ) {
	if ( 'wt_shortcode' !== $column ) {
		return;
	}
	echo '<code>[team_section]</code><br><span style="color:#777;font-size:11px;">shows the whole team</span>';
}
add_action( 'manage_' . WT_CPT . '_posts_custom_column', 'wt_render_shortcode_column', 10, 2 );

/**
 * ============================================================================
 *  SUBSYSTEM S — THE [team_section] SHORTCODE (the front-end display)
 * ============================================================================
 *  WHAT:  Queries published team members and renders them as a responsive grid.
 *  WHY:   Lets you place the whole team section on any page or Elementor widget
 *         just by typing [team_section].
 *  WHEN:  Whenever content containing [team_section] is rendered.
 *
 *  Attributes:
 *    [team_section columns="3" limit="-1"]
 *      columns -> how many across on desktop (default 3)
 *      limit   -> max members to show, -1 = all (default all)
 */
function wt_shortcode_team_section( $atts ) {
	$atts = shortcode_atts(
		array(
			'columns' => '3',
			'limit'   => '-1',
		),
		$atts,
		'team_section'
	);

	// WP_Query is WordPress's tool for fetching posts. We ask for our CPT,
	// ordered by the "Order" field (menu_order) then by title.
	$query = new WP_Query(
		array(
			'post_type'      => WT_CPT,
			'post_status'    => 'publish',
			'posts_per_page' => intval( $atts['limit'] ),
			'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
		)
	);

	if ( ! $query->have_posts() ) {
		return '<p class="wt-empty">No team members yet. Add some under <strong>Team → Add New</strong>.</p>';
	}

	// Build the HTML in a buffer so we can mix loops and markup cleanly.
	$cols = max( 1, intval( $atts['columns'] ) );
	ob_start();
	echo '<div class="wt-grid" style="--wt-cols:' . esc_attr( $cols ) . ';">';

	while ( $query->have_posts() ) {
		$query->the_post();
		$id       = get_the_ID();
		$role     = get_post_meta( $id, '_wt_role', true );
		$email    = get_post_meta( $id, '_wt_email', true );
		$linkedin = get_post_meta( $id, '_wt_linkedin', true );

		echo '<div class="wt-card">';

		// Photo = featured image. Fall back to a neutral placeholder circle.
		if ( has_post_thumbnail( $id ) ) {
			echo '<div class="wt-photo">' . get_the_post_thumbnail( $id, 'medium', array( 'class' => 'wt-img' ) ) . '</div>';
		} else {
			echo '<div class="wt-photo wt-photo--empty" aria-hidden="true"></div>';
		}

		echo '<h3 class="wt-name">' . esc_html( get_the_title() ) . '</h3>';

		if ( $role ) {
			echo '<p class="wt-role">' . esc_html( $role ) . '</p>';
		}

		// Bio: a short, uniform summary. We read the RAW field with
		// get_post_field() (no filters) instead of get_the_excerpt(), because
		// the auto-excerpt runs the 'the_content' filter — which is where OTHER
		// active plugins inject things (e.g. WP Learn Kit was appending its demo
		// note into every team bio). Reading raw keeps bios clean and isolated.
		$raw_bio = has_excerpt()
			? get_post_field( 'post_excerpt', $id )
			: get_post_field( 'post_content', $id );
		$bio = wp_trim_words( wp_strip_all_tags( strip_shortcodes( $raw_bio ) ), 28, '…' );
		if ( $bio ) {
			echo '<p class="wt-bio">' . esc_html( $bio ) . '</p>';
		}

		// Contact links.
		if ( $email || $linkedin ) {
			echo '<p class="wt-links">';
			if ( $email ) {
				echo '<a href="' . esc_url( 'mailto:' . $email ) . '">Email</a>';
			}
			if ( $linkedin ) {
				echo ' <a href="' . esc_url( $linkedin ) . '" target="_blank" rel="noopener">LinkedIn</a>';
			}
			echo '</p>';
		}

		echo '</div>'; // .wt-card
	}

	echo '</div>'; // .wt-grid
	wp_reset_postdata(); // ALWAYS reset after a custom WP_Query loop

	return ob_get_clean();
}
add_shortcode( 'team_section', 'wt_shortcode_team_section' );

/**
 * ============================================================================
 *  SUBSYSTEM E — ENQUEUE THE GRID STYLES
 * ============================================================================
 *  Same pattern as WP Learn Kit Subsystem 8: load our CSS the proper way.
 */
function wt_enqueue_assets() {
	wp_enqueue_style( 'wt-style', WT_URL . 'assets/team.css', array(), WT_VERSION );
}
add_action( 'wp_enqueue_scripts', 'wt_enqueue_assets' );
