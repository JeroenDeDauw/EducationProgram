<?php

/**
 * Maintenance script for fixing redundant ("summary") data that may be
 * corrupted at this writing (30 January, 2014).
 *
 * If all goes according to plan, this script will only need to be run once on
 * any given wiki with Education Program Extension data created by a version
 * prior to commit 79c1b7dead27daaa3ac95c34a51b7ba88b267ae2.
 *
 * See https://www.mediawiki.org/wiki/Wikipedia_Education_Program/Database_Analysis_Notes
 *
 * @since 0.4 alpha
 *
 * @file fixSummaryData.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Andrew Green <andrew.green.df@gmail.com>
 */

namespace EducationProgram;

$basePath = getenv( 'MW_INSTALL_PATH' ) !== false ?
	getenv( 'MW_INSTALL_PATH' ) :
	dirname( __FILE__ ) . '/../../..';

require_once $basePath . '/maintenance/Maintenance.php';

class FixSummaryData extends \Maintenance {
	public function execute() {
		global $basePath;

		require_once $basePath . '/extensions/EducationProgram/EducationProgram.php';

		// rebuild summary fields for all institutions
		Orgs::singleton()->updateSummaryFields();

		// rebuild the contents of the ep_users_per_course table for all courses
		foreach ( Courses::singleton()->select() as $course ) {
			$course->rebuildUPCRows();
		}
	}
}

$maintClass = '\EducationProgram\FixSummaryData'; require_once( RUN_MAINTENANCE_IF_MAIN );
