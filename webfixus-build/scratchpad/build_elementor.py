#!/usr/bin/env python3
"""
WEBFIXUS — generate Elementor page data from the Claude Design prototype.

This build emits NATIVE Elementor widgets (Heading / Text Editor / Button) laid
out with the CLASSIC Section -> Column model (Flexbox Containers are OFF on the
user's site), so every piece is individually editable in the Elementor UI.

Only two things stay as raw HTML widgets, by necessity:
  * the contact form (Elementor Free has no Form widget) — it submits via mailto;
  * the Work-page filter chips (interactive) — a tiny script toggles the native
    project-card columns by CSS class.

Output:
  webfixus/inc/pages/<slug>.json           -> _elementor_data array (theme scaffold)
  webfixus/templates/elementor/<slug>.json -> Elementor "Import Template" export
"""

import json, os, hashlib

HERE = os.path.dirname(os.path.abspath(__file__))
THEME = os.path.normpath(os.path.join(HERE, "..", "webfixus"))
PAGES_DIR = os.path.join(THEME, "inc", "pages")
TPL_DIR = os.path.join(THEME, "templates", "elementor")

INK = "#14110c"
CREAM = "#f3ede1"

_counter = {"n": 0}
def eid():
    _counter["n"] += 1
    return hashlib.md5(f"wfx-{_counter['n']}".encode()).hexdigest()[:7]

# --------------------------------------------------------------------------
# low-level builders
# --------------------------------------------------------------------------
def sz(v, unit="px"):
    return {"unit": unit, "size": v, "sizes": []}

def pad(t, r, b, l, linked=False):
    return {"unit": "px", "top": str(t), "right": str(r), "bottom": str(b),
            "left": str(l), "isLinked": linked}

def border_w(t, r, b, l):
    return {"unit": "px", "top": str(t), "right": str(r), "bottom": str(b),
            "left": str(l), "isLinked": False}

def typo(prefix, family=None, weight=None, size=None, line=None, ls_em=None,
         size_mobile=None, transform=None):
    s = {f"{prefix}_typography": "custom"}
    if family:
        s[f"{prefix}_font_family"] = family
    if weight:
        s[f"{prefix}_font_weight"] = str(weight)
    if size:
        s[f"{prefix}_font_size"] = sz(size)
    if size_mobile:
        s[f"{prefix}_font_size_mobile"] = sz(size_mobile)
    if line is not None:
        s[f"{prefix}_line_height"] = sz(line, "em")
    if ls_em is not None:
        s[f"{prefix}_letter_spacing"] = sz(round(ls_em * (size or 16), 2))
    if transform:
        s[f"{prefix}_text_transform"] = transform
    return s

def widget(wtype, settings):
    return {"id": eid(), "elType": "widget", "widgetType": wtype, "settings": settings}

def column(inline_size, elements, *, bg=None, border=None, shadow=None,
           padding=None, css=None, valign=None, col_size=None):
    s = {"_column_size": col_size if col_size is not None else 100,
         "_inline_size": inline_size}
    if bg:
        s["background_background"] = "classic"
        s["background_color"] = bg
    if border:
        t, r, b, l, color = border
        s["border_border"] = "solid"
        s["border_width"] = border_w(t, r, b, l)
        s["border_color"] = color
    if shadow:
        color, h, v = shadow
        s["box_shadow_box_shadow_type"] = "yes"
        s["box_shadow_box_shadow"] = {"horizontal": h, "vertical": v, "blur": 0,
                                      "spread": 0, "color": color}
    if padding:
        s["padding"] = padding
    if valign:
        s["content_position"] = valign
    if css:
        s["_css_classes"] = css
    return {"id": eid(), "elType": "column", "settings": s, "elements": elements}

