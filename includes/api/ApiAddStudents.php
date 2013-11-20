<?php

namespace EducationProgram;
use ApiBase, User;

/**
 * API module for adding multiple students to a course.
 *
 * @since 0.4 alpha
 *
 * @ingroup EducationProgram
 * @ingroup API
 *
 * @licence GNU GPL v2+
 * @author Andrew Green <andrew.green.df@gmail.com>
 */
class ApiAddStudents extends ApiBase {

	public function execute() {
		$params = $this->extractRequestParams();

		// Note: checks for missing parameters are automatic, as is user token
		// verification, so we don't need to do any of that here.

		// get the course, die if the course id is invalid
		$course = Courses::singleton()->selectRow( null, array( 'id' => $params['courseid'] ) );
		if ( $course === false ) {
			$this->dieUsage( 'Invalid course id', 'invalid-course' );
		}

		$user = $this->getUser();

		// check that the user can do this
		if ( !$user->isAllowed( 'ep-addstudent' ) && !RoleObject::isInRoleObjArray(
				$user->getId(),
				$course->getAllNonStudentRoleObjs() ) ) {

			$this->dieUsage( 'User is not authorized to perform this action', 'no-rights' );
		}

		// check the usernames sent, get user ids
		$apiParams = new \DerivativeRequest(
			$this->getRequest(),
			array(
				'action' => 'query',
				'list' => 'users',
				'ususers' => $params['studentusernames'] )
		);

		$api = new \ApiMain( $apiParams );
		$api->execute();
		$usersData = & $api->getResultData();

		// make lists: valid and invalid users
		$validUsersMap = array(); // associative array, id => name
		$invalidUserNames = array(); // just names, indexed numerically

		foreach ( $usersData['query']['users'] as $userData ) {
			if ( isset ( $userData['missing'] ) ) {
				$invalidUserNames[] = $userData['name'];
			} else {
				$validUsersMap[$userData['userid']] = $userData['name'];
			}
		}

		$r = $this->getResult();

		// if there are invalid user names, don't add any users, but do send a
		// result with these validation results
		if ( count( $invalidUserNames ) > 0 ) {

			$r->addValue( null, 'success', false );
			$r->addValue( null, 'usersAddedCount', 0);

			// Use array_values to make sure we get an array and not an object
			// on the JS end.
			$r->addValue( null, 'invalidUserNames', array_values( $invalidUserNames) );

		// otherwise add the users
		} else {

			$revAction = new RevisionAction();
			$revAction->setUser( $user );
			$addedUserIds = array();

			$enlistmentResult = $course->enlistUsers( array_keys( $validUsersMap ),
				'student', true, $revAction, $addedUserIds );

			// We have to test for actual faleshood, not falsiness, since
			// we might get 0 if no users were added due to all of them
			// already being enrolled.
			if ( $enlistmentResult === false ||
				$enlistmentResult != count( $addedUserIds ) ) {

				$this->dieUsage( 'Somthing bad happened.', 'internal-error' );

			} else {

				$r->addValue( null, 'success', true );

				// Don't worry about not sending the following data if
				// the arrays are empty; in any case, the client JS can
				// asume these fields are always set.

				// Use array_values to make sure we get arrays and not objects
				// on the JS end.
				$r->addValue( null, 'studentsAddedIds',
						array_values( $addedUserIds ) );

				if ( count ( $addedUserIds ) === 1 ) {
					$r->addValue( null, 'oneStudentAddedGender',
						User::newFromId( $addedUserIds[0] )
						->getOption( 'gender' ) );
				}

				$alreadyEnrolledIds =
					array_diff( array_keys( $validUsersMap ), $addedUserIds );

				$r->addValue( null, 'alreadyEnrolledUserNames',
					array_values(
						array_map( function ( $id ) use ( $validUsersMap ) {
							return $validUsersMap[$id];
						}, $alreadyEnrolledIds )
					)
				);

				if ( count ( $alreadyEnrolledIds ) === 1 ) {
					$r->addValue( null, 'oneAlreadyEnrolledGender',
						User::newFromId( $alreadyEnrolledIds[0] )
						->getOption( 'gender' ) );
				}

			}
		}
	}

	public function needsToken() {
		return true;
	}

	public function getTokenSalt() {
		return '';
	}

	public function mustBePosted() {
		return true;
	}

	public function getAllowedParams() {
		return array(
			'studentusernames' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true,
			),
			'courseid' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_REQUIRED => true,
			),
			'token' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true,
			),
		);
	}

	public function getParamDescription() {
		return array(
			'studentusernames' => 'The usernames of the students to add to the course, separated by a |',
			'courseid' => 'The ID of the course to which the students should be added/removed',
			'token' => 'Edit token.',
		);
	}

	public function getDescription() {
		return array(
				'Add multiple students to a course.'
		);
	}

	protected function getResultProperties() {
		return array (
			'result'=> 	array(
				ApiBase::PROP_TYPE => array(
					'success',
					'invalidUsers'
				),
				ApiBase::PROP_NULLABLE => false,
		),

			'studentsAddedIds' => array(
				'' => 'integer',
			),

			'oneStudentAddedGender' => 'string',

			'invalidUserNames' => array(
				'' => 'string',
			),

			'alreadyEnrolledUserNames' => array(
				'' => 'string',
			),

			'oneAlreadyEnrolledGender' => 'string',
		);
	}

	public function getPossibleErrors() {
		return array_merge( parent::getPossibleErrors(), array(
			array( 'code' => 'invalid-course', 'info' => 'There is no course with the provided ID.' ),
			array( 'code' => 'internal-error', 'info' => 'There was a problem enrolling students in this course.' ),
			array( 'code' => 'no-rights', 'info' => 'The user is not authorized to perform this action.' ),
		) );
	}

	protected function getExamples() {
		return array(
			'api.php?action=addstudents&courseid=42&token=123456789&students=User1|User3|AnotherUser',
		);
	}
}