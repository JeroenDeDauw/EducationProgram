/**
 * JavaScript for the Education Program MediaWiki extension.
 * @see https://www.mediawiki.org/wiki/Extension:Education_Program
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw <jeroendedauw at gmail dot com>
 */

(function( $ ) {

	$( document ).ready( function() {

		$( '.ep-course-add' ).closest( 'form' ).submit( function() {
			$( this ).attr(
				'action',
				$( this ).attr( 'action' ).replace(
					'NAME_PLACEHOLDER',
					$( '#neworg' ).text() + '/' + $( '#newname' ).val() + ' (' + $( '#newterm' ).val() + ')'
				)
			);
		} );

		var list = [ 'neworg', 'newname', 'newterm' ];

		for ( var i in list ) {
			if ( list.hasOwnProperty( i ) ) {
				var $element = $( '#' + list[i] ),
				val = $element.val();

				$element.removeAttr( 'value' );
				$element.val( val );
			}
		}

		$( '.ep-course-add' ).removeAttr( 'disabled' );

	} );

})( window.jQuery );