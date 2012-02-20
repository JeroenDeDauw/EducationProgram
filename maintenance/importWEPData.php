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
		
		echo 'Found ' . count( $orgs ) . ' orgs, ' . count( $courses ) . ' courses and ' . count( $students ) . " students.\n";
		
		echo "Inserting orgs ...";
		
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
		
		echo " done!\n";
		
		echo "Inserting courses ...\n";
		
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
			}
			catch ( Exception $ex ) {
				echo "Failed to insert course '$name'.\n";
			}
		}
		
		echo "Inserted courses\n";
		
		echo "\n\n";
	}
	
}

$maintClass = 'ImportWEPData';
require_once( RUN_MAINTENANCE_IF_MAIN );
