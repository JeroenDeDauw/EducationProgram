<?php

/**
 * Special page for listing the history of a course.
 *
 * @since 0.1
 *
 * @file SpecialCourseHistory.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v3 or later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class SpecialCourseHistory extends SpecialEPHistory {

	/**
	 * Constructor.
	 *
	 * @since 0.1
	 */
	public function __construct() {
		parent::__construct( 'CourseHistory', 'EPCourse', 'id', 'Course' );
	}

}
