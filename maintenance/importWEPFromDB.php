<?php

/**
 * Maintenance script for importing Wikipedia Education Program data from before this extension was used.
 *
 * @since 0.1
 *
 * @file importFromDB.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */

namespace EducationProgram;
use ResultWrapper;

$basePath = getenv( 'MW_INSTALL_PATH' ) !== false ? getenv( 'MW_INSTALL_PATH' ) : __DIR__ . '/../../..';

require_once $basePath . '/maintenance/Maintenance.php';

class ImportWEPFromDB extends \Maintenance {

	public function __construct() {
		$this->mDescription = 'Import Wikipedia Education Program data';

		parent::__construct();
	}

	protected function tableName( $table, $incMw = false ) {
		$table = "imp_$table";

		if ( $incMw ) {
			$table = wfGetDB( DB_SLAVE )->tableName( $table );
		}

		return $table;
	}

	protected $orgIds = array();
	protected $courseIds = array();

	protected $errors = array();

	protected $override = true;

	protected $msgLevel = 2;

	public function execute() {
		die( 'Not meant to be run in production.' );

		global $basePath;
		require_once $basePath . '/extensions/EducationProgram/EducationProgram.php';

		$conds = array(
			'orgs' => array( 'university_country <> "India"' ),
			'courses' => array(),
			'students' => array(),
		);

		$tables = array(
			'orgs' => 'imp_universities',
			'courses' => 'imp_courses',
			'students' => 'imp_students',
		);

		$dbr = wfGetDB( DB_SLAVE );

		foreach ( $conds as $name => $cond ) {
			$nr = $dbr->select(
				$tables[$name],
				array( 'COUNT(*) AS rowcount' ),
				$cond,
				__METHOD__,
				array() //array( 'LIMIT' => 200 )
			)->fetchObject()->rowcount;

			$this->msg( "Found $nr $name...", 0 );
		}

		$functions = array(
			'orgs' => 'insertOrgs',
			'courses' => 'insertCourses',
			'students' => 'insertStudents',
		);

		foreach ( $functions as $stuff => $function ) {
			$this->msg( "Inseting $stuff...", 0 );

			$stuff = $dbr->select(
				$tables[$stuff],
				'*',
				$conds[$stuff]
			);

			call_user_func( array( $this, $function ), $stuff );
		}

		$this->msg( 'Import done!', 0 );

		$this->showErrors();
	}

	/**
	 * Show a message.
	 *
	 * @param string $msg
	 * @param integer $level
	 */
	protected function msg( $msg, $level = 1 ) {
		if ( $level <= $this->msgLevel ) {
			echo $msg;
			echo "\n";
		}
	}

	/**
	 * Show an error.
	 *
	 * @param string $msg
	 * @param integer $level
	 */
	protected function err( $msg, $level = 1 ) {
		$this->errors[] = $msg;
		$this->msg( "\tERROR: $msg", $level );
	}

	/**
	 * Insert the orgs.
	 *
	 * @since 0.1
	 *
	 * @param ResultWrapper $orgs
	 */
	public function insertOrgs( ResultWrapper $orgs ) {
		$revAction = new RevisionAction();
		$revAction->setUser( $GLOBALS['wgUser'] );
		$revAction->setComment( 'Import' );

		$countries = array_flip( \CountryNames::getNames( 'EN' ) );

		$dbw = wfGetDB( DB_MASTER );

		$dbw->begin();

		$orgTable = Orgs::singleton();

		foreach ( $orgs as $org ) {
			$this->msg( 'Importing org ' . $org->university_name );

			$currentId = $orgTable->selectFieldsRow( 'id', array( 'name' => $org->university_name ) );

			$this->msg( "\t" . ( $currentId === false ? 'is new, inserting...' : ( $this->override ? 'exists, updating...' : 'exists, skipping...' ) ), 2 );

			if ( $currentId === false || $this->override ) {
				$data = array(
					'name' => $org->university_name,
					'city' => $org->university_city,
					'country' => array_key_exists( $org->university_country, $countries ) ? $countries[$org->university_country] : '',
				);

				if ( $currentId !== false ) {
					$data['id'] = $currentId;
				}

				/**
				 * @var Org $orgObject
				 */
				$orgObject = $orgTable->newRow(
					$data,
					$currentId === false
				);

				$orgObject->revisionedSave( $revAction );
				$this->orgIds[$org->university_id] = $orgObject->getId();
			}
		}

		$dbw->commit();
	}

