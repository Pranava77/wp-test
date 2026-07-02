<?php
/**
 * ============================================================================
 *  WP GALLERY — an image gallery you manage from wp-admin and drop on any page
 *  with the [gallery_grid] shortcode.
 * ============================================================================
 *  Same design as WP Team: instead of inventing our own database tables, we
 *  register a CUSTOM POST TYPE (CPT) so WordPress gives us an admin editor, a
 *  list table, featured images, and ordering — for free.
 *
 *  Each gallery item uses built-in WordPress fields + a few custom ones:
 *    - Images   -> a MULTI-IMAGE picker (Subsystem I) — one item can hold many
 *                  images; the Featured image is used as a fallback cover.
 *    - Caption  -> the post TITLE
 *    - Note     -> the post EXCERPT (a short line under the caption)
 *    - Link     -> a custom field in the details META BOX (Subsystem C)
 *    - Category -> the "Gallery Categories" TAXONOMY (Subsystem T)
 *    - Order    -> the built-in "Order" field (Page Attributes)
 *
 *  On the front end, [gallery_grid] renders a responsive brutalist grid (one
 *  tile per image), an optional CATEGORY FILTER bar, and a tiny built-in
 *  LIGHTBOX (Subsystem J) — no third-party library required.
 *
 * Plugin Name:       WP Gallery
 * Description:       Image gallery with categories and multiple images per item. Show it anywhere with [gallery_grid]; includes a click-to-enlarge lightbox.
 * Version:           1.1.2
 * Requires at least: 5.0
 * Requires PHP:      7.2
 * Author:            You
 * License:           GPL-2.0-or-later
 * Text Domain:       wp-gallery
 * ============================================================================
 */

// Defensive boilerplate: don't run if loaded outside of WordPress.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Reusable constants. "WG_" (WP Gallery) is our unique prefix/namespace.
define( 'WG_VERSION', '1.1.2' );
define( 'WG_URL', plugin_dir_url( __FILE__ ) );
define( 'WG_CPT', 'gallery_item' );  // the internal name of our custom post type
define( 'WG_TAX', 'gallery_cat' );   // the internal name of our category taxonomy

/**
 * ============================================================================
 *  SUBSYSTEM A — REGISTER THE CUSTOM POST TYPE ("Gallery Item")
 * ============================================================================
 *  WHAT:  register_post_type() creates a brand-new content type. After this
 *         runs, a "Gallery" menu appears in wp-admin with Add/Edit/List screens.
 *  WHY:   It's the standard, theme-independent way to store structured content.
 *  WHEN:  Must run on the 'init' hook (WordPress requires CPTs be registered there).
 */
function wg_register_cpt() {
	$labels = array(
		'name'          => 'Gallery',
		'singular_name' => 'Gallery Item',
		'add_new'       => 'Add New',
		'add_new_item'  => 'Add New Gallery Item',
		'edit_item'     => 'Edit Gallery Item',
		'new_item'      => 'New Gallery Item',
		'view_item'     => 'View Gallery Item',
		'search_items'  => 'Search Gallery',
		'not_found'     => 'No gallery items yet',
		'all_items'     => 'All Items',
		'menu_name'     => 'Gallery',
	);

	register_post_type(
		WG_CPT,
		array(
			'labels'        => $labels,
			'public'        => true,           // viewable + queryable on the front end
			'has_archive'   => false,          // we show them via shortcode, not an archive page
			'menu_icon'     => 'dashicons-format-gallery',
			'menu_position' => 26,
			'show_in_rest'  => true,           // enables the modern block editor for this CPT
			'taxonomies'    => array( WG_TAX ),
			// 'supports' lists which built-in editor panels this CPT gets:
			//   title           -> the item CAPTION
			//   thumbnail       -> the "Featured image" box = a FALLBACK cover image
			//   excerpt         -> optional short note under the caption
			//   page-attributes -> the numeric "Order" field, so you can sort items
			'supports'      => array( 'title', 'thumbnail', 'excerpt', 'page-attributes' ),
		)
	);
}
add_action( 'init', 'wg_register_cpt' );

