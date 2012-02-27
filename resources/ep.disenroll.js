/**
 * JavaScript for the Education Program MediaWiki extension.
 * @see https://www.mediawiki.org/wiki/Extension:Education_Program
 *
 * @licence GNU GPL v3 or later
 * @author Jeroen De Dauw <jeroendedauw at gmail dot com>
 */

(function( $, mw ) {

	$( document ).ready( function() {

		$( '.ep-disenroll-cancel, .ep-disenroll' ).button();

		$( '.ep-disenroll-cancel' ).click( function( event ) {
			window.location = $( this ).attr( 'target-url' );
			event.preventDefault();
		} );

	} );

})( window.jQuery, window.mediaWiki );