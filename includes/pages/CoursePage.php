<?php

/**
 * Page for interacting with a course.
 *
 * @since 0.1
 *
 * @file CoursePage.php
 * @ingroup EducationProgram
 * @ingroup Page
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class CoursePage extends EPPage {

	protected static $info = array(
		'edit-right' => 'ep-course',
		'list' => 'Courses',
		'log-type' => 'course',
	);

	/**
	 * (non-PHPdoc)
	 * @see EPPage::getActions()
	 */
	public function getActions() {
		return array(
			'view' => 'ViewCourseAction',
			'edit' => 'EditCourseAction',
			'history' => 'EPHistoryAction',
			'delete' => 'EPDeleteAction',
			'purge' => 'ViewCourseAction',
		);
	}

	/**
	 * (non-PHPdoc)
	 * @see EPPage::getActions()
	 * @return EPPageTable
	 */
	public function getTable() {
		return EPCourses::singleton();
	}

}

