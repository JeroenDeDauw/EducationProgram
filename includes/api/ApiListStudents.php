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

		$courseIds = $params['courseids'];

		// Now go through each of the submitted course IDs,
		// and add all the student users from that course.
		foreach ( $courseIds as $courseId ) {

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

				$this->outputCourseProperties(
					$courseId,
					$course,
					$courseIndex,
					$results
				);

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
					$results,
					$courseIds // The set of all course IDs from for the query
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
	 * @param int[]|int $courseIds A list of one or more course IDs
	 * @param int $courseIndex
	 */
	protected function outputCSVofStudentProperties(
		$studentsList,
		$propName,
		$results,
		$courseIds,
		$courseIndex = null
	) {

		$studentProps = array();

		foreach ( $studentsList as $student ) {
			$studentProps[] = $this->getUserProperty( $student, $propName );
		}

		if ( $studentProps ) {
			$csv = PHP_EOL . implode( PHP_EOL, $studentProps ) . PHP_EOL ;
		} else {
			$csv = '';
		}

		$results->addValue(
			$this->studentPath( $courseIndex ),
			$propName . 's',
			array( '*'  => $csv )
		);

		if ( $studentsList ) {
			$this->outputCSVListofArticles(
				$courseIds,
				$studentsList,
				$results,
				$courseIndex
			);
		}

	}

	/**
	 * For an array of user objects, output
	 * their usernames or user IDs as query results.
	 *
	 * @param array $studentsList
	 * @param string $propName
	 * @param ApiResult $results
	 * @param int $courseId
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
	 * For an array of user objects, output
	 * their usernames or user IDs as query results.
	 *
	 * @param int[]|int $courseIds A list of one or more course IDs
	 * @param array $studentsList A set of student users
	 * @param ApiResult $results
	 * @param int $courseIndex
	 */
	protected function outputCSVListofArticles (
		$courseIds,
		$studentsList,
		$results,
		$courseIndex = null
	) {

		$articleNames = $this->getArticleNames( $courseIds, $studentsList );

		if ( $articleNames ) {
			$articleNames = PHP_EOL . implode( PHP_EOL, $articleNames ) . PHP_EOL;
		}

		$results->addValue(
			$this->articlePath( $courseIndex ),
			null,
			$articleNames
		);

		$results->setIndexedTagName_internal(
			$this->articlePath( $courseIndex ),
			'articles'
		);

	}

	/**
	 * For a course objects, output the institution, course name and term,
	 * and the start and end dates as a query result.
	 *
	 * @param int $courseId
	 * @param Course $course
	 * @param ApiResult $results
	 */
	protected function outputCourseProperties(
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

		// Add course start date.
		$startdate = $this->formatTimestamp( $course->getField( 'start' ) );

		$results->addValue(
			$courseIndex,
			'start',
			$startdate
		);

		// Add course end date.
		$enddate = $this->formatTimestamp( $course->getField( 'end' ) );

		$results->addValue(
			$courseIndex,
			'end',
			$enddate
		);

	}

	/**
	 * List the names of articles being worked on by a set of students
	 * in a set of courses.
	 *
	 * @param int[]|int $courseIds
	 * @param array $students Student objects
	 * @return array
	 */
	protected function getArticleNames( $courseIds, $students ) {

		// ArticleStore used to query which articles students are working on.
		$articleStore = new ArticleStore( 'ep_articles' );

		// Turn array of student objects into array of corresponding user IDs.
		foreach ( $students as $student ) {
			$studentIds[] = $student->getId();
		}

		// These are EPArticle objects, not conventional articles.
		$epArticles = $articleStore->getArticlesByCourseAndUsers( $courseIds, $studentIds );

		$articleNames = '';
		foreach ( $epArticles as $article ) {
			$articleNames[] = $article->getPageTitle();
		}

		return $articleNames;
	}

	/**
	 * Turn a MediaWiki timestamp representing a date into
	 * a human-readable date format.
	 *
	 * @param int $timestamp in MediaWiki timestamp format
	 * @return string
	 */
	protected function formatTimestamp ( $timestamp ) {
		$timestamp = wfTimestamp( TS_UNIX, $timestamp );
		$timestamp = date( 'Y-m-d', $timestamp );
		return $timestamp;
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

	/**
	 * Construct the results path for the <articles> element,
	 * either as part of an numerically indexed parent tag,
	 * or as a top-level element.
	 *
	 * @param int $courseIndex
	 */
	protected function articlePath( $courseIndex ) {
		if ( !is_null ( $courseIndex ) ) {
			return array ( $courseIndex );
		} else {
			return null;
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
			'csv' => 'If csv parameter is given, the query will return usernames in CSV format, and it will return the articles assigned to those students.',
		);
	}

	public function getDescription() {
		return array(
				'Get the usernames and other information for students enrolled in one or more courses.'
		);
	}

	protected function getExamples() {
		return array(
			'api.php?action=liststudents&courseids=3',
			'api.php?action=liststudents&courseids=3|4|5|6&group=&csv=',
			'api.php?action=liststudents&courseids=3|4|5|6&group=&csv=&prop=id'
		);
	}
}