def section(columns, *, boxed=True, bg=None, padding=None, gap="default",
            border=None, shadow=None, css=None, inner=False, content_pos=None):
    s = {"gap": gap}
    if boxed:
        s["layout"] = "boxed"
        s["content_width"] = sz(1120)
    else:
        s["layout"] = "full_width"
    if bg:
        s["background_background"] = "classic"
        s["background_color"] = bg
    if padding:
        s["padding"] = padding
    if border:
        t, r, b, l, color = border
        s["border_border"] = "solid"
        s["border_width"] = border_w(t, r, b, l)
        s["border_color"] = color
    if shadow:
        scolor, h, v = shadow
        s["box_shadow_box_shadow_type"] = "yes"
        s["box_shadow_box_shadow"] = {"horizontal": h, "vertical": v, "blur": 0,
                                      "spread": 0, "color": scolor}
    if content_pos:
        s["content_position"] = content_pos
    if css:
        s["_css_classes"] = css
    return {"id": eid(), "elType": "section", "settings": s,
            "elements": columns, "isInner": inner}

# --------------------------------------------------------------------------
# styled widget helpers
# --------------------------------------------------------------------------
def heading(title, *, tag="h2", color=INK, family="Archivo", weight="900",
            size=40, line=None, ls=None, align=None, size_mobile=None,
            margin=None, bg=None, padding=None, border=None, transform=None,
            css=None):
    s = {"title": title, "header_size": tag, "title_color": color}
    s.update(typo("typography", family, weight, size, line, ls, size_mobile, transform))
    if align:
        s["align"] = align
    if margin:
        s["_margin"] = margin
    if bg:
        s["_background_background"] = "classic"
        s["_background_color"] = bg
    if padding:
        s["_padding"] = padding
    if border:
        t, r, b, l, bcolor = border
        s["_border_border"] = "solid"
        s["_border_width"] = border_w(t, r, b, l)
        s["_border_color"] = bcolor
    if css:
        s["_css_classes"] = css
    return widget("heading", s)

def textw(html, *, color="#3a352c", family="Space Grotesk", weight="400",
          size=16, line=1.5, ls=None, align=None, margin=None, transform=None):
    if not html.strip().startswith("<"):
        html = f"<p>{html}</p>"
    s = {"editor": html, "text_color": color, "align": align or "left"}
    s.update(typo("typography", family, weight, size, line, ls, transform=transform))
    if margin:
        s["_margin"] = margin
    return widget("text-editor", s)

def button(text, url="", *, text_color="#fff", bg="#2b4cff", border_color=INK,
           bw=2, shadow=(INK, 5, 5), padding=(15, 24, 15, 24), family="Space Grotesk",
           weight="700", size=16, ls=None, align=None, transform=None, css=None):
    s = {
        "text": text,
        "link": {"url": url, "is_external": "", "nofollow": "", "custom_attributes": ""},
        "button_text_color": text_color,
        "background_color": bg,
        "border_border": "solid",
        "border_width": border_w(bw, bw, bw, bw),
        "border_color": border_color,
        "text_padding": pad(*padding),
    }
    if shadow:
        color, h, v = shadow
        s["button_box_shadow_box_shadow_type"] = "yes"
        s["button_box_shadow_box_shadow"] = {"horizontal": h, "vertical": v,
                                             "blur": 0, "spread": 0, "color": color}
    s.update(typo("typography", family, weight, size, None, ls, transform=transform))
    if align:
        s["align"] = align
    if css:
        s["_css_classes"] = css
    return widget("button", s)

def kicker(text):
    """The mono '/ SECTION' label."""
    return textw(text, color="#6b6557", family="Space Mono", weight="700",
                 size=13, line=1.4, ls=0.14, margin=pad(0, 0, 0, 0))

