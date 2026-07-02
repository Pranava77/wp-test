<?php
/**
 * ============================================================================
 *  UNINSTALL — runs only when the plugin is DELETED (not on deactivate).
 * ============================================================================
 *  Teaching point: your team members are real user-created content (posts in
 *  the wp_posts table). We deliberately DO NOT delete them here — a user who
 *  removes the plugin should not silently lose every team profile they wrote.
 *
 *  If you ever DO want a clean wipe, you'd uncomment the loop below. It deletes
 *  every team_member post (and its meta/featured-image links) permanently.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/*
// --- OPTIONAL hard cleanup (left disabled on purpose) -----------------------
$members = get_posts( array(
	'post_type'   => 'team_member',
	'numberposts' => -1,
	'post_status' => 'any',
	'fields'      => 'ids',
) );
foreach ( $members as $id ) {
	wp_delete_post( $id, true ); // true = bypass trash, delete permanently
}
*/