/**
 * ============================================================================
 *  SUBSYSTEM T — REGISTER THE CATEGORY TAXONOMY ("Gallery Categories")
 * ============================================================================
 *  WHAT:  register_taxonomy() adds a category-like system to our CPT. A
 *         "Categories" panel appears on the edit screen AND a "Gallery →
 *         Categories" admin page for creating/managing terms.
 *  WHY:   Lets you group images (e.g. "Weddings", "Nature") and show one group
 *         at a time with [gallery_grid category="nature"].
 *  WHEN:  Also on 'init'. WordPress requires taxonomies be registered there.
 */
function wg_register_taxonomy() {
	$labels = array(
		'name'              => 'Gallery Categories',
		'singular_name'     => 'Category',
		'search_items'      => 'Search Categories',
		'all_items'         => 'All Categories',
		'parent_item'       => 'Parent Category',
		'parent_item_colon' => 'Parent Category:',
		'edit_item'         => 'Edit Category',
		'update_item'       => 'Update Category',
		'add_new_item'      => 'Add New Category',
		'new_item_name'     => 'New Category Name',
		'menu_name'         => 'Categories',
	);

	register_taxonomy(
		WG_TAX,
		WG_CPT,
		array(
			'labels'            => $labels,
			'hierarchical'      => true,   // behaves like post categories (checkboxes, parents)
			'public'            => true,
			'show_admin_column' => true,   // adds a "Categories" column to the list table
			'show_in_rest'      => true,   // usable in the block editor
			'rewrite'           => array( 'slug' => 'gallery-category' ),
		)
	);
}
add_action( 'init', 'wg_register_taxonomy' );

/**
 *  Activation hook: register the CPT + taxonomy once, then flush rewrite rules
 *  so the new URLs work immediately. (Registering inside the activation callback
 *  too is required because 'init' hasn't fired yet at activation time.)
 */
function wg_on_activate() {
	wg_register_cpt();
	wg_register_taxonomy();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'wg_on_activate' );

// Mirror on deactivate so leftover CPT/taxonomy routes are cleared.
function wg_on_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'wg_on_deactivate' );

/**
 * ============================================================================
 *  SUBSYSTEM I — THE MULTI-IMAGE PICKER (store many images per item)
 * ============================================================================
 *  WHAT:  Reads the images assigned to an item. They're stored as POST META
 *         under '_wg_images' — an array of Media Library attachment IDs.
 *  WHY:   A single Featured image was the old limitation. Now one item can hold
 *         a whole set, and each image becomes its own tile on the front end.
 *  FALLBACK: if no images were picked, we use the Featured image (if any), so
 *         old items created before this feature keep working.
 */
function wg_get_image_ids( $post_id ) {
	$ids = get_post_meta( $post_id, '_wg_images', true );

	// Meta may come back as an array (normal) or a legacy comma string; normalise.
	if ( is_string( $ids ) ) {
		$ids = explode( ',', $ids );
	}
	if ( ! is_array( $ids ) ) {
		$ids = array();
	}
	$ids = array_values( array_filter( array_map( 'absint', $ids ) ) );

	// Fallback to the Featured image so pre-1.1.0 items still show something.
	if ( empty( $ids ) && has_post_thumbnail( $post_id ) ) {
		$ids = array( (int) get_post_thumbnail_id( $post_id ) );
	}

	return $ids;
}

/**
 * ============================================================================
 *  SUBSYSTEM C — THE DETAILS META BOX (Images picker + Link URL)
 * ============================================================================
 *  WHAT:  A custom panel on the edit screen. It renders the multi-image picker
 *         (backed by the WordPress media library via admin.js) and an optional
 *         "link" field, saving both as POST META.
 *  WHEN:  'add_meta_boxes' builds the edit screen; 'save_post' fires on save.
 */
