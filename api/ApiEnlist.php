<?php

/**
 * API module to associate/disassociate users as instructor or ambassador with/from a course.
 *
 * @since 0.1
 *
 * @file ApiEnlist.php
 * @ingroup EducationProgram
 * @ingroup API
 *
 * @licence GNU GPL v3+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ApiEnlist extends ApiBase {

	public function execute() {
		$params = $this->extractRequestParams();

		if ( !( isset( $params['username'] ) XOR isset( $params['userid'] ) ) ) {
			$this->dieUsage( wfMsg( 'ep-enlist-invalid-user-args' ), 'username-xor-userid' );
		}

		if ( isset( $params['username'] ) ) {
			$user = User::newFromName( $params['username'] );
			$userId = $user === false ? 0 : $user->getId();
		}
		else {
			$userId = $params['userid'];
		}
		
		if ( $userId < 1 ) {
			$this->dieUsage( wfMsg( 'ep-enlist-invalid-user' ), 'invalid-user' );
		}
		
		if ( !$this->userIsAllowed( $userId, $params['role'], $params['subaction'] ) ) {
			$this->dieUsageMsg( array( 'badaccess-groups' ) );
		}

		$roleMap = array(
			'student' => 'students',
			'campus' => 'campus_ambs',
			'online' => 'online_ambs',
			'instructor' => 'instructors',
		);

		$field = $roleMap[$params['role']];

		$course = EPCourse::selectRow( array( 'id', 'name', $field ), array( 'id' => $params['courseid'] ) );

		if ( $course === false ) {
			$this->dieUsage( wfMsg( 'ep-enlist-invalid-course' ), 'invalid-course' );
		}
		
		$success = false;
		
		switch ( $params['subaction'] ) {
			case 'add':
				$success = $course->enlistUsers( array( $userId ), $params['role'], $params['reason'] );
				break;
			case 'remove':
				$success = $course->unenlistUsers( array( $userId ), $params['role'], $params['reason'] );
				break;
		}
		
		$this->getResult()->addValue(
			null,
			'success',
			$success
		);
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
				return $user->isAllowed( 'ep-enroll' ) && $isSelf;
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
		return true;
	}

	public function mustBePosted() {
		return true;
	}

	public function getAllowedParams() {
		return array(
			'subaction' => array(
				ApiBase::PARAM_TYPE => array( 'add', 'remove' ),
				ApiBase::PARAM_REQUIRED => true,
			),
			'role' => array(
				ApiBase::PARAM_TYPE => array( 'instructor', 'online', 'campus', 'student' ),
				ApiBase::PARAM_REQUIRED => true,
			),
			'username' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => false,
			),
			'userid' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_REQUIRED => false,
			),
			'courseid' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_REQUIRED => true,
			),
			'reason' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => false,
				ApiBase::PARAM_DFLT => '',
			),
			'token' => null,
		);
	}

	public function getParamDescription() {
		return array(
			'subaction' => 'Specifies what you want to do with the instructor or ambassador',
			'role' => 'The role to affect. "instructor" for instructor, "online" for online ambassadors and "campus" for campus ambassadors',
			'courseid' => 'The ID of the course to/from which the instructor or ambassador should be added/removed',
			'username' => 'Name of the user to associate as instructor or ambassador',
			'userid' => 'Id of the user to associate as instructor or ambassador',
			'reason' => 'Message with the reason for this change for the log',
			'token' => 'Edit token. You can get one of these through prop=info.',
		);
	}

	public function getDescription() {
		return array(
			'API module for associating/disassociating a user as instructor or ambassador with/from a course.'
		);
	}

	public function getPossibleErrors() {
		return array_merge( parent::getPossibleErrors(), array(
			array( 'code' => 'username-xor-userid', 'info' => 'You need to either provide the username or the userid parameter' ),
			array( 'code' => 'invalid-user', 'info' => 'An invalid user name or id was provided' ),
			array( 'code' => 'invalid-course', 'info' => 'There is no course with the provided ID' ),
		) );
	}

	protected function getExamples() {
		return array(
			'api.php?action=instructor&subaction=add&courseid=42&userid=9001',
			'api.php?action=instructor&subaction=add&courseid=42&username=Jeroen%20De%20Dauw',
			'api.php?action=instructor&subaction=remove&courseid=42&userid=9001',
			'api.php?action=instructor&subaction=remove&courseid=42&username=Jeroen%20De%20Dauw',
			'api.php?action=instructor&subaction=remove&courseid=42&username=Jeroen%20De%20Dauw&reason=Removed%20from%20program%20because%20of%20evil%20plans%20to%20take%20over%20the%20world',
		);
	}

	public function getVersion() {
		return __CLASS__ . ': $Id$';
	}

}
