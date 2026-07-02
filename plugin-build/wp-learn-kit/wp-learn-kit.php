<?php
/**
 * ============================================================================
 *  SUBSYSTEM 1 — THE PLUGIN HEADER
 * ============================================================================
 *  WHAT:  This special comment block is the ONLY thing that turns a plain PHP
 *         file into a "plugin" in WordPress's eyes. WP scans the first comment
 *         of every top-level file in wp-content/plugins/ looking for these
 *         "Plugin Name:" style fields.
 *  WHY:   The text after "Plugin Name:" is exactly what shows up on the
 *         Plugins admin screen. Without this header, your file is invisible.
 *  WHEN:  Read once by WordPress when it lists/loads plugins.
 *
 * Plugin Name:       WP Learn Kit
 * Plugin URI:        https://example.com/wp-learn-kit
 * Description:       A heavily-commented tour of how a WordPress plugin works: hooks, filters, shortcodes, a settings page, and asset loading.
 * Version:           1.0.1
 * Requires at least: 5.0
 * Requires PHP:      7.2
 * Author:            You
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wp-learn-kit
 * ============================================================================
 */

/**
 * ============================================================================
 *  SUBSYSTEM 2 — DEFENSIVE BOILERPLATE + PATH CONSTANTS
 * ============================================================================
 *  WHAT:  Stop the file from running if someone visits it directly in a
 *         browser (e.g. /wp-content/plugins/wp-learn-kit/wp-learn-kit.php).
 *  WHY:   ABSPATH is only defined when WordPress itself is loading. If it's
 *         missing, the request didn't come through WordPress -> bail out.
 *         This is the single most common first line in real-world plugins.
 *  WHEN:  Every time PHP includes this file.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden.
}

/**
 *  Constants we reuse everywhere below.
 *  - WLK_VERSION: bump this when you change CSS/JS so browsers re-download it
 *    (it's passed to wp_enqueue_* as the cache-busting version).
 *  - WLK_PATH:    absolute server path to this plugin folder (for require/include).
 *  - WLK_URL:     public URL to this plugin folder (for loading assets in <link>/<script>).
 *  The "WLK_" prefix (WP Learn Kit) is our namespace — every function, hook,
 *  and option in this file is prefixed the same way so it can never collide
 *  with WordPress core or another plugin.
 */
define( 'WLK_VERSION', '1.0.1' );
define( 'WLK_PATH', plugin_dir_path( __FILE__ ) ); // e.g. /var/www/.../plugins/wp-learn-kit/
define( 'WLK_URL', plugin_dir_url( __FILE__ ) );   // e.g. https://site.com/wp-content/plugins/wp-learn-kit/

/** The single option (a PHP array) where ALL our settings live in the database. */
define( 'WLK_OPTION', 'wlk_settings' );

/**
 * Small helper: read our settings array, falling back to sensible defaults.
 * Centralising this means the banner, the filter, and the shortcode all read
 * the same source of truth.
 */
function wlk_get_settings() {
	$defaults = array(
		'banner_text' => 'WP Learn Kit is active — this banner is printed by an action hook.',
	);
	// get_option() pulls our row out of the wp_options table; wp_parse_args()
	// fills in any missing keys with the defaults above.
	return wp_parse_args( get_option( WLK_OPTION, array() ), $defaults );
}

/**
 * ============================================================================
 *  SUBSYSTEM 3 — ACTIVATION / DEACTIVATION HOOKS
 * ============================================================================
 *  WHAT:  Code that runs ONCE, at the exact moment the user clicks "Activate"
 *         or "Deactivate" on the Plugins screen (NOT on every page load).
 *  WHY:   Activation is where you set up: seed default options, create custom
 *         database tables, register custom post types then flush rewrite rules.
 *         Deactivation is where you undo *temporary* things (scheduled events,
 *         rewrite rules). Note: deactivation should NOT delete user data — that
 *         belongs in uninstall.php (Subsystem 9).
 *  WHEN:  Activation -> once on activate. Deactivation -> once on deactivate.
 */
