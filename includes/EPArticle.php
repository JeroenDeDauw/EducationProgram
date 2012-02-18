<?php

/**
 * Class representing a single single article being worked upon by a student,
 * and can have zero or more associated reviewers.
 *
 * @since 0.1
 *
 * @file EPRevision.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v3 or later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EPArticle extends DBDataObject {
	
	/**
	 * Cached user object for this revision.
	 *
	 * @since 0.1
	 * @var User|false
	 */
	protected $user = false;

	/**
	 * Cached title object for this revision.
	 *
	 * @since 0.1
	 * @var Title|false
	 */
	protected $title = false;

	/**
	 * Returns the user that is working on this article.
	 *
	 * @since 0.1
	 *
	 * @return User
	 */
	public function getUser() {
		if ( $this->user === false ) {
			$this->user = User::newFromId( $this->loadAndGetField( 'user_id' ) );
		}

		return $this->user;
	}

	/**
	 * Display a pager with articles.
	 *
	 * @since 0.1
	 *
	 * @param IContextSource $context
	 * @param array $conditions
	 */
	public static function displayPager( IContextSource $context, array $conditions = array() ) {
		$pager = new EPArticlePager( $context, $conditions );

		if ( $pager->getNumRows() ) {
			$context->getOutput()->addHTML(
				$pager->getFilterControl() .
					$pager->getNavigationBar() .
					$pager->getBody() .
					$pager->getNavigationBar() .
					$pager->getMultipleItemControl()
			);
		}
		else {
			$context->getOutput()->addHTML( $pager->getFilterControl( true ) );
			$context->getOutput()->addWikiMsg( 'ep-articles-noresults' );
		}
	}

	/**
	 * Returns the title of this article.
	 *
	 * @since 0.1
	 *
	 * @return Title
	 */
	public function getTitle() {
		if ( $this->title === false ) {
			$this->title = Title::newFromID( $this->getField( 'page_id' ) );
		}

		return $this->title;
	}

	protected $canBecomeReviwer = array();

	public function canBecomeReviewer( User $user ) {
		if ( !array_key_exists( $user->getId(), $this->canBecomeReviwer ) ) {
			$this->canBecomeReviwer[$user->getId()] = $this->getUser()->isAllowed( 'ep-bereviewer' )
				&& $this->getUser()->getId() !== $user->getId()
				&& !in_array( $this->getUser()->getId(), $this->getField( 'reviewers' ) );
		}

		return $this->canBecomeReviwer[$user->getId()];
	}

}
