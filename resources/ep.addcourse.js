/**
 * JavasSript for the Education Program MediaWiki extension.
 * @see https://www.mediawiki.org/wiki/Extension:Education_Program
 *
 * @licence GNU GPL v3 or later
 * @author Jeroen De Dauw <jeroendedauw at gmail dot com>
 */

(function( $, mw ) {
	
	$( document ).ready( function() {

		$( '.ep-course-add' ).closest( 'form' ).submit( function() {
			var action = $( this ).attr( 'action' );
			$( this ).attr(
				'action', 
				action.substring( 0, action.length - 4 ) + $( '#newname' ).val() + ' (' + $( '#newterm' ).val() + ')'
			);
		} );
		
		$( '.ep-course-add' ).removeAttr( 'disabled' );

	} );

})( window.jQuery );