function wlk_on_activate() {
	// Seed the option only if it doesn't already exist, so re-activating
	// doesn't wipe a user's saved banner text.
	if ( false === get_option( WLK_OPTION ) ) {
		add_option( WLK_OPTION, wlk_get_settings() );
	}

	// flush_rewrite_rules() rebuilds WordPress's URL routing table. We don't
	// strictly need it here (no custom post types/endpoints), but this is the
	// canonical place to call it and shows the standard pattern.
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'wlk_on_activate' );

function wlk_on_deactivate() {
	// Clean up only *temporary* runtime stuff here. We flush rewrite rules
	// again so any routes we (might have) added are removed cleanly.
	flush_rewrite_rules();
	// NOTE: we deliberately do NOT delete WLK_OPTION here — the user might just
	// be toggling the plugin off. Real deletion happens in uninstall.php.
}
register_deactivation_hook( __FILE__, 'wlk_on_deactivate' );

/**
 * ============================================================================
 *  SUBSYSTEMS 4 & 5 — REMOVED (front-end demo injections)
 * ============================================================================
 *  These originally injected two teaching artifacts on EVERY front-end page:
 *    - a "WP Learn Kit is active…" footer banner (a wp_footer action), and
 *    - an appended note on every page's content (a the_content filter).
 *  Both were removed so nothing is auto-added to the live site. The banner
 *  text setting on the admin page is now cosmetic/unused.
 * ============================================================================
 */

/**
 * ============================================================================
 *  SUBSYSTEM 6 — A SHORTCODE
 * ============================================================================
 *  WHAT:  A shortcode turns [learn_box] (typed into any page, post, or an
 *         Elementor Text/Shortcode widget) into HTML your function generates.
 *  WHY:   It's the user-facing way to let non-developers drop a feature exactly
 *         where they want it, without touching code.
 *  WHEN:  Whenever WordPress renders content containing [learn_box ...].
 *
 *  Supports an optional attribute: [learn_box title="Hi"] text here [/learn_box]
 */
function wlk_shortcode_box( $atts, $content = null ) {
	// shortcode_atts() merges user-supplied attributes over our defaults.
	$atts = shortcode_atts(
		array(
			'title' => 'Learn Box',
		),
		$atts,
		'learn_box'
	);

	// Build the output. esc_html on the attribute, and run the inner content
	// (if any) through do_shortcode so nested shortcodes still work.
	$inner = $content
		? do_shortcode( $content )
		: 'This styled box was produced by the [learn_box] shortcode.';

	return sprintf(
		'<div class="wlk-box"><strong class="wlk-box-title">%s</strong><div class="wlk-box-body">%s</div></div>',
		esc_html( $atts['title'] ),
		wp_kses_post( $inner ) // allow basic HTML in the body but strip anything dangerous
	);
}
add_shortcode( 'learn_box', 'wlk_shortcode_box' );

/**
 * ============================================================================
 *  SUBSYSTEM 7 — ADMIN MENU + SETTINGS API
 * ============================================================================
 *  WHAT:  Adds a "WP Learn Kit" item to the wp-admin sidebar and a real
 *         settings form behind it, using WordPress's built-in Settings API
 *         (register_setting / add_settings_section / add_settings_field).
 *  WHY:   The Settings API handles saving, nonces (CSRF protection), and
 *         sanitisation for you — far safer than hand-rolling a form.
 *  WHEN:  'admin_menu' fires while building the dashboard sidebar;
 *         'admin_init' fires on every admin page load to register the fields.
 */

// 7a) Add the sidebar menu item -> renders via wlk_render_settings_page().
function wlk_register_admin_menu() {
	add_menu_page(
		'WP Learn Kit',              // browser <title>
		'WP Learn Kit',              // sidebar label
		'manage_options',            // capability: only admins can see it
		'wp-learn-kit',              // unique menu slug (also the page URL ?page=wp-learn-kit)
		'wlk_render_settings_page',  // function that prints the page HTML
		'dashicons-lightbulb',       // sidebar icon
		80                           // position in the menu
	);
}
add_action( 'admin_menu', 'wlk_register_admin_menu' );

