/**
 * JavaScript for the Education Program MediaWiki extension.
 * @see https://www.mediawiki.org/wiki/Extension:Education_Program
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw <jeroendedauw at gmail dot com>
 */

(function ( $, mw ) {

	var ep = mw.educationProgram;

	$( document ).ready( function () {

		$( '.ep-pager-clear' ).click( function () {
			var $form = $( this ).closest( 'form' );
			$form.find( 'select' ).val( '' );
			$form.submit();
			return false;
		} );

		var $dialog = null,
		$remove = null,
		$summaryInput = null,
		showConfirmDialog,
		onFail;

		showConfirmDialog = function( args, onConfirm ) {
			var names, summaryLabel,
				deferred = $.Deferred();

			args = $.extend( {
				'type': 'unknown',
				'ids': [],
				'names': [],
				'cssClass': ''
			}, args );

			$dialog = $( '<div>' ).html( '' ).dialog( {
				'title': ep.msg( 'ep-pager-confirm-delete-' + args.type, args.ids.length ),
				'minWidth': 550,
				'buttons': [
					{
						// Give grep a chance to find the usages:
						// ep-pager-delete-button-org, ep-pager-delete-button-course
						'text': ep.msg( 'ep-pager-delete-button-' + args.type, args.ids.length ),
						'id': 'ep-pager-remove-button',
						'click': function() {
							$remove.button( 'option', 'disabled', true );
							onConfirm();
							deferred.resolve();
						}
					},
					{
						// Give grep a chance to find the usages:
						// ep-pager-cancel-button-org, ep-pager-cancel-button-course
						'text': ep.msg( 'ep-pager-cancel-button-' + args.type ),
						'id': 'ep-pager-cancel-button',
						'click': function() {
							$dialog.dialog( 'close' );
							deferred.reject();
						}
					}
				]
			} );

			$remove = $( '#ep-pager-remove-button' );

			names = args.names.map( function( name ) {
				return '<strong>' + mw.html.escape( name ) + '</strong>';
			} ).join( ', ' );

			// Give grep a chance to find the usages:
			// ep-pager-confirm-message-org, ep-pager-confirm-message-course,
			// ep-pager-confirm-message-org-many, ep-pager-confirm-message-course-many
			$dialog.append(
				$( '<div>' ).addClass( args.cssClass ).msg(
					'ep-pager-confirm-message-' + args.type + ( args.names.length > 1 ? '-many' : '' ),
					$( '<span>' ).html( names ),
					args.names.length
				)
			);

			// Give grep a chance to find the usages:
			// ep-pager-confirm-message-org, ep-pager-confirm-message-course
			summaryLabel = $( '<label>' ).attr( {
				'for': 'epsummaryinput'
			} ).msg( 'ep-pager-summary-message-' + args.type ).append( '&#160;' );

			$summaryInput = $( '<input>' ).attr( {
				'type': 'text',
				'size': 60,
				'maxlength': 250,
				'id': 'epsummaryinput'
			} );

			$dialog.append( summaryLabel, $summaryInput );

			$summaryInput.keypress( function( event ) {
				if ( event.which == '13' ) {
					event.preventDefault();
					onConfirm();
				}
			} );

			$summaryInput.focus();

			return deferred.promise();
		};

		onFail = function( type ) {
			// Give grep a chance to find the usages:
			// ep-pager-retry-button-org, ep-pager-retry-button-course
			$remove.button( 'option', 'disabled', false );
			$remove.button( 'option', 'label', ep.msg( 'ep-pager-retry-button-' + type ) );
		};

		$( '.ep-pager-delete' ).click( function () {
			var $this = $( this ),
			args = {
				'type': $this.attr( 'data-type' ),
				'ids': [ $this.attr( 'data-id' ) ],
				'names': [ $this.attr( 'data-name' ) ],
				'cssClass': 'pagerDeleteWarning'
			};

			showConfirmDialog(
				args,
				function() {
					ep.api.remove( args, { 'comment': $summaryInput.val() } ).done( function() {
						$dialog.dialog( 'close' );

						var $tr = $this.closest( 'tr' ),
							$table = $tr.closest( 'table' );

						if ( $table.find( 'tr' ).length > 2 ) {
							$tr.slideUp( 'slow', function () {
								$tr.remove();
							} );
						}
						else {
							$table.slideUp( 'slow', function () {
								$table.remove();
							} );
						}
					} ).fail( function() {
						onFail( args.type );
						alert( mw.msg( 'ep-pager-delete-fail' ) );
					} );
				} );
			} );

		$( '.ep-pager-select-all' ).change( function () {
			$( this ).closest( 'table' ).find( 'input:checkbox' ).prop( 'checked', $( this ).is( ':checked' ) );
		} );

		$( '.ep-pager-delete-selected' ).click( function () {
			var $deleteButton = $( this ),
			$selectAllCheckbox = $( '#ep-pager-select-all-' + $( this ).attr( 'data-pager-id' ) ),
			$table = $selectAllCheckbox.closest( 'table' ),
			ids = [],
			names = [],
			pagerId;

			$selectedCheckboxes = $table.find( 'tbody' ).find( 'input[type=checkbox]:checked' );

			$cantDel = $selectedCheckboxes.closest( 'tr[data-no-del-text]' );
			if ($cantDel.length > 0) {
				$( '<div>' ).html( $cantDel.first().attr( 'data-no-del-text' ) )
					.dialog( {
						buttons: [ {
							text: ep.msg( 'ep-delete-org-has-courses-close-dialog' ),
							click: function() { $( this ).dialog( "close" ); }
						} ]
					} );

				return;
			}

			$selectedCheckboxes.each( function ( i, element ) {
				var $element = $( element );
				ids.push( $element.val() );
				names.push( $element.closest( 'tr' ).find( '.ep-pager-delete' ).attr( 'data-name' ) );
			} );

			if ( ids.length < 1 ) {
				return;
			}

			pagerId = $( this ).attr( 'data-pager-id' );

			args = {
				'type': $( this ).attr( 'data-type' ),
				'ids': ids,
				'names': names,
				'cssClass': 'pagerDeleteWarning'
			};

			showConfirmDialog(
				args,
				function() {
					ep.api.remove( args, { 'comment': $summaryInput.val() } ).done( function() {
						$dialog.dialog( 'close' );

						if ( $table.find( 'tr' ).length - ids.length > 1 ) {
							for ( var i in ids ) {
								if ( ids.hasOwnProperty( i ) ) {
									$( '#select-' + pagerId + '-' + ids[i] ).closest( 'tr' ).remove();
								}
							}
						}
						else {
							$table.slideUp( 'slow', function () {
								$table.remove();
								$deleteButton.closest( 'fieldset' ).remove();
							} );
						}
					} ).fail( function() {
						onFail( args.type );
						alert( window.gM( 'ep-pager-delete-selected-fail', ids.length ) );
					} );
				} );
			}
		);
	} );

})( window.jQuery, window.mediaWiki );
