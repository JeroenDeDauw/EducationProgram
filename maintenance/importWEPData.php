<?php

/**
 * Maintenance script for importing Wikipedia Education Program data
 * from before this extension was used.
 *
 * @since 0.1
 *
 * @file importWEPData.php
 * @ingroup EducationProgram
 *
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */

namespace EducationProgram;

$basePath = getenv( 'MW_INSTALL_PATH' ) !== false
	? getenv( 'MW_INSTALL_PATH' )
	: __DIR__ . '/../../..';

require_once $basePath . '/maintenance/Maintenance.php';

class ImportWEPData extends \Maintenance {

	public function __construct() {
		$this->mDescription = 'Import Wikipedia Education Program data';

		$this->addOption( 'file', 'File to read from.', true );

		parent::__construct();
		$this->requireExtension( 'Education Program' );
	}

	public function execute() {
		die( 'Not meant to be run in production.' );

		$text = file_get_contents( $this->getOption( 'file' ) );

		if ( $text === false ) {
			echo "Could not read file";
			return;
		}

		$orgs = []; // org name => org id
		$courses = []; // course => org name
		$students = []; // student => [ courses ]

		$text = str_replace(
			[ '_', '%27' ],
			[ ' ', "'" ],
			$text
		);

		foreach ( explode( "\n", $text ) as $line ) {
			$cells = explode( ',', $line );

			if ( count( $cells ) == 3 ) {
				$student = $cells[0];
				$course = $cells[1];
				$org = $cells[2];

				if ( !array_key_exists( $student, $students ) ) {
					$students[$student] = [];
				}

				if ( !in_array( $course, $students[$student] ) ) {
					$students[$student][] = $course;
				}

				$courses[$course] = $org;
				$orgs[$org] = false;
			}
		}

		echo "\nFound " . count( $orgs ) . ' orgs, ' . count( $courses ) . ' courses and ' .
			count( $students ) . " students.\n\n";

		echo "Inserting orgs ...";
		$this->insertOrgs( $orgs );
		echo " done!\n";

		echo "Inserting courses ...\n";
		$courseIds = $this->insertCourses( $courses, $orgs );
		echo "Inserted courses\n";

		echo "Inserting students ...\n";
		$this->insertStudents( $students, $courseIds );
		echo "Inserted students\n";

		echo "Import completed!\n\n";
	}

	/**
	 * Insert the orgs.
	 *
	 * @param array &$orgs Org names as keys. Values get set to the id after insertion.
	 */
	protected function insertOrgs( array &$orgs ) {
		$revAction = new RevisionAction();
		$revAction->setUser( $GLOBALS['wgUser'] );
		$revAction->setComment( 'Import' );

		wfGetDB( DB_MASTER )->startAtomic( __METHOD__ );

		foreach ( $orgs as $org => &$id ) {
			/**
			 * @var RevisionedObject $org
			 */
			$org = Orgs::singleton()->newRow(
				[
					'name' => $org,
					'country' => 'US',
				],
				true
			);

			$org->revisionedSave( $revAction );
			$id = $org->getId();
		}

		wfGetDB( DB_MASTER )->endAtomic( __METHOD__ );
	}

	/**
	 * Insert the courses.
	 *
	 * @param string[] $courses Associative array, mapping course names to org names
	 * @param int[] $orgs Associative array, mapping org names to org ids
	 *
	 * @return array Inserted courses. keys are names, values are ids
	 */
	protected function insertCourses( array $courses, array $orgs ) {
		$revAction = new RevisionAction();
		$revAction->setUser( $GLOBALS['wgUser'] );
		$revAction->setComment( 'Import' );

		$courseIds = [];

		foreach ( $courses as $course => $org ) {
			$name = $course;

			$start = wfTimestamp( TS_MW );
			$end = wfTimestamp( TS_MW );
			$start{3} = '1';
			$end{3} = '3';

			$course = Courses::singleton()->newRow(
				[
					'org_id' => $orgs[$org],
					'title' => $course,
					'name' => $course,
					'start' => $start,
					'end' => $end,
					'lang' => 'en',
				],
				true
			);

			try {
				/**
				 * @var RevisionedObject $course
				 */
				$course->revisionedSave( $revAction );
				$courseIds[$name] = $course->getId();
				$name = str_replace( '_', ' ', $name );
				echo "Inserted course '$name'.\n";
			}
			catch ( \Exception $ex ) {
				echo "Failed to insert course '$name'.\n";
			}
		}

		return $courseIds;
	}

	/**
	 * Insert the students.
	 * Create user account if none matches the name yet.
	 * Create student profile if none matches the user yet.
	 * Associate with courses.
	 *
	 * @param array $students Keys are names, values are arrays with course names
	 * @param array $courseIds Maps course names to ids
	 */
	protected function insertStudents( array $students, array $courseIds ) {
		foreach ( $students as $name => $courseNames ) {
			$user = \User::newFromName( $name );

			if ( $user === false ) {
				echo "Failed to insert student '$name'. (invalid user name)\n";
			} else {
				if ( $user->getId() === 0 ) {
					if ( !$user->addToDatabase()->isOK() ) {
						echo "Failed to insert student '$name'. (failed to create user)\n";
						continue;
					}
					$user->setPassword( 'ohithere' );
					$user->saveSettings();
				}

				$student = Student::newFromUser( $user );

				if ( is_null( $student->getId() ) ) {
					if ( !$student->save() ) {
						echo "Failed to insert student '$name'. (failed create student profile)\n";
						continue;
					}
				}

				$courses = [];

				foreach ( $courseNames as $courseName ) {
					if ( array_key_exists( $courseName, $courseIds ) ) {
						$revAction = new RevisionAction();
						$revAction->setUser( $user );
						$revAction->setComment( 'Import' );

						/**
						 * @var Course $course
						 */
						$course = Courses::singleton()->selectRow(
							null, [ 'id' => $courseIds[$courseName] ]
						);
						$course->enlistUsers( [ $user->getId() ], 'student', true, $revAction );
					} else {
						echo "Failed to associate student '$name' with course '$courseName'.\n";
					}
				}

				if ( $student->associateWithCourses( $courses ) ) {
					echo "Inserted student '$name'\t\t and associated with courses: " .
						str_replace( '_', ' ', implode( ', ', $courseNames ) ) . "\n";
				} else {
					echo "Failed to insert student '$name'. (failed to associate courses)\n";
				}
			}
		}
	}

}

$maintClass = 'EducationProgram\ImportWEPData';
require_once RUN_MAINTENANCE_IF_MAIN;
