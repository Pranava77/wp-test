=== WP Learn Kit ===
Contributors: you
Tags: learning, example, hooks, shortcode, settings-api
Requires at least: 5.0
Tested up to: 6.7
Requires PHP: 7.2
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A heavily-commented tour of how a WordPress plugin works.

== Description ==

WP Learn Kit is a teaching plugin. Read its source top-to-bottom to learn the
core plugin subsystems, each demonstrated in a self-contained, commented block:

1. The plugin header (what makes WordPress recognise a plugin).
2. Defensive boilerplate + path constants.
3. Activation / deactivation hooks.
4. An action hook (a front-end footer banner).
5. A filter hook (a note appended to post/page content).
6. A shortcode ([learn_box]).
7. An admin menu + the Settings API (a real settings screen).
8. Enqueuing CSS/JS the correct way.
9. Uninstall cleanup (uninstall.php — runs only on plugin delete).

This file (readme.txt) is itself an example: it's the standard format the
WordPress.org plugin directory expects.

== Installation ==

1. In wp-admin, go to Plugins > Add New > Upload Plugin.
2. Choose wp-learn-kit.zip and click Install Now, then Activate.
3. A "WP Learn Kit" menu appears in the sidebar — open it to change the banner.
4. Visit your site's front end to see the banner, content note, and console log.
5. Add [learn_box] to any page (works in an Elementor Text/Shortcode widget too).

== Changelog ==

= 1.0.0 =
* Initial learning release.
