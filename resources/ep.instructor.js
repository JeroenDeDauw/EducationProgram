/**
 * JavasSript for the Education Program MediaWiki extension.
 * @see https://www.mediawiki.org/wiki/Extension:Education_Program
 *
 * @licence GNU GPL v3 or later
 * @author Jeroen De Dauw <jeroendedauw at gmail dot com>
 */

(function( $, mw ) {

	var ep = mw.educationProgram;

	$( document ).ready( function() {
		
		$( '.ep-instructor-remove' ).click( function( event ) {
			var $this = $( this ),
			courseId = $this.attr( 'data-courseid' ),
			courseName = $this.attr( 'data-coursename' ),
			userId = $this.attr( 'data-userid' ),
			userName = $this.attr( 'data-username' ),
			bestName = $this.attr( 'data-bestname' ),
			$dialog = null;
			
			var doRemove = function() {
				var $remove = $( '#ep-instructor-remove-button' );
				var $cancel = $( '#ep-instructor-cancel-button' );

				$remove.button( 'option', 'disabled', true );
				$remove.button( 'option', 'label', ep.msg( 'ep-instructor-removing' ) );

				ep.api.removeInstructor( {
					'courseid': courseId,
					'userid': userId,
					'reason': summaryInput.val()
				} ).done( function() {
					$dialog.text( ep.msg( 'ep-instructor-removal-success' ) );
					$remove.remove();
					$cancel.button( 'option', 'label', ep.msg( 'ep-instructor-close-button' ) );
					$cancel.focus();

					$li = $this.closest( 'li' );
					$ul = $li.closest( 'ul' );
					$li.remove();

					if ( $ul.find( 'li' ).length < 1 ) {
						$ul.closest( 'div' ).text( mw.msg( 'ep-course-no-instructors' ) );
					}
				} ).fail( function() {
					$remove.button( 'option', 'disabled', false );
					$remove.button( 'option', 'label', ep.msg( 'ep-instructor-remove-retry' ) );
					alert( ep.msg( 'ep-instructor-remove-failed' ) );
				} );
			};

			var summaryInput = $( '<input>' ).attr( {
				'type': 'text',
				'size': 60,
				'maxlength': 250
			} );
			
			$dialog = $( '<div>' ).html( '' ).dialog( {
				'title': ep.msg( 'ep-instructor-remove-title' ),
				'minWidth': 550,
				'buttons': [
					{
						'text': ep.msg( 'ep-instructor-remove-button' ),
						'id': 'ep-instructor-remove-button',
						'click': doRemove
					},
					{
						'text': ep.msg( 'ep-instructor-cancel-button' ),
						'id': 'ep-instructor-cancel-button',
						'click': function() {
							$dialog.dialog( 'close' );
						}
					}
				]
			} );
			
			$dialog.append( $( '<p>' ).msg(
				'ep-instructor-remove-text',
				mw.html.escape( userName ),
				$( '<b>' ).text( bestName ),
				$( '<b>' ).text( courseName )
			) );

			//$dialog.append( $( '<p>' ).msg( 'ep-instructor-remove-title' ) );

			$dialog.append( summaryInput );
			
			summaryInput.focus();
			
			summaryInput.keypress( function( event ) {
				if ( event.which == '13' ) {
					event.preventDefault();
					doRemove();
				}
			} );
		} );
		
		$( '.ep-add-instructor' ).click( function( event ) {
			var $this = $( this ), _this = this;
			
			this.courseId = $this.attr( 'data-courseid' );
			this.courseName = $this.attr( 'data-mcname' );
			this.selfMode = $this.attr( 'data-mode' ) === 'self';
			this.$dialog = null;
			
			this.nameInput = $( '<input>' ).attr( {
				'type': 'text',
				'size': 30,
				'maxlength': 250,
				'id': 'ep-instructor-nameinput',
				'name': 'ep-instructor-nameinput'
			} );
			
			this.summaryInput = $( '<input>' ).attr( {
				'type': 'text',
				'size': 60,
				'maxlength': 250,
				'id': 'ep-instructor-summaryinput',
				'name': 'ep-instructor-summaryinput'
			} );

			this.getName = function() {
				return this.selfMode ? mw.user.name : this.nameInput.val();
			};

			this.doAdd = function() {
				var $add = $( '#ep-instructor-add-button' );
				var $cancel = $( '#ep-instructor-add-cancel-button' );

				$add.button( 'option', 'disabled', true );
				$add.button( 'option', 'label', ep.msg( 'ep-instructor-adding' ) );

				ep.api.addInstructor( {
					'courseid': _this.courseId,
					'username': _this.getName(),
					'reason': _this.summaryInput.val()
				} ).done( function() {
					_this.$dialog.text( ep.msg(
						_this.selfMode ? 'ep-instructor-addittion-self-success' : 'ep-instructor-addittion-success',
						_this.getName(),
						_this.courseName
					) );

					$add.remove();
					$cancel.button( 'option', 'label', ep.msg( 'ep-instructor-add-close-button' ) );
					$cancel.focus();

					// TODO: link name to user page and show control links
					$ul = $( '#ep-course-instructors' ).find( 'ul' );

					if ( $ul.length < 1 ) {
						$ul = $( '<ul>' );
						$( '#ep-course-instructors' ).html( $ul );
					}

					$ul.append( $( '<li>' ).text( _this.getName() ) )
				} ).fail( function() {
					// TODO: implement nicer handling for fails caused by invalid user name

					$add.button( 'option', 'disabled', false );
					$add.button( 'option', 'label', ep.msg( 'ep-instructor-add-retry' ) );
					alert( ep.msg( 'ep-instructor-addittion-failed' ) );
				} );
			};

			this.$dialog = $( '<div>' ).html( '' ).dialog( {
				'title': ep.msg( this.selfMode ? 'ep-instructor-add-self-title' : 'ep-instructor-add-title', this.getName() ),
				'minWidth': 550,
				'buttons': [
					{
						'text': ep.msg( this.selfMode ? 'ep-instructor-add-self-button' : 'ep-instructor-add-button', this.getName() ),
						'id': 'ep-instructor-add-button',
						'click': this.doAdd
					},
					{
						'text': ep.msg( 'ep-instructor-add-cancel-button' ),
						'id': 'ep-instructor-add-cancel-button',
						'click': function() {
							_this.$dialog.dialog( 'close' );
						}
					}
				]
			} );
			
			this.$dialog.append( $( '<p>' ).text( gM(
				this.selfMode ? 'ep-instructor-add-self-text' : 'ep-instructor-add-text',
				this.courseName,
				this.getName()
			) ) );
			
			if ( !this.selfMode ) {
				this.$dialog.append(
					$( '<label>' ).attr( {
						'for': 'ep-instructor-nameinput'
					} ).text( ep.msg( 'ep-instructor-name-input' ) + ' ' ),
					this.nameInput,
					'<br />',
					$( '<label>' ).attr( {
						'for': 'ep-instructor-summaryinput'
					} ).text( ep.msg( 'ep-instructor-summary-input' ) )
				);
			}
			
			this.$dialog.append( this.summaryInput );
			
			if ( this.selfMode ) {
				this.summaryInput.focus();
			}
			else {
				this.nameInput.focus();
			}
			
			var enterHandler = function( event ) {
				if ( event.which == '13' ) {
					event.preventDefault();
					_this.doAdd();
				}
			};
			
			this.nameInput.keypress( enterHandler );
			this.summaryInput.keypress( enterHandler );
		} );
		
	} );
	
})( window.jQuery, window.mediaWiki );