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
	
	protected function insertOrgs( array $orgs ) {
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
	
	protected function insertCourses( array $courses, array $orgs ) {
		$courseIds = array();
		
		foreach ( $courses as $course => $org ) {
			$name = $course;
			
			$course = EPCourses::singleton()->newFromArray(
				array(
					'org_id' => $orgs[$org],
					'name' => $course,
					'mc' => $course,
				),
				true
			);
			
			try{
				$course->save();
				$courseIds[$name] = $course->getId();
			}
			catch ( Exception $ex ) {
				echo "Failed to insert course '$name'.\n";
			}
		}

		return $courseIds;
	}
	
	protected function insertStudents( array $students, array $courseIds ) {
		foreach ( $students as $student => $courseNames ) {
			$name = $student;
			$user = User::newFromName( $student );
			
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
					
					if ( $student === false ) {
						$student = new EPStudent(
							array(
								'user_id' => $user->getId(),
								'first_enroll' => wfTimestamp( TS_MW )
							),
							true
						);
						
						if ( !$student->save() ) {
							echo "Failed to insert student '$name'. (failed create student profile)\n";
							continue;
						}
					}
					
					$courses = array();
					
					foreach ( $courseNames as $courseName ) {
						if ( array_key_exists( $courseName, $courseIds ) ) {
							$courses[] = EPCourses::singleton()->newFromArray( array(
								'id' => $courseIds[$courseName],
								'students' => array(),
							) );
						}
						else {
							echo "Failed to associate student '$name' with course '$courseName'.\n";
						}
					}
					
					if ( $student->associateWithCourses( $courses ) ) {
						echo "Imported student $name\n";
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
