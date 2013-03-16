<?php

namespace EducationProgram;

/**
 * Page for interacting with a course.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 * @ingroup Page
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class CoursePage extends EducationPage {

	protected static $info = array(
		'edit-right' => 'ep-course',
		'list' => 'Courses',
		'log-type' => 'course',
	);

	/**
	 * @see Page::getActions()
	 */
	public function getActions() {
		return array(
			'view' => 'EducationProgram\ViewCourseAction',
			'edit' => 'EducationProgram\EditCourseAction',
			'history' => 'EducationProgram\HistoryAction',
			'delete' => 'EducationProgram\DeleteAction',
			'purge' => 'EducationProgram\ViewCourseAction',
		);
	}

	/**
	 * @see Page::getActions()
	 * @return PageTable
	 */
	public function getTable() {
		return Courses::singleton();
	}

	public function exportSession() {
		return array();
	}

}

