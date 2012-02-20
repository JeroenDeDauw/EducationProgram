<?php

/**
 * Maintenance scrtip for importing Wikipedia Education Program data from before this extension was used.
 *
 * @since 0.1
 *
 * @file importWEPData.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v3 or later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */

$basePath = getenv( 'MW_INSTALL_PATH' ) !== false ? getenv( 'MW_INSTALL_PATH' ) : dirname( __FILE__ ) . '/../../..';

require_once $basePath . '/maintenance/Maintenance.php';

class ImportWEPData extends Maintenance {
	
	public function __construct() {
		$this->mDescription = 'Import Wikipedia Education Program data';
		
		$this->addOption( 'file', 'File to read from.', true );
		
		parent::__construct();
	}
	
	public function execute() {
		global $basePath;
		require_once $basePath . '/extensions/EducationProgram/EducationProgram.php';
		
		$text = file_get_contents( $this->getOption( 'file' ) );

		if ( $text === false ) {
			echo "Could not read file";
			return;
		}
		
		$orgs = array(); // org name => org id
		$courses = array(); // course => org name
		$students = array(); // student => [ courses ]
		
		foreach ( explode( "\n", $text ) as $line ) {
			$cells = explode( ',', $line );
			
			if ( count( $cells ) == 3 ) {
				$student = $cells[0];
				$course = $cells[1];
				$org = $cells[2];
				
				if ( !array_key_exists( $student, $students ) ) {
					$students[$student] = array();
				}
				
				if ( !in_array( $course, $students[$student] ) ) {
					$students[$student][] = $course;
				}
				
				$courses[$course] = $org;
				$orgs[$org] = false;
			}
		}
		
		echo "\nFound " . count( $orgs ) . ' orgs, ' . count( $courses ) . ' courses and ' . count( $students ) . " students.\n\n";
		
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
	 * @since 0.1
	 * 
	 * @param array $orgs Org names as keys. Values get set to the id after insertion.
	 */
	protected function insertOrgs( array &$orgs ) {
		wfGetDB( DB_MASTER )->begin();
		
		foreach ( $orgs as $org => &$id ) {
			$org = EPOrgs::singleton()->newFromArray(
				array(
					'name' => $org,
					'country' => 'US',
				),
				true
			);
			
			$org->save();
			$id = $org->getId();
		}
		
		wfGetDB( DB_MASTER )->commit();	
	}
	
	/**
	 * Insert the courses.
	 * 
	 * @param array $courses
	 * @param array $orgs
	 * 
	 * @return array Inserted courses. keys are names, values are ids
	 */
	protected function insertCourses( array $courses, array $orgs ) {
		$courseIds = array();
		
		foreach ( $courses as $course => $org ) {
			$name = $course;
			
			$start = wfTimestamp( TS_MW );
			$end = wfTimestamp( TS_MW );
			$start{3} = '1';
			$end{3} = '3';
			
			$course = EPCourses::singleton()->newFromArray(
				array(
					'org_id' => $orgs[$org],
					'name' => $course,
					'mc' => $course,
					'start' => $start,
					'end' => $end,
					'lang' => 'en',
				),
				true
			);
			
			try{
				$course->save();
				$courseIds[$name] = $course->getId();
				$name = str_replace( '_', ' ', $name );
				echo "Inserted course '$name'.\n";
			}
			catch ( Exception $ex ) {
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
	 * @since 0.1
	 * 
	 * @param array $students Keys are names, values are arrays with course names
	 * @param array $courseIds Maps course names to ids
	 */
	protected function insertStudents( array $students, array $courseIds ) {
		foreach ( $students as $name => $courseNames ) {
			$user = User::newFromName( $name );
			
			if ( $user === false ) {
				echo "Failed to insert student '$name'. (invalid user name)\n";
			}
			else {
				if ( $user->getId() === 0 ) {
					$user->setPassword( 'ohithere' );
					$user->addToDatabase();
				}
				
				if ( $user->getId() === 0 ) {
					echo "Failed to insert student '$name'. (failed to create user)\n";
				}
				else {
					$student = EPStudent::newFromUser( $user );
					
					if ( is_null( $student->getId() ) ) {
						if ( !$student->save() ) {
							echo "Failed to insert student '$name'. (failed create student profile)\n";
							continue;
						}
					}
					
					$courses = array();
					
					foreach ( $courseNames as $courseName ) {
						if ( array_key_exists( $courseName, $courseIds ) ) {
							$revAction = new EPRevisionAction();
							$revAction->setUser( $user );
							$revAction->setComment( 'Import' );
							
							$course = EPCourses::singleton()->selectRow( null, array( 'id' => $courseIds[$courseName] ) );
							$course->enlistUsers( array( $user->getId() ), 'student', true, $revAction );
						}
						else {
							echo "Failed to associate student '$name' with course '$courseName'.\n";
						}
					}

					if ( $student->associateWithCourses( $courses ) ) {
						echo "Inserted student '$name'\t\t and associated with courses: " . str_replace( '_', ' ', implode( ', ', $courseNames ) ) . "\n";
					}
					else {
						echo "Failed to insert student '$name'. (failed to associate courses)\n";
					}
				}
			}
		}	
	}
	
}

$maintClass = 'ImportWEPData';
require_once( RUN_MAINTENANCE_IF_MAIN );