function wg_add_meta_box() {
	add_meta_box(
		'wg_details',          // unique id
		'Gallery Images & Link',
		'wg_render_meta_box',  // callback that prints the fields
		WG_CPT,                // show only on our CPT
		'normal',              // context (main column)
		'high'                 // priority (near the top)
	);
}
add_action( 'add_meta_boxes', 'wg_add_meta_box' );

// Print the picker + link field, pre-filled with any saved values.
function wg_render_meta_box( $post ) {
	// A nonce = a one-time security token proving the save request really came
	// from this form (CSRF protection). We verify it on save.
	wp_nonce_field( 'wg_save_details', 'wg_details_nonce' );

	// --- Multi-image picker ---------------------------------------------------
	$ids = wg_get_image_ids( $post->ID );
	echo '<div class="wg-images-field">';
	echo '<p style="margin:0 0 8px;color:#666;">Add one or more images for this item. On the front end, <strong>each image becomes its own tile</strong>. Drag thumbnails to reorder.</p>';

	echo '<ul class="wg-images-preview">';
	foreach ( $ids as $id ) {
		$thumb = wp_get_attachment_image_url( $id, 'thumbnail' );
		if ( ! $thumb ) {
			continue;
		}
		printf(
			'<li class="wg-thumb" data-id="%1$d"><img src="%2$s" alt="" /><button type="button" class="wg-thumb-remove" aria-label="Remove">&times;</button></li>',
			(int) $id,
			esc_url( $thumb )
		);
	}
	echo '</ul>';

	// The hidden input actually submitted with the form: a comma list of IDs.
	printf(
		'<input type="hidden" class="wg-images-ids" name="_wg_images" value="%s" />',
		esc_attr( implode( ',', $ids ) )
	);
	echo '<button type="button" class="button button-primary wg-images-add">Add / select images</button> ';
	echo '<button type="button" class="button wg-images-clear">Remove all</button>';
	echo '</div>';

	// --- Optional link field --------------------------------------------------
	$link = get_post_meta( $post->ID, '_wg_link', true );
	echo '<p style="margin-top:18px;"><label for="_wg_link" style="display:block;font-weight:600;">Link URL (optional — where a tile points when clicked)</label>';
	printf(
		'<input type="url" id="_wg_link" name="_wg_link" value="%s" class="widefat" placeholder="https://example.com" /></p>',
		esc_attr( $link )
	);
	echo '<p style="color:#666;margin-top:-4px;">If set, clicking a tile opens this link instead of the lightbox. The caption is the title above; the note is the “Excerpt”.</p>';
}

// Save the values when the post is saved.
function wg_save_meta( $post_id ) {
	// Guard clauses — skip if this isn't a real, authorised user save:
	// 1) nonce missing or invalid -> not our trusted form.
	if ( ! isset( $_POST['wg_details_nonce'] ) ||
	     ! wp_verify_nonce( $_POST['wg_details_nonce'], 'wg_save_details' ) ) {
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

	// Images: parse the comma list into a clean, de-duplicated array of IDs.
	if ( isset( $_POST['_wg_images'] ) ) {
		$raw = wp_unslash( $_POST['_wg_images'] );
		$ids = array_filter( array_map( 'absint', explode( ',', $raw ) ) );
		$ids = array_values( array_unique( $ids ) );
		update_post_meta( $post_id, '_wg_images', $ids );
	}

	// Link: sanitise as a URL before storing. NEVER trust raw input.
	if ( isset( $_POST['_wg_link'] ) ) {
		update_post_meta( $post_id, '_wg_link', esc_url_raw( wp_unslash( $_POST['_wg_link'] ) ) );
	}
}
add_action( 'save_post_' . WG_CPT, 'wg_save_meta' ); // note: CPT-specific save hook

/**
 * ============================================================================
 *  SUBSYSTEM ADMIN — LOAD THE MEDIA UPLOADER ON OUR EDIT SCREEN
 * ============================================================================
 *  The multi-image picker relies on the WordPress media modal (wp.media) and a
 *  little jQuery in admin.js. We only load these on the Gallery Item editor.
 */
function wg_admin_assets( $hook ) {
	if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
		return;
	}
	$screen = get_current_screen();
	if ( ! $screen || WG_CPT !== $screen->post_type ) {
		return;
	}
	wp_enqueue_media(); // makes wp.media() available
	wp_enqueue_style( 'wg-admin', WG_URL . 'assets/admin.css', array(), WG_VERSION );
	wp_enqueue_script( 'wg-admin', WG_URL . 'assets/admin.js', array( 'jquery', 'jquery-ui-sortable' ), WG_VERSION, true );
}
add_action( 'admin_enqueue_scripts', 'wg_admin_assets' );

