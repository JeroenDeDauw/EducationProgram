<?php

/**
 * Add an article-student association.
 * Currently only allows students to associate articles with themselves.
 *
 * @since 0.1
 *
 * @file EPAddArticleAction.php
 * @ingroup EducationProgram
 * @ingroup Action
 *
 * @licence GNU GPL v3+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EPAddArticleAction extends FormlessAction {

	/**
	 * (non-PHPdoc)
	 * @see Action::getName()
	 */
	public function getName() {
		return 'epaddarticle';
	}

	/**
	 * (non-PHPdoc)
	 * @see FormlessAction::onView()
	 */
	public function onView() {
		$req = $this->getRequest();
		$user = $this->getUser();

		$salt = 'addarticle' . $req->getInt( 'course-id' );
		$title = Title::newFromText( $req->getText( 'addarticlename' ) );

		if ( $user->matchEditToken( $req->getText( 'token' ), $salt ) && !is_null( $title ) && $title->getArticleID() !== 0 ) {
			$course = EPCourses::singleton()->selectRow(
				array( 'students' ),
				array( 'id' => $req->getInt( 'course-id' ) )
			);

			if ( $course !== false && in_array( $user->getId(), $course->getField( 'students' ) ) ) {
				$articleData = array(
					'user_id' => $user->getId(),
					'course_id' => $req->getInt( 'course-id' ),
					'page_id' => $title->getArticleID(),
				);

				if ( !EPArticles::singleton()->has( $articleData ) ) {
					$article = EPArticles::singleton()->newFromArray( $articleData, true );

					if ( $article->save() ) {
						// TODO: log
					}
				}
			}
		}

		Action::factory( 'view', $this->page, $this->context )->show();
		return '';
	}

}
