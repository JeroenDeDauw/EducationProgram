<?php

namespace EducationProgram;

use ApiBase;
use User;

/**
 * API module to associate/disassociate users as instructor or ambassador with/from a course.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 * @ingroup API
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ApiEnlist extends ApiBase {

	public function execute() {
		$params = $this->extractRequestParams();

		if ( !( isset( $params['username'] ) xor isset( $params['userid'] ) ) ) {
			if ( is_callable( [ $this, 'dieWithError' ] ) ) {
				$this->dieWithError( 'ep-enlist-invalid-user-args', 'username-xor-userid' );
			} else {
				$this->dieUsage(
					$this->msg( 'ep-enlist-invalid-user-args' )->text(), 'username-xor-userid'
				);
			}
		}

		if ( isset( $params['username'] ) ) {
			$user = User::newFromName( $params['username'] );
			$userId = $user === false ? 0 : $user->getId();
		} else {
			$userId = $params['userid'];
		}

		if ( $userId < 1 ) {
			if ( is_callable( [ $this, 'dieWithError' ] ) ) {
				$this->dieWithError( 'ep-enlist-invalid-user', 'invalid-user' );
			} else {
				$this->dieUsage( $this->msg( 'ep-enlist-invalid-user' )->text(), 'invalid-user' );
			}
		}

		if ( !$this->userIsAllowed( $userId, $params['role'], $params['subaction'] ) ) {
			if ( is_callable( [ $this, 'dieWithError' ] ) ) {
				$this->dieWithError( 'apierror-permissiondenied-generic', 'permissiondenied' );
			} else {
				$this->dieUsageMsg( [ 'badaccess-groups' ] );
			}
		}

		$roleMap = [
			'student' => 'students',
			'campus' => 'campus_ambs',
			'online' => 'online_ambs',
			'instructor' => 'instructors',
		];

		$field = $roleMap[$params['role']];

		/**
		 * @var Course $course
		 */
		$course = Courses::singleton()->selectRow(
			[ 'id', 'name', 'title', $field ], [ 'id' => $params['courseid'] ]
		);

		if ( $course === false ) {
			if ( is_callable( [ $this, 'dieWithError' ] ) ) {
				$this->dieWithError( 'ep-enlist-invalid-course', 'invalid-course' );
			} else {
				$this->dieUsage(
					$this->msg( 'ep-enlist-invalid-course' )->text(), 'invalid-course'
				);
			}
		}

		$revAction = new RevisionAction();
		$revAction->setUser( $this->getUser() );
		$revAction->setComment( $params['reason'] );

		switch ( $params['subaction'] ) {
			case 'add':
				$enlistmentResult = $course->enlistUsers(
					[ $userId ], $params['role'], true, $revAction
				);
				break;
			case 'remove':
				$enlistmentResult = $course->unenlistUsers(
					[ $userId ], $params['role'], true, $revAction
				);
				break;
		}

		$this->getResult()->addValue(
			null,
			'success',
			$success = $enlistmentResult !== false
		);

		if ( $enlistmentResult !== false ) {
			$this->getResult()->addValue(
				null,
				'count',
				$enlistmentResult
			);
		}
	}

	/**
	 * Returns if the user is allowed to do the requested action.
	 *
	 * @since 0.1
	 *
	 * @param integer $userId User id of the mentor affected
	 * @param string $role
	 * @param string $subAction
	 *
	 * @return boolean
	 */
	protected function userIsAllowed( $userId, $role, $subAction ) {
		$user = $this->getUser();
		$isSelf = $user->getId() === $userId;
		$isRemove = $subAction === 'remove';

		if ( $isSelf && $isRemove ) {
			return true;
		}

		switch ( $role ) {
			case 'student':
				return ( $isRemove && $user->isAllowed( 'ep-remstudent' ) )
					|| ( ( $user->isAllowed( 'ep-enroll' ) && $isSelf )
					|| $user->isAllowed( 'ep-addstudent' ) );
				break;
			case 'instructor':
				return $user->isAllowed( 'ep-instructor' )
					|| ( $user->isAllowed( 'ep-beinstructor' ) && $isSelf );
				break;
			case 'online':
				return $user->isAllowed( 'ep-online' )
					|| ( $user->isAllowed( 'ep-beonline' ) && $isSelf );
				break;
			case 'campus':
				return $user->isAllowed( 'ep-campus' )
					|| ( $user->isAllowed( 'ep-becampus' ) && $isSelf );
				break;
		}

		return false;
	}

	public function needsToken() {
		return 'csrf';
	}

	public function mustBePosted() {
		return true;
	}

	public function getAllowedParams() {
		return [
			'subaction' => [
				ApiBase::PARAM_TYPE => [ 'add', 'remove' ],
				ApiBase::PARAM_REQUIRED => true,
			],
			'role' => [
				ApiBase::PARAM_TYPE => [ 'instructor', 'online', 'campus', 'student' ],
				ApiBase::PARAM_REQUIRED => true,
			],
			'username' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => false,
			],
			'userid' => [
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_REQUIRED => false,
			],
			'courseid' => [
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_REQUIRED => true,
			],
			'reason' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => false,
				ApiBase::PARAM_DFLT => '',
			],
			'token' => null,
		];
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getParamDescription() {
		return [
			'subaction' => 'Specifies what you want to do with the instructor or ambassador',
			'role' => 'The role to affect. "instructor" for instructor, "online" for online ' .
				'ambassadors and "campus" for campus ambassadors',
			'courseid' => 'The ID of the course to/from which the instructor or ambassador ' .
				'should be added/removed',
			'username' => 'Name of the user to associate as instructor or ambassador',
			'userid' => 'Id of the user to associate as instructor or ambassador',
			'reason' => 'Message with the reason for this change for the log',
			'token' => 'Edit token. You can get one of these through prop=info.',
		];
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getDescription() {
		return [
			'API module for associating/disassociating a user as instructor or ambassador ' .
				'with/from a course.'
		];
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	protected function getExamples() {
		return [
			'api.php?action=instructor&subaction=add&courseid=42&userid=9001',
			'api.php?action=instructor&subaction=add&courseid=42&username=Jeroen%20De%20Dauw',
			'api.php?action=instructor&subaction=remove&courseid=42&userid=9001',
			'api.php?action=instructor&subaction=remove&courseid=42&username=Jeroen%20De%20Dauw',
			'api.php?action=instructor&subaction=remove&courseid=42&username=Jeroen%20De%20Dauw' .
				'&reason=Removed%20from%20program%20because%20of%20evil%20plans%20to%20take%20' .
				'over%20the%20world',
		];
	}

	/**
	 * @see ApiBase::getExamplesMessages()
	 */
	protected function getExamplesMessages() {
		return [
			'action=enlist&role=instructor&subaction=add&courseid=42&userid=9001'
				=> 'apihelp-enlist-example-1',
			'action=enlist&role=instructor&subaction=add&courseid=42&username=Example'
				=> 'apihelp-enlist-example-2',
			'action=enlist&role=instructor&subaction=remove&courseid=42&userid=9001'
				=> 'apihelp-enlist-example-3',
			'action=enlist&role=instructor&subaction=remove&courseid=42&username=Example'
				=> 'apihelp-enlist-example-4',
			'action=enlist&role=instructor&subaction=remove&courseid=42&username=Example' .
				'&reason=Removed%20from%20program%20because%20of%20evil%20plans%20to%20take%20' .
				'over%20the%20world'
				=> 'apihelp-enlist-example-5',
		];
	}
}