PROJECTS = [
    ("The Atlantic",   "Climate cover feature",  "Editorial", "#ffd23f", "#14110c"),
    ("Netflix",        "Animated key art",       "Character", "#ff5a36", "#ffffff"),
    ("Patagonia",      "Brand illustration kit", "Brand",     "#2b4cff", "#ffffff"),
    ("Mailchimp",      "Campaign mascots",       "Character", "#16a06a", "#ffffff"),
    ("Adult Swim",     "Title sequence frames",  "Character", "#ff5a36", "#ffffff"),
    ("The New Yorker", "Spot illustrations",     "Editorial", "#ffd23f", "#14110c"),
    ("Wynwood Walls",  "40ft exterior mural",    "Murals",    "#2b4cff", "#ffffff"),
    ("Nike",           "Sneaker drop graphics",  "Brand",     "#ff5a36", "#ffffff"),
    ("Penguin",        "Classics cover series",  "Editorial", "#16a06a", "#ffffff"),
    ("SXSW",           "Main stage backdrop",    "Murals",    "#ffd23f", "#14110c"),
]

def project_card(client, title, cat, color, ink, *, width=33.333, show_title=False, css=None):
    """A native card: plain outer column > inner section styled as the card."""
    image = heading("[ ILLUSTRATION ]", tag="div", color=ink, family="Space Mono",
                    weight="400", size=11, ls=0.08, align="center",
                    bg=color, padding=pad(86, 0, 86, 0),
                    border=(0, 0, 3, 0, INK), margin=pad(0, 0, 0, 0))
    meta_text = f"{cat.upper()}"
    if show_title:
        meta_text = f"{cat.upper()} · {title}"
    inner_col = column(100, [
        image,
        heading(client, tag="h3", color=INK, family="Archivo", weight="800",
                size=18, align="left", margin=pad(0, 0, 0, 0),
                padding=pad(13, 14, 4, 14)),
        textw(meta_text, color="#6b6557", family="Space Mono", weight="400",
              size=13, line=1.3, margin=pad(0, 0, 0, 0)),
    ], padding=pad(0, 0, 12, 0))
    card = section([inner_col], boxed=False, bg="#ffffff",
                   border=(3, 3, 3, 3, INK), shadow=(INK, 6, 6), gap="no", inner=True)
    return column(width, [card], css=css, col_size=int(round(width)))

