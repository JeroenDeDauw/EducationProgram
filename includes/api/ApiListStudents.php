<?php

namespace EducationProgram;
use ApiBase, User;

/**
 * API module for gathering the usernames of students in one or more courses.
 *
 * @since 0.4 alpha/
 *
 * @ingroup EducationProgram
 * @ingroup API
 *
 * @licence GNU GPL v2+
 * @author Sage Ross <sage@ragesoss.com>
 */
class ApiListStudents extends ApiBase {

	public function execute() {

		// This creates an array of all the submitted parameters of the API call.
		$params = $this->extractRequestParams();

		// This is the normal way of creating
		// an object for returning results with the API.
		$results = $this->getResult();

		// Create an empty list for student usernames or user IDs.
		$allStudents = array();

		// Determine which property to return: usernames or user IDs.
		$propName = $params['prop'];

		// This course index keeps track of which course element to add results to.
		$courseIndex = 0;

		// Now go through each of the submitted course IDs,
		// and add all the student users from that course.
		foreach ( $params['courseids'] as $courseId ) {

			// Get the course, or die if the course id is invalid.
			$course = Courses::singleton()->selectRow(
				null, array( 'id' => $courseId ) );
			if ( $course === false ) {
				$this->dieUsage( 'Invalid course id: ' . $courseId, 'invalid-course' );
			}

			// Get the user objects for students in this course,
			// and add them to the list of all students.
			$courseStudents = $this->getStudentsAsUsers( $course );
			$allStudents = array_merge( $allStudents, $courseStudents );

			// Check that we're not building too large of a query.
			if ( count( $allStudents ) > ApiBase::LIMIT_BIG2 ) {
				$this->dieUsage( 'Query exceeded limit with course '
					. $courseId
					. '. Try again with fewer courses.', 'over-query-limit' );
			}

			// If the 'group' parameter is given, get details for
			// students from this course to the list and display them.
			if ( $params['group'] ) {

				$this->outputCourseName( $courseId, $course, $courseIndex, $results );

				// If 'csv' parameter is given,
				// format and display the results as CSV for each course.
				if ( $params['csv'] ) {

					$this->outputCSVofStudentProperties(
						$courseStudents,
						$propName,
						$results,
						$courseId,
						$courseIndex
					);

				// If 'csv' parameter is not given,
				// format and display the results as structured data.
				} else {

					$this->outputListOfStudentProperties(
						$courseStudents,
						$propName,
						$results,
						$courseId,
						$courseIndex
					);

				}

			$courseIndex++;
			}

		}

		// If not grouping by course, format and display the results after students
		// are collected from all courses.
		if ( !$params['group'] ) {
			// Remove any duplicate students, if not grouping by course.
			$allStudents = array_unique( $allStudents );

			// If csv=true, return the results in CSV format.
			// Otherwise, return them as an array.
			if ( $params['csv'] ) {
				$this->outputCSVofStudentProperties(
					$allStudents,
					$propName,
					$results
				);

			} else {
				$this->outputListOfStudentProperties(
					$allStudents,
					$propName,
					$results
				);
			}
		} else {
			// Replace all the instances of $courseIndex in the results.
			$results->setIndexedTagName_internal(
				null,
				'course'
			);
		}
	}

	/**
	 * Given a course object, return the user objects
	 * of the students in that course.
	 *
	 * @param Course $course
	 * @return array of user objects
	 */
	protected function getStudentsAsUsers( Course $course ) {

		$students = $course->getStudents();

		$studentsInCourse = array();
		foreach ( $students as $student) {
			$studentsInCourse[] = $student->getUser();
		}

		return $studentsInCourse;
	}

	/**
	 * Given a user object and the property requested,
	 * return the either the username or the user ID.
	 *
	 * @param User $user
	 * @param string $propName
	 * @return string
	 */
	protected function getUserProperty( User $user, $propName ) {
		if ( $propName === 'username' ) {
			return $user->getName();
		} else {
			return $user->getId();
		}
	}

