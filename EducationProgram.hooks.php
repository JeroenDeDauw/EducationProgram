<?php

namespace EducationProgram;

use DatabaseUpdater;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
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

	/**
	 * Schema update to set up the needed database tables.
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/LoadExtensionSchemaUpdates
	 *
	 * @since 0.1
	 *
	 * @param DatabaseUpdater $updater
	 *
	 * @return bool
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

		return true;
	}

	/**
	 * Hook to add PHPUnit test cases.
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/UnitTestsList
	 *
	 * @since 0.1
	 *
	 * @param array $files
	 *
	 * @return bool
	 */
	public static function registerUnitTests( array &$files ) {
		// @codeCoverageIgnoreStart
		$directoryIterator = new RecursiveDirectoryIterator( __DIR__ . '/tests/phpunit/' );

		/**
		 * @var SplFileInfo $fileInfo
		 */
		foreach ( new RecursiveIteratorIterator( $directoryIterator ) as $fileInfo ) {
			if ( substr( $fileInfo->getFilename(), -8 ) === 'Test.php' ) {
				$files[] = $fileInfo->getPathname();
			}
		}

		return true;
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Called after the personal URLs have been set up, before they are shown.
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/PersonalUrls
	 *
	 * @since 0.1
	 *
	 * @param array $personal_urls
	 * @param Title $title
	 *
	 * @return bool
	 */
	public static function onPersonalUrls( array &$personal_urls, Title &$title ) {
		if ( Settings::get( 'enableTopLink' ) ) {
			global $wgUser;

			// Find the watchlist item and replace it by the my contests link and itself.
			if ( $wgUser->isLoggedIn() && $wgUser->getOption( 'ep_showtoplink' ) ) {
				$url = \SpecialPage::getTitleFor( 'MyCourses' )->getLinkUrl();
				$myCourses = array(
					'text' => wfMessage( 'ep-toplink' )->text(),
					'href' => $url,
					'active' => ( $url == $title->getLinkUrl() )
				);

				$insertUrls = array( 'mycourses' => $myCourses );

				$personal_urls = wfArrayInsertAfter( $personal_urls, $insertUrls, 'preferences' );
			}
		}

		return true;
	}

	/**
	 * Adds the preferences of Education Program to the list of available ones.
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/GetPreferences
	 *
	 * @since 0.1
	 *
	 * @param User $user
	 * @param array $preferences
	 *
	 * @return bool
	 */
	public static function onGetPreferences( User $user, array &$preferences ) {
		wfProfileIn( __METHOD__ );
		if ( Settings::get( 'enableTopLink' ) ) {
			$preferences['ep_showtoplink'] = array(
				'type' => 'toggle',
				'label-message' => 'ep-prefs-showtoplink',
				'section' => 'rendering/education',
			);
		}

		if ( $user->isAllowed( 'ep-bulkdelorgs' ) ) {
			$preferences['ep_bulkdelorgs'] = array(
				'type' => 'toggle',
				'label-message' => 'ep-prefs-bulkdelorgs',
				'section' => 'rendering/education',
			);
		}

		if ( $user->isAllowed( 'ep-bulkdelcourses' ) ) {
			$preferences['ep_bulkdelcourses'] = array(
				'type' => 'toggle',
				'label-message' => 'ep-prefs-bulkdelcourses',
				'section' => 'rendering/education',
			);
		}

		wfProfileOut( __METHOD__ );
		return true;
	}

	/**
	 * Called to determine the class to handle the article rendering, based on title.
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ArticleFromTitle
	 *
	 * @since 0.1
	 *
	 * @param Title $title
	 * @param \Article|null $article
	 *
	 * @return bool
	 */
	public static function onArticleFromTitle( Title &$title, &$article ) {
		if ( $title->getNamespace() == EP_NS ) {
			$article = EducationPage::factory( $title );
		}

		return true;
	}

	/**
	 * For extensions adding their own namespaces or altering the defaults.
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/CanonicalNamespaces
	 *
	 * @since 0.1
	 *
	 * @param array $list
	 *
	 * @return bool
	 */
	public static function onCanonicalNamespaces( array &$list ) {
		$list[EP_NS] = 'Education_Program';
		$list[EP_NS_TALK] = 'Education_Program_talk';
		return true;
	}

	/**
	 * Alter the structured navigation links in SkinTemplates.
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/SkinTemplateNavigation
	 *
	 * @since 0.1
	 *
	 * @param SkinTemplate $sktemplate
	 * @param array $links
	 *
	 * @return bool
	 */
	public static function onPageTabs( SkinTemplate &$sktemplate, array &$links ) {
		self::displayTabs( $sktemplate, $links, $sktemplate->getTitle() );

		return false;
	}

	/**
	 * Called on special pages after the special tab is added but before variants have been added.
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/SkinTemplateNavigation::SpecialPage
	 *
	 * @since 0.1
	 *
	 * @param SkinTemplate $sktemplate
	 * @param array $links
	 *
	 * @return bool
	 */
	public static function onSpecialPageTabs( SkinTemplate &$sktemplate, array &$links ) {
		wfProfileIn( __METHOD__ );
		$textParts = \SpecialPageFactory::resolveAlias( $sktemplate->getTitle()->getText() );

		if ( in_array( $textParts[0], array( 'Enroll', 'Disenroll' ) )
			&& !is_null( $textParts[1] ) && trim( $textParts[1] ) !== '' ) {

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

		wfProfileOut( __METHOD__ );
		return false;
	}

	/**
	 * Display the tabs for a course or institution.
	 *
	 * @since 0.1
	 *
	 * @param SkinTemplate $sktemplate
	 * @param array $links
	 * @param Title $title
	 */
	protected static function displayTabs( SkinTemplate &$sktemplate, array &$links, Title $title ) {
		wfProfileIn( __METHOD__ );

		if ( $title->getNamespace() == EP_NS ) {
			$links['views'] = array();
			$links['actions'] = array();

			$user = $sktemplate->getUser();
			$class = Utils::isCourse( $title ) ? 'EducationProgram\Courses' : 'EducationProgram\Orgs';
			$exists = $class::singleton()->hasIdentifier( $title->getText() );
			$type = $sktemplate->getRequest()->getText( 'action' );
			$isSpecial = $sktemplate->getTitle()->isSpecialPage();

			if ( $exists ) {
				$links['views']['view'] = array(
					'class' => ( !$isSpecial && $type === '' ) ? 'selected' : false,
					'text' => $sktemplate->msg( 'ep-tab-view' )->text(),
					'href' => $title->getLocalUrl()
				);
			}

			$page = EducationPage::factory( $title );

			if ( $user->isAllowed( $page->getLimitedEditRight() ) ) {

				$links['views']['edit'] = array(
					'class' => $type === 'edit' ? 'selected' : false,
					'text' => $sktemplate->msg( $exists ? 'ep-tab-edit' : 'ep-tab-create' )->text(),
					'href' => $title->getLocalUrl( array( 'action' => 'edit' ) )
				);
			}

			if ( $user->isAllowed( $page->getEditRight() ) && $exists ) {
				$links['actions']['delete'] = array(
					'class' => $type === 'delete' ? 'selected' : false,
					'text' => $sktemplate->msg( 'ep-tab-delete' )->text(),
					'href' => $title->getLocalUrl( array( 'action' => 'delete' ) )
				);
			}

			if ( $exists ) {
				$links['views']['history'] = array(
					'class' => $type === 'history' ? 'selected' : false,
					'text' => $sktemplate->msg( 'ep-tab-history' )->text(),
					'href' => $title->getLocalUrl( array( 'action' => 'history' ) )
				);

				if ( Utils::isCourse( $title ) ) {
					$links['views']['activity'] = array(
						'class' => $type === 'epcourseactivity' ? 'selected' : false,
						'text' => $sktemplate->msg( 'ep-tab-activity' )->text(),
						'href' => $title->getLocalUrl( array( 'action' => 'epcourseactivity' ) )
					);

					$student = Student::newFromUser( $user );
					$hasCourse = $student !== false && $student->hasCourse( array( 'title' => $title->getText() ) );

					if ( $user->isAllowed( 'ep-enroll' ) && !$user->isBlocked() ) {
						if ( !$hasCourse && Courses::singleton()->hasActiveTitle( $title->getText() ) ) {
							$links['views']['enroll'] = array(
								'class' => $isSpecial ? 'selected' : false,
								'text' => $sktemplate->msg( 'ep-tab-enroll' )->text(),
								'href' => \SpecialPage::getTitleFor( 'Enroll', $title->getText() )->getLocalURL()
							);
						}
					}

					if ( $hasCourse && Courses::singleton()->hasActiveTitle( $title->getText() ) ) {
						$links[$isSpecial ? 'views' : 'actions']['disenroll'] = array(
							'class' => $isSpecial ? 'selected' : false,
							'text' => $sktemplate->msg( 'ep-tab-disenroll' )->text(),
							'href' => \SpecialPage::getTitleFor( 'Disenroll', $title->getText() )->getLocalURL()
						);
					}
				}
			}
		}
		wfProfileOut( __METHOD__ );
	}

	/**
	 * Override the isKnown check for course and institution pages, so they don't all show up as redlinks.
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/TitleIsAlwaysKnown
	 *
	 * @since 0.1
	 *
	 * @param Title $title
	 * @param boolean|null $isKnown
	 *
	 * @return bool
	 */
	public static function onTitleIsAlwaysKnown( Title $title, &$isKnown ) {
		wfProfileIn( __METHOD__ );

		if ( $title->getNamespace() == EP_NS ) {
			if ( Utils::isCourse( $title ) ) {
				$class = 'EducationProgram\Courses';
			}
			else {
				$class = 'EducationProgram\Orgs';
			}

			$identifier = $title->getText();

			$isKnown = $class::singleton()->hasIdentifier( $identifier );
		}

		wfProfileOut( __METHOD__ );
		return true;
	}

	public static function onMovePageIsValidMove( Title $oldTitle, Title $newTitle, \Status $status ) {
		$nss = array( EP_NS, EP_NS_TALK );
		$allowed = !in_array( $oldTitle->getNamespace(), $nss ) && !in_array( $newTitle->getNamespace(), $nss );

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
	 * @param string $error
	 * @param string $reason
	 *
	 * @return boolean
	 */
	public static function onAbortMove( Title $oldTitle, Title $newTitle, User $user, &$error, $reason ) {
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
	 * @param integer $index
	 * @param boolean $movable
	 *
	 * @return boolean
	 */
	public static function onNamespaceIsMovable( $index, &$movable ) {
		if ( in_array( $index, array( EP_NS, EP_NS_TALK ) ) ) {
			$movable = false;
		}

		return true;
	}

	/**
	 * Called when a revision was inserted due to an edit.
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/NewRevisionFromEditComplete
	 *
	 * @since 0.1
	 *
	 * @param Page $article
	 * @param Revision $rev
	 * @param integer $baseID
	 * @param User $user
	 *
	 * @return bool
	 */
	public static function onNewRevisionFromEditComplete( Page $article, Revision $rev, $baseID, User $user ) {
		wfProfileIn( __METHOD__ );

		$dbw = wfGetDB( DB_MASTER );

		// TODO: properly inject dependencies
		$userCourseFinder = new UPCUserCourseFinder( $dbw );
		$eventCreator = new \EducationProgram\Events\EditEventCreator( $dbw, $userCourseFinder );
		$events = $eventCreator->getEventsForEdit( $article, $rev, $user );

		$startOwnStransaction = $dbw->trxLevel() === 0;

		$eventStore = new \EducationProgram\Events\EventStore( 'ep_events' );

		if ( $startOwnStransaction ) {
			$dbw->begin();
		}

		foreach ( $events as $event ) {
			$eventStore->insertEvent( $event );
		}

		if ( $startOwnStransaction ) {
			$dbw->commit();
		}

		wfProfileOut( __METHOD__ );

		return true;
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
	public static function onSpecialContributionsBeforeMainOutput( $id, User $user, \SpecialPage $sp ) {
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
	 * @param array $notifications
	 * @param array $notificationCategories
	 * @param array $icons
	 *
	 * @return boolean
	 */
	public static function onBeforeCreateEchoEvent( array &$notifications,
			array &$notificationCategories, array &$icons ) {

		Extension::globalInstance()->getNotificationsManager()->
			setUpTypesAndCategories(
			$notifications, $notificationCategories, $icons );

		return true;
	}

	/**
	 * Determine which users get a notification. (Just hands off to the
	 * NotificationsManager for the actual work.)
	 *
	 * @see https://www.mediawiki.org/wiki/Echo_(Notifications)/Developer_guide
	 *
	 * @since 0.4 alpha
	 *
	 * @param $event \EchoEvent
	 * @param $users array
	 *
	 * @return boolean
	 */
	public static function onEchoGetDefaultNotifiedUsers(
			\EchoEvent $event, array &$users ) {

		Extension::globalInstance()->getNotificationsManager()->
			getUsersNotified( $event, $users );

		return true;
	}

	/**
	 * Check for changes to course talk pages, possibly trigger a notification.
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/PageContentSaveComplete
	 */
	public static function onPageContentSaveComplete( $article, $user, $content,
			$summary, $isMinor, $isWatch, $section, $flags, $revision, $status,
			$baseRevId ) {

		$title = $article->getTitle();

		// check if the page saved was a course in the EP talk namespace
		if ( ( $title->getNamespace() === EP_NS_TALK ) &&
			( Utils::isCourse( $title ) ) ) {

			// Send an event to the notifications manager. Note that there are
			// additional checks that will be peformed further along before a
			// notification is actually sent.
			Extension::globalInstance()->getNotificationsManager()->trigger(
				'ep-course-talk-notification',
				array(
					'course-talk-title' => $title,
					'agent' => $user,
					'revision' => $revision,
				)
			);
		}

		return true;
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
		if ( !array_key_exists( 'CountryNames', $wgAutoloadClasses ) ) { // No version constant to check against :/
			   die( '<strong>Error:</strong> Education Program depends on the <a href="https://www.mediawiki.org/wiki/Extension:CLDR">CLDR</a> extension.' );
		}

		return true;
	}

	/**
	 * Let UserMerge know which of our tables need updating
	 *
	 * @param array $fields
	 * @return bool
	 *
	 * @since 0.5.0 alpha
	 */
	public static function onUserMergeAccountFields( array &$fields ) {

		// Omitting the ep_users_per_course table, since that will be updated
		// automatically by Course::save(), called from onMergeAccountFromTo().

		// array( tableName, idField, textField, 'options' => array() )
		$fields[] = array( 'ep_articles', 'article_user_id', 'options' => array( 'IGNORE' ) );
		$fields[] = array( 'ep_students', 'student_user_id', 'options' => array( 'IGNORE' ) );
		$fields[] = array( 'ep_instructors', 'instructor_user_id',  'options' => array( 'IGNORE' ) );
		$fields[] = array( 'ep_cas', 'ca_user_id', 'options' => array( 'IGNORE' ) );
		$fields[] = array( 'ep_oas', 'oa_user_id', 'options' => array( 'IGNORE' ) );
		$fields[] = array( 'ep_events', 'event_user_id' );
		$fields[] = array( 'ep_revisions', 'rev_user_id', 'rev_user_text' );

		return true;
	}

	/**
	 * If the above tables had unique key conflicts, just delete the conflicting
	 * rows.
	 *
	 * @param array $tables
	 * @return bool
	 *
	 * @since 0.5.0 alpha
	 */
	public static function onUserMergeAccountDeleteTables( array &$tables ) {
		$tables += array(
			'ep_articles' => 'article_user_id',
			'ep_users_per_course' => 'upc_user_id',
			'ep_students' => 'student_user_id',
			'ep_instructors' => 'instructor_user_id',
			'ep_cas' => 'ca_user_id',
			'ep_oas' => 'oa_user_id',
		);

		return true;
	}

	/**
	 * @param User $oldUser
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

		$courseIds =
			$userCourseFinder->getCoursesForUsers( $oldId, array(), false );

		// Fields with arrays of the ids of users that have a role in a course
		$roleFields = array(
				'students',
				'online_ambs',
				'campus_ambs',
				'instructors'
		);

		// A function to usermerge in an array of ids. Returns true if there
		// were changes.
		$mergeUserIds = function( &$ids ) use ( $oldId, $newId ) {

			$i = array_search( $oldId, $ids );

			if ( $i !== false ) {
				$ids[$i] = $newId;
				$ids = array_unique( $ids );
				return true;
			}

			return false;
		};

		foreach( $courseIds as $courseId ) {

			// Fetch the course
			$course = Courses::singleton()->selectRow(
					null, array( 'id' => $courseId ) );

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
			array(
				'oldUserId' => $oldId,
				'newUserId' => $newId
				)
			);

		JobQueueGroup::singleton()->push( $job );

		return true;
	}
}
