<?php

namespace EducationProgram;
use ApiBase, ApiQueryBase, User;

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
class ApiListStudents extends ApiQueryBase {

	public function execute() {

		// This creates an array of all the submitted parameters of the API call.
		$params = $this->extractRequestParams();

		// This is the normal way of creating an object for returning results with the API.
		$results = $this->getResult();

		// Create an empty lists for student usernames.
		$allStudentUsernames = array();

		// Now go through each of the submitted course IDs,
		// and add all the students from that course.
		foreach ( $params['courseids'] as $courseId ) {

			// Get the course, or die if the course id is invalid.
			$course = Courses::singleton()->selectRow( null, array( 'id' => $courseId ) );
			if ( $course === false ) {
				$this->dieUsage( 'Invalid course id: ' . $courseId , 'invalid-course' );
			}

			// Add the students from this course to the list.
			$allStudentUsernames = array_merge (
				$allStudentUsernames ,
				ApiListStudents::getStudentUsernames ( $course ) );

			// Check that we're not building too large of a query.
			if ( count( $allStudentUsernames ) > ApiBase::LIMIT_BIG2 ) {
				$this->dieUsage( 'Query exceeded limit with course '
					. $courseId
					. '. Try again with fewer courses.' , 'over-query-limit' );
			}
		}

		// Remove any duplicate usernames.
		$allStudentUsernames = array_unique ( $allStudentUsernames );

		// If csv=true, return the results in CSV format.
		// Otherwise, return them as an array.
		if ( $params['csv'] ) {

			// Transform array of students into CSV format, which Wikimetrics requires.
			$csv = PHP_EOL . implode ( PHP_EOL , $allStudentUsernames ) . PHP_EOL ;

			// Return results. The '*' triggers the xml formatter to use
			// the associated value as the content of the tag,
			// per https://www.mediawiki.org/wiki/Manual:ApiResult.php
			$results->addValue( 'students', 'usernames' , array ( '*'  => $csv ) );

		} else {
			foreach ( $allStudentUsernames as $student ) {
				$results->addValue(
					array( 'query', $this->getModuleName() ),
					NULL ,
					$student );
			}

			$results->setIndexedTagName_internal(
				array( 'query', $this->getModuleName() ) ,
				'username' );
		}

	}

	/**
	 * Given a course object, provide
	 * an array of usernames of all students in that course.
	 *
	 * @param Course $course
	 * @return array
	 */
	protected function getStudentUsernames( Course $course ) {

		// Get the students from the course.
		$students = $course->getStudents();

		$studentsInCourse = array();
		foreach ( $students as $student) {
			$name = $student->getUser()->getName();
			$studentsInCourse[] = $name;
		}

		return $studentsInCourse;
	}

	public function getAllowedParams() {
		return array(
			'courseids'=> array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_REQUIRED => true,
				// This allows multiple pipe-separated values in courseids parameter.
				ApiBase::PARAM_ISMULTI => true,
			),

			'csv'=> false,
		);
	}

	public function getParamDescription() {
		return array(
			'courseids' => 'The IDs of courses, each separated by a |',
			'csv' => 'If csv=true instead of the default of false, the query will return usernames in CSV format.'
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
			'api.php?action=liststudents&courseids=3|4|5',
		);
	}
}
