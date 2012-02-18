/**
 * JavasSript for the Education Program MediaWiki extension.
 * @see https://www.mediawiki.org/wiki/Extension:Education_Program
 *
 * @licence GNU GPL v3 or later
 * @author Jeroen De Dauw <jeroendedauw at gmail dot com>
 */

(function( $, ep ) {
	
	function addReviewer() {
		$dialog = $( '<div>' ).html( '' ).dialog( {
			'title': ep.msg( 'ep-articletable-addreviwer-title' ),
			'minWidth': 550,
			'buttons': [
				{
					'text': ep.msg( 'ep-articletable-addreviwer-button' ),
					'id': 'ep-addreviwer-button',
					'click': function() {
						alert( 'submit' );
						// TODO
					}
				},
				{
					'text': ep.msg( 'ep-articletable-addreviwer-cancel' ),
					'id': 'ep-addreviwer-cancel',
					'click': function() {
						$dialog.dialog( 'close' );
					}
				}
			]
		} );
	}
	
	function addArticle() {
		// TODO
	}
	
	function removeStudent() {
		// TODO
	}
	
	function removeArticle() {
		// TODO
	}
	
	function removeReviewer() {
		// TODO
	}
	
	$( document ).ready( function() {

		$( '.ep-rem-reviewer-self, .ep-become-reviewer' ).removeAttr( 'disabled' );

		$( '.ep-become-reviewer' ).click( addReviewer );
		
		$( '.ep-rem-reviewer-self' ).click( removeReviewer );
		
		// TODO
	} );

})( window.jQuery, mw.educationProgram );