	/**
	 * For an array of user objects, output their usernames or user IDs
	 * as query results in CSV format, which is the format required for
	 * uploading cohorts of users to Wikimetrics.
	 *
	 * @param array $studentsList
	 * @param string $propName
	 * @param ApiResult @results
	 * @param int $courseID
	 * @param int $courseIndex
	 */
	protected function outputCSVofStudentProperties(
		$studentsList,
		$propName,
		$results,
		$courseId = null,
		$courseIndex = null
	) {

		$studentProps = array();
		foreach ( $studentsList as $student ) {
			$studentProps[] = $this->getUserProperty( $student, $propName );
		}
		$csv = PHP_EOL . implode( PHP_EOL, $studentProps ) . PHP_EOL ;

		$results->addValue(
			$this->studentPath( $courseIndex ),
			$propName . 's',
			array( '*'  => $csv )
		);
	}

	/**
	 * For an array of user objects, output
	 * their usernames or user IDs as query results.
	 *
	 * @param array $studentsList
	 * @param string $propName
	 * @param ApiResult $results
	 * @param int $courseID
	 * @param int $courseIndex
	 */
	protected function outputListOfStudentProperties(
		$studentsList,
		$propName,
		$results,
		$courseId = null,
		$courseIndex = null
	) {

		// Add the properties for each student to the result.
		foreach ( $studentsList as $student ) {
			$studentProp = $this->getUserProperty( $student, $propName );
			$results->addValue(
				$this->studentPath( $courseIndex ),
				null,
				$studentProp
			);
		}

		$results->setIndexedTagName_internal(
			$this->studentPath( $courseIndex ),
			$propName
		);
	}

	/**
	 * For a course objects, output the institution, course name and term
	 * as a query result.
	 *
	 * @param int $courseId
	 * @param Course $course
	 * @param ApiResult $results
	 */
	protected function outputCourseName(
		$courseId, $course, $courseIndex, $results ) {

		// Use an unambiguous name for the course that
		// includes the institution, title, term and ID.

		$results->addValue(
			$courseIndex,
			'id',
			$courseId
		);

		$results->addValue(
			$courseIndex,
			'name',
			array( '*' => $course->getField('title') )
		);
	}

	/**
	 * Construct the results path for the <students> element,
	 * either as part of an numerically indexed parent tag,
	 * or as a top-level element.
	 *
	 * @param int $courseIndex
	 */
	protected function studentPath( $courseIndex ) {
		if ( !is_null ( $courseIndex ) ) {
			return array ( $courseIndex, 'students');
		} else {
			return 'students';
		}
	}

	public function getAllowedParams() {
		return array(

			'courseids'=> array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_REQUIRED => true,
				// This allows multiple pipe-separated values in courseids parameter.
				ApiBase::PARAM_ISMULTI => true,
			),

			'prop' => array(
				ApiBase::PARAM_DFLT => 'username',
				ApiBase::PARAM_TYPE => array(
					'username',
					'id',
				)
			),
			'group'=> false,
			'csv'=> false,
		);
	}

	public function getParamDescription() {
		return array(
			'courseids' => 'The IDs of courses, each separated by a |',
			'prop' => array(
				'Which property to get for each student:',
				' username       - The username of the student',
				' id             - The user ID of the student',
			),
			'group' => 'If group parameter is given, the query will group students by course.',
			'csv' => 'If csv parameter is given, the query will return usernames in CSV format.',
		);
	}

	public function getDescription() {
		return array(
				'Get the usernames of students enrolled in one or more courses.'
		);
	}

	public function getPossibleErrors() {
		return array_merge( parent::getPossibleErrors(), array(
			array( 'code' => 'invalid-course',
				'info' => 'There is no course with the provided ID.' ),
			array( 'code' => 'over-query-limit',
				'info' => 'This query would return more usernames than allowed.' ),
		));
	}

	protected function getExamples() {
		return array(
			'api.php?action=liststudents&courseids=3',
			'api.php?action=liststudents&courseids=3|4|5|6&group=&csv=',
			'api.php?action=liststudents&courseids=3|4|5|6&group=&csv=&prop=id'
		);
	}
}
