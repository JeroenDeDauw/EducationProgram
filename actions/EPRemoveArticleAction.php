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
			$article = EPArticles::singleton()->selectRow( 'id',  array(
				'id' => $req->getInt( 'article-id' ),
				'user_id' => $user->getId(),
			) );

			if ( $article !== false && $article->remove() ) {
				// TODO: log
			}
		}

		Action::factory( 'view', $this->page, $this->context )->show();
		return '';
	}

}
