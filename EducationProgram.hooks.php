<?php

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
final class EPHooks {

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
			dirname( __FILE__ ) . '/sql/EducationProgram.sql'
		);

		$updater->addExtensionField(
			'ep_courses',
			'course_title',
			dirname( __FILE__ ) . '/sql/AddCourseTitleField.sql'
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
		$testFiles = array(
			'EPUtils',

			'tables/EPOrgs',
		);

		foreach ( $testFiles as $file ) {
			$files[] = dirname( __FILE__ ) . '/tests/phpunit/' . $file . 'Test.php';
		}

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
	 * @return bool
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
	 * @return bool
	 */
	public static function onGetPreferences( User $user, array &$preferences ) {
		wfProfileIn( __METHOD__ );
		if ( EPSettings::get( 'enableTopLink' ) ) {
			$preferences['ep_showtoplink'] = array(
				'type' => 'toggle',
				'label-message' => 'ep-prefs-showtoplink',
				'section' => 'education',
			);
		}

		if ( EPSettings::get( 'enableDykSetting' ) ) {
			$preferences['ep_showdyk'] = array(
				'type' => 'toggle',
				'label-message' => 'ep-prefs-showdyk',
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
	 * @param Article|null $article
	 *
	 * @return bool
	 */
	public static function onArticleFromTitle( Title &$title, &$article ) {
		if ( $title->getNamespace() == EP_NS ) {
			$article = EPPage::factory( $title );
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
			$class = EPUtils::isCourse( $title ) ? 'EPCourses' : 'EPOrgs';
			$exists = $class::singleton()->hasIdentifier( $title->getText() );
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

				if ( EPUtils::isCourse( $title ) ) {
					$student = EPStudent::newFromUser( $user );
					$hasCourse = $student !== false && $student->hasCourse( array( 'title' => $title->getText() ) );

					if ( $user->isAllowed( 'ep-enroll' ) ) {
						if ( !$hasCourse && EPCourses::singleton()->hasActiveTitle( $title->getText() ) ) {
							$links['views']['enroll'] = array(
								'class' => $isSpecial ? 'selected' : false,
								'text' => wfMsg( 'ep-tab-enroll' ),
								'href' => SpecialPage::getTitleFor( 'Enroll', $title->getText() )->getLocalURL()
							);
						}
					}

					if ( $hasCourse && EPCourses::singleton()->hasActiveTitle( $title->getText() ) ) {
						$links[$isSpecial ? 'views' : 'actions']['disenroll'] = array(
							'class' => $isSpecial ? 'selected' : false,
							'text' => wfMsg( 'ep-tab-disenroll' ),
							'href' => SpecialPage::getTitleFor( 'Disenroll', $title->getText() )->getLocalURL()
						);
					}
				}
			}
		}
		wfProfileIn( __METHOD__ );
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
			if ( EPUtils::isCourse( $title ) ) {
				$class = 'EPCourses';
			}
			else {
				$class = 'EPOrgs';
			}

			$identifier = $title->getText();

			$isKnown = $class::singleton()->hasIdentifier( $identifier );
		}

		wfProfileOut( __METHOD__ );
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
		$nss = array( EP_NS, EP_NS_TALK );
		$allowed = !in_array( $oldTitle->getNamespace(), $nss ) && !in_array( $newTitle->getNamespace(), $nss );

		if ( !$allowed ) {
			$error = wfMsg( 'ep-move-error' );
		}

		return $allowed;
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
	 * @param Article $article
	 * @param Revision $rev
	 * @param integer $baseID
	 * @param User $user
	 *
	 * @return bool
	 */
	public static function onNewRevisionFromEditComplete( $article, Revision $rev, $baseID, User $user ) {
		if ( !$user->isLoggedIn() ) {
			return true;
		}
		wfProfileIn( __METHOD__ );
		$namespace = $article->getTitle()->getNamespace();

		if ( !in_array( $namespace, array( NS_MAIN, NS_TALK, NS_USER, NS_USER_TALK ) ) ) {
			wfProfileOut( __METHOD__ );
			return true;
		}
		wfProfileIn( __METHOD__ . '-ns' );
		$conds = array( 'upc_user_id' => $user->getId() );

		$upc = wfGetDB( DB_SLAVE )->select(
			array( 'ep_users_per_course', 'ep_courses' ),
			array( 'upc_course_id' ),
			array_merge( $conds, EPCourses::getStatusConds( 'current', true ) ),
			__METHOD__,
			array( 'DISTINCT' ),
			array(
				'ep_courses' => array( 'INNER JOIN', array( 'upc_course_id=course_id' ) ),
			)
		);

		$hasCourses = $upc->numRows() !== 0;
		wfProfileOut( __METHOD__ . '-ns' );

		if ( $hasCourses ) {
			wfProfileIn( __METHOD__ . '-courses' );
			if ( !is_null( $rev->getTitle() ) ) {
				$event = EPEvent::newFromRevision( $rev, $user );
			}

			$dbw = wfGetDB( DB_MASTER );

			$dbw->begin();

			if ( !is_null( $rev->getTitle() ) ) {
				while ( $link = $upc->fetchObject() ) {
					$eventForCourse = clone $event;
					$eventForCourse->setField( 'course_id', $link->upc_course_id );
					$eventForCourse->save();
				}
			}

			if ( in_array( $namespace, array( NS_MAIN, NS_TALK ) ) ) {
				$student = EPStudent::newFromUserId( $user->getId(), true );

				$student->setFields( array(
					'last_active' => wfTimestampNow()
				) );

				$student->save();
			}

			$dbw->commit();
			wfProfileIn( __METHOD__ . '-courses' );
		}
		wfProfileOut( __METHOD__ );
		return true;
	}

}
