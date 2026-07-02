=== WP Gallery ===
Contributors: you
Tags: gallery, images, photos, categories, lightbox, custom-post-type, shortcode
Requires at least: 5.0
Tested up to: 6.7
Requires PHP: 7.2
Stable tag: 1.1.0
License: GPLv2 or later

Image gallery with categories and multiple images per item. Show it anywhere with [gallery_grid]; includes a click-to-enlarge lightbox and an optional category filter bar.

== Description ==

WP Gallery registers a "Gallery Item" custom post type plus a "Gallery
Categories" taxonomy, so you can manage everything from wp-admin like normal
posts and categories:

* Images   = a multi-image picker (add MANY images to one item; drag to reorder).
             Each image becomes its own tile on the front end. The Featured
             image is used as a fallback if no images are picked.
* Caption  = the post Title
* Note     = the Excerpt (a short line under the caption)
* Link     = optional URL in the "Gallery Images & Link" box (a tile with a link
             opens it instead of the lightbox)
* Category = the "Gallery Categories" taxonomy (create under Gallery → Categories)
* Order    = the Page Attributes "Order" field (lower numbers show first)

Display the gallery on any page (or an Elementor Shortcode/Text widget):

    [gallery_grid]
    [gallery_grid columns="4"]
    [gallery_grid columns="3" limit="12"]
    [gallery_grid category="nature"]
    [gallery_grid category="nature,weddings"]
    [gallery_grid filter="yes"]
    [gallery_grid lightbox="no"]

Attributes:
* columns  — tiles across on desktop (default 3)
* limit    — max ITEMS to query, -1 = all (default all)
* category — show only these category slugs (one, or a comma list)
* filter   — "yes" shows a clickable category filter bar above the grid
* lightbox — "yes" (default) click-to-enlarge; "no" to disable

== Installation ==

1. Plugins > Add New > Upload Plugin > choose wp-gallery.zip > Install > Activate.
2. A "Gallery" menu appears in the sidebar.
   - Gallery → Categories: create categories (e.g. Nature, Weddings).
   - Gallery → Add New for each item:
     * Type the caption in the title field.
     * (Optional) Write a short note in the "Excerpt" box.
     * Click "Add / select images" and pick ONE OR MANY images. Drag to reorder.
     * (Optional) Add a Link URL.
     * Assign one or more Categories in the "Gallery Categories" box.
     * Publish.
3. Edit a page and add [gallery_grid] (with any attributes above) where you want it.

== Changelog ==

= 1.1.1 =
* New: a "How to display this gallery" box on the edit screen with click-to-copy
  shortcodes, plus a shortcode reminder above the Gallery list. (The shortcode is
  [gallery_grid] — it just wasn't shown anywhere in wp-admin before.)

= 1.1.0 =
* New: multiple images per Gallery Item via a media-library picker (drag to reorder).
* New: "Gallery Categories" taxonomy + category="" shortcode filter and an
  optional front-end filter bar (filter="yes").
* Featured image is now a fallback cover when no images are picked.

= 1.0.0 =
* Initial release: Gallery Image CPT, link meta box, [gallery_grid] shortcode,
  and a dependency-free lightbox.
