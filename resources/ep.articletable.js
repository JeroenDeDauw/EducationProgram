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
			'name': 'token',
			'value': $this.attr( 'data-token' )
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
	
	function removeStudent() {
		var $this = $( this );

		var $form = $( '<form>' ).attr( {
			'method': 'post',
			'action': window.location
		} ).msg(
			'ep-articletable-remstudent-text',
			mw.user.name,
			$( '<b>' ).text( $this.attr( 'data-course-name' ) ),
			$( '<b>' ).text( $this.attr( 'data-user-name' ) )
		);

		$form.append( $( '<input>' ).attr( {
			'type': 'hidden',
			'name': 'action',
			'value': 'epremstudent'
		} ) );

		$form.append( $( '<input>' ).attr( {
			'type': 'hidden',
			'name': 'token',
			'value': $this.attr( 'data-token' )
		} ) );

		$form.append( $( '<input>' ).attr( {
			'type': 'hidden',
			'name': 'user-id',
			'value': $this.attr( 'data-user-id' )
		} ) );

		$form.append( $( '<input>' ).attr( {
			'type': 'hidden',
			'name': 'course-id',
			'value': $this.attr( 'data-course-id' )
		} ) );

		var $dialog = $( '<div>' ).html( '' ).dialog( {
			'title': ep.msg('ep-articletable-remstudent-title', mw.user.name ),
			'minWidth': 550,
			'buttons': [
				{
					'text': ep.msg( 'ep-articletable-remstudent-button', mw.user.name ),
					'id': 'ep-remstudent-button',
					'click': function() {
						$form.submit();
					}
				},
				{
					'text': ep.msg( 'ep-articletable-remstudent-cancel' ),
					'id': 'ep-remstudent-cancel',
					'click': function() {
						$dialog.dialog( 'close' );
					}
				}
			]
		} );

		$dialog.append( $form );
	}
	
	function removeArticle() {
		var $this = $( this ),
		courseName = $this.attr( 'data-course-name' );

		var $form = $( '<form>' ).attr( {
			'method': 'post',
			'action': window.location
		} ).msg(
			'ep-articletable-remarticle-text' + ( courseName === undefined ? '' : '-course' ),
			$( '<b>' ).text( $this.attr( 'data-article-name' ) ),
			$( '<b>' ).text( courseName )
		);

		$form.append( $( '<input>' ).attr( {
			'type': 'hidden',
			'name': 'action',
			'value': 'epremarticle'
		} ) );

		$form.append( $( '<input>' ).attr( {
			'type': 'hidden',
			'name': 'token',
			'value': $this.attr( 'data-token' )
		} ) );

		$form.append( $( '<input>' ).attr( {
			'type': 'hidden',
			'name': 'article-id',
			'value': $this.attr( 'data-article-id' )
		} ) );

		var $dialog = $( '<div>' ).html( '' ).dialog( {
			'title': ep.msg( 'ep-articletable-remarticle-title', $this.attr( 'data-article-name' ) ),
			'minWidth': 550,
			'buttons': [
				{
					'text': ep.msg( 'ep-articletable-remarticle-button' ),
					'id': 'ep-remarticle-button',
					'click': function() {
						$form.submit();
					}
				},
				{
					'text': ep.msg( 'ep-articletable-remarticle-cancel' ),
					'id': 'ep-remarticle-cancel',
					'click': function() {
						$dialog.dialog( 'close' );
					}
				}
			]
		} );

		$dialog.append( $form );
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
			'name': 'token',
			'value': $this.attr( 'data-token' )
		} ) );

		$form.append( $( '<input>' ).attr( {
			'type': 'hidden',
			'name': 'article-id',
			'value': $this.attr( 'data-article-id' )
		} ) );

		if ( !isSelf ) {
			$form.append( $( '<input>' ).attr( {
				'type': 'hidden',
				'name': 'user-id',
				'value': $this.attr( 'data-reviewer-id' )
			} ) );			
		}

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

		$( '.ep-rem-student' ).click( removeStudent );

		$( '.ep-rem-article' ).click( removeArticle );
	} );

})( window.jQuery, mw.educationProgram );