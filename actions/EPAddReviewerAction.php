<?php

/**
 * Add a reviewer to an article-student association.
 * Currently only the current user can be added.
 *
 * @since 0.1
 *
 * @file EPAddReviewerAction.php
 * @ingroup EducationProgram
 * @ingroup Action
 *
 * @licence GNU GPL v3+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EPAddReviewerAction extends FormlessAction {

	/**
	 * (non-PHPdoc)
	 * @see Action::getName()
	 */
	public function getName() {
		return 'epaddreviewer';
	}

	/**
	 * (non-PHPdoc)
	 * @see FormlessAction::onView()
	 */
	public function onView() {
		$req = $this->getRequest();
		$user = $this->getUser();

		$salt = 'addreviewer' . $req->getInt( 'article-id' );

		if ( $user->matchEditToken( $req->getText( 'token' ), $salt )
			&& $user->isAllowed( 'ep-bereviewer' ) ) {

			$article = EPArticles::singleton()->selectRow(
				array( 'id', 'reviewers' ),
				array( 'id' => $req->getInt( 'article-id' ) )
			);

			if ( $article !== false ) {
				$addedReviewers = $article->addReviewers( array( $req->getInt( 'user-id' ) ) );

				if ( !empty( $addedReviewers ) ) {
					if ( $article->save() ) {
						$article->logReviewersAdittion( $addedReviewers );
					}
				}
			}
		}

		Action::factory( 'view', $this->page, $this->context )->show();
		return '';
	}

}
