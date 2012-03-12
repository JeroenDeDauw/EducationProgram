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
	 * Cached list of user allowances to become reviewer.
	 * int userId => bool canBecomereviewer
	 *
	 * @since 0.1
	 * @var array
	 */
	protected $canBecomeReviwer = array();

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
			$this->title = $this->getField( 'page_id' ) === 0 ?
				Title::newFromText( $this->getField( 'page_title' ) )
				: Title::newFromID( $this->getField( 'page_id' ) );
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

	/**
	 * Returns if the provided User can become a reviwers for this article.
	 *
	 * @since 0.1
	 *
	 * @param User $user
	 *
	 * @return boolean
	 */
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

	/**
	 * Adds the users matching the provided ids as reviewers to this article.
	 * USers already a reviewer will be ignored. An array with actually added user ids
	 * is returned.
	 *
	 * @since 0.1
	 *
	 * @param array $userIds
	 *
	 * @return array
	 */
	public function addReviewers( array $userIds ) {
		$addedIds = array_diff( $userIds, $this->getField( 'reviewers' ) );

		if ( !empty( $addedIds ) ) {
			$this->setField( 'reviewers', array_merge( $this->getField( 'reviewers' ), $addedIds ) );
		}

		return $addedIds;
	}

	/**
	 * Logs the adittion of the users matching the provided ids as reviewers to this article.
	 *
	 * @since 0.1
	 *
	 * @param array $userIds
	 */
	public function logReviewersAdittion( array $userIds ) {
		foreach ( $userIds as $userId ) {
			EPUtils::log( array(
				'user' => User::newFromId( $userId ),
				'title' => $this->getTitle(),
				'type' => 'eparticle',
				'subtype' => 'review',
			) );
		}
	}

	/**
	 * Removes the users matching the provided ids as reviewers from this article.
	 * Users that are not a reviwer will just be ignored. An array with actually removed
	 * user ids is returned.
	 *
	 * @since 0.1
	 *
	 * @param array $userIds
	 *
	 * @return array
	 */
	public function removeReviewers( array $userIds ) {
		$removedIds = array_intersect( $userIds, $this->getField( 'reviewers' ) );

		if ( !empty( $removedIds ) ) {
			$this->setField( 'reviewers', array_diff( $this->getField( 'reviewers' ), $userIds ) );
		}

		return $removedIds;
	}

	/**
	 * Logs the removal of the users matching the provided ids as reviewers for this article.
	 *
	 * @since 0.1
	 *
	 * @param array $userIds
	 */
	public function logReviewersRemoval( array $userIds ) {
		foreach ( $userIds as $userId ) {
			EPUtils::log( array(
				'user' => User::newFromId( $userId ),
				'title' => $this->getTitle(),
				'type' => 'eparticle',
				'subtype' => 'unreview',
			) );
		}
	}

}
