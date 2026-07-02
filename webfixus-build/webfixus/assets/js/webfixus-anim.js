/* ============================================================
   WEBFIXUS — scroll reveal + sticky-header shadow.
   ------------------------------------------------------------
   SAFETY BY DESIGN: this script HIDES sections (by adding the
   .wfx-reveal class) and then reveals them as they scroll in.
   Because the hidden state is added by JS, if this file never
   runs the page shows everything normally — it can't go blank.
   Extra guards: skipped inside the Elementor editor, skipped for
   reduced-motion, and a 3s safety net force-reveals anything the
   observer somehow misses.
   ============================================================ */
( function () {
	'use strict';

	var d = document;
	var root = d.documentElement;

	// Give the sticky header a hard shadow once the page is scrolled.
	function setupHeader() {
		if ( ! d.querySelector( '.wfx-header' ) ) {
			return;
		}
		var onScroll = function () {
			root.classList.toggle( 'wfx-scrolled', window.pageYOffset > 8 );
		};
		onScroll();
		window.addEventListener( 'scroll', onScroll, { passive: true } );
	}

	function setupReveal() {
		// Never touch the Elementor editor canvas.
		if ( d.body && d.body.classList.contains( 'elementor-editor-active' ) ) {
			return;
		}

		// Respect reduced-motion: leave everything visible, don't hide anything.
		if ( window.matchMedia &&
			window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
			return;
		}

		// No IntersectionObserver (very old browser): don't hide anything.
		if ( ! ( 'IntersectionObserver' in window ) ) {
			return;
		}

		var els = d.querySelectorAll( '.wfx-page .elementor-top-section' );
		if ( ! els.length ) {
			els = d.querySelectorAll( '.wfx-page .elementor-section' );
		}
		if ( ! els.length ) {
			return;
		}

		// Hide now (add the class), then reveal each as it enters the viewport.
		var i;
		for ( i = 0; i < els.length; i++ ) {
			els[ i ].classList.add( 'wfx-reveal' );
		}

		var io = new IntersectionObserver( function ( entries ) {
			entries.forEach( function ( entry ) {
				if ( entry.isIntersecting ) {
					entry.target.classList.add( 'is-inview' );
					io.unobserve( entry.target );
				}
			} );
		}, { rootMargin: '0px 0px -8% 0px', threshold: 0.06 } );

		for ( i = 0; i < els.length; i++ ) {
			io.observe( els[ i ] );
		}

		// Safety net: whatever is still hidden after 3s gets revealed, so the
		// page can never stay blank if the observer misbehaves.
		setTimeout( function () {
			for ( var k = 0; k < els.length; k++ ) {
				els[ k ].classList.add( 'is-inview' );
			}
		}, 3000 );
	}

	function init() {
		setupReveal();
		setupHeader();
	}

	if ( 'loading' !== d.readyState ) {
		init();
	} else {
		d.addEventListener( 'DOMContentLoaded', init );
	}
}() );
