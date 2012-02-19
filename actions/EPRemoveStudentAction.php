<?php

/**
 * Remove a student from a course.
 *
 * @since 0.1
 *
 * @file EPRemoveStudentAction.php
 * @ingroup EducationProgram
 * @ingroup Action
 *
 * @licence GNU GPL v3+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EPRemoveStudentAction extends FormlessAction {

	/**
	 * (non-PHPdoc)
	 * @see Action::getName()
	 */
	public function getName() {
		return 'epremstudent';
	}

	/**
	 * (non-PHPdoc)
	 * @see FormlessAction::onView()
	 */
	public function onView() {
		$api = new ApiMain( new FauxRequest( array(
			'action' => 'enlist',
			'subaction' => 'remove',
			'format' => 'json',
			'courseid' => $this->getRequest()->getInt( 'course-id' ),
			'userid' => $this->getRequest()->getInt( 'user-id' ),
			'reason' => '', // TODO
			'role' => 'student'
		), true ), true );

		try { $api->execute(); } catch ( Exception $exception ) {}

		$this->getOutput()->redirect( $this->getTitle()->getLocalURL() );
		return '';
	}

}
