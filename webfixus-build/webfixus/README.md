# WEBFIXUS — WordPress theme

A neo-brutalist freelance-illustrator portfolio, built from the **WEBFIXUS Site** Claude Design.
Elementor-ready: **Home, Work, About, Contact** are auto-created as **editable Elementor pages**
when you activate the theme.

## Install (Hostinger / any WordPress)
1. **Plugins → Add New** → install & activate **Elementor** (free) if it isn't already.
2. **Appearance → Themes → Add New → Upload Theme** → choose `webfixus-theme.zip` → **Install** → **Activate**.
3. On activation the theme automatically:
   - creates the 4 pages (`home`, `work`, `about`, `contact`) with their Elementor content,
   - sets **Home** as the static front page,
   - builds a **WEBFIXUS Menu** and assigns it to the Primary location.
4. **Settings → Permalinks** → choose **Post name** (so in-content links like `/work/` resolve), then Save.

That's it — visit the site. To tweak content, open any page with **Edit with Elementor**.

### Updating / re-installing
If you already installed an earlier build, just upload this zip again (**Replace current** when prompted)
and load any **wp-admin** page once. The theme detects the new version and **refreshes the 4 pages'
Elementor content automatically** — no need to delete pages. (This refresh only runs on a version change,
so your own edits are never overwritten during normal use.) The pages use Elementor's classic
**Section/Column** structure, so they open in the editor whether or not the Flexbox Container feature is on.

## Editing
- Headlines, paragraphs and buttons are real Elementor widgets (Heading / Text / Button) — edit inline.
- Repeating/decorative blocks (stats strip, project tiles, cards, logos, the contact form) are **HTML
  widgets** — double-click to edit their markup. Each project tile keeps its `data-cat` for filtering.
- The neo-brutalist look (borders, hard shadows, fonts) lives in `assets/css/webfixus.css`. Edit there to
  retune the whole system. Fonts (Archivo, Space Grotesk, Space Mono) load from Google Fonts automatically.
- Header & footer are part of the theme (`header.php` / `footer.php`) so they're global. With Elementor
  **Pro** you could rebuild them in Theme Builder instead.

## Contact form
Posts to WordPress' `admin-post.php` (no extra plugin). On submit it emails the site admin
(`Settings → General → Administration Email`) and returns to the Contact page with a green
**"GOT IT!"** confirmation. A hidden honeypot field blocks basic spam. If your host doesn't send mail,
install any free SMTP plugin, or wire the form to Contact Form 7 / WPForms if you already use one.

## Swapping in real artwork
Project tiles are color-block placeholders by design. To use real images, edit a tile's HTML widget and
replace `<div class="wfx-proj__img …">[ ILLUSTRATION ]</div>` with an `<img>` (or insert an Elementor
Image widget). The portrait on About works the same way.

## QA checklist after activating
- [ ] Home is the front page; menu shows Home / Work / About / Contact (Contact is the blue button).
- [ ] Fonts render (heavy Archivo headlines), 3px borders + hard offset shadows visible.
- [ ] Stats strip spans full width with 4 colored cells.
- [ ] Services / Selected Work / Process grids align (4-col and 3-col).
- [ ] Work page: clicking a chip filters the grid with no reload; "ALL" shows everything.
- [ ] Contact: submitting shows the green success card; "SEND ANOTHER" returns to the form.
- [ ] Header is sticky and highlights the current page.
- [ ] Mobile: grids collapse, hero type scales, nav wraps.

## Notes
- Requires Elementor (free) to render/edit the page bodies; an admin notice reminds you if it's inactive.
- Activating replaces only the front-end theme — your existing posts/pages stay in the admin untouched.
