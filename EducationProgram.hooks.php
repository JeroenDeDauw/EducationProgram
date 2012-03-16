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
			'ep_orgs',
			'org_courses',
			dirname( __FILE__ ) . '/sql/AddCoursesField.sql',
			true
		) );

		$updater->addExtensionUpdate( array(
			'addField',
			'ep_articles',
			'article_page_title',
			dirname( __FILE__ ) . '/sql/AddArticleTitleField.sql',
			true
		) );

		$updater->addExtensionUpdate( array(
			'addField',
			'ep_oas',
			'oa_visible',
			dirname( __FILE__ ) . '/sql/AddAmbVisibleField.sql',
			true
		) );

		$updater->addExtensionUpdate( array(
			'addField',
			'ep_users_per_course',
			'upc_time',
			dirname( __FILE__ ) . '/sql/AddActivityStuff.sql',
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

		//$files[] = $testDir . 'EPTests.php';
		
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
		
		if ( $user->isAllowed( 'ep-bulkdelorgs' ) ) {
			$preferences['ep_bulkdelorgs'] = array(
				'type' => 'toggle',
				'label-message' => 'ep-prefs-bulkdelorgs',
				'section' => 'education',
			);
		}
		
		if ( $user->isAllowed( 'ep-bulkdelcourses' ) ) {
			$preferences['ep_bulkdelcourses'] = array(
				'type' => 'toggle',
				'label-message' => 'ep-prefs-bulkdelcourses',
				'section' => 'education',
			);
		}

		return true;
	}
	
	/**
	 * Called to determine the class to handle the article rendering, based on title.
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ArticleFromTitle
	 * 
	 * @since 0.1
	 * 
	 * @param Title $title
	 * @param Article|null $article
	 * 
	 * @return true
	 */
	public static function onArticleFromTitle( Title &$title, &$article ) {
		if ( $title->getNamespace() == EP_NS_COURSE ) {
			$article = new CoursePage( $title );
		}
		elseif ( $title->getNamespace() == EP_NS_INSTITUTION ) {
			$article = new OrgPage( $title );
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
	 * @return true
	 */
	public static function onCanonicalNamespaces( array &$list ) {
		$list[EP_NS_COURSE] = 'Course';
		$list[EP_NS_INSTITUTION] = 'Institution';
		$list[EP_NS_COURSE_TALK] = 'Course_talk';
		$list[EP_NS_INSTITUTION_TALK] = 'Institution_talk';
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
	 * @return false
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
	 * @return false
	 */
	public static function onSpecialPageTabs( SkinTemplate &$sktemplate, array &$links ) {
		$textParts = SpecialPageFactory::resolveAlias( $sktemplate->getTitle()->getText() );

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

			$title = EPCourses::singleton()->getTitleFor( $textParts[1] );

			if ( !is_null( $title ) ) {
				self::displayTabs( $sktemplate, $links, $title );
			}
		}

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
		$classes = array(
			EP_NS_INSTITUTION => 'EPOrgs',
			EP_NS_COURSE => 'EPCourses',
		);

		$exists = null;
		
		if ( array_key_exists( $title->getNamespace(), $classes ) ) {
			$links['views'] = array();
			$links['actions'] = array();

			$user = $sktemplate->getUser();
			$exists = $classes[$title->getNamespace()]::singleton()->hasIdentifier( $title->getText() );
			$type = $sktemplate->getRequest()->getText( 'action' );
			$isSpecial = $sktemplate->getTitle()->isSpecialPage();

			if ( $exists ) {
				$links['views']['view'] = array(
					'class' => ( !$isSpecial && $type === '' ) ? 'selected' : false,
					'text' => wfMsg( 'ep-tab-view' ),
					'href' => $title->getLocalUrl()
				);
			}
			
			if ( $user->isAllowed( EPPage::factory( $title )->getEditRight() ) ) {
				$links['views']['edit'] = array(
					'class' => $type === 'edit' ? 'selected' : false,
					'text' => wfMsg( $exists ? 'ep-tab-edit' : 'ep-tab-create' ),
					'href' => $title->getLocalUrl( array( 'action' => 'edit' ) )
				);

				if ( $exists ) {
					$links['actions']['delete'] = array(
						'class' => $type === 'delete' ? 'selected' : false,
						'text' => wfMsg( 'ep-tab-delete' ),
						'href' => $title->getLocalUrl( array( 'action' => 'delete' ) )
					);
				}
			}
			
			if ( $exists ) {
				$links['views']['history'] = array(
					'class' => $type === 'history' ? 'selected' : false,
					'text' => wfMsg( 'ep-tab-history' ),
					'href' => $title->getLocalUrl( array( 'action' => 'history' ) )
				);

				if ( $title->getNamespace() === EP_NS_COURSE ) {
					$student = EPStudent::newFromUser( $user );
					$hasCourse = $student !== false && $student->hasCourse( array( 'name' => $title->getText() ) );

					if ( $user->isAllowed( 'ep-enroll' ) ) {
						if ( !$hasCourse && EPCourses::singleton()->hasActiveName( $title->getText() ) ) {
							$links['views']['enroll'] = array(
								'class' => $isSpecial ? 'selected' : false,
								'text' => wfMsg( 'ep-tab-enroll' ),
								'href' => SpecialPage::getTitleFor( 'Enroll', $title->getText() )->getLocalURL()
							);
						}
					}

					if ( $hasCourse && EPCourses::singleton()->hasActiveName( $title->getText() ) ) {
						$links[$isSpecial ? 'views' : 'actions']['disenroll'] = array(
							'class' => $isSpecial ? 'selected' : false,
							'text' => wfMsg( 'ep-tab-disenroll' ),
							'href' => SpecialPage::getTitleFor( 'Disenroll', $title->getText() )->getLocalURL()
						);
					}
				}
			}
		}
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
	 * @return true
	 */
	public static function onTitleIsAlwaysKnown( Title $title, &$isKnown ) {
		if ( in_array( $title->getNamespace(), array( EP_NS_COURSE, EP_NS_INSTITUTION ) ) ) {
			$classes = array(
				EP_NS_COURSE => 'EPCourses',
				EP_NS_INSTITUTION => 'EPOrgs',
			);

			$isKnown = $classes[$title->getNamespace()]::singleton()->hasIdentifier( $title->getText() );
		}

		return true;
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
		$nss = array( EP_NS_COURSE, EP_NS_INSTITUTION, EP_NS_COURSE_TALK, EP_NS_INSTITUTION_TALK );
		$allowed = !in_array( $oldTitle->getNamespace(), $nss ) && !in_array( $newTitle->getNamespace(), $nss );
		
		if ( !$allowed ) {
			$error = wfMsg( 'ep-move-error' );
		}
		
		return $allowed;
	}

	/**
	 * Called when a revision was inserted due to an edit.
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/NewRevisionFromEditComplete
	 *
	 * @since 0.1
	 *
	 * @param weirdStuffButProbablyWikiPage $article
	 * @param Revision $rev
	 * @param integer $baseID
	 * @param User $user
	 *
	 * @return true
	 */
	public static function onNewRevisionFromEditComplete( $article, Revision $rev, $baseID, User $user ) {
		if ( $article->getTitle()->inNamespaces( NS_MAIN, NS_TALK ) ) {
			$studentId = EPStudents::singleton()->selectFieldsRow( 'id', array( 'user_id' => $user->getId() ) );

			if ( $studentId !== false ) {
				$student = EPStudent::newFromUserId( $user->getId() );
				$student->setFields( array(
					'id' => $studentId,
					'last_active' => wfTimestampNow()
				) );
				$student->save();
			}
		}

		return true;
	}
	
}
