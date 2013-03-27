<?php

namespace EducationProgram;

/**
 * Remove an article-student association.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 * @ingroup Action
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class RemoveArticleAction extends \FormlessAction {

	/**
	 * @see Action::getName()
	 */
	public function getName() {
		return 'epremarticle';
	}

	/**
	 * @see FormlessAction::onView()
	 */
	public function onView() {
		$req = $this->getRequest();
		$user = $this->getUser();

		if ( $user->matchEditToken( $req->getText( 'token' ), 'remarticle' . $req->getInt( 'article-id' ) ) ) {

			// TODO: create dedicated ArticleRemover use case

			$articleStore = Extension::globalInstance()->newArticleStore();

			$article = $articleStore->getArticle( $req->getInt( 'article-id' ) );

			if ( $article !== false
				&& $article->userCanRemove( $this->getUser() )
				&& $articleStore->deleteArticle( $article->getId() ) ) {

				$article->logRemoval( $this->getUser() );
			}
		}

		$returnTo = null;

		if ( $req->getCheck( 'returnto' ) ) {
			$returnTo = \Title::newFromText( $req->getText( 'returnto' ) );
		}

		if ( is_null( $returnTo ) ) {
			$returnTo = $this->getTitle();
		}

		$this->getOutput()->redirect( $returnTo->getLocalURL() );

		return '';
	}

}
