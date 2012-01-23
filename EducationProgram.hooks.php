<?php

/**
 * Static class for hooks handled by the Education Program extension.
 *
 * @since 0.1
 *
 * @file EducationProgram.hooks.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v3+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
final class EPHooks {

	/**
	 * Schema update to set up the needed database tables.
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/LoadExtensionSchemaUpdates
	 *
	 * @since 0.1
	 *
	 * @param DatabaseUpdater $updater
	 *
	 * @return true
	 */
	public static function onSchemaUpdate( DatabaseUpdater $updater ) {
		$updater->addExtensionTable(
			'ep_orgs',
			dirname( __FILE__ ) . '/sql/EducationProgram.sql'
		);

		$updater->addExtensionUpdate( array(
			'addField',
			'ep_courses',
			'course_name',
			dirname( __FILE__ ) . '/sql/AddExtraFields.sql',
			true
		) );

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
	 * @return true
	 */
	public static function registerUnitTests( array &$files ) {
		$testDir = dirname( __FILE__ ) . '/test/';

		return true;
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
	 * @return true
	 */
	public static function onPersonalUrls( array &$personal_urls, Title &$title ) {
		if ( EPSettings::get( 'enableTopLink' ) ) {
			global $wgUser;

			// Find the watchlist item and replace it by the my contests link and itself.
			if ( $wgUser->isLoggedIn() && $wgUser->getOption( 'ep_showtoplink' ) ) {
				$url = SpecialPage::getTitleFor( 'MyCourses' )->getLinkUrl();
				$myCourses = array(
					'text' => wfMsg( 'ep-toplink' ),
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
	 * @return true
	 */
	public static function onGetPreferences( User $user, array &$preferences ) {
		if ( EPSettings::get( 'enableTopLink' ) ) {
			$preferences['ep_showtoplink'] = array(
				'type' => 'toggle',
				'label-message' => 'ep-prefs-showtoplink',
				'section' => 'education',
			);
		}

		return true;
	}
	
	/**
	 * Handles formatting of log entries for MediaWiki 1.18.x. 
	 * 
	 * @since 0.1
	 * 
	 * @param string $type
	 * @param string $action
	 * @param Title $title
	 * @param boolean|null $forUI
	 * @param array $params
	 * 
	 * @return string
	 */
	public static function formatLogEntry( $type, $action, Title $title, $forUI, array $params ) {
		global $wgContLang, $wgLang;

		$message = wfMessage( 'logentry-' . $type . '-' . $action );

		$message = call_user_func_array(
			array( $message, 'params' ),
			array_merge(
				array(
					'', // User link in the new system
					'#', // User name for gender in the new system
					Message::rawParam( $forUI ? Linker::link( $title ) : $title->getPrefixedText() )
				),
				$params
			)
		);

		return $message->inLanguage( $forUI === null ? $wgContLang : $wgLang )->text();
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
	 * @return true
	 */
	public static function onSpecialPageTabs( SkinTemplate &$sktemplate, array &$links ) {
		$viewLinks = $links['views'];

		// The Title getBaseText and getSubpageText methods only do what we want when
		// the special pages NS is in teh list of NS with subpages.
		$textParts = SpecialPageFactory::resolveAlias( $sktemplate->getTitle()->getText() );
		$baseText = $textParts[0];

		$specials = array(
			array(
				'view' => 'Institution',
				'edit' => 'EditInstitution',
				//'history' => 'InstitutionHistory',
			),
			array(
				'view' => 'MasterCourse',
				'edit' => 'EditMasterCourse',
				//'history' => 'MasterCourseHistory',
			),
			array(
				'view' => 'Course',
				'edit' => 'EditCourse',
				//'history' => 'CourseHistory',
				'enroll' => 'Enroll',
			),
		);

		$editRights = array(
			'EditInstitution' => 'ep-org',
			'EditMasterCourse' => 'ep-mc',
			'EditCourse' => 'ep-course',
		);

		$classes = array(
			'Institution' => 'EPOrg',
			'Course' => 'EPCourse',
			'MasterCourse' => 'EPMC',
		);

		$specialSet = false;
		$type = false;

		foreach ( $specials as $set ) {
			if ( in_array( $baseText, $set ) ) {
				$specialSet = $set;
				$flipped = array_flip( $set );
				$type = $flipped[$baseText];
				break;
			}
		}

		// TODO: messages
		if ( $specialSet !== false ) {
			$canonicalSet = $specialSet;

			foreach ( $specialSet as &$special ) {
				$special = SpecialPageFactory::getLocalNameFor( $special );
			}

			$identifier = $canonicalSet['view'] === 'Course' ? 'id' : 'name';
			$exists = $classes[$canonicalSet['view']]::has( array( $identifier => $textParts[1] ) );

			$viewLinks['view'] = array(
				'class' => $type === 'view' ? 'selected' : false,
				'text' => wfMsg( 'ep-tab-view' ),
				'href' => SpecialPage::getTitleFor( $specialSet['view'], $textParts[1] )->getLocalUrl()
			);

			if ( $exists ) {
				if ( $sktemplate->getUser()->isAllowed( $editRights[$canonicalSet['edit']] ) ) {
					$viewLinks['edit'] = array(
						'class' => $type === 'edit' ? 'selected' : false,
						'text' => wfMsg( 'ep-tab-edit' ),
						'href' => SpecialPage::getTitleFor( $specialSet['edit'], $textParts[1] )->getLocalUrl()
					);
				}

				$viewLinks['history'] = array(
					'class' => $type === 'history' ? 'selected' : false,
					'text' => wfMsg( 'ep-tab-history' ),
					'href' => '' // TODO
					//SpecialPage::getTitleFor( $specialSet['history'], $textParts[1] )->getLocalUrl()
				);

				if ( $canonicalSet['view'] === 'Course' ) {
					$user = $sktemplate->getUser();

					if ( $user->isAllowed( 'ep-enroll' ) ) {
						$student = EPStudent::newFromUser( $user );

						if ( $student === false || !$student->hasCourse( array( 'id' => $textParts[1] ) ) ) {
							$viewLinks['enroll'] = array(
								'class' => $type === 'enroll' ? 'selected' : false,
								'text' => wfMsg( 'ep-tab-enroll' ),
								'href' => SpecialPage::getTitleFor( 'Enroll', $textParts[1] )->getLocalUrl()
							);
						}
					}
				}
			}
		}

		$links['views'] = $viewLinks;

		return true;
	}

}
