#!/usr/bin/env python3
"""
WEBFIXUS — generate Elementor page data from the Claude Design prototype.

Output (two deliverables, same content):
  webfixus/inc/pages/<slug>.json          -> _elementor_data array, consumed by
                                             inc/scaffold.php on theme activation.
  webfixus/templates/elementor/<slug>.json -> Elementor "Import Template" export.

Constraint (from project memory): the user's Elementor has Flexbox Containers
OFF, so the JSON uses the CLASSIC Section -> Column -> Widget model ONLY.
Every block is one full-width Section > one 100% Column > one HTML widget that
carries the prototype's inline styles (plus wfx-* class hooks for responsiveness).
"""

import json, os, hashlib

HERE = os.path.dirname(os.path.abspath(__file__))
THEME = os.path.normpath(os.path.join(HERE, "..", "webfixus"))
PAGES_DIR = os.path.join(THEME, "inc", "pages")
TPL_DIR = os.path.join(THEME, "templates", "elementor")

# Deterministic 7-char hex ids so rebuilds produce stable diffs.
_counter = {"n": 0}
def eid():
    _counter["n"] += 1
    return hashlib.md5(f"wfx-{_counter['n']}".encode()).hexdigest()[:7]

def section(html):
    """Wrap a block of HTML in classic Section > Column > HTML-widget."""
    return {
        "id": eid(),
        "elType": "section",
        "settings": {
            "layout": "full_width",
            "gap": "no",
            "padding": {"unit": "px", "top": "0", "right": "0",
                        "bottom": "0", "left": "0", "isLinked": True},
        },
        "elements": [{
            "id": eid(),
            "elType": "column",
            "settings": {"_column_size": 100, "_inline_size": None},
            "elements": [{
                "id": eid(),
                "elType": "widget",
                "widgetType": "html",
                "settings": {"html": html},
            }],
        }],
        "isInner": False,
    }

# --------------------------------------------------------------------------
# shared style snippets (kept identical to the prototype)
# --------------------------------------------------------------------------
INK = "#14110c"

def tag_style():
    return ("font-family:'Space Mono',monospace;font-size:10px;font-weight:700;"
            "letter-spacing:.06em;text-transform:uppercase;background:#14110c;"
            "color:#fff;padding:3px 7px")

def block_style(color, ink, ratio):
    return (f"aspect-ratio:{ratio};background:{color};color:{ink};opacity:.96;"
            "display:flex;align-items:center;justify-content:center;"
            "font-family:'Space Mono',monospace;font-size:11px;letter-spacing:.08em")

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

