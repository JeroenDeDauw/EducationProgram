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
			var courseName = $( '#newname' ).val();
			courseName = courseName.charAt( 0 ).toUpperCase() + courseName.slice( 1 );

			$( this ).attr(
				'action',
				$( this ).attr( 'action' ).replace(
					'NAME_PLACEHOLDER',
					$( '#neworg option:selected' ).text() + '/' + courseName + ' (' + $( '#newterm' ).val() + ')'
				)
			);
		} );

		var list = [ 'neworg', 'newname', 'newterm' ],
			i, $element, val;

		for ( i in list ) {
			if ( list.hasOwnProperty( i ) ) {
				$element = $( '#' + list[i] );
				val = $element.val();

				$element.removeAttr( 'value' );
				$element.val( val );
			}
		}

		$( '.ep-course-add' ).removeAttr( 'disabled' );

	} );

})( window.jQuery );
