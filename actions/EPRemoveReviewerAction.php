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

		$salt = $req->getInt( 'user-id' ) .'remarticle' . $req->getInt( 'article-id' );

		if ( $user->matchEditToken( $req->getText( 'token' ), $salt )
			&& ( $user->getId() === $req->getInt( 'user-id' ) || $user->isAllowed( 'ep-remreviewer' ) ) ) {

			$article = EPArticles::singleton()->selectRow(
				array( 'id', 'reviewers' ),
				array( 'id' => $req->getInt( 'article-id' ) )
			);

			if ( $article !== false ) {
				$removedReviewers = $article->removeReviewers( array( $req->getInt( 'user-id' ) ) );

				if ( !empty( $removedReviewers ) ) {
					if ( $article->save() ) {
						$article->logReviewersRemoval( $removedReviewers );
					}
				}
			}
		}

		Action::factory( 'view', $this->page, $this->context )->show();
		return '';
	}

}