// 7b) Tell WordPress about our setting, section, and field.
function wlk_register_settings() {
	// Register the option + a sanitisation callback that runs on every save.
	register_setting(
		'wlk_settings_group', // a group name we reference in the form below
		WLK_OPTION,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'wlk_sanitize_settings',
			'default'           => wlk_get_settings(),
		)
	);

	// A visual section (a heading + optional intro) on the settings page.
	add_settings_section(
		'wlk_main_section',
		'Banner Settings',
		function () {
			echo '<p>Change the text shown in the front-end footer banner. Save, then reload your site.</p>';
		},
		'wp-learn-kit' // must match the menu slug so the section appears there
	);

	// A single input field inside that section.
	add_settings_field(
		'wlk_banner_text',
		'Banner text',
		'wlk_field_banner_text', // function that prints the <input>
		'wp-learn-kit',
		'wlk_main_section'
	);
}
add_action( 'admin_init', 'wlk_register_settings' );

// 7c) Sanitise EVERY value before it touches the database.
function wlk_sanitize_settings( $input ) {
	$clean = array();
	$clean['banner_text'] = isset( $input['banner_text'] )
		? sanitize_text_field( $input['banner_text'] ) // strips tags, trims, etc.
		: '';
	return $clean;
}

// 7d) Print the actual <input>, pre-filled with the saved value.
function wlk_field_banner_text() {
	$settings = wlk_get_settings();
	printf(
		'<input type="text" name="%1$s[banner_text]" value="%2$s" class="regular-text" />',
		esc_attr( WLK_OPTION ),                 // name="wlk_settings[banner_text]"
		esc_attr( $settings['banner_text'] )    // escape for an HTML attribute
	);
}

// 7e) The settings page wrapper. settings_fields() + do_settings_sections()
//     are the two magic calls that wire the Settings API into a normal form.
function wlk_render_settings_page() {
	// Block users who somehow reach this without the capability.
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1>WP Learn Kit</h1>
		<p>This whole screen is Subsystem 7. The form below is rendered and saved entirely by WordPress's Settings API.</p>
		<form method="post" action="options.php">
			<?php
			settings_fields( 'wlk_settings_group' );   // hidden fields + nonce (CSRF token)
			do_settings_sections( 'wp-learn-kit' );    // prints our section + field
			submit_button();                           // the "Save Changes" button
			?>
		</form>
		<hr />
		<h2>Try it</h2>
		<ul style="list-style:disc;margin-left:20px;">
			<li>Visit your site's front end → see the footer banner (Subsystem 4) + JS console log (Subsystem 8).</li>
			<li>Open any post/page → note appended at the end (Subsystem 5).</li>
			<li>Add <code>[learn_box title="Demo"]hello[/learn_box]</code> to a page (Subsystem 6).</li>
		</ul>
	</div>
	<?php
}

/**
 * ============================================================================
 *  SUBSYSTEM 8 — ENQUEUING ASSETS (CSS / JS)
 * ============================================================================
 *  WHAT:  The CORRECT way to load a stylesheet or script: register it with
 *         WordPress so it manages order, dependencies, and de-duplication.
 *         Never hard-code <link>/<script> tags in your output.
 *  WHY:   wp_enqueue_* prevents the same file loading twice, lets other code
 *         depend on yours, and adds the ?ver= cache-buster automatically.
 *  WHEN:  'wp_enqueue_scripts' fires when building the FRONT-END page assets.
 *         (For admin pages you'd use 'admin_enqueue_scripts' instead.)
 */
function wlk_enqueue_front_assets() {
	// handle, file URL, dependencies, version (for cache-busting), media.
	wp_enqueue_style(
		'wlk-style',
		WLK_URL . 'assets/learn.css',
		array(),
		WLK_VERSION
	);

	// handle, file URL, dependencies, version, load-in-footer = true.
	wp_enqueue_script(
		'wlk-script',
		WLK_URL . 'assets/learn.js',
		array(),
		WLK_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'wlk_enqueue_front_assets' );

/**
 * ----------------------------------------------------------------------------
 *  That's the whole tour. Subsystem 9 (uninstall cleanup) lives in its own
 *  file, uninstall.php, because WordPress runs that file ONLY when the plugin
 *  is deleted — this main file is never even loaded at delete time.
 * ----------------------------------------------------------------------------
 */