# ==========================================================================
# HOME
# ==========================================================================
def home():
    blocks = []

    # hero
    blocks.append(r'''
<section class="wfx-wrap wfx-pad" style="max-width:1120px;margin:0 auto;padding:72px 32px 64px">
  <span style="display:inline-block;background:#ffd23f;border:2px solid #14110c;font-family:'Space Mono',monospace;font-size:12px;font-weight:700;letter-spacing:.06em;padding:7px 12px;box-shadow:3px 3px 0 #14110c">★ FREELANCE ILLUSTRATOR — OPEN FOR WORK</span>
  <h1 class="wfx-h1-lg" style="font-family:'Archivo',sans-serif;font-weight:900;font-size:65px;line-height:.92;letter-spacing:-.035em;margin:26px 0 0;max-width:920px">ILLUSTRATION WITH A <span style="color:#2b4cff">PULSE.</span></h1>
  <p style="font-size:19px;line-height:1.5;color:#3a352c;margin:24px 0 0;max-width:560px">I'm WEBFIXUS — I make hand-drawn editorial, character &amp; brand art that turns scrollers into starers. Big ideas, bold lines, fast turnarounds.</p>
  <div style="display:flex;gap:14px;margin-top:34px;flex-wrap:wrap">
    <a href="/work/" class="wfx-a" style="background:#2b4cff;color:#fff;font-weight:700;font-size:16px;padding:15px 24px;border:2px solid #14110c;box-shadow:5px 5px 0 #14110c">SEE MY WORK →</a>
    <a href="/contact/" class="wfx-a" style="background:#fff;color:#14110c;font-weight:700;font-size:16px;padding:15px 24px;border:2px solid #14110c;box-shadow:5px 5px 0 #14110c">START A PROJECT</a>
  </div>
</section>'''.strip())

    # stats strip
    blocks.append(r'''
<section style="border-top:3px solid #14110c;border-bottom:3px solid #14110c">
  <div class="wfx-stats" style="max-width:1120px;margin:0 auto;display:flex;font-family:'Space Mono',monospace;font-weight:700;letter-spacing:.04em">
    <span style="flex:1;text-align:center;padding:18px 6px;background:#ff5a36;color:#fff;border-right:3px solid #14110c;font-size:15px">120+ PROJECTS</span>
    <span style="flex:1;text-align:center;padding:18px 6px;background:#2b4cff;color:#fff;border-right:3px solid #14110c;font-size:15px">40 CLIENTS</span>
    <span style="flex:1;text-align:center;padding:18px 6px;background:#16a06a;color:#fff;border-right:3px solid #14110c;font-size:15px">7 YEARS</span>
    <span style="flex:1;text-align:center;padding:18px 6px;background:#ffd23f;color:#14110c;font-size:15px">2-DAY REPLIES</span>
  </div>
</section>'''.strip())

    # services
    cards = ""
    svc = [("01","#2b4cff","Editorial","Covers, op-eds &amp; feature spreads with a point of view."),
           ("02","#ff5a36","Characters","Original casts &amp; mascots for IP, games and brands."),
           ("03","#ffb300","Brand Art","Illustration systems that scale across every touchpoint."),
           ("04","#16a06a","Murals","Big walls, festival stages &amp; spaces people walk into.")]
    for num, col, title, desc in svc:
        cards += (f'<div style="background:#fff;border:3px solid #14110c;box-shadow:6px 6px 0 #14110c;padding:22px">'
                  f'<div style="font-family:\'Space Mono\',monospace;font-size:13px;color:{col};font-weight:700">{num}</div>'
                  f'<div style="font-family:\'Archivo\',sans-serif;font-weight:800;font-size:22px;margin-top:10px">{title}</div>'
                  f'<div style="font-size:14px;color:#3a352c;margin-top:8px;line-height:1.45">{desc}</div></div>')
    blocks.append(f'''
<section class="wfx-wrap wfx-pad" style="max-width:1120px;margin:0 auto;padding:72px 32px 24px">
  <div style="font-family:'Space Mono',monospace;font-size:13px;letter-spacing:.14em;color:#6b6557">/ WHAT I MAKE</div>
  <h2 class="wfx-h2" style="font-family:'Archivo',sans-serif;font-weight:900;font-size:40px;letter-spacing:-.02em;margin:10px 0 32px">FOUR THINGS, DONE LOUD.</h2>
  <div class="wfx-grid wfx-cols-4" style="gap:20px">{cards}</div>
</section>'''.strip())

    # selected work (first 6)
    grid = ""
    for client, title, cat, color, ink in PROJECTS[:6]:
        grid += (f'<a href="/work/" class="wfx-a" style="display:block;border:3px solid #14110c;box-shadow:6px 6px 0 #14110c">'
                 f'<div style="{block_style(color, ink, "4 / 3")}">[ ILLUSTRATION ]</div>'
                 f'<div style="padding:12px 14px;background:#fff;display:flex;justify-content:space-between;align-items:center;border-top:3px solid #14110c">'
                 f'<span style="font-weight:700;font-size:15px">{client}</span>'
                 f'<span style="{tag_style()}">{cat}</span></div></a>')
    blocks.append(f'''
<section class="wfx-wrap wfx-pad" style="max-width:1120px;margin:0 auto;padding:56px 32px 24px">
  <div style="display:flex;justify-content:space-between;align-items:flex-end;margin-bottom:28px;flex-wrap:wrap;gap:14px">
    <div>
      <div style="font-family:'Space Mono',monospace;font-size:13px;letter-spacing:.14em;color:#6b6557">/ SELECTED WORK</div>
      <h2 class="wfx-h2" style="font-family:'Archivo',sans-serif;font-weight:900;font-size:40px;letter-spacing:-.02em;margin:10px 0 0">RECENT FAVORITES.</h2>
    </div>
    <a href="/work/" class="wfx-a" style="font-family:'Space Mono',monospace;font-size:13px;font-weight:700;letter-spacing:.04em;border-bottom:3px solid #2b4cff;padding-bottom:2px">VIEW ALL WORK →</a>
  </div>
  <div class="wfx-grid wfx-cols-3" style="gap:20px">{grid}</div>
</section>'''.strip())

    # testimonial
    blocks.append(r'''
<section class="wfx-wrap wfx-pad" style="max-width:1120px;margin:0 auto;padding:56px 32px">
  <div style="background:#2b4cff;color:#fff;border:3px solid #14110c;box-shadow:8px 8px 0 #14110c;padding:48px 44px">
    <div style="font-family:'Space Mono',monospace;font-size:13px;letter-spacing:.14em;color:#c3cfff">/ WORD ON THE STREET</div>
    <p style="font-family:'Archivo',sans-serif;font-weight:800;font-size:30px;line-height:1.22;margin:18px 0 0;max-width:820px">"WEBFIXUS gave our brand a face. Sales decks, packaging, socials — all of it finally looks like us, and nothing like our competitors."</p>
    <div style="font-family:'Space Mono',monospace;font-size:13px;margin-top:22px;letter-spacing:.06em;color:#cdd6ff">— HEAD OF BRAND, PATAGONIA</div>
  </div>
</section>'''.strip())

    # CTA
    blocks.append(r'''
<section class="wfx-wrap wfx-pad" style="max-width:1120px;margin:0 auto;padding:0 32px 80px">
  <div style="background:#14110c;color:#f3ede1;border:3px solid #14110c;padding:64px 32px;text-align:center">
    <h2 class="wfx-h2-xl" style="font-family:'Archivo',sans-serif;font-weight:900;font-size:54px;line-height:.96;letter-spacing:-.025em;margin:0 0 14px">GOT SOMETHING TO DRAW?</h2>
    <p style="font-size:17px;color:#bdb6a6;margin:0 0 28px">Tell me about it. I reply within a day.</p>
    <a href="mailto:hello@webfixus.studio" class="wfx-a" style="display:inline-block;background:#ffd23f;color:#14110c;font-weight:700;font-size:17px;padding:16px 28px;border:2px solid #f3ede1;box-shadow:5px 5px 0 #2b4cff">HELLO@WEBFIXUS.STUDIO →</a>
  </div>
</section>'''.strip())

    return blocks