# ==========================================================================
# HOME
# ==========================================================================
def home():
    secs = []

    # hero
    hero_btns = section([
        column(50, [button("SEE MY WORK →", "/work/", bg="#2b4cff",
                           text_color="#ffffff")], col_size=50),
        column(50, [button("START A PROJECT", "/contact/", bg="#ffffff",
                           text_color=INK)], col_size=50),
    ], boxed=False, gap="narrow", inner=True)
    secs.append(section([column(100, [
        button("★ FREELANCE ILLUSTRATOR — OPEN FOR WORK", "", bg="#ffd23f",
               text_color=INK, border_color=INK, shadow=(INK, 3, 3),
               padding=(7, 12, 7, 12), family="Space Mono", size=12, ls=0.06),
        heading("ILLUSTRATION WITH A PULSE.", tag="h1", size=65, line=0.92,
                ls=-0.035, size_mobile=40, margin=pad(26, 0, 0, 0)),
        textw("I'm WEBFIXUS — I make hand-drawn editorial, character &amp; brand "
              "art that turns scrollers into starers. Big ideas, bold lines, fast "
              "turnarounds.", size=19, margin=pad(24, 0, 0, 0)),
        hero_btns,
    ])], padding=pad(72, 32, 56, 32)))

    # stats strip (full-bleed border, 4 colored columns)
    stat_data = [("120+ PROJECTS", "#ff5a36", "#fff", True),
                 ("40 CLIENTS", "#2b4cff", "#fff", True),
                 ("7 YEARS", "#16a06a", "#fff", True),
                 ("2-DAY REPLIES", "#ffd23f", INK, False)]
    stat_cols = []
    for label, bg, fg, rb in stat_data:
        stat_cols.append(column(25, [
            heading(label, tag="div", color=fg, family="Space Mono", weight="700",
                    size=15, ls=0.04, align="center", margin=pad(0, 0, 0, 0))
        ], bg=bg, padding=pad(18, 6, 18, 6),
           border=(0, 3, 0, 0, INK) if rb else None, col_size=25))
    secs.append(section(stat_cols, gap="no", border=(3, 0, 3, 0, INK)))

    # services
    svc = [("01", "#2b4cff", "Editorial", "Covers, op-eds &amp; feature spreads with a point of view."),
           ("02", "#ff5a36", "Characters", "Original casts &amp; mascots for IP, games and brands."),
           ("03", "#ffb300", "Brand Art", "Illustration systems that scale across every touchpoint."),
           ("04", "#16a06a", "Murals", "Big walls, festival stages &amp; spaces people walk into.")]
    svc_cols = []
    for num, col_c, title, desc in svc:
        inner = section([column(100, [
            textw(num, color=col_c, family="Space Mono", weight="700", size=13,
                  margin=pad(0, 0, 0, 0)),
            heading(title, tag="h3", family="Archivo", weight="800", size=22,
                    margin=pad(10, 0, 0, 0)),
            textw(desc, size=14, line=1.45, margin=pad(8, 0, 0, 0)),
        ], padding=pad(22, 22, 22, 22))], boxed=False, bg="#ffffff",
           border=(3, 3, 3, 3, INK), shadow=(INK, 6, 6), gap="no", inner=True)
        svc_cols.append(column(25, [inner], col_size=25))
    secs.append(section([column(100, [
        kicker("/ WHAT I MAKE"),
        heading("FOUR THINGS, DONE LOUD.", tag="h2", size=40, ls=-0.02,
                margin=pad(10, 0, 0, 0)),
    ])], padding=pad(64, 32, 24, 32)))
    secs.append(section(svc_cols, gap="wide", padding=pad(0, 32, 24, 32)))

    # selected work
    cards = [project_card(*p) for p in PROJECTS[:6]]
    secs.append(section([column(100, [
        kicker("/ SELECTED WORK"),
        heading("RECENT FAVORITES.", tag="h2", size=40, ls=-0.02, margin=pad(10, 0, 4, 0)),
        button("VIEW ALL WORK →", "/work/", bg="", text_color=INK, border_color="",
               bw=0, shadow=None, padding=(2, 0, 4, 0), family="Space Mono",
               weight="700", size=13, ls=0.04),
    ])], padding=pad(48, 32, 16, 32)))
    secs.append(section(cards, gap="wide", padding=pad(0, 32, 24, 32)))

    # testimonial
    secs.append(section([column(100, [
        textw("/ WORD ON THE STREET", color="#c3cfff", family="Space Mono",
              weight="700", size=13, ls=0.14, margin=pad(0, 0, 0, 0)),
        heading('"WEBFIXUS gave our brand a face. Sales decks, packaging, socials — '
                'all of it finally looks like us, and nothing like our competitors."',
                tag="div", color="#ffffff", family="Archivo", weight="800", size=30,
                line=1.22, margin=pad(18, 0, 0, 0)),
        textw("— HEAD OF BRAND, PATAGONIA", color="#cdd6ff", family="Space Mono",
              size=13, ls=0.06, margin=pad(22, 0, 0, 0)),
    ], bg="#2b4cff", border=(3, 3, 3, 3, INK), shadow=(INK, 8, 8),
       padding=pad(48, 44, 48, 44))], padding=pad(48, 32, 48, 32)))

    # CTA
    secs.append(section([column(100, [
        heading("GOT SOMETHING TO DRAW?", tag="h2", color=CREAM, size=54, line=0.96,
                ls=-0.025, align="center", margin=pad(0, 0, 14, 0)),
        textw("Tell me about it. I reply within a day.", color="#bdb6a6", size=17,
              align="center", margin=pad(0, 0, 24, 0)),
        button("HELLO@WEBFIXUS.STUDIO →", "mailto:hello@webfixus.studio",
               bg="#ffd23f", text_color=INK, border_color=CREAM,
               shadow=("#2b4cff", 5, 5), padding=(16, 28, 16, 28), size=17,
               align="center"),
    ], bg=INK, padding=pad(64, 32, 64, 32))], padding=pad(0, 32, 72, 32)))

    return secs

