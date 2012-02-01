<?php

/**
 * Action for viewing a course.
 *
 * @since 0.1
 *
 * @file ViewCourseAction.php
 * @ingroup EducationProgram
 * @ingroup Action
 *
 * @licence GNU GPL v3+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ViewCourseAction extends EPViewAction {
	
	public function getName() {
		return 'viewcourse';
	}

	protected function getItemClass() {
		return 'EPCourse';
	}

	protected function displayPage( EPDBObject $course ) {
		parent::displayPage( $course );

		$out = $this->getOutput();

		$out->addHTML( Html::element( 'h2', array(), wfMsg( 'ep-course-description' ) ) );

		$out->addHTML( $this->getOutput()->parse( $course->getField( 'description' ) ) );

		$studentIds = array_map(
			function( EPStudent $student ) {
				return $student->getId();
			},
			$course->getStudents( 'id' )
		);

		if ( count( $studentIds ) > 0 ) {
			$out->addHTML( Html::element( 'h2', array(), wfMsg( 'ep-course-students' ) ) );
			EPStudent::displayPager( $this->getContext(), array( 'id' => $studentIds ) );
		}
		else {
			// TODO
		}
	}

	/**
	 * Gets the summary data.
	 *
	 * @since 0.1
	 *
	 * @param EPCourse $course
	 *
	 * @return array
	 */
	protected function getSummaryData( EPDBObject $course ) {
		$stats = array();

		$orgName = EPOrg::selectFieldsRow( 'name', array( 'id' => $course->getField( 'org_id' ) ) );
		$stats['org'] = EPOrg::getLinkFor( $orgName );

		$lang = $this->getLanguage();

		$stats['term'] = htmlspecialchars( $course->getField( 'term' ) );
		$stats['start'] = htmlspecialchars( $lang->timeanddate( $course->getField( 'start' ), true ) );
		$stats['end'] = htmlspecialchars( $lang->timeanddate( $course->getField( 'end' ), true ) );

		$stats['students'] = htmlspecialchars( $lang->formatNum( $course->getField( 'students' ) ) );

		$stats['status'] = htmlspecialchars( EPCourse::getStatusMessage( $course->getStatus() ) );

		if ( $this->getUser()->isAllowed( 'ep-token' ) ) {
			$stats['token'] = Linker::linkKnown(
				SpecialPage::getTitleFor( 'Enroll', $course->getId() . '/' . $course->getField( 'token' ) ),
				htmlspecialchars( $course->getField( 'token' ) )
			);
		}

		$stats['instructors'] = $this->getInstructorsList( $course ) . $this->getInstructorControls( $course );

		return $stats;
	}

	/**
	 * Returns a list with the instructors for the provided course
	 * or a message indicating there are none.
	 *
	 * @since 0.1
	 *
	 * @param EPCourse $course
	 *
	 * @return string
	 */
	protected function getInstructorsList( EPCourse $course ) {
		$instructors = $course->getInstructors();

		if ( count( $instructors ) > 0 ) {
			$instList = array();

			foreach ( $instructors as /* EPInstructor */ $instructor ) {
				$instList[] = $instructor->getUserLink() . $instructor->getToolLinks( $this->getContext(), $course );
			}

			if ( false ) { // count( $instructors ) == 1
				$html = $instList[0];
			}
			else {
				$html = '<ul><li>' . implode( '</li><li>', $instList ) . '</li></ul>';
			}
		}
		else {
			$html = wfMsgHtml( 'ep-course-no-instructors' );
		}

		return Html::rawElement(
			'div',
			array( 'id' => 'ep-course-instructors' ),
			$html
		);
	}

	/**
	 * Returns instructor addition controls for the course if the
	 * current user has the right permissions.
	 *
	 * @since 0.1
	 *
	 * @param EPCourse $course
	 *
	 * @return string
	 */
	protected function getInstructorControls( EPCourse $course ) {
		$user = $this->getUser();
		$links = array();

		if ( ( $user->isAllowed( 'ep-instructor' ) || $user->isAllowed( 'ep-beinstructor' ) )
			&& !in_array( $user->getId(), $course->getField( 'instructors' ) )
		) {
			$links[] = Html::element(
				'a',
				array(
					'href' => '#',
					'class' => 'ep-add-instructor',
					'data-courseid' => $course->getId(),
					'data-coursename' => $course->getField( 'name' ),
					'data-mode' => 'self',
				),
				wfMsg( 'ep-course-become-instructor' )
			);
		}

		if ( $user->isAllowed( 'ep-instructor' ) ) {
			$links[] = Html::element(
				'a',
				array(
					'href' => '#',
					'class' => 'ep-add-instructor',
					'data-courseid' => $course->getId(),
					'data-coursename' => $course->getField( 'name' ),
				),
				wfMsg( 'ep-course-add-instructor' )
			);
		}

		if ( count( $links ) > 0 ) {
			$this->getOutput()->addModules( 'ep.instructor' );
			return '<br />' . $this->getLanguage()->pipeList( $links );
		}
		else {
			return '';
		}
	}

	/**
	 * Returns ambassador addiction controls for the course if the
	 * current user has the right permissions.
	 *
	 * @since 0.1
	 *
	 * @param EPCourse $course
	 * @param string $type
	 *
	 * @return string
	 */
	protected function getAmbassadorControls( EPCourse $course, $type ) {
		$user = $this->getUser();
		$links = array();

		if ( ( $user->isAllowed( 'ep-' . $type ) || $user->isAllowed( 'ep-be' . $type ) )
			&& !in_array( $user->getId(), $course->getField( 'instructors' ) )
		) {
			$links[] = Html::element(
				'a',
				array(
					'href' => '#',
					'class' => 'ep-add-instructor',
					'data-courseid' => $course->getId(),
					'data-coursename' => $course->getField( 'name' ),
					'data-mode' => 'self',
				),
				wfMsg( 'ep-course-become-instructor' )
			);
		}

		if ( $user->isAllowed( 'ep-instructor' ) ) {
			$links[] = Html::element(
				'a',
				array(
					'href' => '#',
					'class' => 'ep-add-instructor',
					'data-courseid' => $course->getId(),
					'data-coursename' => $course->getField( 'name' ),
				),
				wfMsg( 'ep-course-add-instructor' )
			);
		}

		if ( count( $links ) > 0 ) {
			$this->getOutput()->addModules( 'ep.instructor' );
			return '<br />' . $this->getLanguage()->pipeList( $links );
		}
		else {
			return '';
		}
	}
	
}