/**
 * ============================================================================
 *  SUBSYSTEM H — "HOW TO DISPLAY" HELP (so the shortcode is easy to find)
 * ============================================================================
 *  WHAT:  A side panel on the edit screen + a hint above the Gallery list that
 *         show the exact [gallery_grid] shortcode to copy.
 *  WHY:   Nothing in wp-admin told you the shortcode's name before — this makes
 *         it discoverable instead of something you have to already know.
 */

// H1) A "How to display" box in the sidebar of the edit screen.
function wg_add_help_meta_box() {
	add_meta_box(
		'wg_help',
		'How to display this gallery',
		'wg_render_help_meta_box',
		WG_CPT,
		'side',   // right-hand column
		'high'
	);
}
add_action( 'add_meta_boxes', 'wg_add_help_meta_box' );

// A tiny helper: a read-only field that selects itself on click (easy copy).
function wg_copy_field( $code ) {
	printf(
		'<input type="text" readonly onclick="this.select();" value="%s" class="widefat code" style="margin-bottom:6px;font-family:monospace;" />',
		esc_attr( $code )
	);
}

function wg_render_help_meta_box( $post ) {
	echo '<p style="margin-top:0;">Paste this on any page/post, or in an Elementor <strong>Shortcode</strong> widget:</p>';
	wg_copy_field( '[gallery_grid]' );

	echo '<p style="color:#666;margin:10px 0 4px;">Handy variations:</p>';
	wg_copy_field( '[gallery_grid columns="4"]' );
	wg_copy_field( '[gallery_grid filter="yes"]' );

	// If this item has a category, offer a ready-made filtered shortcode.
	$terms = get_the_terms( $post->ID, WG_TAX );
	if ( $terms && ! is_wp_error( $terms ) ) {
		echo '<p style="color:#666;margin:10px 0 4px;">Only this item\'s category:</p>';
		wg_copy_field( '[gallery_grid category="' . $terms[0]->slug . '"]' );
	}

	echo '<p style="color:#666;margin:10px 0 0;font-size:11px;">Tip: click a field to select it, then copy.</p>';
}

// H2) A one-line reminder banner at the top of the Gallery list table.
function wg_list_hint() {
	$screen = get_current_screen();
	if ( $screen && 'edit-' . WG_CPT === $screen->id ) {
		echo '<div class="notice notice-info" style="margin-top:12px;"><p>'
			. 'Show your gallery on a page with the shortcode <code>[gallery_grid]</code> '
			. '(options: <code>columns</code>, <code>category</code>, <code>filter="yes"</code>, <code>limit</code>).'
			. '</p></div>';
	}
}
add_action( 'admin_notices', 'wg_list_hint' );

// H3) A "Shortcode" column in the Gallery list table, so the shortcode shows on
//     every row. NOTE: this plugin has ONE shortcode for the whole gallery —
//     [gallery_grid] renders all images together; a row is a single image, not
//     its own gallery. For items with a category we also show the filtered form.
function wg_add_shortcode_column( $columns ) {
	$new = array();
	foreach ( $columns as $key => $label ) {
		if ( 'date' === $key ) {
			$new['wg_shortcode'] = 'Shortcode';
		}
		$new[ $key ] = $label;
	}
	// If there was no date column, just append it.
	if ( ! isset( $new['wg_shortcode'] ) ) {
		$new['wg_shortcode'] = 'Shortcode';
	}
	return $new;
}
add_filter( 'manage_' . WG_CPT . '_posts_columns', 'wg_add_shortcode_column' );

