<?php

/**
 * Page for deleting a course.
 *
 * @since 0.1
 *
 * @file DeleteCourseAction.php
 * @ingroup EducationProgram
 * @ingroup Action
 *
 * @licence GNU GPL v3+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DeleteCourseAction extends EPDeleteAction {

	/**
	 * Constructor.
	 *
	 * @since 0.1
	 *
	 * @param Page $page
	 * @param IContextSource $context
	 */
	protected function __construct( Page $page, IContextSource $context = null ) {
		parent::__construct( $page, $context, EPCourses::singleton() );
	}

	public function getName() {
		return 'deletecourse';
	}

	public function getRestriction() {
		return 'ep-course';
	}

}