<?php

namespace EducationProgram\Events;

use EducationProgram\Courses;
use EducationProgram\Student;
use EducationProgram\UserCourseFinder;

use Revision;
use User;
use Page;
use DatabaseBase;
use MWNamespace;
use Diff;
use DiffOp;

/**
 * Class that generates edit based events by handling new edits.
 *
 * TODO: properly inject dependencies
 * - Profiler
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
 * @since 0.3
 *
 * @file
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EditEventCreator {

	/**
	 * @since 0.3
	 *
	 * @var DatabaseBase
	 */
	private $db;

	/**
	 * @since 0.3
	 *
	 * @var UserCourseFinder
	 */
	private $userCourseFinder;

	/**
	 * Constructor.
	 *
	 * @since 0.3
	 *
	 * @param DatabaseBase $db
	 * @param UserCourseFinder $userCourseFinder
	 */
	public function __construct( DatabaseBase $db, UserCourseFinder $userCourseFinder ) {
		$this->db = $db;
		$this->userCourseFinder = $userCourseFinder;
	}

	/**
	 * Takes the information of a newly created revision and uses this to
	 * create a list of education program events which is then returned.
	 *
	 * @since 0.3
	 *
	 * @param Page $article
	 * @param Revision $rev
	 * @param User $user
	 *
	 * @return Event[]
	 */
	public function getEventsForEdit( Page $article, Revision $rev, User $user ) {
		wfProfileIn( __METHOD__ );

		if ( !$user->isLoggedIn() ) {
			wfProfileOut( __METHOD__ );
			return array();
		}

		$namespace = $article->getTitle()->getNamespace();

		if ( !in_array( $namespace, array( NS_MAIN, NS_TALK, NS_USER, NS_USER_TALK ) ) ) {
			wfProfileOut( __METHOD__ );
			return array();
		}

		$courseIds = $this->userCourseFinder->getCoursesForUsers( $user->getId(), EP_STUDENT );

		if ( empty( $courseIds ) ) {
			$events = array();
		}
		else {
			$events = $this->createEditEvents( $rev, $user, $courseIds );

			$this->updateLastActive( $namespace, $user );
		}

		wfProfileOut( __METHOD__ );

		return $events;
	}

	/**
	 * Creates the actual edit events.
	 *
	 * @since 0.3
	 *
	 * @param Revision $revision
	 * @param User $user
	 * @param int[] $courseIds
	 *
	 * @return Event[]
	 */
	protected function createEditEvents( Revision $revision, User $user, array $courseIds ) {
		if ( is_null( $revision->getTitle() ) ) {
			return array();
		}

		$events = array();

		$title = $revision->getTitle();

		$info = array(
			'page' => $title->getFullText(),
			'comment' => $revision->getComment(),
			'minoredit' => $revision->isMinor(),
			'parent' => $revision->getParentId()
		);

		if ( MWNamespace::isTalk( $title->getNamespace() ) && !is_null( $revision->getParentId() ) ) {
			$diff = new Diff(
				explode( "\n", Revision::newFromId( $revision->getParentId() )->getText() ),
				explode( "\n", $revision->getText() )
			);

			// Only an order of magnitude more lines then the python equivalent, but oh well... >_>
			// lines = [ diffOp->closing for diffOp in diff->edits if diffOp->type == 'add' ]
			$lines = array_map(
				function( DiffOp $diffOp ) {
					return $diffOp->closing;
				},
				array_filter(
					$diff->edits,
					function( DiffOp $diffOp ) {
						return $diffOp->type == 'add';
					}
				)
			);

			if ( $lines !== array() ) {
				$lines = call_user_func_array( 'array_merge', $lines );
			}

			$info['addedlines'] = $lines;
		}

		foreach ( $courseIds as $courseId ) {
			$events[] = new Event(
				null,
				$courseId,
				$user->getId(),
				$revision->getTimestamp(),
				'edit-' . $title->getNamespace(),
				$info
			);
		}

		return $events;
	}

	/**
	 * Updates the last activity of the student to be now.
	 *
	 * TODO: this should go into its own class
	 *
	 * @since 0.3
	 *
	 * @param int $namespace
	 * @param User $user
	 */
	protected function updateLastActive( $namespace, User $user ) {
		wfProfileIn( __METHOD__ );

		if ( in_array( $namespace, array( NS_MAIN, NS_TALK ) ) ) {
			$student = Student::newFromUserId( $user->getId(), true );

			$student->setFields( array(
				'last_active' => wfTimestampNow()
			) );

			if ( !defined( 'MW_PHPUNIT_TEST' ) ) {
				$student->save();
			}
		}

		wfProfileOut( __METHOD__ );
	}

}
