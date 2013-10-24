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
		$req = $this->getRequest();
		$user = $this->getUser();
		$courseId = $req->getInt( 'course-id' );
		$studentUserId = $req->getInt( 'student-user-id' );

		$salt = 'addarticle' . $courseId . $studentUserId;
		$title = \Title::newFromText( $req->getText( 'addarticlename' ) );

		// TODO: some kind of warning when entering invalid title
		if ( $user->matchEditToken( $req->getText( 'token' ), $salt ) && !is_null( $title ) ) {

			// TODO: migrate into ArticleAdder
			$course = Courses::singleton()->selectRow(
				array( 'students', 'name' ),
				array( 'id' => $courseId )
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
