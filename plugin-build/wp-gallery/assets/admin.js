/* ==========================================================================
   WP Gallery — admin multi-image picker.
   --------------------------------------------------------------------------
   WHAT: lets you pick MANY images from the WordPress Media Library for a single
         Gallery Item, preview them, drag to reorder, and remove.
   HOW:  opens the native wp.media modal (multiple: true), then keeps a hidden
         input in sync with a comma-separated list of attachment IDs. The list
         order always follows the on-screen thumbnail order.
   ========================================================================== */
( function ( $ ) {
	'use strict';

	$( function () {
		$( '.wg-images-field' ).each( function () {
			var $wrap    = $( this );
			var $input   = $wrap.find( '.wg-images-ids' );
			var $preview = $wrap.find( '.wg-images-preview' );
			var frame;

			// Rebuild the hidden input from the current thumbnail order.
			function sync() {
				var ids = [];
				$preview.find( '.wg-thumb' ).each( function () {
					ids.push( String( $( this ).data( 'id' ) ) );
				} );
				$input.val( ids.join( ',' ) );
			}

			// Drag-to-reorder (jQuery UI sortable ships with wp-admin).
			if ( $preview.sortable ) {
				$preview.sortable( { update: sync } );
			}

			// Open the media modal.
			$wrap.on( 'click', '.wg-images-add', function ( e ) {
				e.preventDefault();

				if ( frame ) {
					frame.open();
					return;
				}

				frame = wp.media( {
					title:    'Select gallery images',
					button:   { text: 'Use these images' },
					multiple: true,
					library:  { type: 'image' }
				} );

				frame.on( 'select', function () {
					// Existing IDs so we don't add duplicates.
					var have = $input.val() ? $input.val().split( ',' ).filter( Boolean ) : [];

					frame.state().get( 'selection' ).each( function ( att ) {
						att = att.toJSON();
						var id = String( att.id );
						if ( have.indexOf( id ) !== -1 ) {
							return; // already added
						}
						have.push( id );

						var url = ( att.sizes && att.sizes.thumbnail )
							? att.sizes.thumbnail.url
							: att.url;

						$preview.append(
							'<li class="wg-thumb" data-id="' + id + '">' +
								'<img src="' + url + '" alt="" />' +
								'<button type="button" class="wg-thumb-remove" aria-label="Remove">&times;</button>' +
							'</li>'
						);
					} );

					sync();
				} );

				frame.open();
			} );

			// Remove a single thumbnail.
			$preview.on( 'click', '.wg-thumb-remove', function ( e ) {
				e.preventDefault();
				$( this ).closest( '.wg-thumb' ).remove();
				sync();
			} );

			// Remove all.
			$wrap.on( 'click', '.wg-images-clear', function ( e ) {
				e.preventDefault();
				$preview.empty();
				sync();
			} );
		} );
	} );
}( jQuery ) );
