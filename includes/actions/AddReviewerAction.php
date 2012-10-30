<?php

namespace EducationProgram;

/**
 * Add a reviewer to an article-student association.
 * Currently only the current user can be added.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 * @ingroup Action
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class AddReviewerAction extends FormlessAction {

	/**
	 * @see Action::getName()
	 */
	public function getName() {
		return 'epaddreviewer';
	}

	/**
	 * @see FormlessAction::onView()
	 */
	public function onView() {
		$req = $this->getRequest();
		$user = $this->getUser();

		$salt = 'addreviewer' . $req->getInt( 'article-id' );

		if ( $user->matchEditToken( $req->getText( 'token' ), $salt ) ) {

			$article = Articles::singleton()->selectRow(
				null,
				array( 'id' => $req->getInt( 'article-id' ) )
			);

			if ( $article !== false && $article->canBecomeReviewer( $user ) ) {
				$addedReviewers = $article->addReviewers( array( $user->getId() ) );

				if ( !empty( $addedReviewers ) ) {
					if ( $article->save() ) {
						$article->logReviewersAdittion( $addedReviewers );
					}
				}
			}
		}

		$this->getOutput()->redirect( $this->getTitle()->getLocalURL() );
		return '';
	}

}