# ==========================================================================
# WORK  (filter chips + grid, made interactive with a tiny inline script)
# ==========================================================================
def work():
    blocks = []
    cats = ["all", "Editorial", "Character", "Brand", "Murals"]
    chips = ""
    for c in cats:
        active = c == "all"
        label = "ALL" if c == "all" else c.upper()
        bg = "#14110c" if active else "#fff"
        fg = "#fff" if active else "#14110c"
        shadow = "3px 3px 0 #2b4cff" if active else "3px 3px 0 #14110c"
        chips += (f'<span class="wfx-chip" data-filter="{c}" '
                  f'style="cursor:pointer;font-family:\'Space Mono\',monospace;font-size:13px;font-weight:700;'
                  f'letter-spacing:.06em;padding:9px 16px;border:2px solid #14110c;background:{bg};color:{fg};'
                  f'box-shadow:{shadow}">{label}</span>')

    blocks.append(f'''
<section class="wfx-wrap wfx-pad" style="max-width:1120px;margin:0 auto;padding:64px 32px 28px">
  <div style="font-family:'Space Mono',monospace;font-size:13px;letter-spacing:.14em;color:#6b6557">/ PORTFOLIO — 2019 → 2026</div>
  <h1 class="wfx-h1" style="font-family:'Archivo',sans-serif;font-weight:900;font-size:72px;line-height:.92;letter-spacing:-.03em;margin:14px 0 0">THE WORK.</h1>
  <p style="font-size:18px;line-height:1.5;color:#3a352c;margin:18px 0 0;max-width:560px">A decade of editorial covers, characters, brand systems and murals. Filter by what you need.</p>
  <div class="wfx-chips" style="display:flex;flex-wrap:wrap;gap:12px;margin-top:30px">{chips}</div>
</section>'''.strip())

    # grid (all projects, each carries data-cat)
    grid = ""
    for client, title, cat, color, ink in PROJECTS:
        grid += (f'<div class="wfx-card" data-cat="{cat}" style="border:3px solid #14110c;box-shadow:6px 6px 0 #14110c;cursor:pointer">'
                 f'<div style="{block_style(color, ink, "1 / 1")}">[ ILLUSTRATION ]</div>'
                 f'<div style="padding:14px;background:#fff;border-top:3px solid #14110c">'
                 f'<div style="display:flex;justify-content:space-between;align-items:center">'
                 f'<span style="font-family:\'Archivo\',sans-serif;font-weight:800;font-size:18px">{client}</span>'
                 f'<span style="{tag_style()}">{cat}</span></div>'
                 f'<div style="font-size:13px;color:#6b6557;margin-top:6px;font-family:\'Space Mono\',monospace">{title}</div>'
                 f'</div></div>')

    grid_js = r'''
<script>
(function(){
  var root = document.currentScript.closest('section');
  if(!root) return;
  var chipWrap = root.previousElementSibling ? null : null;
  // chips live in the previous section; find them in the document scope
  var chips = document.querySelectorAll('.wfx-chip');
  var cards = root.querySelectorAll('.wfx-card');
  function apply(f){
    cards.forEach(function(c){
      c.style.display = (f === 'all' || c.getAttribute('data-cat') === f) ? '' : 'none';
    });
    chips.forEach(function(ch){
      var on = ch.getAttribute('data-filter') === f;
      ch.style.background = on ? '#14110c' : '#fff';
      ch.style.color = on ? '#fff' : '#14110c';
      ch.style.boxShadow = on ? '3px 3px 0 #2b4cff' : '3px 3px 0 #14110c';
    });
  }
  chips.forEach(function(ch){
    ch.addEventListener('click', function(){ apply(ch.getAttribute('data-filter')); });
  });
})();
</script>'''
    blocks.append(f'''
<section class="wfx-wrap wfx-pad" style="max-width:1120px;margin:0 auto;padding:8px 32px 80px">
  <div class="wfx-grid wfx-cols-3" style="gap:22px">{grid}</div>{grid_js}
</section>'''.strip())

    # CTA
    blocks.append(r'''
<section class="wfx-wrap wfx-pad" style="max-width:1120px;margin:0 auto;padding:0 32px 80px">
  <div style="background:#ff5a36;color:#fff;border:3px solid #14110c;box-shadow:8px 8px 0 #14110c;padding:48px 32px;text-align:center">
    <h2 class="wfx-h2" style="font-family:'Archivo',sans-serif;font-weight:900;font-size:42px;line-height:.98;letter-spacing:-.02em;margin:0 0 18px">WANT YOUR PROJECT IN THIS GRID?</h2>
    <a href="/contact/" class="wfx-a" style="display:inline-block;background:#14110c;color:#fff;font-weight:700;font-size:16px;padding:15px 26px;border:2px solid #fff;box-shadow:5px 5px 0 #ffd23f">LET'S TALK →</a>
  </div>
</section>'''.strip())

    return blocks

