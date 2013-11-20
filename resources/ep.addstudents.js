/**
 * JavaScript for the Education Program MediaWiki extension.
 * This file provides functions for adding students to courses.
 * @see https://www.mediawiki.org/wiki/Extension:Education_Program
 *
 * @license GNU GPL v2+
 * @author Andrew Green <andrew.green.df at gmail dot com>
 *
 * This file defines three singleton:
 * - A "view" object that takes care of low-level display logic.
 * - A userChecker object that checks the validity of user names,
 *   keeps track of invalid user names currently displayed, and filters
 *   user names according to MW constraints.
 * - A "presenter" object that sets everything up (including tagsinput
 *   and typeahead libraries), deals with higher-level state,
 *   and manipulates the view and userChecker as necessary.
 *
 * Everything starts up via the call to presenter.initialize().
 *
 * (A small utility class, SimpleSet, is also included, for manipulating
 * sets of unique values.)
 */
( function( $, mw ) {

	var TYPEAHEAD_FLIP_UP_LIMIT, TYPEAHEAD_MAX_SUGGESTIONS,
		view, userChecker, presenter;

	// If typeahead has more suggestions than this value, the suggestions
	// list appears above, rather than below, the input area.
	TYPEAHEAD_FLIP_UP_LIMIT = 4;

	// maximum number of suggestions that typeahead will provide
	TYPEAHEAD_MAX_SUGGESTIONS = 6;

	/**
	 * The "view" object.
	 *
	 * This object takes care of low-level display logic and DOM manipulation.
	 * It is isolated from higher-level state and content, and does not
	 * contain references to other objects defined here.
	 *
	 * All text that goes in the DOM is HTML-escaped via jQuery's text() method.
	 */
	view = ( function () {

		var CSS, $errorMsgEl, $addBtnEl, errorMsgShowing;

		// CSS constants
		CSS = {
			// coordinate with ep.addstudents.css
			addStudentsMsgId:                 'ep-addstudent-msg',
			addStudentsMsgMainCls:            'ep-addstudent-msg-main',
			addStudentsMsgSubCls:             'ep-addstudent-msg-sub',
			epInvalidUserTagCls:              'ep-invalid-user-tag',
			epAddstudentsBtnId:               'ep-addstudents-btn',
			epAddstudentsErrorId:             'ep-addstudents-error',

			// coordinate with ep.addstudents.css and ViewCourseAction.php
			mwCustomcollapsibleAddstudentsId: 'mw-customcollapsible-addstudents',
			mwCustomtoggleAddstudentsCls:     'mw-customtoggle-addstudents',
			epAddstudentsLinkId:              'ep-addstudents-link',

			// coordinate with ViewCourseAction.php
			epAddstudentsInputId:             'ep-addstudents-input',

			// coordinate with ep.articletable.css
			epAddedstudentCellInitialCls:     'ep-addedstudent-cell-initial',
			epAddedstudentCellCls:            'ep-addedstudent-cell',

			// coordinate with ep.articletable.css and ArticleTable.php
			epArticletableRowCls:             'ep-articletable-row',

			// coordinate with ep.tagsinput.css and ep.tagsinput.js
			epTagsinfoLabelCls:               'ep-tagsinfo-label'
		};

		return {

			/**
			 * Show a hideable message above the students table. (Used to inform
			 * the user of the results of his or her attempt to add students.)
			 *
			 * @param {String} mainText The main text to display
			 * @param {String} subText (optional) Text to display below the
			 *   main text, in a smaller font
			 */
			showResultsMsg: function ( mainText, subText ) {

				var $msgEl = $( '#' + CSS.addStudentsMsgId );

				if ( $msgEl.length === 0 ) {

					$msgEl = $( '<div id="' + CSS.addStudentsMsgId +'">' +
						'<div data-role="remove" >x</div>' +
						'<p class="' + CSS.addStudentsMsgMainCls + '" />' +
						'<p class="' + CSS.addStudentsMsgSubCls + '" />' +
						'</div>"' );

					$( 'a[name="studentstable"]' ).after( $msgEl );

					$msgEl.find( '[data-role="remove"]' ).click( function() {
						$msgEl.fadeOut();
					} );
				}

				$msgEl.find( '.' + CSS.addStudentsMsgMainCls ).text( mainText );

				if ( subText ) {
					$msgEl.find( '.' + CSS.addStudentsMsgSubCls ).text( subText );
				}

				$msgEl.show();
			},

			/**
			 * Show a message below the input area. Used for errors.
			 *
			 * @param {String} text The text to display
			 */
			showErrorMsg: function ( text ) {
				$errorMsgEl.text( text );

				if ( !errorMsgShowing ) {
					errorMsgShowing = true;
					$errorMsgEl.fadeIn();
				}
			},

			/**
			 * Hide the message below the input area.
			 */
			hideErrorMsg: function () {
				if ( errorMsgShowing ) {

					errorMsgShowing = false;

					$errorMsgEl.fadeOut( {
						complete: function() { $errorMsgEl.text( '' ); }
					} );
				}
			},

			/**
			 * Enable or disable the "add" button below the input area.
			 *
			 * @param {Boolean} enabled
			 */
			setAddButtonEnabled: function ( enabled ) {
				$addBtnEl.prop( 'disabled', !enabled );
			},

			/**
			 * Set the callback for when the "add" button is clicked.
			 *
			 * @param {Function} callback
			 */
			setAddButtonClick: function ( callback ) {
				$addBtnEl.click( callback );
			},

			/**
			 * In the students table, highlight and flash (once) rows that
			 * are associated with the user ids provided.
			 *
			 * @param {Array} studentUserIds
			 */
			highlightAndFlashStudentRows: function ( studentUserIds ) {

				// find the cells for students with ids in studentUserIds
				$addStudentCells = $( '.' + CSS.epArticletableRowCls )
					.filter( function(i) {
						return( $.inArray( $( this ).attr( 'data-user-id' ),
							studentUserIds ) !== -1 );
					}
				).find('td');

				// set the initial css class, then program setting the final
				// class, which will be faded to using a css transition
				$addStudentCells.addClass( CSS.epAddedstudentCellInitialCls );
				setTimeout( function() {
					$addStudentCells.removeClass( CSS.epAddedstudentCellInitialCls );
					$addStudentCells.addClass( CSS.epAddedstudentCellCls );
				}, 1 );
			},

			/**
			 * Set the text for the custom expand and collapse link
			 *
			 * @param {String} expandText
			 * @param {String} collapseText
			 */
			setExpandAndCollapseText: function( expandText, collapseText ) {
				// set handlers to switch text on expand or collapse
				$( '#' + CSS.mwCustomcollapsibleAddstudentsId ).on(
					'afterCollapse.mw-collapsible',
					function() {
						$( '.' + CSS.mwCustomtoggleAddstudentsCls ).text(
							expandText );
					}

				).on(
					'afterExpand.mw-collapsible',
					function() {
						$( '.'  + CSS.mwCustomtoggleAddstudentsCls ).text(
							collapseText );
					}
				);
			},

			/**
			 * JQuery object for the DOM element on which the tagsinput library
			 * will be activated.
			 */
			$tagsinputEl: $( '#' + CSS.epAddstudentsInputId ),

			/**
			 * Get the CSS class for a tags of valid or invalid users.
			 *
			 * @param {Boolean} valid true for a valid user, false for invalid
			 * @returns {String} the appropriate CSS class for the tag
			 */
			getTagClass: function( valid ) {
				return valid ?
					CSS.epTagsinfoLabelCls :
					CSS.epTagsinfoLabelCls + ' ' +
					CSS.epInvalidUserTagCls;
			},

			/**
			 * Initial setup
			 */
			initialize: function () {
				$addBtnEl = $( '#' + CSS.epAddstudentsBtnId );
				$errorMsgEl = $( '#' + CSS.epAddstudentsErrorId );
				errorMsgShowing = false;

				// if the user clicks on the URL that students can use for enrolling
				// themselves, select it
				$enrollLink = $( '#' + CSS.epAddstudentsLinkId );

				$enrollLink.click( function() {
					var el, range;

					el = document.getElementById( CSS.epAddstudentsLinkId  )
						.firstChild;

					range = document.createRange();
					range.setStart( el, 0 );
					range.setEnd( el, $enrollLink.text().length );
					window.getSelection().addRange( range );
				} );
			}
		};
	}() );

	/**
	 * Simple set class for managing sets of users in userChecker.
	 */
	function SimpleSet() {
		this.values = [];
	}

	/**
	 * Add a value to the set.
	 *
	 * @param v The value to add
	 * @returns {Boolean} true if v was added, false if v wasn't because it
	 *   was already in the set
	 */
	SimpleSet.prototype.add = function ( v ) {
		if ( !this.contains( v ) ) {
			this.values.push( v );
			return true;
		}

		return false;
	};

	/**
	 * Remove a value from the set.
	 *
	 * @param v The value remove
	 * @returns {Boolean} true if v was removed, false if v wasn't because it
	 *   wasn't in the set
	 */
	SimpleSet.prototype.remove = function ( v ) {
		var i = $.inArray( v, this.values );
		if ( i !== -1 ) {
			this.values.splice( i, 1 );
			return true;
		}

		return false;
	};

	/**
	 * Does the set contain this value?
	 *
	 * @param v
	 * @returns {Boolean}
	 */
	SimpleSet.prototype.contains = function ( v ) {
		return $.inArray( v, this.values ) !== -1;
	};

	/**
	 * Get a new array with the values in the set.
	 *
	 * @returns {Array}
	 */
	SimpleSet.prototype.asArray = function () {
		return this.values.slice(); // use slice() to return a copy
	};

	/**
	 * Get the number of values in the set.
	 *
	 * @returns {Integer}
	 */
	SimpleSet.prototype.size = function () {
		return this.values.length;
	};

	/**
	 * Remove all values in the set.
	 */
	SimpleSet.prototype.clear = function () {
		this.values = [];
	};

	/**
	 * Keeps track of whether usernames are valid or not, and calls the
	 * server to validate them.
	 *
	 * Note: in this object, "users" are always just username strings.
	 */
	userChecker = ( function () {

		var validUsers, invalidUsers, showingInvalidUsers, pendingUsersToCheck,
			showingInvalidChangeCallback, checkTimeoutId;

		validUsers = new SimpleSet();          // users known to be valid
		invalidUsers = new SimpleSet();        // users known to be invalid
		showingInvalidUsers = new SimpleSet(); // invalid users now showing
		pendingUsersToCheck = new SimpleSet(); // users to verify
		checkTimeoutId = null;

		/**
		 * Modifies a username in accordance with certain MW username constraints,
		 * to improve usability and security.
		 *
		 * Specifically: capitalizes the first letter (since usernames must begin
		 * with a capital), trims and collapses whitespace (since usernames can't have
		 * consecutive spaces), removes forbidden characters.
		 *
		 * See http://en.wikipedia.org/wiki/Wikipedia:Naming_conventions_%28technical_restrictions%29#Restrictions_on_usernames
		 */
		function mungeUsernameForConstraints( n, encoded ) {

			// TODO use wgInvalidUsernameCharacters

			// trim & collapse whitespace
			if ( encoded ) {
				n = n.replace( /_/g, '%20' )                // underscores
					.replace ( /((^(%20)*|(%20)*$))/g, '')  // trim
					.replace( /%20(%20)*/g, '%20' )         // collapse
					.replace(
					/(%23)|(%3C)|(%3E)|(%5B)|(%5D)|(%7C)|(%7B)|(%7D)|(%2F)|(%40)/g,
					'' );                                   // forbidden
			} else {
				n = n.replace( /_/g, ' ')                         // underscores
					.trim()                                       // trim
					.replace( / \s*/g, ' ' )                      // collapse
					.replace( /#|<|>|\[|\]|\||\{|\}|\/|@/g, '' ); // forbidden
			}

			// initial capital
			return n.charAt( 0 ).toUpperCase() + n.slice( 1 );
		}

		/**
		 * Schedule a server call to verify users that have been added. We do
		 * this in a timeout in case many users are added in quick succession
		 * (for example, if a list of users is pasted into the input control).
		 * In that case, we'll verify all the users in a single server call.
		 */
		function scheduleCheck() {

			if ( pendingUsersToCheck.size() > 0 ) {

				if ( checkTimeoutId !== null ) {
					clearTimeout( checkTimeoutId );
				}

				checkTimeoutId = setTimeout( function() {

					var usersToCheckParam;

					checkTimeoutId = null;

					usersToCheckParam = ( $.map( pendingUsersToCheck.asArray(),
						function ( u ) {
							return  mungeUsernameForConstraints( u, false );
						}
					) ).join('|');

					( new mw.Api() ).get( {
						action: 'query',
						list: 'users',
						ususers: usersToCheckParam

					} ).done( function ( data ) {

						var initShowingInvalidSize = showingInvalidUsers.size();

						$.each( data.query.users, function ( i, val) {
							var name = val.name;

							if ( val.userid ) {
								validUsers.add( name );

								// practically, these removes shouldn't be needed
								// but just in case
								showingInvalidUsers.remove( name );
								invalidUsers.remove( name );

							} else {
								showingInvalidUsers.add( name );
								invalidUsers.add( name );

								// is this necessary? well, just in case...
								validUsers.remove( name );
							}
						} );

						pendingUsersToCheck.clear();

						// callback if there was a change showingInvalidUsers
						if ( initShowingInvalidSize !== showingInvalidUsers.size() ) {
							showingInvalidChangeCallback();
						}

					// Failure when verifying users are not serious, since users
					// will be verified on the server again when they're
					// submitted. Only log to console to spare the user the
					// trouble of worrying about them.
					} ).fail( function ( error ) {
						console.log( 'Failed to verify users to add:' + error );
					} );

				}, 200);
			}
		}

		return {

			/**
			 * The user indicated has been added to the input area. If
			 * assumeValid is true, we won't verify the user, otherwise we will.
			 * (Users are assumed to be valid if they're chosen from the
			 * dropdown list.)
			 *
			 * @param {String} user
			 * @param {Boolean} assumeValid
			 */
			addUser: function ( user, assumeValid ) {
				var mungedUser = mungeUsernameForConstraints( user, false );

				// nothing to do if we already know the user is valid
				if ( !validUsers.contains( mungedUser ) ) {

					// add the user to the list of valid users if we're assuming
					// he or she is valid
					if ( assumeValid ) {
						validUsers.add( mungedUser );

					// if we already know the user if invalid, just add him or
					// her to the list of invalid users that are showing
					} else if ( invalidUsers.contains( mungedUser ) ) {

						// If this user was not already among the invalid users
						// showing, call the callback. (This will always happen,
						// really.)
						if ( showingInvalidUsers.add( mungedUser ) ) {
							showingInvalidChangeCallback();
						}

					// if nothing was known about the user, schedule a
					// verification
					} else {
						pendingUsersToCheck.add( mungedUser );
						scheduleCheck();
					}
				}
			},

			/**
			 * The user indicated has been removed from the input area.
			 *
			 * @param {String} user
			 */
			removeUser: function ( user ) {
				var mungedUser = mungeUsernameForConstraints( user, false );
				if ( showingInvalidUsers.remove( mungedUser ) ) {
					showingInvalidChangeCallback();
				}
				pendingUsersToCheck.remove( mungedUser );
			},

			/**
			 * Clear the list of invalid users showing and replace it with the
			 * users provided. This method is useful for refreshing state after
			 * an attempt to add users that included invalid users. In this case
			 * we don't call the showingInvalidChangeCallback.
			 *
			 * @param {Array} users
			 */
			setShowingInvalid: function ( users ) {

				var mungedUser;
				showingInvalidUsers.clear();

				$.each( users, function ( i, val ) {
					mungedUser = mungeUsernameForConstraints( val, false );
					showingInvalidUsers.add( mungedUser );
					invalidUsers.add( mungedUser );
				} );
			},

			/**
			 * Get the invalid users currently showing.
			 *
			 * @returns {Array}
			 */
			showingInvalid: function () {
				return showingInvalidUsers.asArray();
			},

			showingInvalidCount: function () {
				return showingInvalidUsers.size();
			},

			/**
			 * Is the user valid? We're optimistic: if a user hasn't been
			 * found to be invalid, he or she is valid. This should keep things
			 * usable even if background user validation runs into network
			 * issues.
			 *
			 * @param {String} user
			 * @returns {Boolean}
			 */
			isValid: function ( user ) {
				return !invalidUsers.contains(
					mungeUsernameForConstraints( user, false ) );
			},

			/**
			 * Set a callback for when there's a change in the invalid users
			 * showing.
			 *
			 * @param {Function} callback
			 */
			setShowingInvalidChangeCallback: function ( callback ) {
				showingInvalidChangeCallback = callback;
			},

			/**
			 * Prepare a URL-encoded username for a server call. We
			 * standardize it according to MW username constraints.
			 *
			 * @param {String} user URL-encoded username
			 * @returns {String} the processed username
			 */
			prepareURLEncoded: function ( user )  {
				return mungeUsernameForConstraints( user, true );
			},

			/**
			 * Prepare a username for a server call. We standardize it
			 * according to MW username constraints.
			 *
			 * @param {String} user the username
			 * @returns {String} the processed username
			 */
			prepare: function ( user )  {
				return mungeUsernameForConstraints( user, false );
			}

		};
	}() );

	/**
	 * The "presenter" object.
	 *
	 * This object takes care of higher-level logic for adding students to a
	 * course. Only this object may refer to the view, the userChecker,
	 * the tagsinput library and typeahead library. Provides an initialize()
	 * method for setting everything up.
	 */
	presenter = ( function () {

		var $realInputEl, courseId, enableAdd;

		/**
		 * Initialize and wire up the tagsinput library.
		 */
		function initializeTagsinput() {

			view.$tagsinputEl.tagsinput( {

				// Confirm: enter or comma.
				// Tab is used with typeahead to confirm a hint (which will
				// also add it as a tag).
				// Enter won't confirm a hint, so if there's a hint and the user
				// presses enter (without typing it out fully, and without using
				// the arrow keys to select an item from the suggestions menu)
				// only what they've actually typed so far becomes a hint.
				// This is the desired behavior.
				confirmKeys: [13, 188],

				// If there's no text that hasn't been turned into a tag, and
				// the user presses this key, trigger the confirmWithEmptyInput
				// event. This is used to provide standard "submit on enter"
				// behavior.
				confirmWithEmptyInputKeys: [13],

				// set custom function for css class, will highlight invalid users
				tagClass: function( val ) {
					return view.getTagClass( userChecker.isValid( val ) );
				},

				// filter tags before they're added
				addTagFilter: function ( val ) {
					return userChecker.prepare( val );
				}
			} );

			// handler for tag added: ensure the typeahead menu disappears,
			// validate the user, refresh state
			view.$tagsinputEl.on( 'itemAdded', function ( data ) {
				hideTypeahead();
				userChecker.addUser( data.item, false );
			} );

			// handler for tag removed: if the user removed was invalid, remove him/her
			// from the invalid list, refresh state
			view.$tagsinputEl.on( 'itemRemoved', function ( data ) {
				userChecker.removeUser( data.item );
				refresh();
			} );

			// if the user presses enter without entering any text, assume
			// that they mean to "submit" the form
			view.$tagsinputEl.on( 'confirmWithEmptyInput', function () {
				if ( enableAdd ) {
					doAddStudents();
				}
			} );
		}

		/**
		 * Initialize and wire up the typeahead library.
		 */
		function initializeTypeahead() {

			$realInputEl = view.$tagsinputEl.tagsinput( 'input' );
			$realInputEl.typeahead( {

				// fetch suggestions via api
				remote: {
					url: mw.util.wikiScript( 'api' ) +
						'?action=query&list=allusers&format=json&aulimit=' +
						TYPEAHEAD_MAX_SUGGESTIONS +
						'&auprefix=',

					replace: function(url, uriEncodedQuery) {
						return url + userChecker.prepareURLEncoded( uriEncodedQuery, true );
					},

					filter: function( data ) {
						return $.map( data.query.allusers, function( u ) {
							return u.name;
						} );
					},
				},

				limit: TYPEAHEAD_MAX_SUGGESTIONS,

				// if there are more than 4 suggestions, show menu above instead
				// of below
				flipUpLimit: TYPEAHEAD_FLIP_UP_LIMIT

			// add a tag when a typeahead option is selected
			} ).bind( 'typeahead:selected', function ( e, datum ) {
				hideTypeahead();
				userChecker.addUser( datum.value, true );
				view.$tagsinputEl.tagsinput( 'add', datum.value );

			// add a tag when the user autocompletes from a hint (via tab key)
			} ).bind( 'typeahead:autocompleted', function ( e, datum ) {
				hideTypeahead();
				userChecker.addUser( datum.value, true );
				view.$tagsinputEl.tagsinput( 'add', datum.value );

			// update add state (enabled/disabled) on each input
			} ).on( 'input', updateAdd );
		}

		/**
		 * Check if this is a page reload after students were added, and display
		 * messages and highlight students in the table as required.
		 */
		function doStudentsJustAdded() {

			var currentLoc, q, studentsAddedIds, alreadyEnrolled,
				oneStudentAddedGender, oneAlreadyEnrolledGender,
				studentsAddedText, alreadyEnrolledText;

			currentLoc = new mw.Uri( window.location.href );
			q = currentLoc.query;

			if ( 'studentsadded' in q ) {

				// Construct and show a message confirming that students were,
				// added, and possibly another one about students that were
				// already enrolled. Re-verify all input.
				if ( q.studentsadded.length > 0 ) {
					studentsAddedIds =
						restrictToDigits( q.studentsadded.split('|') );

				} else {
					studentsAddedIds = [];
				}

				oneStudentAddedGender = q.oneStudentAddedGender ?
					restrictGenderStr( q.oneStudentAddedGender ) :
					null;

				studentsAddedText = mw.message( 'ep-addstudents-success',
						studentsAddedIds.length, oneStudentAddedGender )
						.text();

				if ( q.alreadyenrolled.length > 0 ) {

					alreadyEnrolled = $.map(
						q.alreadyenrolled.split('|'), function ( u ) {
							return userChecker.prepare( u );
						}
					);

					oneAlreadyEnrolledGender = q.oneAlreadyEnrolledGender ?
						restrictGenderStr( q.oneAlreadyEnrolledGender ) :
						null;

					alreadyEnrolledText = mw.message(
						'ep-addstudents-alreadyenrolled',
						alreadyEnrolled.length,
						alreadyEnrolled.join(
							mw.message( 'comma-separator' ).text() ),
						oneAlreadyEnrolledGender)
						.text();

					view.showResultsMsg( studentsAddedText, alreadyEnrolledText );

				} else {
					view.showResultsMsg( studentsAddedText );
				}

				// in student the table, highlight and flash rows of students added
				view.highlightAndFlashStudentRows( studentsAddedIds );

				// remove the query parameters from the URL without changing history
				delete q.studentsadded;
				delete q.oneStudentAddedGender;
				delete q.alreadyenrolled;
				delete q.oneAlreadyEnrolledGender;
				window.history.replaceState( '', '', currentLoc.toString() );
			}
		}

		/**
		 * Call the server to add students. If successful, reload the page
		 * sending some info as URL parameters. If there were problems,
		 * display a message without reloading.
		 */
		function doAddStudents() {

			var inputElVal = $realInputEl.val();

			// If there's a bit of text in the input field that has not been made
			// a tag, make it one. It will get validated in the following call
			// to add students.
			if (inputElVal.length > 0) {
				view.$tagsinputEl.tagsinput( 'add', inputElVal);
			}

			// api call
			( new mw.Api() ).post( {
				action: 'addstudents',
				courseid: courseId,
				token: mw.user.tokens.get( 'editToken' ),
				studentusernames: view.$tagsinputEl.val().replace( /,/g ,'|' )

			} ).done( function ( data ) {
				var newUri;

				// Success!! Reload the page so the students table includes
				// the newly added students, pass some info to the page
				// reload via URL parameters, and jump straight to the students'
				// table. Note also that the presence of these parameters will
				// make the controls start out expanded (handled in PHP).
				if ( data.success ) {
					newUri = new mw.Uri();

					// validate all the data received as we add it to the URI
					newUri.extend( {
						studentsadded:
							restrictToDigits( data.studentsAddedIds ).join( '|' )
					} );

					if ( data.oneStudentAddedGender ) {
						newUri.extend( {
							oneStudentAddedGender:
								restrictGenderStr( data.oneStudentAddedGender )
						} );
					}

					newUri.extend( {
						alreadyenrolled:
							$.map( data.alreadyEnrolledUserNames, function ( u ) {
								return userChecker.prepare( u );
							} ).join( '|' )
					} );

					if ( data.oneAlreadyEnrolledGender ) {
						newUri.extend( {
							oneAlreadyEnrolledGender:
								restrictGenderStr( data.oneAlreadyEnrolledGender )
						} );
					}

					newUri.fragment = 'studentstable';

					window.location.replace( newUri.toString() );

				// some user names were invalid, tell the user
				} else if ( data.invalidUserNames.length > 0 ) {

					// userChecker will validate user names
					userChecker.setShowingInvalid( data.invalidUserNames );
					setupErrorMsgForInvalidUsers();
					view.$tagsinputEl.tagsinput('refresh');
				}

			// Something went wrong. We could show a message above the students
			// table, which is where our success messages go, but the page will
			// not reload in this case, and the user won't be looking there.
			// So we show the message below the input form, where on-the-fly
			// validation errors are shown.
			} ).fail( function ( error ) {
				view.showErrorMsg(
					mw.message( 'ep-addstudents-servercallerror' ).text() +
					error );

			} ).always( function () {
				addCurrentlyInProcess = false;
			} );
		}

		/**
		 *  hide typeahead elements by setting empty query
		 */
		function hideTypeahead() {
			view.$tagsinputEl.tagsinput( 'clearInputVal' );
			$realInputEl.typeahead( 'setQuery', '' );
		}

		/**
		 * Refresh the state of the controls, setting the error message, tag styles
		 * and controls as necessary, in view of valid/invalid users in input area.
		 */
		function refresh() {
			// refresh tags
			view.$tagsinputEl.tagsinput('refresh');

			// set or remove error message
			setupErrorMsgForInvalidUsers();

			// enable or disable add and update button
			updateAdd();
		}

		/**
		 * Turns anything toStringable or an array of toStringable things
		 * into a string with only digits or an array of strings with only digits.
		 *
		 * @param v the value or array to restrict
		 */
		function restrictToDigits( v ) {

			if ( v instanceof Array ) {
				return $.map( v, function ( val ) {
					return restrictToDigits( val );
				} );

			} else {
				return v.toString().replace( /\D/g, '' );
			}
		}

		/**
		 * Gender string validation: only male, female or unknown
		 *
		 * @param {String} s the string to restrict
		 */
		function restrictGenderStr( s ) {
			if ( s === 'male' || s === 'female' ) {
				return s;
			} else {
				return 'unknown';
			}
		}

		/**
		 * Sets the error message about invalid users, and hides
		 * or shows it, as appropriate.
		 */
		function setupErrorMsgForInvalidUsers() {
			var invalidCount = userChecker.showingInvalidCount();
			if ( invalidCount > 0 ) {

				msg = mw.message(
					'ep-addstudents-invalid-users',
					invalidCount,
					userChecker.showingInvalid().join(
						mw.message( 'comma-separator' ).text() ) )
					.text();

				view.showErrorMsg( msg );

			} else {
				view.hideErrorMsg();
			}
		}

		/**
		 * Should the actual adding of students be enabled? It depends on
		 * whether there are invalid users, users added as tags, or unvalidated
		 * text.
		 *
		 * @returns {Boolean}
		 */
		function shouldEnableAdd() {

			// get the tags added, as a comma-separated list
			var tagsStr = view.$tagsinputEl.val();

			// If there are no invalid users in the input area...
			if ( userChecker.showingInvalidCount() === 0 &&

				// ...and there's text in the input area...
				( $realInputEl.val().length > 0 ||

				// ...or there's at least one tag...
				( ( tagsStr.length > 0 ) &&
				( tagsStr.split(',').length > 0 ) ) ) ) {

				// ... then enable adding users
				return true;

			} else {
				return false;
			}
		}

		/**
		 * Update enabled state (variable and button) for adding users.
		 */
		function updateAdd() {
			enableAdd = shouldEnableAdd();
			view.setAddButtonEnabled( enableAdd );
		}

		return {
			/**
			 * Boot everything up.
			 */
			initialize: function () {

				// tell the view to do some initial stuff
				view.initialize();

				// get courseId, munge to only digits
				courseId =
					restrictToDigits( view.$tagsinputEl.attr( 'data-courseid' ) );

				// Send the view the text for the custom expand and collapse link
				view.setExpandAndCollapseText(
						mw.message( 'collapsible-expand' ).text(),
						mw.message( 'collapsible-collapse' ).text()
				);

				// initialize tagsinput and typeahead functions
				initializeTagsinput();
				initializeTypeahead();

				// check if this is a page reload following an attempt to add
				// students, and set up some UI elements accordingly
				doStudentsJustAdded();

				// set userChecker's callback for when invalid users are added
				// or removed from view
				userChecker.setShowingInvalidChangeCallback( refresh );

				// set click handler for "Add" button
				view.setAddButtonClick( doAddStudents );

				// enable or disable add and update button
				// (might be enabled in the browser auto-filled the form)
				updateAdd();
			}
		};
	}() );

	// boot everything up
	presenter.initialize();

} ) ( window.jQuery, window.mediaWiki );