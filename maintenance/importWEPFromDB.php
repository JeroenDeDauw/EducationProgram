<?php

/**
 * Maintenance scrtip for importing Wikipedia Education Program data from before this extension was used.
 *
 * @since 0.1
 *
 * @file importFromDB.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v3 or later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */

$basePath = getenv( 'MW_INSTALL_PATH' ) !== false ? getenv( 'MW_INSTALL_PATH' ) : dirname( __FILE__ ) . '/../../..';

require_once $basePath . '/maintenance/Maintenance.php';

class ImportWEPFromDB extends Maintenance {

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

	protected $override = true;

	public function execute() {
		global $basePath;
		require_once $basePath . '/extensions/EducationProgram/EducationProgram.php';

		$conds = array(
			'orgs' => array(),//array( 'university_country <> "India"' ),
			'courses'=> array(),
			'students' => array(),
		);

		$tables = array(
			'orgs' => 'imp_universities',
			'courses'=> 'imp_courses',
			'students' => 'imp_students',
		);

		$dbr = wfGetDB( DB_SLAVE );

		foreach ( $conds as $name => $cond ) {
			$nr = $dbr->select(
				$tables[$name],
				array( 'COUNT(*) AS rowcount' ),
				$cond
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
	}

	protected function msg( $msg, $level = 1 ) {
		if ( $level <= 2 ) {
			echo $msg;
			echo "\n";
		}
	}

	/**
	 * Insert the orgs.
	 *
	 * @since 0.1
	 *
	 * @param ResultWrapper $orgs
	 */
	public function insertOrgs( ResultWrapper $orgs ) {
		$revAction = new EPRevisionAction();
		$revAction->setUser( $GLOBALS['wgUser'] );
		$revAction->setComment( 'Import' );

		$countries = array_flip( CountryNames::getNames( 'EN' ) );

		$dbw = wfGetDB( DB_MASTER );

		$dbw->begin();

		$orgTable = EPOrgs::singleton();

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

				$orgObject = $orgTable->newFromArray(
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
		$revAction = new EPRevisionAction();
		$revAction->setUser( $GLOBALS['wgUser'] );
		$revAction->setComment( 'Import' );

		$courseTable = EPCourses::singleton();

		foreach ( $courses as $course ) {
			$term = $course->course_term . ' ' . $course->course_year;
			$name = $course->course_coursename . ' (' . $term . ')';

			$this->msg( 'Importing course ' . $name );

			$currentId = $courseTable->selectFieldsRow( 'id', array( 'name' => $name ) );

			$this->msg( "\t" . ( $currentId === false ? 'is new, inserting...' : ( $this->override ? 'exists, updating...' : 'exists, skipping...' ) ), 2 );

			$course->course_startdate = str_replace( '-', '', $course->course_startdate );
			$course->course_enddate = str_replace( '-', '', $course->course_enddate );

			if ( $currentId === false || $this->override ) {
				$data = array(
					'org_id' => $this->orgIds[$course->course_university_id],
					'name' => $name,
					'mc' => $course->course_coursename,
					'start' => $course->course_startdate . '000000',
					'end' => ( $course->course_enddate === '' ? $course->course_startdate : $course->course_enddate ) . '000000',
					'lang' => $course->course_language,
					'term' => $term,
				);

				if ( $currentId !== false ) {
					$data['id'] = $currentId;
				}

				$courseObject = $courseTable->newFromArray(
					$data,
					$currentId === false
				);

				try{
					$courseObject->revisionedSave( $revAction );
					$this->courseIds[$course->course_id] = $courseObject->getId();
				}
				catch ( Exception $ex ) {
					$this->msg( "\t ERROR: Failed to insert course '$name'.\n" );
				}
			}
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
	 * @param ResultWrapper $courses
	 */
	public function insertStudents( ResultWrapper $courses ) {
//		foreach ( $students as $name => $courseNames ) {
//			$user = User::newFromName( $name );
//
//			if ( $user === false ) {
//				echo "Failed to insert student '$name'. (invalid user name)\n";
//			}
//			else {
//				if ( $user->getId() === 0 ) {
//					$user->setPassword( 'ohithere' );
//					$user->addToDatabase();
//				}
//
//				if ( $user->getId() === 0 ) {
//					echo "Failed to insert student '$name'. (failed to create user)\n";
//				}
//				else {
//					$student = EPStudent::newFromUser( $user );
//
//					if ( is_null( $student->getId() ) ) {
//						if ( !$student->save() ) {
//							echo "Failed to insert student '$name'. (failed create student profile)\n";
//							continue;
//						}
//					}
//
//					$courses = array();
//
//					foreach ( $courseNames as $courseName ) {
//						if ( array_key_exists( $courseName, $courseIds ) ) {
//							$revAction = new EPRevisionAction();
//							$revAction->setUser( $user );
//							$revAction->setComment( 'Import' );
//
//							$course = EPCourses::singleton()->selectRow( null, array( 'id' => $courseIds[$courseName] ) );
//							$course->enlistUsers( array( $user->getId() ), 'student', true, $revAction );
//						}
//						else {
//							echo "Failed to associate student '$name' with course '$courseName'.\n";
//						}
//					}
//
//					if ( $student->associateWithCourses( $courses ) ) {
//						echo "Inserted student '$name'\t\t and associated with courses: " . str_replace( '_', ' ', implode( ', ', $courseNames ) ) . "\n";
//					}
//					else {
//						echo "Failed to insert student '$name'. (failed to associate courses)\n";
//					}
//				}
//			}
//		}
	}

}

$maintClass = 'ImportWEPFromDB';
require_once( RUN_MAINTENANCE_IF_MAIN );
