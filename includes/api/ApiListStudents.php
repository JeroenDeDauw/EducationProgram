<?php

namespace EducationProgram;

use ApiBase;
use ApiResult;
use User;

/**
 * API module for gathering the usernames of students in one or more courses.
 *
 * @since 0.4 alpha/
 *
 * @ingroup EducationProgram
 * @ingroup API
 *
 * @license GPL-2.0-or-later
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
		$allStudents = [];

		// Determine which property to return: usernames or user IDs.
		$propName = $params['prop'];

		// ArticleStore used to query which articles students are working on.
		$articleStore = new ArticleStore( 'ep_articles' );

		// This course index keeps track of which course element to add results to.
		$courseIndex = 0;

		$courseIds = $params['courseids'];

		// Now go through each of the submitted course IDs,
		// and add all the student users from that course.
		foreach ( $courseIds as $courseId ) {
			// Get the course, or die if the course id is invalid.
			$course = Courses::singleton()->selectRow(
				null, [ 'id' => $courseId ] );
			if ( $course === false ) {
				$this->dieWithError(
					[ 'apierror-badparameter', 'courseids' ], 'invalid-course'
				);
			}

			// Get the user objects for students in this course,
			// and add them to the list of all students.
			$courseStudents = $this->getParticipantsAsUsers( $course, 'student' );
			$allStudents = array_merge( $allStudents, $courseStudents );

			// Check that we're not building too large of a query.
			if ( count( $allStudents ) > ApiBase::LIMIT_BIG2 ) {
				$this->dieWithError(
					[ 'apierror-educationprogram-overlimit', $courseId ], 'over-query-limit'
				);
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
						$courseIndex,
						$articleStore
					);

				// If 'csv' parameter is not given,
				// format and display the results as structured data.
				} else {

					// List the instructors
					$this->outputListOfNonStudentParticipantsProperties(
						$course,
						$results,
						$courseIndex,
						'instructor'
					);

					// List the online volunteers
					$this->outputListOfNonStudentParticipantsProperties(
						$course,
						$results,
						$courseIndex,
						'online_volunteer'
					);

					// List the campus volunteers
					$this->outputListOfNonStudentParticipantsProperties(
						$course,
						$results,
						$courseIndex,
						'campus_volunteer'
					);

					// List the students
					$this->outputListOfStudentProperties(
						$courseStudents,
						$propName,
						$results,
						$courseId,
						$courseIndex,
						$articleStore
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
					$courseIds, // The set of all course IDs from for the query
					null,
					$articleStore
				);
			} else {
				$this->outputListOfStudentProperties(
					$allStudents,
					$propName,
					$results,
					null,
					null,
					$articleStore
				);
			}
		} else {
			// Replace all the instances of $courseIndex in the results.
			if ( defined( 'ApiResult::META_CONTENT' ) ) {
				$results->addIndexedTagName( null, 'course' );
			} else {
				$results->setIndexedTagName_internal(
					null,
					'course'
				);
			}
		}
	}

	/**
	 * Given a course object, return the user objects of the
	 * students, instructors or volunteers in that course.
	 *
	 * @param Course $course
	 * @param string $courseRole
	 * @return array of user objects
	 */
	protected function getParticipantsAsUsers( Course $course, $courseRole ) {
		if ( $courseRole == 'instructor' ) {
			$participants = $course->getInstructors();
		} elseif ( $courseRole == 'online_volunteer' ) {
			$participants = $course->getOnlineAmbassadors();
		} elseif ( $courseRole == 'campus_volunteer' ) {
			$participants = $course->getCampusAmbassadors();
		} elseif ( $courseRole == 'student' ) {
			$participants = $course->getStudents();
		}

		$participantsInCourse = [];
		foreach ( $participants as $participant ) {
			$participantsInCourse[] = $participant->getUser();
		}

		return $participantsInCourse;
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
	 * @param ApiResult $results
	 * @param int[]|int $courseIds A list of one or more course IDs
	 * @param int|null $courseIndex
	 * @param ArticleStore $articleStore
	 */
	protected function outputCSVofStudentProperties(
		$studentsList,
		$propName,
		ApiResult $results,
		$courseIds,
		$courseIndex = null,
		ArticleStore $articleStore
	) {
		$studentProps = [];

		foreach ( $studentsList as $student ) {
			$studentProps[] = $this->getUserProperty( $student, $propName );
		}

		if ( $studentProps ) {
			$csv = PHP_EOL . implode( PHP_EOL, $studentProps ) . PHP_EOL;
		} else {
			$csv = '';
		}

		$results->addValue(
			$this->usersPath( $courseIndex ),
			$propName . 's',
			[ '*'  => $csv ]
		);

		if ( $studentsList ) {
			$this->outputCSVListofArticles(
				$courseIds,
				$studentsList,
				$results,
				$courseIndex,
				$articleStore
			);
		}
	}

	/**
	 * For an array of user objects of instructors or course
	 * volunteers, output their usernames or user IDs as query results.
	 *
	 * @param Course $course
	 * @param ApiResult $results
	 * @param int $courseIndex
	 * @param string $courseRole
	 */
	protected function outputListOfNonStudentParticipantsProperties(
		Course $course,
		ApiResult $results,
		$courseIndex,
		$courseRole
	) {
		// Determine the plural of the role name.
		$courseRolePlural = $courseRole . 's';

		// Get the user objects for instructors or course volunteers for this course.
		$participantList = $this->getParticipantsAsUsers( $course, $courseRole );

		// Add the properties for each participant to the result.
		$participantIndex = 0;

		foreach ( $participantList as $participant ) {
			// Add username to the result.
			$results->addValue(
				$this->userPath( $courseIndex, $courseRolePlural, $participantIndex ),
				'username',
				$participant->getName()
			);

			// Add user id to the result.
			$results->addValue(
				$this->userPath( $courseIndex, $courseRolePlural, $participantIndex ),
				'id',
				$participant->getId()
			);

			$participantIndex++;
		}

		// Index the participants.
		if ( defined( 'ApiResult::META_CONTENT' ) ) {
			$results->addIndexedTagName(
				$this->usersPath( $courseIndex, $courseRolePlural ),
				$courseRole
			);
		} else {
			$results->setIndexedTagName_internal(
				$this->usersPath( $courseIndex, $courseRolePlural ),
				$courseRole
			);
		}
	}

	/**
	 * For an array of user objects of students, output
	 * their usernames or user IDs as query results.
	 * Also output the articles assigned to each user,
	 * if possible.
	 *
	 * @param array $studentsList
	 * @param string $propName
	 * @param \ApiResult $results
	 * @param int|null $courseId
	 * @param int|null $courseIndex
	 * @param ArticleStore $articleStore
	 */
	protected function outputListOfStudentProperties(
		$studentsList,
		$propName,
		ApiResult $results,
		$courseId = null,
		$courseIndex = null,
		ArticleStore $articleStore
	) {
		// Add the properties for each student to the result.
		$studentIndex = 0;

		foreach ( $studentsList as $student ) {
			// Add username to the result.
			$results->addValue(
				$this->userPath( $courseIndex, 'students', $studentIndex ),
				'username',
				$student->getName()
			);

			// Add user id to the result.
			$results->addValue(
				$this->userPath( $courseIndex, 'students', $studentIndex ),
				'id',
				$student->getId()
			);

			// If output is grouped by course, get the assigned articles for each student.
			if ( $courseId ) {
				$studentEPArticles = $this->getEPArticles( $courseId, $student, $articleStore );

				$articleIndex = 0;
				foreach ( $studentEPArticles as $studentEPArticle ) {
					$studentArticle = $studentEPArticle->getPageTitle();
					$articlePath = [
						$courseIndex,
						'students',
						$studentIndex,
						$articleIndex
					];
					$results->addValue(
						$articlePath,
						'title',
						$studentArticle
					);

					// Get the reviewers for the article.
					$articleReviewers = $this->getArticleReviewerIds( $studentEPArticle );
					$reviewerIndex = 0;
					foreach ( $articleReviewers as $articleReviewer ) {
						$reviewerPath = [
							$courseIndex,
							'students',
							$studentIndex,
							$articleIndex,
							$reviewerIndex
						];

						$results->addValue(
							$reviewerPath,
							'username',
							User::newFromId( $articleReviewer )->getName()
						);
						$results->addValue(
							$reviewerPath,
							'id',
							$articleReviewer
						);
						$reviewerIndex++;
					}

					// Index the reviewers for the article.
					if ( defined( 'ApiResult::META_CONTENT' ) ) {
						$results->addIndexedTagName( $articlePath, 'reviewer' );
					} else {
						$results->setIndexedTagName_internal(
							$articlePath,
							'reviewer'
						);
					}

					$articleIndex++;
				}

				// Index the articles for the student.
				if ( defined( 'ApiResult::META_CONTENT' ) ) {
					$results->addIndexedTagName(
						$this->userPath( $courseIndex, 'students', $studentIndex ),
						'article'
					);
				} else {
					$results->setIndexedTagName_internal(
						$this->userPath( $courseIndex, 'students', $studentIndex ),
						'article'
					);
				}
			}

			$studentIndex++;
		}

		// Index the students.
		if ( defined( 'ApiResult::META_CONTENT' ) ) {
			$results->addIndexedTagName(
				$this->usersPath( $courseIndex, 'students' ),
				'student'
			);
		} else {
			$results->setIndexedTagName_internal(
				$this->usersPath( $courseIndex, 'students' ),
				'student'
			);
		}
	}

	/**
	 * For an array of user objects, output
	 * their usernames or user IDs as query results.
	 *
	 * @param int[]|int $courseIds A list of one or more course IDs
	 * @param array $studentsList A set of student users
	 * @param \ApiResult $results
	 * @param int|null $courseIndex
	 * @param ArticleStore $articleStore
	 */
	protected function outputCSVListofArticles(
		$courseIds,
		$studentsList,
		ApiResult $results,
		$courseIndex = null,
		ArticleStore $articleStore
	) {
		$articleNames = $this->getArticleNames( $courseIds, $studentsList, $articleStore );

		if ( $articleNames ) {
			$articleNames = PHP_EOL . implode( PHP_EOL, $articleNames ) . PHP_EOL;
		}

		$results->addValue(
			$this->articlesPath( $courseIndex ),
			null,
			$articleNames
		);

		if ( defined( 'ApiResult::META_CONTENT' ) ) {
			$results->addIndexedTagName(
				$this->articlesPath( $courseIndex ),
				'articles'
			);
		} else {
			$results->setIndexedTagName_internal(
				$this->articlesPath( $courseIndex ),
				'articles'
			);
		}
	}

	/**
	 * For a course objects, output the institution, course name and term,
	 * and the start and end dates as a query result.
	 *
	 * @param int $courseId
	 * @param Course $course
	 * @param int $courseIndex
	 * @param ApiResult $results
	 */
	protected function outputCourseProperties(
		$courseId,
		Course $course,
		$courseIndex,
		ApiResult $results
	) {
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
			$course->getField( 'title' )
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
	 * @param ArticleStore $articleStore
	 * @return array
	 */
	protected function getArticleNames( $courseIds, $students, ArticleStore $articleStore ) {
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
	 * Get the EPArticle objects for articles being worked on
	 * by a student in a course.
	 *
	 * @param int $courseId
	 * @param Student $student Student object
	 * @param ArticleStore $articleStore
	 * @return array
	 */
	protected function getEPArticles( $courseId, $student, ArticleStore $articleStore ) {
		$studentId = $student->getId();

		// These are EPArticle objects, not conventional articles.
		$epArticles = $articleStore->getArticlesByCourseAndUsers( $courseId, $studentId );

		return $epArticles;
	}

	/**
	 * Given an EPArticle, list the reviewers for that article.
	 *
	 * @param EPArticle $epArticle EPArticle object
	 * @return array
	 */
	protected function getArticleReviewerIds( EPArticle $epArticle ) {
		$reviewerIds = $epArticle->getReviewers();

		return $reviewerIds;
	}

	/**
	 * Turn a MediaWiki timestamp representing a date into
	 * a human-readable date format.
	 *
	 * @param int $timestamp in MediaWiki timestamp format
	 * @return string
	 */
	protected function formatTimestamp( $timestamp ) {
		$timestamp = wfTimestamp( TS_UNIX, $timestamp );
		$timestamp = date( 'Y-m-d', $timestamp );
		return $timestamp;
	}

	/**
	 * Construct the results path for the element for a group
	 * (<students>, <instructors>, <online_volunteers> or
	 * <campus_volunteers>), either as part of a numerically
	 * indexed parent tag, or as a top-level element.
	 *
	 * @param int $courseIndex
	 * @param string $userLabel
	 * @return array|string
	 */
	protected function usersPath( $courseIndex, $userLabel = 'students' ) {
		if ( !is_null( $courseIndex ) ) {
			return [ $courseIndex, $userLabel ];
		} else {
			return $userLabel;
		}
	}

	/**
	 * Construct the results path for individual users in a course:
	 * <student>, <instructor>, <online_volunteer> or <campus_volunteer>.
	 *
	 * @param int $courseIndex
	 * @param string $userLabel
	 * @param int|null $userIndex
	 * @return array
	 */
	protected function userPath( $courseIndex, $userLabel, $userIndex = null ) {
		if ( !is_null( $courseIndex ) ) {
			return [ $courseIndex , $userLabel, $userIndex ];
		} else {
			return [ $userLabel, $userIndex ];
		}
	}

	/**
	 * Construct the results path for the <articles> element,
	 * either as part of an numerically indexed parent tag,
	 * or as a top-level element.
	 *
	 * @param int $courseIndex
	 * @return int[]|null
	 */
	protected function articlesPath( $courseIndex ) {
		if ( !is_null( $courseIndex ) ) {
			return [ $courseIndex ];
		} else {
			return null;
		}
	}

	public function getAllowedParams() {
		return [

			'courseids' => [
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_REQUIRED => true,
				// This allows multiple pipe-separated values in courseids parameter.
				ApiBase::PARAM_ISMULTI => true,
			],

			'prop' => [
				ApiBase::PARAM_DFLT => 'username',
				ApiBase::PARAM_TYPE => [
					'username',
					'id',
				]
			],
			'group' => false,
			'csv' => false,
		];
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getParamDescription() {
		return [
			'courseids' => 'The IDs of courses, each separated by a |',
			'prop' => [
				'Which property to get for each student if using csv format:',
				' username       - The username of the student',
				' id             - The user ID of the student',
			],
			'group' => 'If group parameter is given, the query will group students by course.',
			'csv' => 'If csv parameter is given, the query will return usernames in CSV format, ' .
				'and it will return the articles assigned to those students.',
		];
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getDescription() {
		return [
			'Get the usernames and other information for students enrolled in one or more courses.'
		];
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	protected function getExamples() {
		return [
			'api.php?action=liststudents&courseids=3',
			'api.php?action=liststudents&courseids=3|4|5|6&group=',
			'api.php?action=liststudents&courseids=3|4|5|6&group=&csv=&prop=id'
		];
	}

	/**
	 * @see ApiBase::getExamplesMessages()
	 */
	protected function getExamplesMessages() {
		return [
			'action=liststudents&courseids=3'
				=> 'apihelp-liststudents-example-1',
			'action=liststudents&courseids=3|4|5|6&group='
				=> 'apihelp-liststudents-example-2',
			'action=liststudents&courseids=3|4|5|6&group=&csv=&prop=id'
				=> 'apihelp-liststudents-example-3',
		];
	}

}
