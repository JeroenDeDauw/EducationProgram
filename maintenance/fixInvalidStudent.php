<?php

/**
 * Maintenance script for fixing database corruption due to ApiAddStudents
 * adding invalid users.
 *
 * A bug in ApiAddStudents allowed some users to inadvertently enroll
 * invalid users in courses. This script reviews all courses in a project
 * to check for invalid users and removes any that it finds.
 *
 * This bug was eliminated in commit 3ea735d39a72b6467ab71618a85dc889aee2b068.
 *
 * See:
 * https://bugzilla.wikimedia.org/show_bug.cgi?id=66624
 * https://bugzilla.wikimedia.org/show_bug.cgi?id=66631
 *
 * @since 0.5 alpha
 *
 * @file fixInvalidStudent.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Andrew Green <andrew.green.df@gmail.com>
 */

namespace EducationProgram;

$basePath = getenv( 'MW_INSTALL_PATH' ) !== false ?
	getenv( 'MW_INSTALL_PATH' ) :
	__DIR__ . '/../../..';

require_once $basePath . '/maintenance/Maintenance.php';

class FixInvalidStudent extends \Maintenance {

	private $courseStore = null;
	private $dryRun = false;

	public function __construct() {

		parent::__construct();
		$this->addOption( 'courseId', 'ID of the course to repair', false, true );
		$this->addOption( 'dryRun', 'Go through the moves but don\'t really do anything', false );
	}

	public function execute() {

		if ( $this->getOption( 'dryRun' ) ) {
			$this->dryRun = true;
			$this->output( "Dry run. No changes will be made to the DB.\n" );
		}

		$this->courseStore = Extension::globalInstance()->newCourseStore();
		$courseId = $this->getOption( 'courseId' );

		if ( $courseId ) {
			$this->repairCourse( $courseId );
			return;
		}

		$dbr = wfGetDB( DB_SLAVE );
		$results = $dbr->select(
			'ep_users_per_course',
			'upc_course_id',
			[ 'upc_user_id' => 0 ],
			__METHOD__
		);

		$numAffected = $results->numRows();

		$this->output( "Found user id 0 associated with " . $numAffected .
			" course(s).\n" );

		if ( $numAffected === 0 ) {
			return;
		}

		// This will repair ep_courses, ep_users_per_course, as well as
		// course and summary data.
		foreach ( $results as $r ) {
			$this->repairCourse( $r->upc_course_id );
		}

		$this->output( "Repairing ep_students table.\n" );

		if ( !$this->dryRun ) {
			$dbw = wfGetDB( DB_MASTER );
			$dbw->delete( 'ep_students', [ 'student_user_id' => 0 ] );
		}
	}

	private function repairCourse( $id ) {
		$course = $this->courseStore->getCourseById( $id );
		$this->output( "Repairing " . $course->getTitle()->getText() . "\n" );

		$students = $course->getField( 'students' );

		$key = array_search( '', $students );
		if ( $key !== false ) {
			unset( $students[$key] );
		}

		if ( !$this->dryRun ) {

			$course->setField( 'students', $students );
			$course->disableLogging();

			// This has lots of side effects
			$course->save();
		}
	}
}

$maintClass = '\EducationProgram\FixInvalidStudent';
require_once RUN_MAINTENANCE_IF_MAIN;
