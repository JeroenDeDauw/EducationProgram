/**
 * JavaScript for the Education Program MediaWiki extension.
 * @see https://www.mediawiki.org/wiki/Extension:Education_Program
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw <jeroendedauw at gmail dot com>
 */

(function( $, mw ) {

	$( '#bodyContent' ).find( '[type="submit"]' ).button();

	$( document ).ready( function() {
		window.onbeforeunload = null;
	} );

})( window.jQuery, window.mediaWiki );