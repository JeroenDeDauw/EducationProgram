<?php

namespace EducationProgram;

use User;
use Title;
use MWException;

/**
 * Class representing an article being worked on by a single user as part of a course
 * of which the work is being reviewed by zero or more reviewers.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @since 0.1, big changes in 0.3
 *
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EPArticle {

	/**
	 * Cached user object for this article.
	 *
	 * @since 0.1
	 * @deprecated since 0.3
	 * @var User|bool false
	 */
	protected $user = false;

	/**
	 * Cached course object for this article.
	 *
	 * @since 0.1
	 * @deprecated since 0.3
	 * @var Course|bool false
	 */
	protected $course = false;

	/**
	 * Cached list of user allowances to become reviewer.
	 * int userId => bool canBecomereviewer
	 *
	 * @since 0.1
	 * @deprecated since 0.3
	 * @var array
	 */
	protected $canBecomeReviwer = array();

	/**
	 * @since 0.3
	 *
	 * @var int|null
	 */
	protected $id;

	/**
	 * @since 0.3
	 *
	 * @var int
	 */
	protected $courseId;

	/**
	 * @since 0.3
	 *
	 * @var int
	 */
	protected $userId;

	/**
	 * @since 0.3
	 *
	 * @var int
	 */
	protected $pageId;

	/**
	 * @since 0.3
	 *
	 * @var string
	 */
	protected $pageTitle;

	/**
	 * @since 0.3
	 *
	 * @var int[]
	 */
	protected $reviewers;

	/**
	 * Constructor.
	 *
	 * @since 0.3
	 *
	 * @param int|null $id
	 * @param int $courseId
	 * @param int $userId
	 * @param int $pageId
	 * @param string $pageTitle
	 * @param int[] $reviewers Array of user ids (this list should not contain duplicates)
	 */
	public function __construct( $id, $courseId, $userId, $pageId, $pageTitle, array $reviewers ) {
		$this->id = $id;
		$this->courseId = $courseId;
		$this->userId = $userId;
		$this->pageId = $pageId;
		$this->pageTitle = $pageTitle;
		$this->reviewers = $reviewers;
	}

	/**
	 * Returns the user that is working on this article.
	 *
	 * @since 0.1
	 * @deprecated since 0.3
	 *
	 * @return User
	 */
	public function getUser() {
		if ( $this->user === false ) {
			$this->user = User::newFromId( $this->userId );
		}

		return $this->user;
	}

	/**
	 * Returns the course this article is linked to.
	 *
	 * @since 0.1
	 *
	 * @param array|string|null $fields
	 * @deprecated since 0.3
	 *
	 * @return Course|bool false
	 */
	public function getCourse( $fields = null ) {
		if ( $this->course === false ) {
			$course = Courses::singleton()->selectRow( $fields, array( 'id' => $this->courseId ) );

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
	 * @deprecated since 0.3
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
				&& !in_array( $user->getId(), $this->reviewers );
		}

		return $this->canBecomeReviwer[$user->getId()];
	}

	/**
	 * Adds the users matching the provided ids as reviewers to this article.
	 * Users already a reviewer will be ignored. An array with actually added user ids
	 * is returned.
	 *
	 * @since 0.1
	 *
	 * @param array $userIds
	 *
	 * @return array
	 */
	public function addReviewers( array $userIds ) {
		$userIds = array_unique( $userIds );
		$addedIds = array_diff( $userIds, $this->reviewers );

		if ( !empty( $addedIds ) ) {
			$this->reviewers = array_merge( $this->reviewers, $addedIds );
		}

		return $addedIds;
	}

	/**
	 * Removes the users matching the provided ids as reviewers from this article.
	 * Users that are not a reviewer will just be ignored. An array with actually removed
	 * user ids is returned.
	 *
	 * @since 0.1
	 *
	 * @param array $userIds
	 *
	 * @return array
	 */
	public function removeReviewers( array $userIds ) {
		$removedIds = array_intersect( $userIds, $this->reviewers );

		if ( !empty( $removedIds ) ) {
			$this->reviewers = array_diff( $this->reviewers, $userIds );
		}

		return array_unique( $removedIds );
	}

	/**
	 * Logs the addition of the users matching the provided ids as reviewers to this article.
	 *
	 * @since 0.1
	 * @deprecated since 0.3
	 *
	 * @param array $userIds
	 * @param bool|string $comment
	 */
	public function logReviewersAddition( array $userIds, $comment = false ) {
		foreach ( $userIds as $userId ) {
			$this->log( User::newFromId( $userId ), 'review', $comment );
		}
	}

	/**
	 * Logs the removal of the users matching the provided ids as reviewers for this article.
	 *
	 * @since 0.1
	 * @deprecated since 0.3
	 *
	 * @param array $userIds
	 * @param bool|string $comment
	 */
	public function logReviewersRemoval( array $userIds, $comment = false ) {
		foreach ( $userIds as $userId ) {
			$this->log( User::newFromId( $userId ), 'unreview', $comment );
		}
	}

	/**
	 * Log addition of the article.
	 *
	 * @deprecated since 0.3
	 *
	 * @param User $actionUser
	 * @param bool|string $comment
	 */
	public function logAddition( User $actionUser, $comment = false ) {
		$this->log(
			$actionUser,
			$actionUser->getId() === $this->getUser()->getId() ? 'selfadd' : 'add',
			$comment
		);
	}

	/**
	 * Log removal of the article.
	 *
	 * @deprecated since 0.3
	 *
	 * @param User $actionUser
	 * @param bool|string $comment
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
	 * @deprecated since 0.3
	 *
	 * @param User $actionUser
	 * @param string $subType
	 * @param bool|string $comment
	 */
	protected function log( User $actionUser, $subType, $comment = false ) {
		$articleOwner = $this->getUser();

		$title = Title::newFromID( $this->pageId );
		$title = $title === null ? Title::newFromText( $this->pageTitle ) : $title;

		$logData = array(
			'user' => $actionUser,
			'title' => $title,
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

		Utils::log( $logData );
	}

	/**
	 * Returns if the provided user can remove the article.
	 *
	 * @since 0.1
	 * @deprecated since 0.3
	 *
	 * @param User $user
	 *
	 * @return boolean
	 */
	public function userCanRemove( User $user ) {
		return $user->isAllowed( 'ep-remarticle' ) || $user->getId() === $this->getUserId();
	}

	/**
	 * Returns the id of the EPArticle.
	 *
	 * @since 0.3
	 *
	 * @return int|null
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * Returns the id of the course for which the article is being worked on.
	 *
	 * @since 0.3
	 *
	 * @return int
	 */
	public function getCourseId() {
		return $this->courseId;
	}

	/**
	 * Returns the id of the user working on the article.
	 *
	 * @since 0.3
	 *
	 * @return int
	 */
	public function getUserId() {
		return $this->userId;
	}

	/**
	 * Returns the id of the article being worked on itself.
	 *
	 * @since 0.3
	 *
	 * @return int
	 */
	public function getPageId() {
		return $this->pageId;
	}

	/**
	 * Returns the title of the article being worked on itself.
	 *
	 * @since 0.3
	 *
	 * @return string
	 */
	public function getPageTitle() {
		return $this->pageTitle;
	}

	/**
	 * Returns the user ids of the reviewers.
	 *
	 * @since 0.3
	 *
	 * @return int[]
	 */
	public function getReviewers() {
		return $this->reviewers;
	}

}