# ==========================================================================
# ABOUT
# ==========================================================================
def about():
    blocks = []

    blocks.append(r'''
<section class="wfx-wrap wfx-pad wfx-split" style="max-width:1120px;margin:0 auto;padding:64px 32px 40px;display:grid;grid-template-columns:1.3fr 1fr;gap:48px;align-items:center">
  <div>
    <div style="font-family:'Space Mono',monospace;font-size:13px;letter-spacing:.14em;color:#6b6557">/ ABOUT</div>
    <h1 class="wfx-h1" style="font-family:'Archivo',sans-serif;font-weight:900;font-size:64px;line-height:.94;letter-spacing:-.03em;margin:14px 0 0">HI, I DRAW FOR A LIVING.</h1>
    <p style="font-size:18px;line-height:1.55;color:#3a352c;margin:22px 0 0">WEBFIXUS is the one-person illustration studio of an artist who's been making pictures for editorial, games and brands since 2019. No account managers, no hand-offs — you work directly with the person holding the pen.</p>
    <p style="font-size:18px;line-height:1.55;color:#3a352c;margin:16px 0 0">I believe illustration should have an opinion. Every brief gets a real idea before it gets a single line, and every line is drawn by hand before it's cleaned up in pixels.</p>
  </div>
  <div style="aspect-ratio:4/5;background:#ffd23f;border:3px solid #14110c;box-shadow:8px 8px 0 #14110c;display:flex;align-items:center;justify-content:center;font-family:'Space Mono',monospace;font-size:12px;color:#14110c">[ PORTRAIT / SELF-PORTRAIT ]</div>
</section>'''.strip())

    steps = [("01","#2b4cff","Brief","We talk. I learn the goal, the audience and the deadline."),
             ("02","#ff5a36","Sketch","2–3 rough directions. We pick one before any polish."),
             ("03","#16a06a","Draw","Final art, hand-drawn, with one round of revisions."),
             ("04","#ffb300","Deliver","Every format you need, source files, full rights.")]
    cards = ""
    for num, col, title, desc in steps:
        cards += (f'<div style="background:#fff;border:3px solid #14110c;box-shadow:6px 6px 0 #14110c;padding:22px">'
                  f'<div style="font-family:\'Archivo\',sans-serif;font-weight:900;font-size:34px;color:{col}">{num}</div>'
                  f'<div style="font-weight:700;font-size:17px;margin-top:8px">{title}</div>'
                  f'<div style="font-size:14px;color:#3a352c;margin-top:6px;line-height:1.45">{desc}</div></div>')
    blocks.append(f'''
<section class="wfx-wrap wfx-pad" style="max-width:1120px;margin:0 auto;padding:40px 32px">
  <div style="font-family:'Space Mono',monospace;font-size:13px;letter-spacing:.14em;color:#6b6557">/ HOW I WORK</div>
  <h2 class="wfx-h2" style="font-family:'Archivo',sans-serif;font-weight:900;font-size:40px;letter-spacing:-.02em;margin:10px 0 30px">FOUR STEPS, NO SURPRISES.</h2>
  <div class="wfx-grid wfx-cols-4" style="gap:20px">{cards}</div>
</section>'''.strip())

    brands = ["THE ATLANTIC","NETFLIX","PATAGONIA","MAILCHIMP","ADULT SWIM","THE NEW YORKER","NIKE","PENGUIN"]
    chips = "".join(
        f'<span style="font-family:\'Archivo\',sans-serif;font-weight:800;font-size:18px;border:2px solid #14110c;padding:10px 16px;background:#fff">{b}</span>'
        for b in brands)
    blocks.append(f'''
<section class="wfx-wrap wfx-pad" style="max-width:1120px;margin:0 auto;padding:40px 32px 24px">
  <div style="font-family:'Space Mono',monospace;font-size:13px;letter-spacing:.14em;color:#6b6557">/ TRUSTED BY</div>
  <div style="display:flex;flex-wrap:wrap;gap:14px;margin-top:18px">{chips}</div>
</section>'''.strip())

    info = [("#16a06a","#bdf3d8","TOOLS","Ink, Procreate, Photoshop, Risograph"),
            ("#2b4cff","#cdd6ff","RECOGNITION","AOI Award · ADC Merit · It's Nice That feature"),
            ("#ff5a36","#ffd6cc","BASED IN","Brooklyn, NY — working worldwide")]
    cards = ""
    for bg, lab, label, val in info:
        cards += (f'<div style="background:{bg};color:#fff;border:3px solid #14110c;box-shadow:6px 6px 0 #14110c;padding:24px">'
                  f'<div style="font-family:\'Space Mono\',monospace;font-size:12px;letter-spacing:.1em;color:{lab}">{label}</div>'
                  f'<div style="font-family:\'Archivo\',sans-serif;font-weight:800;font-size:20px;margin-top:8px;line-height:1.25">{val}</div></div>')
    blocks.append(f'''
<section class="wfx-wrap wfx-pad" style="max-width:1120px;margin:0 auto;padding:40px 32px 80px">
  <div class="wfx-grid wfx-cols-3" style="gap:20px">{cards}</div>
</section>'''.strip())

    return blocks

