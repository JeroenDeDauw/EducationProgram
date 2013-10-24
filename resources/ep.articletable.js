/**
 * JavaScript for the Education Program MediaWiki extension.
 * @see https://www.mediawiki.org/wiki/Extension:Education_Program
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw <jeroendedauw at gmail dot com>
 */

(function( $, ep ) {

	function addReviewer() {
		var $this, $form, $dialog;

		$this = $( this );

		$form = $( '<form>' ).attr( {
			'method': 'post',
			'action': window.location
		} ).msg(
			'ep-articletable-addreviwer-text',
			mw.user.getName(),
			$( '<strong>' ).text( $this.attr( 'data-article-name' ) ),
			$( '<strong>' ).text( $this.attr( 'data-user-name' ) )
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

		$dialog = $( '<div>' ).html( '' ).dialog( {
			'title': ep.msg('ep-articletable-addreviwer-title', mw.user.getName() ),
			'minWidth': 550,
			'buttons': [
				{
					'text': ep.msg( 'ep-articletable-addreviwer-button', mw.user.getName() ),
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

		return false;
	}

	function removeStudent() {
		var $this, $form, $dialog;

		$this = $( this );

		$form = $( '<form>' ).attr( {
			'method': 'post',
			'action': window.location
		} ).msg(
			'ep-articletable-remstudent-text',
			mw.user.getName(),
			$( '<strong>' ).text( $this.attr( 'data-course-name' ) ),
			$( '<strong>' ).text( $this.attr( 'data-user-name' ) )
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

		$dialog = $( '<div>' ).html( '' ).dialog( {
			'title': ep.msg('ep-articletable-remstudent-title', mw.user.getName() ),
			'minWidth': 550,
			'buttons': [
				{
					'text': ep.msg( 'ep-articletable-remstudent-button', mw.user.getName() ),
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

		return false;
	}

	function removeArticle() {
		var $this = $( this ),
		$dialog,
		courseName = $this.attr( 'data-course-name' ),
		isSelf = $this.attr( 'data-student-name' ) === undefined,
		selfSuffix = isSelf ? '-self' : '',
		studentName = isSelf ? mw.user.getName() : $this.attr( 'data-student-name' ),
		$form = $( '<form>' ).attr( {
			'method': 'post',
			'action': $this.attr( 'data-remove-target' )
		} ).msg(
			'ep-articletable-remarticle-text' + selfSuffix,
			$( '<strong>' ).text( $this.attr( 'data-article-name' ) ),
			$( '<strong>' ).text( courseName ),
			$( '<strong>' ).text( studentName ),
			mw.user
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

		$dialog = $( '<div>' ).html( '' ).dialog( {
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

		return false;
	}

	function removeReviewer() {
		var $this = $( this ),
		$dialog, $form,
		isSelf = $this.attr( 'data-reviewer-name' ) === undefined,
		selfSuffix = isSelf ? '-self' : '',
		reviewerName = isSelf ? mw.user.getName() : $this.attr( 'data-reviewer-name' );

		$form = $( '<form>' ).attr( {
			'method': 'post',
			'action': window.location
		} ).msg(
			'ep-articletable-remreviwer-text' + selfSuffix,
			reviewerName,
			$( '<strong>' ).text( $this.attr( 'data-article-name' ) ),
			$( '<strong>' ).text( $this.attr( 'data-student-name' ) ),
			$( '<strong>' ).text( reviewerName )
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

		$dialog = $( '<div>' ).html( '' ).dialog( {
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

		return false;
	}

	function autocompleteSource( request, response ) {
		$.getJSON(
			mw.config.get( 'wgScriptPath' ) + '/api.php',
			{
				'action': 'opensearch',
				'format': 'json',
				'search': request.term,
				'limit': 8
			},
			function( data ) {
				response( $.map( data[1], function( item ) {
					return {
						'label': item,
						'value': item
					};
				} ) );
			}
		);
	}

	function autocompleteOpen() {
		$( this ).removeClass( "ui-corner-all" ).addClass( "ui-corner-top" );
	}

	function autocompleteClose() {
		$( this ).removeClass( "ui-corner-top" ).addClass( "ui-corner-all" );
	}

	$( document ).ready( function() {

		$( '.ep-rem-reviewer-self, .ep-become-reviewer' ).removeAttr( 'disabled' );

		$( '.ep-become-reviewer' ).click( addReviewer );

		$( '.ep-rem-reviewer, .ep-rem-reviewer-self' ).click( removeReviewer );

		$( '.ep-rem-student' ).click( removeStudent );

		$( '.ep-rem-article' ).click( removeArticle );

		$( '.ep-addarticlename' ).autocomplete( {
			source: autocompleteSource,
			minLength: 2,
			open: autocompleteOpen,
			close: autocompleteClose
		} );

	} );

})( window.jQuery, mw.educationProgram );
