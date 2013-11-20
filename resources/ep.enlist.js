/**
 * JavaScript for the Education Program MediaWiki extension.
 * @see https://www.mediawiki.org/wiki/Extension:Education_Program
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw <jeroendedauw at gmail dot com>
 */

(function( $, mw ) {

	var ep = mw.educationProgram;

	$( document ).ready( function() {

		$( '.ep-remove-role' ).click( function( event ) {
			var $this = $( this ),
			courseId = $this.attr( 'data-courseid' ),
			courseName = $this.attr( 'data-coursename' ),
			userId = $this.attr( 'data-userid' ),
			userName = $this.attr( 'data-username' ),
			bestName = $this.attr( 'data-bestname' ),
			role = $this.attr( 'data-role' ),
			$dialog = null,
			summaryLabel, summaryInput,
			doRemove;

			doRemove = function() {
				var $remove = $( '#ep-' + role + '-remove-button' ),
					$cancel = $( '#ep-' + role + '-cancel-button' );

				// Give grep a chance to find the usages:
				// ep-instructor-removing, ep-online-removing, ep-campus-removing
				$remove.button( 'option', 'disabled', true );
				$remove.button( 'option', 'label', ep.msg( 'ep-' + role + '-removing' ) );

				ep.api.unenlistUser( {
					'courseid': courseId,
					'userid': userId,
					'reason': summaryInput.val(),
					'role': role
				} ).done( function() {
					// Give grep a chance to find the usages:
					// ep-instructor-removal-success, ep-online-removal-success, ep-campus-removal-success,
					// ep-instructor-close-button, ep-online-close-button, ep-campus-close-button
					$dialog.text( ep.msg( 'ep-' + role + '-removal-success' ) );
					$remove.remove();
					$cancel.button( 'option', 'label', ep.msg( 'ep-' + role + '-close-button' ) );
					$cancel.focus();

					var $li = $this.closest( 'li'),
						$ul = $li.closest( 'ul' );
					$li.remove();

					// Give grep a chance to find the usages:
					// ep-course-no-instructor, ep-course-no-online, ep-course-no-campus
					if ( $ul.find( 'li' ).length < 1 ) {
						$ul.closest( 'div' ).text( mw.msg( 'ep-course-no-' + role ) );
					}
				} ).fail( function() {
					// Give grep a chance to find the usages:
					// ep-instructor-remove-retry, ep-online-remove-retry, ep-campus-remove-retry,
					// ep-instructor-remove-failed, ep-online-remove-failed, ep-campus-remove-failed
					$remove.button( 'option', 'disabled', false );
					$remove.button( 'option', 'label', ep.msg( 'ep-' + role + '-remove-retry' ) );
					alert( ep.msg( 'ep-' + role + '-remove-failed' ) );
				} );
			};

			// Give grep a chance to find the usages:
			// ep-instructor-summary, ep-online-summary, ep-campus-summary
			summaryLabel = $( '<label>' ).attr( {
				'for': 'epenlistsummary'
			} ).msg( 'ep-' + role + '-summary' ).append( '&#160;' );

			summaryInput = $( '<input>' ).attr( {
				'type': 'text',
				'size': 60,
				'maxlength': 250,
				'id': 'epenlistsummary'
			} );

			// Give grep a chance to find the usages:
			// ep-instructor-remove-title, ep-online-remove-title, ep-campus-remove-title,
			// ep-instructor-remove-button, ep-online-remove-button, ep-campus-remove-button,
			// ep-instructor-cancel-button, ep-online-cancel-button, ep-campus-cancel-button
			$dialog = $( '<div>' ).html( '' ).dialog( {
				'title': ep.msg( 'ep-' + role + '-remove-title' ),
				'minWidth': 550,
				'buttons': [
					{
						'text': ep.msg( 'ep-' + role + '-remove-button' ),
						'id': 'ep-' + role + '-remove-button',
						'click': doRemove
					},
					{
						'text': ep.msg( 'ep-' + role + '-cancel-button' ),
						'id': 'ep-' + role + '-cancel-button',
						'click': function() {
							$dialog.dialog( 'close' );
						}
					}
				]
			} );

			// Give grep a chance to find the usages:
			// ep-instructor-remove-text, ep-online-remove-text, ep-campus-remove-text
			$dialog.append( $( '<p>' ).msg(
				'ep-' + role + '-remove-text',
				mw.html.escape( userName ),
				$( '<strong>' ).text( bestName ),
				$( '<strong>' ).text( courseName )
			) );

			//$dialog.append( $( '<p>' ).msg( 'ep-instructor-remove-title' ) );

			$dialog.append( summaryLabel, summaryInput );

			summaryInput.focus();

			summaryInput.keypress( function( event ) {
				if ( event.which == '13' ) {
					event.preventDefault();
					doRemove();
				}
			} );

			return false;
		} );

		$( '.ep-add-role' ).click( function( event ) {
			var $this = $( this ),
			_this = this,
			role = $this.attr( 'data-role' ),
			isCompletionEnter = false;

			this.courseId = $this.attr( 'data-courseid' );
			this.courseName = $this.attr( 'data-coursename' );
			this.selfMode = $this.attr( 'data-mode' ) === 'self';
			this.$dialog = null;

			this.nameInput = $( '<input>' ).attr( {
				'type': 'text',
				'size': 30,
				'maxlength': 250,
				'id': 'ep-' + role + '-nameinput',
				'name': 'ep-' + role + '-nameinput'
			} );

			this.summaryInput = $( '<input>' ).attr( {
				'type': 'text',
				'size': 60,
				'maxlength': 250,
				'id': 'ep-' + role + '-summaryinput',
				'name': 'ep-' + role + '-summaryinput'
			} );

			this.getName = function() {
				return this.selfMode ? mw.user.getName() : this.nameInput.val();
			};

			this.doAdd = function() {
				var $add = $( '#ep-' + role + '-add-button' ),
				$cancel = $( '#ep-' + role + '-add-cancel-button' ),
				enterHandler;

				// Give grep a chance to find the usages:
				// ep-instructor-adding, ep-online-adding, ep-campus-adding
				$add.button( 'option', 'disabled', true );
				$add.button( 'option', 'label', ep.msg( 'ep-' + role + '-adding' ) );

				ep.api.enlistUser( {
					'courseid': _this.courseId,
					'username': _this.getName(),
					'reason': _this.summaryInput.val(),
					'role': role
				} ).done( function( data ) {
					var	messageKey = null, $ul;

					// Give grep a chance to find the usages:
					// ep-instructor-addition-null, ep-online-addition-null, ep-campus-addition-null,
					// ep-instructor-addition-self-success, ep-online-addition-self-success, ep-campus-addition-self-success,
					// ep-instructor-addition-success, ep-online-addition-success, ep-campus-addition-success
					if ( data.count === 0 ) {
						messageKey = 'ep-' + role + '-addition-null';
					}
					else {
						messageKey = _this.selfMode ? 'ep-' + role + '-addition-self-success' : 'ep-' + role + '-addition-success';
					}

					_this.$dialog.text( ep.msg(
						messageKey,
						_this.getName(),
						_this.courseName
					) );

					// Give grep a chance to find the usages:
					// ep-instructor-add-close-button, ep-online-add-close-button, ep-campus-add-close-button
					$add.remove();
					$cancel.button( 'option', 'label', ep.msg( 'ep-' + role + '-add-close-button' ) );
					$cancel.focus();

					if ( data.count > 0 ) {
						// TODO: link name to user page and show control links
						$ul = $( '#ep-course-' + role ).find( 'ul' );

						if ( $ul.length < 1 ) {
							$ul = $( '<ul>' );
							$( '#ep-course-' + role ).html( $ul );
						}

						$ul.append( $( '<li>' ).text( _this.getName() ) );
					}
				} ).fail( function( data ) {
					// Give grep a chance to find the usages:
					// ep-instructor-add-retry, ep-online-add-retry, ep-campus-add-retry
					$add.button( 'option', 'disabled', false );
					$add.button( 'option', 'label', ep.msg( 'ep-' + role + '-add-retry' ) );

					// Give grep a chance to find the usages:
					// ep-instructor-addition-failed, ep-online-addition-failed, ep-campus-addition-failed
					var msgKey = data.error ? 'ep-' + role + '-addition-' + data.error.code : 'ep-' + role + '-addition-failed';

					alert( ep.msg(
						msgKey,
						_this.getName(),
						_this.courseName
					) );
				} );
			};

			// Give grep a chance to find the usages:
			// ep-instructor-add-self-title, ep-online-add-self-title, ep-campus-add-self-title,
			// ep-instructor-add-title, ep-online-add-title, ep-campus-add-title,
			// ep-instructor-add-self-button, ep-online-add-self-button, ep-campus-add-self-button,
			// ep-instructor-add-button, ep-online-add-button, ep-campus-add-button,
			// ep-instructor-add-cancel-button, ep-online-add-cancel-button, ep-campus-add-cancel-button
			this.$dialog = $( '<div>' ).html( '' ).dialog( {
				'title': ep.msg( this.selfMode ? 'ep-' + role + '-add-self-title' : 'ep-' + role + '-add-title', this.getName() ),
				'minWidth': 550,
				'buttons': [
					{
						'text': ep.msg(
							this.selfMode ? 'ep-' + role + '-add-self-button' : 'ep-' + role + '-add-button',
							this.getName()
						),
						'id': 'ep-' + role + '-add-button',
						'click': this.doAdd
					},
					{
						'text': ep.msg( 'ep-' + role + '-add-cancel-button' ),
						'id': 'ep-' + role + '-add-cancel-button',
						'click': function() {
							_this.$dialog.dialog( 'close' );
						}
					}
				]
			} );

			// Give grep a chance to find the usages:
			// ep-instructor-add-self-text, ep-online-add-self-text, ep-campus-add-self-text,
			// ep-instructor-add-text, ep-online-add-text, ep-campus-add-text
			this.$dialog.append( $( '<p>' ).text( gM(
				this.selfMode ? 'ep-' + role + '-add-self-text' : 'ep-' + role + '-add-text',
				this.courseName,
				this.getName()
			) ) );

			// Give grep a chance to find the usages:
			// ep-instructor-name-input, ep-online-name-input, ep-campus-name-input,
			// ep-instructor-summary-input, ep-online-summary-input, ep-campus-summary-input
			if ( !this.selfMode ) {
				this.$dialog.append(
					$( '<label>' ).attr( {
						'for': 'ep-' + role + '-nameinput'
					} ).text( ep.msg( 'ep-' + role + '-name-input' ) + ' ' ),
					this.nameInput,
					'<br />',
					$( '<label>' ).attr( {
						'for': 'ep-' + role + '-summaryinput'
					} ).text( ep.msg( 'ep-' + role + '-summary-input' ) + ' ' )
				);

				this.nameInput.autocomplete( {
					source: function( request, response ) {
						ep.api.getMatchingUsers( _this.nameInput.val() ).done( function( users ) {
							response( $.map( users, function( user ) {
								return {
									'label': user.getName(),
									'value': user.getName()
								};
							} ) );
						} );
					},
					minLength: 2,
					open: function() {
						$( this ).removeClass( "ui-corner-all" ).addClass( "ui-corner-top" );
					},
					close: function() {
						$( this ).removeClass( "ui-corner-top" ).addClass( "ui-corner-all" );
					},
					select: function( event, ui ) {
						if ( ( event.keyCode ? event.keyCode : event.which ) === 13 ) {
							isCompletionEnter = true;
						}
					}
				} );
			}

			this.$dialog.append( this.summaryInput );

			if ( this.selfMode ) {
				this.summaryInput.focus();
			}
			else {
				this.nameInput.focus();
			}

			enterHandler = function( event ) {
				if ( event.which == '13' ) {
					event.preventDefault();

					if ( isCompletionEnter ) {
						isCompletionEnter = false;
					}
					else {
						_this.doAdd();
					}
				}
			};

			this.nameInput.keypress( enterHandler );
			this.summaryInput.keypress( enterHandler );

			return false;
		} );

	} );

})( window.jQuery, window.mediaWiki );
