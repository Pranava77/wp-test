<?php
/**
 * ============================================================================
 *  SUBSYSTEM 9 — UNINSTALL CLEANUP
 * ============================================================================
 *  WHAT:  WordPress automatically runs THIS file (and only this file) when the
 *         user clicks "Delete" on the plugin — not on deactivate, only on
 *         permanent deletion. It's where you remove the data you created so you
 *         don't leave junk rows in the database.
 *  WHY:   Deactivation = temporary off switch (keep user data). Uninstall =
 *         the user is throwing the plugin away, so it's correct to delete the
 *         option we stored. Good plugins clean up after themselves.
 *  WHEN:  Once, at plugin deletion. The main plugin file is NOT loaded here,
 *         so none of its constants/functions exist — we redefine what we need.
 */

// Safety guard: only run when WordPress itself triggered the uninstall.
// WP_UNINSTALL_PLUGIN is defined exclusively during the delete process.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Remove our single option row from the wp_options table.
// (Must match the option name used in wp-learn-kit.php — 'wlk_settings'.)
delete_option( 'wlk_settings' );

// If this were a multisite network, you'd also loop sites and call
// delete_option() per site, or delete_site_option() for network settings.
// Left out here to keep the learning example focused.
