/* WEBFIXUS — front-end interactions
   - Work page: category filter chips
   - Contact: success-state toggle (?sent=1) + reset
   - Active nav highlight (fallback when no WP menu is set)
   - Optional reduced-motion-safe scroll reveal
*/
(function () {
  'use strict';

  function ready(fn) {
    if (document.readyState !== 'loading') fn();
    else document.addEventListener('DOMContentLoaded', fn);
  }

  /* ---------- Work: filter chips ---------- */
  function initFilter() {
    var chipWrap = document.querySelector('.wfx-chips');
    if (!chipWrap) return;
    var chips = chipWrap.querySelectorAll('.wfx-chip');
    var cards = document.querySelectorAll('.wfx-proj[data-cat]');
    if (!chips.length || !cards.length) return;

    function apply(cat) {
      cards.forEach(function (card) {
        var match = cat === 'all' || card.getAttribute('data-cat') === cat;
        // Tiles are plain grid-item divs inside one HTML widget.
        card.classList.toggle('wfx-hide', !match);
      });
    }

    chips.forEach(function (chip) {
      chip.addEventListener('click', function () {
        chips.forEach(function (c) { c.classList.remove('is-active'); });
        chip.classList.add('is-active');
        apply(chip.getAttribute('data-cat') || 'all');
      });
    });
  }

  /* ---------- Contact: success-state reset ----------
     The success card is shown server-side via body.wfx-sent (?sent=1), so it
     works without JS. Here we only wire "SEND ANOTHER" to reset cleanly. */
  function initContact() {
    var success = document.querySelector('.wfx-success');
    if (!success) return;
    var again = success.querySelector('.wfx-success__btn');
    if (!again) return;
    again.addEventListener('click', function (e) {
      e.preventDefault();
      if (history.replaceState) {
        history.replaceState({}, '', window.location.pathname);
      }
      document.body.classList.remove('wfx-sent');
    });
  }

  /* ---------- Active nav (fallback, when no WP menu assigned) ---------- */
  function initNav() {
    var links = document.querySelectorAll('.wfx-nav a[href]');
    if (!links.length) return;
    var here = window.location.pathname.replace(/\/+$/, '') || '/';
    links.forEach(function (a) {
      try {
        var path = new URL(a.href, window.location.origin).pathname.replace(/\/+$/, '') || '/';
        if (path === here && !a.classList.contains('wfx-nav-cta')) a.classList.add('is-active');
      } catch (e) {}
    });
  }

  /* ---------- Optional scroll reveal ---------- */
  function initReveal() {
    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    var targets = document.querySelectorAll('.wfx-reveal');
    if (!targets.length || !('IntersectionObserver' in window)) return;
    document.body.classList.add('wfx-anim');
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (en.isIntersecting) { en.target.classList.add('is-in'); io.unobserve(en.target); }
      });
    }, { threshold: 0.12 });
    targets.forEach(function (t) { io.observe(t); });
  }

  ready(function () {
    initFilter();
    initContact();
    initNav();
    initReveal();
  });
})();