function wg_render_shortcode_column( $column, $post_id ) {
	if ( 'wg_shortcode' !== $column ) {
		return;
	}
	echo '<code>[gallery_grid]</code><br><span style="color:#777;font-size:11px;">shows the whole gallery</span>';

	$terms = get_the_terms( $post_id, WG_TAX );
	if ( $terms && ! is_wp_error( $terms ) ) {
		printf(
			'<br><code>[gallery_grid category="%s"]</code><br><span style="color:#777;font-size:11px;">only the “%s” category</span>',
			esc_attr( $terms[0]->slug ),
			esc_html( $terms[0]->name )
		);
	}
}
add_action( 'manage_' . WG_CPT . '_posts_custom_column', 'wg_render_shortcode_column', 10, 2 );

/**
 * ============================================================================
 *  SUBSYSTEM S — THE [gallery_grid] SHORTCODE (the front-end display)
 * ============================================================================
 *  WHAT:  Queries published gallery items and renders every image as a tile.
 *  WHY:   Lets you place the gallery on any page or Elementor widget by typing
 *         [gallery_grid].
 *
 *  Attributes:
 *    [gallery_grid columns="3" limit="-1" category="" filter="no" lightbox="yes"]
 *      columns  -> how many across on desktop (default 3)
 *      limit    -> max ITEMS to query, -1 = all (default all)
 *      category -> show only this category (slug or comma-list of slugs)
 *      filter   -> "yes" shows a clickable category filter bar above the grid
 *      lightbox -> "yes" (default) enables click-to-enlarge; "no" disables it
 */