# ==========================================================================
# WORK
# ==========================================================================
def work():
    secs = []

    # intro + chips (chips kept as an HTML widget so the filter script can drive
    # the native card columns by class)
    cats = ["all", "Editorial", "Character", "Brand", "Murals"]
    chips_html = '<div class="wfx-chips" style="display:flex;flex-wrap:wrap;gap:12px">'
    for c in cats:
        active = c == "all"
        label = "ALL" if c == "all" else c.upper()
        bg = INK if active else "#fff"
        fg = "#fff" if active else INK
        shadow = "3px 3px 0 #2b4cff" if active else "3px 3px 0 #14110c"
        chips_html += (f'<span class="wfx-chip" data-filter="{c}" style="cursor:pointer;'
                       f"font-family:'Space Mono',monospace;font-size:13px;font-weight:700;"
                       f'letter-spacing:.06em;padding:9px 16px;border:2px solid #14110c;'
                       f'background:{bg};color:{fg};box-shadow:{shadow}">{label}</span>')
    chips_html += '</div>'
    chips_html += r'''
<script>
(function(){
  var chips=document.querySelectorAll('.wfx-chip');
  function apply(f){
    var cls='wfxcat-'+f.toLowerCase();
    document.querySelectorAll('.wfx-card').forEach(function(c){
      c.style.display=(f==='all'||c.classList.contains(cls))?'':'none';
    });
    chips.forEach(function(ch){
      var on=ch.getAttribute('data-filter')===f;
      ch.style.background=on?'#14110c':'#fff';
      ch.style.color=on?'#fff':'#14110c';
      ch.style.boxShadow=on?'3px 3px 0 #2b4cff':'3px 3px 0 #14110c';
    });
  }
  chips.forEach(function(ch){ ch.addEventListener('click',function(){apply(ch.getAttribute('data-filter'));}); });
})();
</script>'''

    secs.append(section([column(100, [
        kicker("/ PORTFOLIO — 2019 → 2026"),
        heading("THE WORK.", tag="h1", size=72, line=0.92, ls=-0.03,
                size_mobile=44, margin=pad(14, 0, 0, 0)),
        textw("A decade of editorial covers, characters, brand systems and murals. "
              "Filter by what you need.", size=18, margin=pad(18, 0, 26, 0)),
        widget("html", {"html": chips_html}),
    ])], padding=pad(56, 32, 20, 32)))

    # grid — native cards, each outer column tagged for the filter
    cards = []
    for client, title, cat, color, ink in PROJECTS:
        # the outer column carries the filter classes (wfx-card + wfxcat-<cat>)
        c = project_card(client, title, cat, color, ink, show_title=True,
                         css=f"wfx-card wfxcat-{cat.lower()}")
        cards.append(c)
    secs.append(section(cards, gap="wide", padding=pad(8, 32, 72, 32)))

    # CTA
    secs.append(section([column(100, [
        heading("WANT YOUR PROJECT IN THIS GRID?", tag="h2", color="#ffffff",
                size=42, line=0.98, ls=-0.02, align="center", margin=pad(0, 0, 18, 0)),
        button("LET'S TALK →", "/contact/", bg=INK, text_color="#ffffff",
               border_color="#ffffff", shadow=("#ffd23f", 5, 5),
               padding=(15, 26, 15, 26), align="center"),
    ], bg="#ff5a36", border=(3, 3, 3, 3, INK), shadow=(INK, 8, 8),
       padding=pad(48, 32, 48, 32))], padding=pad(0, 32, 72, 32)))

    return secs

