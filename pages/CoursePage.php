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
 * @licence GNU GPL v3+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class CoursePage extends EPPage {
	
	protected static $info = array(
		'ns' => EP_NS_COURSE,
		'actions' => array(
			'view' => false,
			'edit' => 'ep-course',
			'history' => false,
			'enroll' => 'ep-enroll',
		),
		'edit-right' => 'ep-course',
		'identifier' => 'name',
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
			'history' => 'CourseHistoryAction',
			'delete' => 'EPDeleteAction',
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

