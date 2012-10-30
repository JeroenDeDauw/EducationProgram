<?php

namespace EducationProgram;

/**
 * Remove a student from a course.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 * @ingroup Action
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class RemoveStudentAction extends \FormlessAction {

	/**
	 * @see Action::getName()
	 */
	public function getName() {
		return 'epremstudent';
	}

	/**
	 * @see FormlessAction::onView()
	 */
	public function onView() {
		$api = new \ApiMain( new \FauxRequest( array(
			'action' => 'enlist',
			'subaction' => 'remove',
			'format' => 'json',
			'courseid' => $this->getRequest()->getInt( 'course-id' ),
			'userid' => $this->getRequest()->getInt( 'user-id' ),
			'reason' => '', // TODO high
			'role' => 'student'
		), true ), true );

		try { $api->execute(); } catch ( \Exception $exception ) {}

		$this->getOutput()->redirect( $this->getTitle()->getLocalURL() );
		return '';
	}

}
