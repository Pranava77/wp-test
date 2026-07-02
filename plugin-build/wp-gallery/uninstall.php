<?php
/**
 * ============================================================================
 *  UNINSTALL — runs only when the plugin is DELETED (not on deactivate).
 * ============================================================================
 *  Teaching point: your gallery images are real user-created content (posts in
 *  the wp_posts table, plus the attachments in the Media Library). We
 *  deliberately DO NOT delete them here — a user who removes the plugin should
 *  not silently lose the gallery they built, and definitely not their uploaded
 *  images (which may be used elsewhere).
 *
 *  If you ever DO want a clean wipe of the gallery ITEMS (never the media
 *  attachments), you'd uncomment the loop below.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/*
// --- OPTIONAL cleanup of gallery items (left disabled on purpose) -----------
// NOTE: this removes only the gallery_item posts, NOT the underlying images.
$items = get_posts( array(
	'post_type'   => 'gallery_item',
	'numberposts' => -1,
	'post_status' => 'any',
	'fields'      => 'ids',
) );
foreach ( $items as $id ) {
	wp_delete_post( $id, true ); // true = bypass trash, delete permanently
}
*/
