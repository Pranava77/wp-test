/* ==========================================================================
   WP Gallery — a tiny, dependency-free LIGHTBOX.
   --------------------------------------------------------------------------
   WHAT: intercepts clicks on any tile marked ".wg-lb", and shows the full-size
         image in the overlay (.wg-lightbox) that the shortcode printed.
   WHY:  a gallery feels broken without click-to-enlarge, and pulling in a big
         third-party library for a single overlay would be overkill.
   HOW:  one delegated click listener on the document handles every grid on the
         page, so it also works for tiles added later (e.g. by AJAX).
   ========================================================================== */
( function () {
	'use strict';

	// Find (or bail if there is no) overlay on the page.
	function overlay() {
		return document.querySelector( '.wg-lightbox' );
	}

	function open( fullSrc, caption ) {
		var box = overlay();
		if ( ! box ) {
			return;
		}
		var img = box.querySelector( '.wg-lb-img' );
		var cap = box.querySelector( '.wg-lb-caption' );

		img.setAttribute( 'src', fullSrc );
		img.setAttribute( 'alt', caption || '' );
		cap.textContent = caption || '';

		box.hidden = false;
		box.setAttribute( 'aria-hidden', 'false' );
		// Stop the page behind the overlay from scrolling.
		document.documentElement.style.overflow = 'hidden';
	}

	function close() {
		var box = overlay();
		if ( ! box ) {
			return;
		}
		box.hidden = true;
		box.setAttribute( 'aria-hidden', 'true' );
		// Clear the src so a heavy image isn't kept in memory while hidden.
		var img = box.querySelector( '.wg-lb-img' );
		if ( img ) {
			img.setAttribute( 'src', '' );
		}
		document.documentElement.style.overflow = '';
	}

	// Delegated click handling for the whole document.
	document.addEventListener( 'click', function ( e ) {
		// 1) A tile that opts into the lightbox.
		var tile = e.target.closest ? e.target.closest( '.wg-lb' ) : null;
		if ( tile ) {
			e.preventDefault();
			open(
				tile.getAttribute( 'data-wg-full' ) || tile.getAttribute( 'href' ),
				tile.getAttribute( 'data-wg-caption' ) || ''
			);
			return;
		}

		// 2) The close button, or a click on the dim backdrop (but not the image).
		if ( e.target.closest( '.wg-lb-close' ) ) {
			close();
			return;
		}
		var box = overlay();
		if ( box && ! box.hidden && e.target === box ) {
			close();
		}
	} );

	// Escape key closes the overlay.
	document.addEventListener( 'keydown', function ( e ) {
		if ( 'Escape' === e.key || 'Esc' === e.key ) {
			var box = overlay();
			if ( box && ! box.hidden ) {
				close();
			}
		}
	} );

	/* ======================================================================
	   CATEGORY FILTER BAR (only present when the shortcode used filter="yes").
	   Clicking a button shows only tiles whose data-cats includes that slug.
	   ====================================================================== */
	document.addEventListener( 'click', function ( e ) {
		var btn = e.target.closest ? e.target.closest( '.wg-filter-btn' ) : null;
		if ( ! btn ) {
			return;
		}

		// The bar and the grid that immediately follows it.
		var bar = btn.closest( '.wg-filter' );
		if ( ! bar ) {
			return;
		}
		var grid = bar.nextElementSibling;
		while ( grid && ! grid.classList.contains( 'wg-grid' ) ) {
			grid = grid.nextElementSibling;
		}
		if ( ! grid ) {
			return;
		}

		var filter = btn.getAttribute( 'data-filter' );

		// Toggle the active button.
		bar.querySelectorAll( '.wg-filter-btn' ).forEach( function ( b ) {
			b.classList.toggle( 'is-active', b === btn );
		} );

		// Show/hide tiles. "*" means show everything.
		grid.querySelectorAll( '.wg-tile' ).forEach( function ( tile ) {
			var cats = ( tile.getAttribute( 'data-cats' ) || '' ).split( /\s+/ );
			var show = ( '*' === filter ) || cats.indexOf( filter ) !== -1;
			tile.style.display = show ? '' : 'none';
		} );
	} );
}() );
