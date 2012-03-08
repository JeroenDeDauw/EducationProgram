<?php

/**
 * Class representing a single single article being worked upon by a student,
 * and can have zero or more associated reviewers.
 *
 * @since 0.1
 *
 * @file EPArticle.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v3 or later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EPArticle extends DBDataObject {
	
	/**
	 * Cached user object for this article.
	 *
	 * @since 0.1
	 * @var User|false
	 */
	protected $user = false;

	/**
	 * Cached title object for this article.
	 *
	 * @since 0.1
	 * @var Title|false
	 */
	protected $title = false;

	/**
	 * Cached course object for this article.
	 *
	 * @since 0.1
	 * @var Course|false
	 */
	protected $course = false;

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

	/**
	 * Returns the course this article is linked to.
	 *
	 * @since 0.1
	 *
	 * @param array|string|null $fields
	 *
	 * @return EPCourse|false
	 */
	public function getCourse( $fields = null ) {
		if ( $this->course === false ) {
			$course = EPCourses::singleton()->selectRow( $fields, array( 'id' => $this->getField( 'course_id' ) ) );

			if ( is_null( $fields ) ) {
				$this->course = $course;
			}
		}

		return $this->course === false ? $course : $this->course;
	}

	protected $canBecomeReviwer = array();

	public function canBecomeReviewer( User $user ) {
		if ( !array_key_exists( $user->getId(), $this->canBecomeReviwer ) ) {
			$this->canBecomeReviwer[$user->getId()] =
				$user->isLoggedIn()
				&& $user->isAllowed( 'ep-bereviewer' )
				&& $this->getUser()->getId() !== $user->getId()
				&& !in_array( $user->getId(), $this->getField( 'reviewers' ) );
		}

		return $this->canBecomeReviwer[$user->getId()];
	}

	public function addReviewers( array $userIds ) {
		$addedIds = array_diff( $userIds, $this->getField( 'reviewers' ) );

		if ( !empty( $addedIds ) ) {
			$this->setField( 'reviewers', array_merge( $this->getField( 'reviewers' ), $addedIds ) );
		}

		return $addedIds;
	}

	public function logReviewersAdittion( array $userIds ) {
		// TODO
	}

	public function removeReviewers( array $userIds ) {
		$removedIds = array_intersect( $userIds, $this->getField( 'reviewers' ) );

		if ( !empty( $removedIds ) ) {
			$this->setField( 'reviewers', array_diff( $this->getField( 'reviewers' ), $userIds ) );
		}

		return $removedIds;
	}

	public function logReviewersRemoval( array $userIds ) {
		// TODO
	}

}
