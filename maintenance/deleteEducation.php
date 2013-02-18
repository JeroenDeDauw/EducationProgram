<?php

/**
 * Maintenance script for deleting all Wikipedia Education Program database tables.
 *
 * @since 0.1
 *
 * @file DeleteEducation.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */

$basePath = getenv( 'MW_INSTALL_PATH' ) !== false ? getenv( 'MW_INSTALL_PATH' ) : __DIR__ . '/../../..';

require_once $basePath . '/maintenance/Maintenance.php';

class DeleteEducation extends Maintenance {

	public function __construct() {
		$this->mDescription = 'Drop the Wikipedia Education Program tables';

		parent::__construct();
	}

	public function execute() {
		echo "Are you really really sure you want to delete all EP date?? If so, type YES\n";

		if ( $this->readconsole() !== 'YES' ) {
			return;
		}

		$tables = array(
			'orgs',
			'courses',
			'students',
			'users_per_course',
			'instructors',
			'oas',
			'cas',
			'articles',
			'revisions',
			'events'
		);

		$dbw = wfGetDB( DB_MASTER );

		foreach ( $tables as $table ) {
			$name = "ep_$table";

			echo "Dropping table $name...";

			$dbw->query( 'DROP TABLE IF EXISTS ' . $dbw->tableName( $name ) );

			echo "done!\n";
		}
	}

}

$maintClass = 'DeleteEducation';
require_once( RUN_MAINTENANCE_IF_MAIN );
