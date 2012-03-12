<?php

/**
 * Remove an article-student association.
 *
 * @since 0.1
 *
 * @file EPRemoveArticleAction.php
 * @ingroup EducationProgram
 * @ingroup Action
 *
 * @licence GNU GPL v3+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EPRemoveArticleAction extends FormlessAction {

	/**
	 * (non-PHPdoc)
	 * @see Action::getName()
	 */
	public function getName() {
		return 'epremarticle';
	}

	/**
	 * (non-PHPdoc)
	 * @see FormlessAction::onView()
	 */
	public function onView() {
		$req = $this->getRequest();
		$user = $this->getUser();

		if ( $user->matchEditToken( $req->getText( 'token' ), 'remarticle' . $req->getInt( 'article-id' ) ) ) {
			$article = EPArticles::singleton()->selectRow(
				null,
				array(
					'id' => $req->getInt( 'article-id' ),
				)
			);

			if ( $article !== false && $article->userCanRemove( $this->getUser() ) && $article->remove() ) {
				$article->logRemoval( $this->getUser() );
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
