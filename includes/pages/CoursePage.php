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
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class CoursePage extends EducationPage {

	protected static $info = [
		'edit-right' => 'ep-course',
		'limited-edit-right' => 'edit',
		'list' => 'Courses',
		'log-type' => 'course',
	];

	/**
	 * @see Page::getActions()
	 */
	public function getActions() {
		return [
			'view' => 'EducationProgram\ViewCourseAction',
			'edit' => 'EducationProgram\EditCourseAction',
			'history' => 'EducationProgram\HistoryAction',
			'delete' => 'EducationProgram\DeleteAction',
			'purge' => 'EducationProgram\ViewCourseAction',
		];
	}

	/**
	 * @see Page::getActions()
	 * @return PageTable
	 */
	public function getTable() {
		return Courses::singleton();
	}

	/**
	 * @see EducationPage::getLimitedEditRight()
	 */
	public function getLimitedEditRight() {
		return static::$info['limited-edit-right'];
	}
}
