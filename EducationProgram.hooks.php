<?php

namespace EducationProgram;

use DatabaseUpdater;
use EchoEvent;
use EducationProgram\Events\EditEventCreator;
use EducationProgram\Events\EventStore;
use Title;
use User;
use SkinTemplate;
use Revision;
use Page;
use JobQueueGroup;

/**
 * Static class for hooks handled by the Education Program extension.
 *
 * @since 0.1
 *
 * @file EducationProgram.hooks.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
final class Hooks {

	public static function registerExtension() {
		// Define named constants corresponding to the user roles introduced by the extension.
		define( 'EP_STUDENT', 0 );      // Students
		define( 'EP_INSTRUCTOR', 1 );   // Instructors
		define( 'EP_OA', 2 );           // Online volunteers
		define( 'EP_CA', 3 );           // Campus volunteers

		global $wgEPSettings, $wgExtensionAssetsPath, $wgScriptPath;

		$epResourceDir = $wgExtensionAssetsPath === false
			? $wgScriptPath . '/extensions'
			: $wgExtensionAssetsPath;

		$wgEPSettings['resourceDir'] = $epResourceDir;
		$wgEPSettings['imageDir'] = $epResourceDir . 'images/';
	}

	/**
	 * @param \ResourceLoader &$resourceLoader
	 */
	public static function onResourceLoaderRegisterModules( \ResourceLoader &$resourceLoader ) {
		$extraDependancies = [];
		if ( \ExtensionRegistry::getInstance()->isLoaded( 'WikiEditor' ) ) {
			$extraDependancies[] = 'ext.wikiEditor.toolbar';
		}
		$moduleTemplate = [
			'localBasePath' => __DIR__ . '/resources',
			'remoteExtPath' => 'EducationProgram/resources'
		];
		$resourceLoader->register( [
			'ep.formpage' => $moduleTemplate + [
				'scripts' => [
					'ep.formpage.js',
				],
				'styles' => [
					'ep.formpage.css',
				],
				'dependencies' => [
						'jquery.ui.button',
					] + $extraDependancies
			],
			'ep.ambprofile' => $moduleTemplate + [
				'scripts' => [
					'ep.ambprofile.js',
				],
				'styles' => [
					'ep.ambprofile.css',
				],
				'dependencies' => [
						'jquery.ui.button',
						'ep.imageinput',
					] + $extraDependancies
			]
		] );
	}

	/**
	 * Schema update to set up the needed database tables.
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/LoadExtensionSchemaUpdates
	 *
	 * @since 0.1
	 *
	 * @param DatabaseUpdater $updater
	 */
	public static function onSchemaUpdate( DatabaseUpdater $updater ) {
		$updater->addExtensionTable(
			'ep_orgs',
			__DIR__ . '/sql/EducationProgram.sql'
		);

		$updater->addExtensionField(
			'ep_courses',
			'course_title',
			__DIR__ . '/sql/AddCourseTitleField.sql'
		);

		$updater->addExtensionField(
			'ep_courses',
			'course_touched',
			__DIR__ . '/sql/AddTouched.sql'
		);

		$updater->renameExtensionIndex(
			'ep_users_per_course',
			'ep_users_per_course',
			'ep_upc_user_courseid_role',
			__DIR__ . '/sql/rename_upc_index.sql'
		);

		$updater->addExtensionField(
			'ep_orgs',
			'org_last_active_date',
			__DIR__ . '/sql/AddOrgLastActiveDate.sql'
		);
	}

	/**
	 * Called after the personal URLs have been set up, before they are shown.
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/PersonalUrls
	 *
	 * @since 0.1
	 *
	 * @param array &$personal_urls
	 * @param Title &$title
	 */
	public static function onPersonalUrls( array &$personal_urls, Title &$title ) {
		if ( Settings::get( 'enableTopLink' ) ) {
			global $wgUser;

			// Find the watchlist item and replace it by the my contests link and itself.
			if ( $wgUser->isLoggedIn() && $wgUser->getOption( 'ep_showtoplink' ) ) {
				$url = \SpecialPage::getTitleFor( 'MyCourses' )->getLinkURL();
				$myCourses = [
					'text' => wfMessage( 'ep-toplink' )->text(),
					'href' => $url,
					'active' => ( $url == $title->getLinkURL() )
				];

				$insertUrls = [ 'mycourses' => $myCourses ];

				$personal_urls = wfArrayInsertAfter( $personal_urls, $insertUrls, 'preferences' );
			}
		}
	}

	/**
	 * Adds the preferences of Education Program to the list of available ones.
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/GetPreferences
	 *
	 * @since 0.1
	 *
	 * @param User $user
	 * @param array &$preferences
	 */
	public static function onGetPreferences( User $user, array &$preferences ) {
		if ( Settings::get( 'enableTopLink' ) ) {
			$preferences['ep_showtoplink'] = [
				'type' => 'toggle',
				'label-message' => 'ep-prefs-showtoplink',
				'section' => 'rendering/education',
			];
		}

		if ( $user->isAllowed( 'ep-bulkdelorgs' ) ) {
			$preferences['ep_bulkdelorgs'] = [
				'type' => 'toggle',
				'label-message' => 'ep-prefs-bulkdelorgs',
				'section' => 'rendering/education',
			];
		}

		if ( $user->isAllowed( 'ep-bulkdelcourses' ) ) {
			$preferences['ep_bulkdelcourses'] = [
				'type' => 'toggle',
				'label-message' => 'ep-prefs-bulkdelcourses',
				'section' => 'rendering/education',
			];
		}
	}

	/**
	 * Called to determine the class to handle the article rendering, based on title.
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ArticleFromTitle
	 *
	 * @since 0.1
	 *
	 * @param Title &$title
	 * @param \Article|null &$article
	 */
	public static function onArticleFromTitle( Title &$title, &$article ) {
		if ( $title->getNamespace() == EP_NS ) {
			$article = EducationPage::factory( $title );
		}
	}

	/**
	 * For extensions adding their own namespaces or altering the defaults.
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/CanonicalNamespaces
	 *
	 * @since 0.1
	 *
	 * @param array &$list
	 */
	public static function onCanonicalNamespaces( array &$list ) {
		$list[EP_NS] = 'Education_Program';
		$list[EP_NS_TALK] = 'Education_Program_talk';
	}

	/**
	 * Alter the structured navigation links in SkinTemplates.
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/SkinTemplateNavigation
	 *
	 * @since 0.1
	 *
	 * @param SkinTemplate &$sktemplate
	 * @param array &$links
	 */
	public static function onPageTabs( SkinTemplate &$sktemplate, array &$links ) {
		self::displayTabs( $sktemplate, $links, $sktemplate->getTitle() );
	}

	/**
	 * Called on special pages after the special tab is added but before variants have been added.
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/SkinTemplateNavigation::SpecialPage
	 *
	 * @since 0.1
	 *
	 * @param SkinTemplate &$sktemplate
	 * @param array &$links
	 */
	public static function onSpecialPageTabs( SkinTemplate &$sktemplate, array &$links ) {
		$textParts = \SpecialPageFactory::resolveAlias( $sktemplate->getTitle()->getText() );

		if ( in_array( $textParts[0], [ 'Enroll', 'Disenroll' ] )
			&& !is_null( $textParts[1] ) && trim( $textParts[1] ) !== ''
		) {
			// Remove the token from the title if needed.
			if ( !$sktemplate->getRequest()->getCheck( 'wptoken' ) ) {
				$textParts[1] = explode( '/', $textParts[1] );

				if ( count( $textParts[1] ) > 1 ) {
					array_pop( $textParts[1] );
				}

				$textParts[1] = implode( '/', $textParts[1] );
			}

			$title = Courses::singleton()->getTitleFor( $textParts[1] );

			if ( !is_null( $title ) ) {
				self::displayTabs( $sktemplate, $links, $title );
			}
		}
	}

	/**
	 * Display the tabs for a course or institution.
	 *
	 * @since 0.1
	 *
	 * @param SkinTemplate &$sktemplate
	 * @param array &$links
	 * @param Title $title
	 */
	protected static function displayTabs(
		SkinTemplate &$sktemplate, array &$links, Title $title
	) {
		if ( $title->getNamespace() == EP_NS ) {
			$links['views'] = [];
			$links['actions'] = [];

			$user = $sktemplate->getUser();
			$class = Utils::isCourse( $title )
				? 'EducationProgram\Courses'
				: 'EducationProgram\Orgs';
			$exists = $class::singleton()->hasIdentifier( $title->getText() );
			$type = $sktemplate->getRequest()->getText( 'action' );
			$isSpecial = $sktemplate->getTitle()->isSpecialPage();

			if ( $exists ) {
				$links['views']['view'] = [
					'class' => ( !$isSpecial && $type === '' ) ? 'selected' : false,
					'text' => $sktemplate->msg( 'ep-tab-view' )->text(),
					'href' => $title->getLocalURL()
				];
			}

			$page = EducationPage::factory( $title );

			if ( $user->isAllowed( $page->getLimitedEditRight() ) ) {
				$links['views']['edit'] = [
					'class' => $type === 'edit' ? 'selected' : false,
					'text' => $sktemplate->msg( $exists ? 'ep-tab-edit' : 'ep-tab-create' )->text(),
					'href' => $title->getLocalURL( [ 'action' => 'edit' ] )
				];
			}

			if ( $user->isAllowed( $page->getEditRight() ) && $exists ) {
				$links['actions']['delete'] = [
					'class' => $type === 'delete' ? 'selected' : false,
					'text' => $sktemplate->msg( 'ep-tab-delete' )->text(),
					'href' => $title->getLocalURL( [ 'action' => 'delete' ] )
				];
			}

			if ( $exists ) {
				$links['views']['history'] = [
					'class' => $type === 'history' ? 'selected' : false,
					'text' => $sktemplate->msg( 'ep-tab-history' )->text(),
					'href' => $title->getLocalURL( [ 'action' => 'history' ] )
				];

				if ( Utils::isCourse( $title ) ) {
					$links['views']['activity'] = [
						'class' => $type === 'epcourseactivity' ? 'selected' : false,
						'text' => $sktemplate->msg( 'ep-tab-activity' )->text(),
						'href' => $title->getLocalURL( [ 'action' => 'epcourseactivity' ] )
					];

					$student = Student::newFromUser( $user );
					$hasCourse = $student !== false &&
						$student->hasCourse( [ 'title' => $title->getText() ] );

					if ( $user->isAllowed( 'ep-enroll' ) && !$user->isBlocked() ) {
						if ( !$hasCourse &&
							Courses::singleton()->hasActiveTitle( $title->getText() )
						) {
							$links['views']['enroll'] = [
								'class' => $isSpecial ? 'selected' : false,
								'text' => $sktemplate->msg( 'ep-tab-enroll' )->text(),
								'href' => \SpecialPage::getTitleFor( 'Enroll', $title->getText() )
									->getLocalURL()
							];
						}
					}

					if ( $hasCourse && Courses::singleton()->hasActiveTitle( $title->getText() ) ) {
						$links[$isSpecial ? 'views' : 'actions']['disenroll'] = [
							'class' => $isSpecial ? 'selected' : false,
							'text' => $sktemplate->msg( 'ep-tab-disenroll' )->text(),
							'href' => \SpecialPage::getTitleFor( 'Disenroll', $title->getText() )
								->getLocalURL()
						];
					}
				}
			}
		}
	}

	/**
	 * Override the isKnown check for course and institution pages,
	 * so they don't all show up as redlinks.
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/TitleIsAlwaysKnown
	 *
	 * @since 0.1
	 *
	 * @param Title $title
	 * @param bool|null &$isKnown
	 */
	public static function onTitleIsAlwaysKnown( Title $title, &$isKnown ) {
		if ( $title->getNamespace() == EP_NS ) {
			if ( Utils::isCourse( $title ) ) {
				$class = 'EducationProgram\Courses';
			} else {
				$class = 'EducationProgram\Orgs';
			}

			$identifier = $title->getText();

			$isKnown = $class::singleton()->hasIdentifier( $identifier );
		}
	}

	public static function onMovePageIsValidMove(
		Title $oldTitle, Title $newTitle, \Status $status
	) {
		$nss = [ EP_NS, EP_NS_TALK ];
		$allowed = !in_array( $oldTitle->getNamespace(), $nss ) &&
			!in_array( $newTitle->getNamespace(), $nss );

		if ( !$allowed ) {
			$status->fatal( 'ep-move-error' );
		}

		return $allowed;
	}

	/**
	 * Allows canceling the move of one title to another.
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/AbortMove
	 *
	 * @since 0.1
	 *
	 * @param Title $oldTitle
	 * @param Title $newTitle
	 * @param User $user
	 * @param string &$error
	 * @param string $reason
	 *
	 * @return bool
	 */
	public static function onAbortMove(
		Title $oldTitle, Title $newTitle, User $user, &$error, $reason
	) {
		$status = new \Status();
		self::onMovePageIsValidMove( $oldTitle, $newTitle, $status );
		if ( !$status->isOK() ) {
			$error = $status->getHTML();
		}

		return $status->isOK();
	}

	/**
	 * Allows overriding if the pages in a certain namespace can be moved or not.
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/NamespaceIsMovable
	 *
	 * @since 0.1
	 *
	 * @param int $index
	 * @param bool &$movable
	 */
	public static function onNamespaceIsMovable( $index, &$movable ) {
		if ( in_array( $index, [ EP_NS, EP_NS_TALK ] ) ) {
			$movable = false;
		}
	}

	/**
	 * Called when a revision was inserted due to an edit.
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/NewRevisionFromEditComplete
	 *
	 * @since 0.1
	 *
	 * @param Page $article
	 * @param Revision $rev
	 * @param int $baseID
	 * @param User $user
	 */
	public static function onNewRevisionFromEditComplete(
		Page $article, Revision $rev, $baseID, User $user
	) {
		\DeferredUpdates::addCallableUpdate( function () use ( $article, $rev, $user ) {
			$dbw = wfGetDB( DB_MASTER );

			// TODO: properly inject dependencies
			$courseFinder = new UPCUserCourseFinder( $dbw );
			$eventCreator = new EditEventCreator( $courseFinder );

			$events = $eventCreator->getEventsForEdit( $article, $rev, $user );
			if ( $events ) {
				$eventStore = new EventStore( 'ep_events' );

				$dbw->startAtomic( __METHOD__ );
				foreach ( $events as $event ) {
					$eventStore->insertEvent( $event );
				}
				$dbw->endAtomic( __METHOD__ );
			}
		} );
	}

	/**
	 * Intercept the generation of Special:Contributions output. If the user
	 * whose contributions are displayed particaptes in a course (as a
	 * student, instructor or volunteer), we add a message about said
	 * participation.
	 *
	 * @param int $id the id of the user whose contributions are displayed
	 * @param User $user
	 * @param \SpecialPage $sp
	 */
	public static function onSpecialContributionsBeforeMainOutput(
		$id, User $user, \SpecialPage $sp
	) {
		if ( $user->isAnon() ) {
			// bug 66624, db schema can't handle anon users
			return;
		}

		$userRolesMessage = new UserRolesMessage( $user->getId(), $sp->getOutput() );
		$userRolesMessage->prepare();

		if ( $userRolesMessage->userHasRoles() ) {
			$userRolesMessage->output();
		}
	}

	/**
	 * Register Echo notification types and categories. (Just hands off to the
	 * NotificationsManager for the actual work.)
	 *
	 * @see https://www.mediawiki.org/wiki/Extension:Echo/BeforeCreateEchoEvent
	 *
	 * @since 0.4 alpha
	 *
	 * @param array &$notifications
	 * @param array &$notificationCategories
	 * @param array &$icons
	 */
	public static function onBeforeCreateEchoEvent(
		array &$notifications,
		array &$notificationCategories,
		array &$icons
	) {
		Extension::globalInstance()->getNotificationsManager()->
			setUpTypesAndCategories(
			$notifications, $notificationCategories, $icons );
	}

	/**
	 * Determine which users get a notification. (Just hands off to the
	 * NotificationsManager for the actual work.)
	 *
	 * @see https://www.mediawiki.org/wiki/Echo_(Notifications)/Developer_guide
	 *
	 * @since 0.4 alpha
	 *
	 * @param EchoEvent $event
	 * @param array &$users
	 */
	public static function onEchoGetDefaultNotifiedUsers(
		EchoEvent $event,
		array &$users
	) {
		Extension::globalInstance()->getNotificationsManager()->
			getUsersNotified( $event, $users );
	}

	/**
	 * Check for changes to course talk pages, possibly trigger a notification.
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/PageContentSaveComplete
	 */
	public static function onPageContentSaveComplete(
		$article,
		$user,
		$content,
		$summary,
		$isMinor,
		$isWatch,
		$section,
		$flags,
		$revision,
		$status,
		$baseRevId
	) {
		$title = $article->getTitle();

		// check if the page saved was a course in the EP talk namespace
		if ( $title->getNamespace() === EP_NS_TALK
			&& Utils::isCourse( $title )
		) {
			// Send an event to the notifications manager. Note that there are
			// additional checks that will be peformed further along before a
			// notification is actually sent.
			Extension::globalInstance()->getNotificationsManager()->trigger(
				'ep-course-talk-notification',
				[
					'course-talk-title' => $title,
					'agent' => $user,
					'revision' => $revision,
				]
			);
		}
	}

	/**
	 * Make sure cldr extension is loaded.
	 *
	 * Will die() if CountryNames PHP class is not found.
	 *
	 * @since 0.5 alpha
	 */
	public static function onSetupAfterCache() {
		global $wgAutoloadClasses;

		// No version constant to check against :/
		if ( !array_key_exists( 'CountryNames', $wgAutoloadClasses ) ) {
			die( '<strong>Error:</strong> Education Program depends on the ' .
				'<a href="https://www.mediawiki.org/wiki/Extension:CLDR">CLDR</a> extension.' );
		}
	}

	/**
	 * Let UserMerge know which of our tables need updating
	 *
	 * @param array &$fields
	 *
	 * @since 0.5.0 alpha
	 */
	public static function onUserMergeAccountFields( array &$fields ) {
		// Omitting the ep_users_per_course table, since that will be updated
		// automatically by Course::save(), called from onMergeAccountFromTo().

		// array( tableName, idField, textField, 'options' => array() )
		$fields[] = [ 'ep_articles', 'article_user_id', 'options' => [ 'IGNORE' ] ];
		$fields[] = [ 'ep_students', 'student_user_id', 'options' => [ 'IGNORE' ] ];
		$fields[] = [ 'ep_instructors', 'instructor_user_id',  'options' => [ 'IGNORE' ] ];
		$fields[] = [ 'ep_cas', 'ca_user_id', 'options' => [ 'IGNORE' ] ];
		$fields[] = [ 'ep_oas', 'oa_user_id', 'options' => [ 'IGNORE' ] ];
		$fields[] = [ 'ep_events', 'event_user_id' ];
		$fields[] = [ 'ep_revisions', 'rev_user_id', 'rev_user_text' ];
	}

	/**
	 * If the above tables had unique key conflicts, just delete the conflicting
	 * rows.
	 *
	 * @param array &$tables
	 *
	 * @since 0.5.0 alpha
	 */
	public static function onUserMergeAccountDeleteTables( array &$tables ) {
		$tables += [
			'ep_articles' => 'article_user_id',
			'ep_users_per_course' => 'upc_user_id',
			'ep_students' => 'student_user_id',
			'ep_instructors' => 'instructor_user_id',
			'ep_cas' => 'ca_user_id',
			'ep_oas' => 'oa_user_id',
		];
	}

	/**
	 * @param User &$oldUser
	 * @param User $newUser
	 * @return bool
	 *
	 * @since 0.5.0 alpha
	 */
	public static function onMergeAccountFromTo( &$oldUser, $newUser ) {
		$oldId = $oldUser->getId();
		$newId = $newUser->getId();

		$dbw = wfGetDB( DB_MASTER );

		// Get all the courses (including inactive) that the old user had roles in.
		// This will return only unique values.
		$userCourseFinder = new UPCUserCourseFinder( $dbw );

		$courseIds = $userCourseFinder->getCoursesForUsers( $oldId );

		// Fields with arrays of the ids of users that have a role in a course
		$roleFields = [
				'students',
				'online_ambs',
				'campus_ambs',
				'instructors'
		];

		// A function to usermerge in an array of ids. Returns true if there
		// were changes.
		$mergeUserIds = function ( &$ids ) use ( $oldId, $newId ) {
			$i = array_search( $oldId, $ids );

			if ( $i !== false ) {
				$ids[$i] = $newId;
				$ids = array_unique( $ids );
				return true;
			}

			return false;
		};

		foreach ( $courseIds as $courseId ) {
			// Fetch the course
			$course = Courses::singleton()->selectRow(
					null, [ 'id' => $courseId ] );

			// Sanity check
			if ( !$course ) {
				continue;
			}

			// Go through the role fields, update the user id, de-dupe and save
			foreach ( $roleFields as $roleField ) {
				$ids = $course->getField( $roleField );

				if ( $mergeUserIds( $ids ) ) {
					$course->setField( $roleField, $ids );
				}
			}

			// At least one of the fields should have changed, so save.
			// This will the update ep_users_per_course table and
			// summary fields in ep_orgs
			$course->save();
		}

		// We'll merge reviewers in a batch job just in case there are a lot
		// of rows to change (unlikely but not impossible).
		$job = new UserMergeArticleReviewersJob(
			Title::newFromText( 'EducationProgram UserMerge reviewers/' . uniqid() ),
			[
				'oldUserId' => $oldId,
				'newUserId' => $newId
				]
			);

		JobQueueGroup::singleton()->push( $job );

		return true;
	}
}
