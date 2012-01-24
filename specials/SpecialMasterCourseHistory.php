<?php

/**
 * Special page for listing the history of a master course.
 *
 * @since 0.1
 *
 * @file SpecialMasterCourseHistory.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v3 or later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class SpecialMasterCourseHistory extends SpecialEPHistory {

	/**
	 * Constructor.
	 *
	 * @since 0.1
	 */
	public function __construct() {
		parent::__construct( 'MasterCourseHistory', 'EPMC', 'name' );
	}

}
