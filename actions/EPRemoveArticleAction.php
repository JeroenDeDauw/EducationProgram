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
				array(
					'id',
					'course_id',
					'page_id',
					'page_title',
				),
				array(
					'id' => $req->getInt( 'article-id' ),
					'user_id' => $user->getId(),
				)
			);

			if ( $article !== false && $article->remove() ) {
				EPUtils::log( array(
					'type' => 'eparticle',
					'subtype' => 'remove',
					'user' => $this->getUser(),
					'title' => $article->getCourse()->getTitle(),
					'parameters' => array(
						'4::articlename' => $article->getTitle()->getFullText(),
					),
				) );
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
