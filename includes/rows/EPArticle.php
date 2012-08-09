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
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EPArticle extends ORMRow {

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
	 * Constructs and returns the HTML to display an article pager.
	 *
	 * @since 0.1
	 *
	 * @param IContextSource $context
	 * @param array $conditions
	 *
	 * @return string
	 */
	public static function getPagerHTML( IContextSource $context, array $conditions = array() ) {
		$pager = new EPArticlePager( $context, $conditions );

		if ( $pager->getNumRows() ) {
			return
				$pager->getFilterControl() .
				$pager->getNavigationBar() .
				$pager->getBody() .
				$pager->getNavigationBar() .
				$pager->getMultipleItemControl();
		}
		else {
			return $pager->getFilterControl( true ) . $context->msg( 'ep-articles-noresults' )->escaped();
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
	 * Logs the adittion of the users matching the provided ids as reviewers to this article.
	 *
	 * @since 0.1
	 *
	 * @param array $userIds
	 * @param string|false $comment
	 */
	public function logReviewersAdittion( array $userIds, $comment = false ) {
		foreach ( $userIds as $userId ) {
			$this->log( User::newFromId( $userId ), 'review', $comment );
		}
	}

	/**
	 * Logs the removal of the users matching the provided ids as reviewers for this article.
	 *
	 * @since 0.1
	 *
	 * @param array $userIds
	 * @param string|false $comment
	 */
	public function logReviewersRemoval( array $userIds, $comment = false ) {
		foreach ( $userIds as $userId ) {
			$this->log( User::newFromId( $userId ), 'unreview', $comment );
		}
	}

	/**
	 * Log adittion of the article.
	 *
	 * @param User $actionUser
	 * @param string|false $comment
	 */
	public function logAdittion( User $actionUser, $comment = false ) {
		$this->log(
			$actionUser,
			$actionUser->getId() === $this->getUser()->getId() ? 'selfadd' : 'add',
			$comment
		);
	}

	/**
	 * Log removal of the article.
	 *
	 * @param User $actionUser
	 * @param string|false $comment
	 */
	public function logRemoval( User $actionUser, $comment = false ) {
		$this->log(
			$actionUser,
			$actionUser->getId() === $this->getUser()->getId() ? 'selfremove' : 'remove',
			$comment
		);
	}

	/**
	 * Logging helper method.
	 *
	 * @since 0.1
	 *
	 * @param User $actionUser
	 * @param string $subType
	 * @param string|false $comment
	 */
	protected function log( User $actionUser, $subType, $comment = false ) {
		$articleOwner = $this->getUser();

		$logData = array(
			'user' => $actionUser,
			'title' => $this->getTitle(),
			'type' => 'eparticle',
			'subtype' => $subType,
			'parameters' => array(
				'4::coursename' => $this->getCourse()->getTitle()->getFullText(),
				'5::owner' => array( $articleOwner->getId(), $articleOwner->getName() ),
			),
		);

		if ( $comment !== false ) {
			$logData['comment'] = $comment;
		}

		EPUtils::log( $logData );
	}

	/**
	 * Returns if the provided user can remove the article.
	 *
	 * @since 0.1
	 *
	 * @param User $user
	 *
	 * @return boolean
	 */
	public function userCanRemove( User $user ) {
		return $user->isAllowed( 'ep-remarticle' ) || $user->getId() === $this->getField( 'user_id' );
	}

}