	/**
	 * Insert the courses.
	 *
	 * @param ResultWrapper $courses
	 */
	public function insertCourses( ResultWrapper $courses ) {
		$revAction = new RevisionAction();
		$revAction->setUser( $GLOBALS['wgUser'] );
		$revAction->setComment( 'Import' );

		$courseTable = Courses::singleton();

		foreach ( $courses as $course ) {
			$term = $course->course_term . ' ' . $course->course_year;
			$title = $course->course_coursename . ' (' . $term . ')';

			$this->msg( 'Importing course ' . $title );

			$currentId = $courseTable->selectFieldsRow( 'id', array( 'title' => $title ) );

			$this->msg( "\t" . ( $currentId === false ? 'is new, inserting...' : ( $this->override ? 'exists, updating...' : 'exists, skipping...' ) ), 2 );

			$course->course_startdate = str_replace( '-', '', $course->course_startdate );
			$course->course_enddate = str_replace( '-', '', $course->course_enddate );

			if ( array_key_exists( $course->course_university_id, $this->orgIds ) ) {
				if ( $currentId === false || $this->override ) {
					$this->insertCourse( $currentId, $course, $title, $term, $revAction );
				}
			}
			else {
				$this->err( "Failed to insert course '$title'. Linked org ($course->course_university_id) does not exist!" );
			}
		}
	}

	/**
	 * Inset the provided course.
	 *
	 * @param integer $currentId
	 * @param \stdClass $course
	 * @param string $title
	 * @param string $term
	 * @param RevisionAction $revAction
	 */
	protected function insertCourse( $currentId, $course, $title, $term, RevisionAction $revAction ) {
		$data = array(
			'org_id' => $this->orgIds[$course->course_university_id],
			'title' => $title,
			'name' => $course->course_coursename,
			'start' => $course->course_startdate . '000000',
			'end' => ( $course->course_enddate === '' ? $course->course_startdate : $course->course_enddate ) . '000000',
			'lang' => $course->course_language,
			'term' => $term,
		);

		if ( $currentId !== false ) {
			$data['id'] = $currentId;
		}

		/**
		 * @var Course $courseObject
		 */
		$courseObject = Courses::singleton()->newRow(
			$data,
			$currentId === false
		);

		try {
			$courseObject->revisionedSave( $revAction );
			$this->courseIds[$course->course_id] = $courseObject->getId();
		}
		catch ( \Exception $ex ) {
			$this->err( "Failed to insert course '$course->course_coursename'." );
		}
	}

	/**
	 * Insert the students.
	 * Create user account if none matches the name yet.
	 * Create student profile if none matches the user yet.
	 * Associate with courses.
	 *
	 * @since 0.1
	 *
	 * @param ResultWrapper $students
	 */
	public function insertStudents( ResultWrapper $students ) {
		foreach ( $students as $student ) {
			$name  = $student->student_username;

			$this->msg( 'Importing student ' . $name );

			$user = \User::newFromName( $name );

			if ( $user === false ) {
				$this->err( "Failed to insert student '$name'. (invalid user name)" );
			}
			else {
				if ( $user->getId() === 0 ) {
					$user->setPassword( 'ohithere' );

					if ( $student->student_lastname !== '' && $student->student_firstname !== '' ) {
						$user->setRealName( $student->student_firstname . ' ' . $student->student_lastname );
					}

					if ( $student->student_email !== '' ) {
						$user->setEmail( $student->student_email );
					}

					$user->addToDatabase();
				}

				if ( $user->getId() === 0 ) {
					$this->err( "Failed to insert student '$name'. (failed to create user)" );
				}
				else {
					$studentObject = Student::newFromUser( $user );

					if ( is_null( $studentObject->getId() ) ) {
						if ( !$studentObject->save() ) {
							$this->err( "Failed to insert student '$name'. (failed create student profile)" );
							continue;
						}
					}

					foreach ( array( $student->student_course_id ) as $courseId ) {
						$success = false;

						if ( array_key_exists( $courseId, $this->courseIds ) ) {
							$revAction = new RevisionAction();
							$revAction->setUser( $user );
							$revAction->setComment( 'Import' );

							/**
							 * @var Course $course
							 */
							$course = Courses::singleton()->selectRow( null, array( 'id' => $this->courseIds[$courseId] ) );
							$success = $course->enlistUsers( array( $user->getId() ), 'student', true, $revAction );
						}

						if ( $success !== false ) {
							$this->msg( "\tAssociated student '$name' with course '$courseId'.", 2 );
						}
						else {
							$this->msg( "\tFailed to associate student '$name' with course '$courseId'." );
						}
					}
				}
			}
		}
	}

	/**
	 * Show the errors encountered by the script.
	 */
	protected function showErrors() {
		if ( !empty( $this->errors ) ) {
			$count = count( $this->errors );
			$this->msg( "\nThe import script encountered some errors ($count)" );

			foreach ( $this->errors as $error ) {
				$this->msg( "* $error" );
			}
		}
	}

}

$maintClass = 'EducationProgram\ImportWEPFromDB';
require_once( RUN_MAINTENANCE_IF_MAIN );