# ==========================================================================
# ABOUT
# ==========================================================================
def about():
    secs = []

    # intro split
    portrait = column(42, [heading("[ PORTRAIT / SELF-PORTRAIT ]", tag="div",
                      color=INK, family="Space Mono", weight="400", size=12,
                      align="center", bg="#ffd23f", padding=pad(120, 20, 120, 20),
                      margin=pad(0, 0, 0, 0))],
                      border=(3, 3, 3, 3, INK), shadow=(INK, 8, 8), col_size=42)
    intro = column(58, [
        kicker("/ ABOUT"),
        heading("HI, I DRAW FOR A LIVING.", tag="h1", size=64, line=0.94, ls=-0.03,
                size_mobile=42, margin=pad(14, 0, 0, 0)),
        textw("WEBFIXUS is the one-person illustration studio of an artist who's "
              "been making pictures for editorial, games and brands since 2019. No "
              "account managers, no hand-offs — you work directly with the person "
              "holding the pen.", size=18, line=1.55, margin=pad(22, 0, 0, 0)),
        textw("I believe illustration should have an opinion. Every brief gets a "
              "real idea before it gets a single line, and every line is drawn by "
              "hand before it's cleaned up in pixels.", size=18, line=1.55,
              margin=pad(16, 0, 0, 0)),
    ], col_size=58)
    secs.append(section([intro, portrait], gap="wide", padding=pad(56, 32, 40, 32),
                        content_pos="center"))

    # how I work
    steps = [("01", "#2b4cff", "Brief", "We talk. I learn the goal, the audience and the deadline."),
             ("02", "#ff5a36", "Sketch", "2–3 rough directions. We pick one before any polish."),
             ("03", "#16a06a", "Draw", "Final art, hand-drawn, with one round of revisions."),
             ("04", "#ffb300", "Deliver", "Every format you need, source files, full rights.")]
    step_cols = []
    for num, col_c, title, desc in steps:
        inner = section([column(100, [
            heading(num, tag="div", color=col_c, family="Archivo", weight="900",
                    size=34, margin=pad(0, 0, 0, 0)),
            heading(title, tag="h3", family="Space Grotesk", weight="700", size=17,
                    margin=pad(8, 0, 0, 0)),
            textw(desc, size=14, line=1.45, margin=pad(6, 0, 0, 0)),
        ], padding=pad(22, 22, 22, 22))], boxed=False, bg="#ffffff",
           border=(3, 3, 3, 3, INK), shadow=(INK, 6, 6), gap="no", inner=True)
        step_cols.append(column(25, [inner], col_size=25))
    secs.append(section([column(100, [
        kicker("/ HOW I WORK"),
        heading("FOUR STEPS, NO SURPRISES.", tag="h2", size=40, ls=-0.02,
                margin=pad(10, 0, 0, 0)),
    ])], padding=pad(32, 32, 26, 32)))
    secs.append(section(step_cols, gap="wide", padding=pad(0, 32, 32, 32)))

    # trusted by
    brands = ["THE ATLANTIC", "NETFLIX", "PATAGONIA", "MAILCHIMP", "ADULT SWIM",
              "THE NEW YORKER", "NIKE", "PENGUIN"]
    brand_html = '<div style="display:flex;flex-wrap:wrap;gap:14px">'
    for b in brands:
        brand_html += (f"<span style=\"font-family:'Archivo',sans-serif;font-weight:800;"
                       f'font-size:18px;border:2px solid #14110c;padding:10px 16px;'
                       f'background:#fff">{b}</span>')
    brand_html += '</div>'
    secs.append(section([column(100, [
        kicker("/ TRUSTED BY"),
        textw(brand_html, margin=pad(16, 0, 0, 0)),
    ])], padding=pad(32, 32, 24, 32)))

    # info cards
    info = [("#16a06a", "#bdf3d8", "TOOLS", "Ink, Procreate, Photoshop, Risograph"),
            ("#2b4cff", "#cdd6ff", "RECOGNITION", "AOI Award · ADC Merit · It's Nice That feature"),
            ("#ff5a36", "#ffd6cc", "BASED IN", "Brooklyn, NY — working worldwide")]
    info_cols = []
    for bg, lab, label, val in info:
        inner = section([column(100, [
            textw(label, color=lab, family="Space Mono", weight="400", size=12,
                  ls=0.1, margin=pad(0, 0, 0, 0)),
            heading(val, tag="div", color="#ffffff", family="Archivo", weight="800",
                    size=20, line=1.25, margin=pad(8, 0, 0, 0)),
        ], padding=pad(24, 24, 24, 24))], boxed=False, bg=bg,
           border=(3, 3, 3, 3, INK), shadow=(INK, 6, 6), gap="no", inner=True)
        info_cols.append(column(33.333, [inner], col_size=33))
    secs.append(section(info_cols, gap="wide", padding=pad(32, 32, 72, 32)))

    return secs

