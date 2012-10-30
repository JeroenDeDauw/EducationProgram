<?php

namespace EducationProgram;

/**
 * Remove a reviewer from an article-student association.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 * @ingroup Action
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class RemoveReviewerAction extends \FormlessAction {

	/**
	 * @see Action::getName()
	 */
	public function getName() {
		return 'epremreviewer';
	}

	/**
	 * @see FormlessAction::onView()
	 */
	public function onView() {
		$req = $this->getRequest();
		$user = $this->getUser();
		$userIdToRemove = $req->getCheck( 'user-id' ) ? $req->getInt( 'user-id' ) : $user->getId();

		$salt = $userIdToRemove .'remreviewer' . $req->getInt( 'article-id' );

		if ( $user->matchEditToken( $req->getText( 'token' ), $salt )
			&& ( $user->getId() === $userIdToRemove || $user->isAllowed( 'ep-remreviewer' ) ) ) {

			/**
			 * @var Article $article
			 */
			$article = Articles::singleton()->selectRow(
				null,
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