function wg_shortcode_gallery_grid( $atts ) {
	$atts = shortcode_atts(
		array(
			'columns'  => '3',
			'limit'    => '-1',
			'category' => '',
			'filter'   => 'no',
			'lightbox' => 'yes',
		),
		$atts,
		'gallery_grid'
	);

	$args = array(
		'post_type'      => WG_CPT,
		'post_status'    => 'publish',
		'posts_per_page' => intval( $atts['limit'] ),
		'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
	);

	// Optional category filter (accepts one slug or a comma-separated list).
	if ( '' !== trim( $atts['category'] ) ) {
		$slugs         = array_filter( array_map( 'sanitize_title', explode( ',', $atts['category'] ) ) );
		$args['tax_query'] = array(
			array(
				'taxonomy' => WG_TAX,
				'field'    => 'slug',
				'terms'    => $slugs,
			),
		);
	}

	$query = new WP_Query( $args );

	if ( ! $query->have_posts() ) {
		return '<p class="wg-empty">No images yet. Add some under <strong>Gallery → Add New</strong>.</p>';
	}

	$cols       = max( 1, intval( $atts['columns'] ) );
	$lightbox   = ( 'no' !== strtolower( $atts['lightbox'] ) );
	$want_filter = ( 'yes' === strtolower( $atts['filter'] ) );

	$tiles      = '';        // buffer of tile HTML
	$used_terms = array();   // slug => name, for building the filter bar

	while ( $query->have_posts() ) {
		$query->the_post();
		$id      = get_the_ID();
		$caption = get_the_title();
		$note    = get_the_excerpt();
		$link    = get_post_meta( $id, '_wg_link', true );
		$images  = wg_get_image_ids( $id );

		if ( empty( $images ) ) {
			continue; // an item with no image can't produce tiles
		}

		// Which categories this item belongs to -> data attribute + filter bar.
		$terms      = get_the_terms( $id, WG_TAX );
		$item_slugs = array();
		if ( $terms && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $t ) {
				$item_slugs[]            = $t->slug;
				$used_terms[ $t->slug ]  = $t->name;
			}
		}
		$cats_attr = esc_attr( implode( ' ', $item_slugs ) );

		// One tile per image on this item.
		foreach ( $images as $img_id ) {
			$img_html = wp_get_attachment_image( $img_id, 'large', false, array( 'class' => 'wg-img', 'loading' => 'lazy' ) );
			if ( ! $img_html ) {
				continue;
			}

			$full_src = wp_get_attachment_image_url( $img_id, 'full' );
			if ( ! $full_src ) {
				$full_src = wp_get_attachment_image_url( $img_id, 'large' );
			}

			$has_link     = ! empty( $link );
			$use_lightbox = $lightbox && ! $has_link && $full_src;

			if ( $has_link ) {
				$open  = '<a class="wg-tile" data-cats="' . $cats_attr . '" href="' . esc_url( $link ) . '" target="_blank" rel="noopener">';
				$close = '</a>';
			} elseif ( $use_lightbox ) {
				$open  = '<a class="wg-tile wg-lb" data-cats="' . $cats_attr . '" href="' . esc_url( $full_src ) . '"'
					. ' data-wg-full="' . esc_url( $full_src ) . '"'
					. ' data-wg-caption="' . esc_attr( $caption ) . '">';
				$close = '</a>';
			} else {
				$open  = '<figure class="wg-tile" data-cats="' . $cats_attr . '">';
				$close = '</figure>';
			}

			$tiles .= $open;
			$tiles .= '<div class="wg-photo">' . $img_html . '</div>';
			if ( $caption || $note ) {
				$tiles .= '<figcaption class="wg-meta">';
				if ( $caption ) {
					$tiles .= '<span class="wg-caption">' . esc_html( $caption ) . '</span>';
				}
				if ( $note ) {
					$tiles .= '<span class="wg-note">' . esc_html( $note ) . '</span>';
				}
				$tiles .= '</figcaption>';
			}
			$tiles .= $close;
		}
	}
	wp_reset_postdata(); // ALWAYS reset after a custom WP_Query loop

	if ( '' === $tiles ) {
		return '<p class="wg-empty">No images yet. Add some under <strong>Gallery → Add New</strong>.</p>';
	}

	// Assemble the final output: optional filter bar + grid + lightbox overlay.
	ob_start();

	// Category filter bar (only when asked for AND there's more than one category).
	if ( $want_filter && count( $used_terms ) > 1 ) {
		asort( $used_terms ); // alphabetical by name
		echo '<div class="wg-filter" role="tablist">';
		echo '<button type="button" class="wg-filter-btn is-active" data-filter="*">All</button>';
		foreach ( $used_terms as $slug => $name ) {
			printf(
				'<button type="button" class="wg-filter-btn" data-filter="%1$s">%2$s</button>',
				esc_attr( $slug ),
				esc_html( $name )
			);
		}
		echo '</div>';
	}

	echo '<div class="wg-grid' . ( $lightbox ? ' wg-grid--lightbox' : '' ) . '" style="--wg-cols:' . esc_attr( $cols ) . ';">';
	echo $tiles; // already escaped as it was built
	echo '</div>';

	if ( $lightbox ) {
		echo '<div class="wg-lightbox" hidden aria-hidden="true">'
			. '<button type="button" class="wg-lb-close" aria-label="Close">&times;</button>'
			. '<figure class="wg-lb-figure">'
			. '<img class="wg-lb-img" src="" alt="" />'
			. '<figcaption class="wg-lb-caption"></figcaption>'
			. '</figure>'
			. '</div>';
	}

	return ob_get_clean();
}
add_shortcode( 'gallery_grid', 'wg_shortcode_gallery_grid' );

/**
 * ============================================================================
 *  SUBSYSTEM E / J — ENQUEUE THE GRID STYLES AND THE FRONT-END SCRIPT
 * ============================================================================
 *  Load our CSS + JS the proper way. The JS (lightbox + filtering) is tiny and
 *  dependency-free.
 */
function wg_enqueue_assets() {
	wp_enqueue_style( 'wg-style', WG_URL . 'assets/gallery.css', array(), WG_VERSION );
	wp_enqueue_script( 'wg-lightbox', WG_URL . 'assets/gallery.js', array(), WG_VERSION, true );
}
add_action( 'wp_enqueue_scripts', 'wg_enqueue_assets' );