# ==========================================================================
# CONTACT  (left column native; right column = HTML form -> mailto)
# ==========================================================================
def contact():
    form_html = r'''
<div id="wfx-sent" style="display:none;background:#16a06a;color:#fff;border:3px solid #14110c;box-shadow:8px 8px 0 #14110c;padding:48px 32px;box-sizing:border-box;flex-direction:column;justify-content:center">
  <div style="font-family:'Archivo',sans-serif;font-weight:900;font-size:40px;line-height:1;letter-spacing:-.02em">GOT IT! ✊</div>
  <p style="font-size:17px;line-height:1.5;margin:18px 0 0;color:#dffaec">Thanks for reaching out. I'll get back to you within one business day — usually sooner.</p>
  <span id="wfx-again" style="cursor:pointer;margin-top:26px;display:inline-block;background:#14110c;color:#fff;font-family:'Space Mono',monospace;font-size:13px;font-weight:700;letter-spacing:.06em;padding:12px 18px;border:2px solid #fff;width:fit-content">SEND ANOTHER →</span>
</div>
<form id="wfx-form" style="background:#fff;border:3px solid #14110c;box-shadow:8px 8px 0 #14110c;padding:30px">
  <label style="display:block;font-family:'Space Mono',monospace;font-size:12px;font-weight:700;letter-spacing:.08em;margin-bottom:8px">NAME</label>
  <input name="name" required placeholder="Your name" style="width:100%;box-sizing:border-box;font-family:'Space Grotesk',sans-serif;font-size:15px;padding:13px 14px;border:2px solid #14110c;background:#f3ede1;outline:none;margin-bottom:18px" />
  <label style="display:block;font-family:'Space Mono',monospace;font-size:12px;font-weight:700;letter-spacing:.08em;margin-bottom:8px">EMAIL</label>
  <input name="email" type="email" required placeholder="you@company.com" style="width:100%;box-sizing:border-box;font-family:'Space Grotesk',sans-serif;font-size:15px;padding:13px 14px;border:2px solid #14110c;background:#f3ede1;outline:none;margin-bottom:18px" />
  <label style="display:block;font-family:'Space Mono',monospace;font-size:12px;font-weight:700;letter-spacing:.08em;margin-bottom:8px">PROJECT TYPE</label>
  <select name="ptype" style="width:100%;box-sizing:border-box;font-family:'Space Grotesk',sans-serif;font-size:15px;padding:13px 14px;border:2px solid #14110c;background:#f3ede1;outline:none;margin-bottom:18px">
    <option>Editorial illustration</option><option>Character design</option><option>Brand art / system</option><option>Mural</option><option>Something else</option>
  </select>
  <label style="display:block;font-family:'Space Mono',monospace;font-size:12px;font-weight:700;letter-spacing:.08em;margin-bottom:8px">THE BRIEF</label>
  <textarea name="brief" rows="4" placeholder="What are we making, and when do you need it?" style="width:100%;box-sizing:border-box;font-family:'Space Grotesk',sans-serif;font-size:15px;padding:13px 14px;border:2px solid #14110c;background:#f3ede1;outline:none;resize:vertical;margin-bottom:22px"></textarea>
  <button type="submit" style="cursor:pointer;display:block;width:100%;text-align:center;background:#2b4cff;color:#fff;font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:16px;padding:15px;border:2px solid #14110c;box-shadow:5px 5px 0 #14110c">SEND THE BRIEF →</button>
</form>
<script>
(function(){
  var form=document.getElementById('wfx-form'),sent=document.getElementById('wfx-sent'),again=document.getElementById('wfx-again');
  if(!form)return;
  form.addEventListener('submit',function(e){
    e.preventDefault();var f=form;
    var subject='New brief: '+(f.ptype.value||'Project')+' — '+(f.name.value||'');
    var body='Name: '+f.name.value+'\nEmail: '+f.email.value+'\nProject type: '+f.ptype.value+'\n\n'+f.brief.value;
    window.location.href='mailto:hello@webfixus.studio?subject='+encodeURIComponent(subject)+'&body='+encodeURIComponent(body);
    form.style.display='none';sent.style.display='flex';
  });
  if(again){again.addEventListener('click',function(){sent.style.display='none';form.style.display='block';form.reset();});}
})();
</script>'''.strip()

    left = column(50, [
        kicker("/ CONTACT"),
        heading("LET'S MAKE SOMETHING.", tag="h1", size=66, line=0.92, ls=-0.03,
                size_mobile=44, margin=pad(14, 0, 0, 0)),
        textw("Tell me what you're building. I take on a handful of projects a "
              "month and reply within a day.", size=18, line=1.55, margin=pad(22, 0, 28, 0)),
        # email card (button -> mailto, styled white)
        button("hello@webfixus.studio", "mailto:hello@webfixus.studio", bg="#ffffff",
               text_color=INK, shadow=(INK, 5, 5), padding=(18, 20, 18, 20),
               family="Archivo", weight="800", size=22, align="left"),
        # availability card
        section([column(100, [
            textw("AVAILABILITY", color="#7a6a1f", family="Space Mono", weight="400",
                  size=12, ls=0.1, margin=pad(0, 0, 0, 0)),
            heading("Booking from Q3 2026", tag="div", family="Archivo", weight="800",
                    size=22, margin=pad(4, 0, 0, 0)),
        ], padding=pad(18, 20, 18, 20))], boxed=False, bg="#ffd23f",
           border=(3, 3, 3, 3, INK), shadow=(INK, 5, 5), gap="no", inner=True),
        textw('<span style="border-bottom:3px solid #2b4cff;padding-bottom:2px">INSTAGRAM</span> &nbsp; '
              '<span style="border-bottom:3px solid #ff5a36;padding-bottom:2px">BEHANCE</span> &nbsp; '
              '<span style="border-bottom:3px solid #16a06a;padding-bottom:2px">DRIBBBLE</span>',
              family="Space Mono", weight="700", size=13, ls=0.06, color=INK,
              margin=pad(16, 0, 0, 0)),
    ], col_size=50)

    right = column(50, [widget("html", {"html": form_html})], col_size=50)

    return [section([left, right], gap="wide", padding=pad(56, 32, 72, 32))]

# ==========================================================================
PAGE_BUILDERS = {"home": ("Home", home), "work": ("Work", work),
                 "about": ("About", about), "contact": ("Contact", contact)}

def main():
    os.makedirs(PAGES_DIR, exist_ok=True)
    os.makedirs(TPL_DIR, exist_ok=True)
    for slug, (title, fn) in PAGE_BUILDERS.items():
        _counter["n"] = 0
        secs = fn()
        with open(os.path.join(PAGES_DIR, f"{slug}.json"), "w") as f:
            json.dump(secs, f, ensure_ascii=False, indent=1)
        export = {"version": "0.4", "title": title, "type": "page",
                  "content": secs, "page_settings": []}
        with open(os.path.join(TPL_DIR, f"{slug}.json"), "w") as f:
            json.dump(export, f, ensure_ascii=False, indent=1)
        # count native widgets
        n = json.dumps(secs).count('"widgetType"')
        print(f"  {slug}: {len(secs)} sections, {n} widgets")

if __name__ == "__main__":
    main()
    print("done.")