# ==========================================================================
# CONTACT  (real form -> mailto on submit, then success panel)
# ==========================================================================
def contact():
    form = r'''
<section class="wfx-wrap wfx-pad wfx-split" style="max-width:1120px;margin:0 auto;padding:64px 32px 80px;display:grid;grid-template-columns:1fr 1fr;gap:48px">
  <div>
    <div style="font-family:'Space Mono',monospace;font-size:13px;letter-spacing:.14em;color:#6b6557">/ CONTACT</div>
    <h1 class="wfx-h1" style="font-family:'Archivo',sans-serif;font-weight:900;font-size:66px;line-height:.92;letter-spacing:-.03em;margin:14px 0 0">LET'S MAKE<br>SOMETHING.</h1>
    <p style="font-size:18px;line-height:1.55;color:#3a352c;margin:22px 0 30px;max-width:440px">Tell me what you're building. I take on a handful of projects a month and reply within a day.</p>
    <div style="display:flex;flex-direction:column;gap:16px">
      <a href="mailto:hello@webfixus.studio" class="wfx-a" style="display:block;background:#fff;border:3px solid #14110c;box-shadow:5px 5px 0 #14110c;padding:18px 20px"><div style="font-family:'Space Mono',monospace;font-size:12px;letter-spacing:.1em;color:#6b6557">EMAIL</div><div style="font-family:'Archivo',sans-serif;font-weight:800;font-size:22px;margin-top:4px">hello@webfixus.studio</div></a>
      <div style="background:#ffd23f;border:3px solid #14110c;box-shadow:5px 5px 0 #14110c;padding:18px 20px"><div style="font-family:'Space Mono',monospace;font-size:12px;letter-spacing:.1em;color:#7a6a1f">AVAILABILITY</div><div style="font-family:'Archivo',sans-serif;font-weight:800;font-size:22px;margin-top:4px">Booking from Q3 2026</div></div>
      <div style="display:flex;gap:14px;font-family:'Space Mono',monospace;font-size:13px;font-weight:700;letter-spacing:.06em">
        <span style="border-bottom:3px solid #2b4cff;padding-bottom:2px;cursor:pointer">INSTAGRAM</span>
        <span style="border-bottom:3px solid #ff5a36;padding-bottom:2px;cursor:pointer">BEHANCE</span>
        <span style="border-bottom:3px solid #16a06a;padding-bottom:2px;cursor:pointer">DRIBBBLE</span>
      </div>
    </div>
  </div>

  <div>
    <div id="wfx-sent" style="display:none;background:#16a06a;color:#fff;border:3px solid #14110c;box-shadow:8px 8px 0 #14110c;padding:48px 32px;height:100%;box-sizing:border-box;flex-direction:column;justify-content:center">
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
        <option>Editorial illustration</option>
        <option>Character design</option>
        <option>Brand art / system</option>
        <option>Mural</option>
        <option>Something else</option>
      </select>
      <label style="display:block;font-family:'Space Mono',monospace;font-size:12px;font-weight:700;letter-spacing:.08em;margin-bottom:8px">THE BRIEF</label>
      <textarea name="brief" rows="4" placeholder="What are we making, and when do you need it?" style="width:100%;box-sizing:border-box;font-family:'Space Grotesk',sans-serif;font-size:15px;padding:13px 14px;border:2px solid #14110c;background:#f3ede1;outline:none;resize:vertical;margin-bottom:22px"></textarea>
      <button type="submit" style="cursor:pointer;display:block;width:100%;text-align:center;background:#2b4cff;color:#fff;font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:16px;padding:15px;border:2px solid #14110c;box-shadow:5px 5px 0 #14110c">SEND THE BRIEF →</button>
    </form>
  </div>
</section>
<script>
(function(){
  var form = document.getElementById('wfx-form');
  var sent = document.getElementById('wfx-sent');
  var again = document.getElementById('wfx-again');
  if(!form) return;
  form.addEventListener('submit', function(e){
    e.preventDefault();
    var f = form;
    var subject = 'New brief: ' + (f.ptype.value || 'Project') + ' — ' + (f.name.value || '');
    var body = 'Name: ' + f.name.value + '\n'
             + 'Email: ' + f.email.value + '\n'
             + 'Project type: ' + f.ptype.value + '\n\n'
             + f.brief.value;
    window.location.href = 'mailto:hello@webfixus.studio'
      + '?subject=' + encodeURIComponent(subject)
      + '&body=' + encodeURIComponent(body);
    form.style.display = 'none';
    sent.style.display = 'flex';
  });
  if(again){
    again.addEventListener('click', function(){
      sent.style.display = 'none';
      form.style.display = 'block';
      form.reset();
    });
  }
})();
</script>'''.strip()
    return [form]

# ==========================================================================
PAGE_BUILDERS = {
    "home":    ("Home",    home),
    "work":    ("Work",    work),
    "about":   ("About",   about),
    "contact": ("Contact", contact),
}

def main():
    os.makedirs(PAGES_DIR, exist_ok=True)
    os.makedirs(TPL_DIR, exist_ok=True)
    for slug, (title, fn) in PAGE_BUILDERS.items():
        _counter["n"] = 0  # stable ids per page
        sections = [section(b) for b in fn()]

        # 1) raw _elementor_data array for the theme scaffold
        with open(os.path.join(PAGES_DIR, f"{slug}.json"), "w") as f:
            json.dump(sections, f, ensure_ascii=False, indent=1)

        # 2) Elementor "Import Template" export
        export = {"version": "0.4", "title": title, "type": "page",
                  "content": sections, "page_settings": []}
        with open(os.path.join(TPL_DIR, f"{slug}.json"), "w") as f:
            json.dump(export, f, ensure_ascii=False, indent=1)

        print(f"  {slug}: {len(sections)} sections")

if __name__ == "__main__":
    main()
    print("done.")
