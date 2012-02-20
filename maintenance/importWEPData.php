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
		
		$orgs = array(); // org
		$courses = array(); // course => org
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
				$orgs[] = $org;
			}
		}
		
		$orgs = array_unique( $orgs );
		
		echo 'Found ' . count( $orgs ) . ' orgs, ' . count( $courses ) . ' courses and ' . count( $students ) . " students.\n";
		
		wfGetDB( DB_MASTER )->begin();
		
		foreach ( $orgs as $org ) {
			$org = EPOrgs::singleton()->newFromArray(
				array(
					'name' => $org,
					'country' => 'us',
				),
				true
			);
			
			$org->save();
		}
		
		wfGetDB( DB_MASTER )->commit();
		
		echo "\n\n";
	}
	
}

$maintClass = 'ImportWEPData';
require_once( RUN_MAINTENANCE_IF_MAIN );
