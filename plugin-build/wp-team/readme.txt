=== WP Team ===
Contributors: you
Tags: team, staff, members, custom-post-type, shortcode
Requires at least: 5.0
Tested up to: 6.7
Requires PHP: 7.2
Stable tag: 1.0.0
License: GPLv2 or later

Add team members (photo, name, role, bio, links) and display them anywhere with [team_section].

== Description ==

WP Team registers a "Team Member" custom post type so you can manage your team
from wp-admin like normal posts:

* Photo  = the Featured image
* Name   = the post Title
* Bio    = the main editor content
* Role / Email / LinkedIn = custom fields in the "Team Member Details" box
* Order  = the Page Attributes "Order" field (lower numbers show first)

Display the whole team on any page (or an Elementor Shortcode/Text widget):

    [team_section]
    [team_section columns="4"]
    [team_section columns="3" limit="6"]

== Installation ==

1. Plugins > Add New > Upload Plugin > choose wp-team.zip > Install > Activate.
2. A "Team" menu appears in the sidebar. Click Add New for each member:
   - Type the name in the title field.
   - Write the bio in the editor.
   - Set the photo via "Set featured image".
   - Fill Role / Email / LinkedIn in the "Team Member Details" box.
   - Publish.
3. Edit a page and add the shortcode [team_section] where you want the grid.

== Changelog ==

= 1.0.0 =
* Initial release: Team Member CPT, details meta box, and [team_section] shortcode.
