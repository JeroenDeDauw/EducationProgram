<?php

/**
 * Static class with utility functions for the Education Program extension.
 *
 * @since 0.1
 *
 * @file EPUtils.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v3 or later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EPUtils {

	/**
	 * Create a log entry using the provided info.
	 * Takes care about the logging interface changes in MediaWiki 1.19.
	 * 
	 * @since 0.1
	 * 
	 * @param array $info
	 */
	public static function log( array $info ) {
		$user = array_key_exists( 'user', $info ) ? $info['user'] : $GLOBALS['wgUser'];
		
		$logEntry = new ManualLogEntry( $info['type'], $info['subtype'] );

		$logEntry->setPerformer( $user );
		$logEntry->setTarget( $info['title'] );
		
		if ( array_key_exists( 'comment', $info ) ) {
			$logEntry->setComment( $info['comment'] );
		}
		
		if ( array_key_exists( 'parameters', $info ) ) {
			$logEntry->setParameters( $info['parameters'] );
		}

		$logid = $logEntry->insert();
		$logEntry->publish( $logid );
	}
	
	/**
	 * Returns a list of country names that can be used by
	 * a select input localized in the lang of which the code is provided.
	 * 
	 * @since 0.1
	 * 
	 * @param string $langCode
	 * 
	 * @return array
	 */
	public static function getCountryOptions( $langCode ) {
		return self::getKeyPrefixedValues( CountryNames::getNames( $langCode ) );
	}

	/**
	 * Returns a list of language names that can be used by
	 * a select input localized in the lang of which the code is provided.
	 *
	 * @since 0.1
	 *
	 * @param string $langCode
	 *
	 * @return array
	 */
	public static function getLanguageOptions( $langCode ) {
		return self::getKeyPrefixedValues( LanguageNames::getNames( $langCode ) );
	}

	/**
	 * Returns the array but with each value prefixed by it's provided key.
	 *
	 * @since 0.1
	 *
	 * @param array $list
	 *
	 * @return array
	 */
	protected static function getKeyPrefixedValues( array $list ) {
		return array_merge(
			array( '' => '' ),
			array_combine(
				array_map(
					function( $value, $key ) {
						return $key . ' - ' . $value;
					} ,
					array_values( $list ),
					array_keys( $list )
				),
				array_keys( $list )
			)
		);
	}
	
	/**
	 * Returns the tool links for this ambassador.
	 * 
	 * @since 0.1
	 * 
	 * @param EPIRole $role
	 * @param IContextSource $context
	 * @param EPCourse|null $course
	 * 
	 * @return string
	 */
	public static function getRoleToolLinks( EPIRole $role, IContextSource $context, EPCourse $course = null ) {
		$roleName = $role->getRoleName();
		$links = array();

		$user = $role->getUser();

		if ( !is_null( $course ) &&
			( $context->getUser()->isAllowed( 'ep-' . $roleName ) || $user->getId() == $context->getUser()->getId() ) ) {
			$links[] = Html::element(
				'a',
				array(
					'href' => '#',
					'class' => 'ep-remove-role',
					'data-role' => $roleName,
					'data-courseid' => $course->getId(),
					'data-coursename' => $course->getField( 'name' ),
					'data-userid' => $user->getId(),
					'data-username' => $user->getName(),
					'data-bestname' => $role->getName(),
				),
				wfMsg( 'ep-' . $roleName . '-remove' )
			);
			
			$context->getOutput()->addModules( 'ep.enlist' );
		}
		
		return self::getToolLinks( $user->getId(), $user->getName(), $context, $links );
	}

	/**
	 * Returns tool links for the provided user details plus any adittional links.
	 *
	 * @since 0.1
	 *
	 * @param integer $userId
	 * @param string $userName
	 * @param IContextSource $context
	 * @param array $extraLinks
	 *
	 * @return string
	 */
	public static function getToolLinks( $userId, $userName, IContextSource $context, array $extraLinks = array() ) {
		$links = array();

		$links[] = Linker::userTalkLink( $userId, $userName );

		$links[] = Linker::link( SpecialPage::getTitleFor( 'Contributions', $userName ), wfMsgHtml( 'contribslink' ) );

		return ' <span class="mw-usertoollinks">(' . $context->getLanguage()->pipeList( array_merge( $links, $extraLinks ) ) . ')</span>';
	}
	
	/**
	 * Adds a navigation menu with the provided links.
	 * Links should be provided in an array with:
	 * label => Title (object)
	 * 
	 * @since 0.1
	 * 
	 * @param IContextSource $context
	 * @param array $items
	 */
	public static function displayNavigation( IContextSource $context, array $items = array() ) {
		$links = array();

		foreach ( $items as $label => $data ) {
			if ( is_array( $data ) ) {
				$target = array_shift( $data );
				$attribs = $data;
			}
			else {
				$target = $data;
				$attribs = array();
			}

			$links[] = Linker::link(
				$target,
				htmlspecialchars( $label ),
				$attribs
			);
		}

		$context->getOutput()->addHTML(
			Html::rawElement( 'p', array(), $context->getLanguage()->pipeList( $links ) )
		);
	}
	
	/**
	 * Returns the default nav items for @see displayNavigation.
	 *
	 * @since 0.1
	 *
	 * @param  IContextSource $context
	 *
	 * @return array of Title
	 */
	public static function getDefaultNavigationItems( IContextSource $context ) {
		$items = array(
			wfMsg( 'ep-nav-orgs' ) => SpecialPage::getTitleFor( 'Institutions' ),
			wfMsg( 'ep-nav-courses' ) => SpecialPage::getTitleFor( 'Courses' ),
		);

		$items[wfMsg( 'ep-nav-students' )] = SpecialPage::getTitleFor( 'Students' );

		$items[wfMsg( 'ep-nav-oas' )] = SpecialPage::getTitleFor( 'OnlineAmbassadors' );

		$items[wfMsg( 'ep-nav-cas' )] = SpecialPage::getTitleFor( 'CampusAmbassadors' );

		$user = $context->getUser();
		
		if ( EPStudents::singleton()->has( array( 'user_id' => $user->getId() ) ) ) {
			$items[wfMsg( 'ep-nav-mycourses' )] = SpecialPage::getTitleFor( 'MyCourses' );
		}
		
		if ( EPOA::newFromUser( $user )->hasCourse() ) {
			$items[wfMsg( 'ep-nav-oaprofile' )] = SpecialPage::getTitleFor( 'OnlineAmbassadorProfile' );
		}
		
		if ( EPCA::newFromUser( $user )->hasCourse() ) {
			$items[wfMsg( 'ep-nav-caprofile' )] = SpecialPage::getTitleFor( 'CampusAmbassadorProfile' );
		}

		return $items;		
	}

	public static function displayResult( IContextSource $context ) {
		$req = $context->getRequest();
		$out = $context->getOutput();

		if ( $req->getSessionData( 'epsuccess' ) ) {
			$out->addHTML(
				'<div class="successbox"><strong><p>' . $req->getSessionData( 'epsuccess' ) . '</p></strong></div>'
					. '<hr style="display: block; clear: both; visibility: hidden;" />'
			);
			$req->setSessionData( 'epsuccess', false );
		}

		if ( $req->getSessionData( 'epfail' ) ) {
			$out->addHTML(
				'<p class="visualClear errorbox">' . $req->getSessionData( 'epfail' ). '</p>'
					. '<hr style="display: block; clear: both; visibility: hidden;" />'
			);
			$req->setSessionData( 'epfail', false );
		}
	}
	/**
	 * Gets the content of the article with the provided page name,
	 * or an empty string when there is no such article.
	 *
	 * @since 0.1
	 *
	 * @param string $pageName
	 *
	 * @return string|false
	 */
	public static function getArticleContent( $pageName ) {
		$title = Title::newFromText( $pageName );

		if ( is_null( $title ) ) {
			return '';
		}

		$article = new Article( $title, 0 );
		return $article->fetchContent();
	}

	public static function formatDuration( $seconds, $units = array( 'days', 'hours', 'minutes', 'seconds' ) ) {
		// TODO: compat
		return $GLOBALS['wgLang']->formatDuration( $seconds, $units );
	}

}
