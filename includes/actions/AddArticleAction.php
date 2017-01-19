<?php

namespace EducationProgram;

use Title;

/**
 * Add an article-student association.
 * Currently only allows students to associate articles with themselves.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 * @ingroup Action
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class AddArticleAction extends \FormlessAction {

	/**
	 * @see Action::getName()
	 */
	public function getName() {
		return 'epaddarticle';
	}

	/**
	 * @see FormlessAction::onView()
	 */
	public function onView() {
		global $wgServer;
		global $wgArticlePath;

		$req = $this->getRequest();
		$user = $this->getUser();
		$courseId = $req->getInt( 'course-id' );
		$studentUserId = $req->getInt( 'student-user-id' );

		$salt = 'addarticle' . $courseId . $studentUserId;
		$reqArticleName = $req->getText( 'addarticlename' );
		$title = \Title::newFromText( $reqArticleName );

		// If no article was found, test whether the user inputted a URL instead
		// of an article name, and ensure the URL is from this domain.
		if ( is_null( $title ) || !$title->isKnown() ) {

			if ( strpos( $wgServer, "http://" ) !== false ) {
				$serverWithoutProtocol = substr( $wgServer, strlen( "http://" ) );
			} elseif ( strpos( $wgServer, "https://" ) !== false ) {
				$serverWithoutProtocol = substr( $wgServer, strlen( "https://" ) );
			} else {
				$serverWithoutProtocol = substr( $wgServer, strlen( "//" ) );
			}

			// $wgArticlePath is something like "/wiki/$1", so strip off the $1
			$articlePathDelimiter = substr( $wgArticlePath, 0, strlen( $wgArticlePath ) - 2 );
			$serverRegex = "#^(?:http(?:s)?://)?" . $serverWithoutProtocol . $articlePathDelimiter . "(.+)#";
			preg_match( $serverRegex, $reqArticleName, $matches );

			// If the URL regex matched, update the $title
			if ( count( $matches ) ) {
				$title = \Title::newFromText( $matches[1] );
			}
		}

		// TODO: some kind of warning when entering invalid title
		if ( $user->matchEditToken( $req->getText( 'token' ), $salt ) && !is_null( $title ) ) {

			// TODO: migrate into ArticleAdder
			$course = Courses::singleton()->selectRow(
				[ 'students', 'name' ],
				[ 'id' => $courseId ]
			);

			if ( $course !== false && in_array( $studentUserId, $course->getField( 'students' ) ) ) {
				Extension::globalInstance()->newArticleAdder()->addArticle(
					$user,
					$courseId,
					$studentUserId,
					$title->getArticleID(),
					$title->getFullText()
				);
			}
		}

		$returnTo = null;

		if ( $req->getCheck( 'returnto' ) ) {
			$returnTo = Title::newFromText( $req->getText( 'returnto' ) );
		}

		if ( is_null( $returnTo ) ) {
			$returnTo = $this->getTitle();
		}

		$this->getOutput()->redirect( $returnTo->getLocalURL() );
		return '';
	}

}
