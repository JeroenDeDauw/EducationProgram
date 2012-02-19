<?php

/**
 * Remove a reviewer from an article-student association.
 *
 * @since 0.1
 *
 * @file EPRemoveReviewerAction.php
 * @ingroup EducationProgram
 * @ingroup Action
 *
 * @licence GNU GPL v3+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EPRemoveReviewerAction extends FormlessAction {

	/**
	 * (non-PHPdoc)
	 * @see Action::getName()
	 */
	public function getName() {
		return 'epremreviewer';
	}

	/**
	 * (non-PHPdoc)
	 * @see FormlessAction::onView()
	 */
	public function onView() {
		$req = $this->getRequest();
		$user = $this->getUser();
		$userIdToRemove = $req->getCheck( 'user-id' ) ? $req->getInt( 'user-id' ) : $user->getId();

		$salt = $userIdToRemove .'remreviewer' . $req->getInt( 'article-id' );

		if ( $user->matchEditToken( $req->getText( 'token' ), $salt )
			&& ( $user->getId() === $userIdToRemove || $user->isAllowed( 'ep-remreviewer' ) ) ) {

			$article = EPArticles::singleton()->selectRow(
				array( 'id', 'reviewers' ),
				array( 'id' => $req->getInt( 'article-id' ) )
			);

			if ( $article !== false ) {
				$removedReviewers = $article->removeReviewers( array( $userIdToRemove ) );

				if ( !empty( $removedReviewers ) ) {
					if ( $article->save() ) {
						$article->logReviewersRemoval( $removedReviewers );
					}
				}
			}
		}

		$this->getOutput()->redirect( $this->getTitle()->getLocalURL() );
		return '';
	}

}
