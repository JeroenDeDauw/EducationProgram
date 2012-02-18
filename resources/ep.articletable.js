/**
 * JavasSript for the Education Program MediaWiki extension.
 * @see https://www.mediawiki.org/wiki/Extension:Education_Program
 *
 * @licence GNU GPL v3 or later
 * @author Jeroen De Dauw <jeroendedauw at gmail dot com>
 */

(function( $, ep ) {
	
	function addReviewer() {
		var $this = $( this );

		var $form = $( '<form>' ).attr( {
			'method': 'post',
			'action': window.location
		} ).msg(
			'ep-articletable-addreviwer-text',
			mw.user.name,
			$( '<b>' ).text( $this.attr( 'data-article-name' ) ),
			$( '<b>' ).text( $this.attr( 'data-user-name' ) )
		);

		$form.append( $( '<input>' ).attr( {
			'type': 'hidden',
			'name': 'action',
			'value': 'epaddreviewer'
		} ) );

		$form.append( $( '<input>' ).attr( {
			'type': 'hidden',
			'name': 'article-id',
			'value': $this.attr( 'data-article-id' )
		} ) );

		var $dialog = $( '<div>' ).html( '' ).dialog( {
			'title': ep.msg('ep-articletable-addreviwer-title', mw.user.name ),
			'minWidth': 550,
			'buttons': [
				{
					'text': ep.msg( 'ep-articletable-addreviwer-button', mw.user.name ),
					'id': 'ep-addreviwer-button',
					'click': function() {
						$form.submit();
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

		$dialog.append( $form );
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
		var $this = $( this ),
		isSelf = $this.attr( 'data-reviewer-name' ) === undefined,
		selfSuffix = isSelf ? '-self' : '',
		reviewerName = isSelf ? mw.user.name : $this.attr( 'data-reviewer-name' );

		var $form = $( '<form>' ).attr( {
			'method': 'post',
			'action': window.location
		} ).msg(
			'ep-articletable-remreviwer-text' + selfSuffix,
			reviewerName,
			$( '<b>' ).text( $this.attr( 'data-article-name' ) ),
			$( '<b>' ).text( $this.attr( 'data-student-name' ) ),
			$( '<b>' ).text( reviewerName )
		);

		$form.append( $( '<input>' ).attr( {
			'type': 'hidden',
			'name': 'action',
			'value': 'epremreviewer'
		} ) );

		$form.append( $( '<input>' ).attr( {
			'type': 'hidden',
			'name': 'article-id',
			'value': $this.attr( 'data-article-id' )
		} ) );

		$form.append( $( '<input>' ).attr( {
			'type': 'hidden',
			'name': 'user-id',
			'value': isSelf ? mw.user.id : $this.attr( 'data-reviewer-id' )
		} ) );

		var $dialog = $( '<div>' ).html( '' ).dialog( {
			'title': ep.msg('ep-articletable-remreviwer-title' + selfSuffix, reviewerName ),
			'minWidth': 550,
			'buttons': [
				{
					'text': ep.msg( 'ep-articletable-remreviwer-button' + selfSuffix, reviewerName ),
					'id': 'ep-remreviwer-button',
					'click': function() {
						$form.submit();
					}
				},
				{
					'text': ep.msg( 'ep-articletable-remreviwer-cancel' ),
					'id': 'ep-remreviwer-cancel',
					'click': function() {
						$dialog.dialog( 'close' );
					}
				}
			]
		} );

		$dialog.append( $form );
	}
	
	$( document ).ready( function() {

		$( '.ep-rem-reviewer-self, .ep-become-reviewer' ).removeAttr( 'disabled' );

		$( '.ep-become-reviewer' ).click( addReviewer );
		
		$( '.ep-rem-reviewer, .ep-rem-reviewer-self' ).click( removeReviewer );
		
		// TODO
	} );

})( window.jQuery, mw.educationProgram );