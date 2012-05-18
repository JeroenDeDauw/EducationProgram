/**
 * JavaScript for the Education Program MediaWiki extension.
 * @see https://www.mediawiki.org/wiki/Extension:Education_Program
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw <jeroendedauw at gmail dot com>
 */

(function( $ ) {

	$( document ).ready( function() {

		$( '.ep-datepicker-tr' ).find( 'input' ).datepicker( {
			dateFormat: 'yy-mm-dd',
			showOn: 'focus',
			changeMonth: true,
			changeYear: true,
			showButtonPanel: true
		} );

	} );

})( window.jQuery );