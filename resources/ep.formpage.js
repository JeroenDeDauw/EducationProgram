/**
 * JavaScript for the Education Program MediaWiki extension.
 * @see https://www.mediawiki.org/wiki/Extension:Education_Program
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw <jeroendedauw at gmail dot com>
 */

(function( $, mw ) {

	$( document ).ready( function() {

		window.onbeforeunload = null;

		$( '#bodyContent' ).find( '[type="submit"]' ).button();

		$( '.ep-cancel' ).button().click( function( event ) {
			window.location = $( this ).attr( 'data-target-url' );
			event.preventDefault();
		} );

	} );

})( window.jQuery, window.mediaWiki );