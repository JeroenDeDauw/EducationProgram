<?php

/**
 * Static class with utility functions for the Education Program extension.
 *
 * @since 0.1
 *
 * @file EPUtils.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
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
		return array_merge(
			array( '' => '' ),
			self::getValuesAppendedKeys( CountryNames::getNames( $langCode ) )
		);
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
		return array_merge(
			array( '' => '' ),
			self::getValuesAppendedKeys( LanguageNames::getNames( $langCode ) )
		);
	}

	/**
	 * Returns the array but with each key postfixed by the value and the value replaced by the original key.
	 *
	 * @since 0.1
	 *
	 * @param array $list
	 *
	 * @return array
	 */
	public static function getValuesAppendedKeys( array $list ) {
		return array_combine(
			array_map(
				function( $value, $key ) {
					return $key . ' - ' . $value;
				} ,
				array_values( $list ),
				array_keys( $list )
			),
			array_keys( $list )
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
	 * Displays any epsuccess or epfail message and then clears the session value so it does not get displayed again.
	 * Should typically be called before anything else is outputted.
	 *
	 * @since 0.1
	 *
	 * @param IContextSource $context
	 */
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

	/**
	 * Takes a number of seconds and turns it into a text using values such as hours and minutes.
	 * Compatibility method. As of MW 1.20, this can be found at Language::formatDuration
	 *
	 * @since 0.1
	 *
	 * @param integer $seconds The amount of seconds.
	 * @param array $chosenIntervals The intervals to enable.
	 *
	 * @return string
	 */
	public static function formatDuration( $seconds, array $chosenIntervals = array() ) {
		global $wgLang;

		if ( method_exists( $wgLang, 'formatDuration' ) ) {
			return $wgLang->formatDuration( $seconds, $chosenIntervals );
		}

		$intervals = array(
			'millennia' => 1000 * 31557600,
			'centuries' => 100 * 31557600,
			'decades' => 10 * 31557600,
			'years' => 31557600, // 86400 * 365.25
			'weeks' => 604800,
			'days' => 86400,
			'hours' => 3600,
			'minutes' => 60,
			'seconds' => 1,
		);

		if ( empty( $chosenIntervals ) ) {
			$chosenIntervals = array( 'millennia', 'centuries', 'decades', 'years', 'days', 'hours', 'minutes', 'seconds' );
		}

		$intervals = array_intersect_key( $intervals, array_flip( $chosenIntervals ) );
		$sortedNames = array_keys( $intervals );
		$smallestInterval = array_pop( $sortedNames );

		$segments = array();

		foreach ( $intervals as $name => $length ) {
			$value = floor( $seconds / $length );

			if ( $value > 0 || ( $name == $smallestInterval && empty( $segments ) ) ) {
				$seconds -= $value * $length;
				$message = new Message( 'duration-' . $name, array( $value ) );
				$segments[] = $message->inLanguage( $wgLang )->escaped();
			}
		}

		return $wgLang->listToText( $segments );
	}

	/**
	 * Returns if the provided title is for a course, assuming it is in the EP_NS namespace.
	 *
	 * @since 0.2
	 *
	 * @param string|Title $title
	 *
	 * @return boolean
	 */
	public static function isCourse( $title ) {
		if ( $title instanceof Title ) {
			$title = $title->getFullText();
		}

		return in_string( '/', $title );
	}

}
