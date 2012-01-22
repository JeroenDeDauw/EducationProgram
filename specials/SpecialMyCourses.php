<?php

/**
 * Special page listing the courses in which the current user is enrolled.
 * When a subpage param is provided, and it's a valid course
 * name, info for that course is shown.
 *
 * @since 0.1
 *
 * @file SpecialMyCourses.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v3 or later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class SpecialMyCourses extends SpecialEPPage {

	/**
	 * Constructor.
	 *
	 * @since 0.1
	 */
	public function __construct() {
		parent::__construct( 'MyCourses' );
	}

	/**
	 * Main method.
	 *
	 * @since 0.1
	 *
	 * @param string $subPage
	 */
	public function execute( $subPage ) {
		parent::execute( $subPage );

		if ( $this->getRequest()->getCheck( 'enrolled' ) ) {
			EPStudent::setReadDb( DB_MASTER );
		}

		$student = EPStudent::newFromUser( $this->getUser() );

		if ( $student === false ) {
			$this->getOutput()->addWikiMsg( 'ep-mycourses-not-a-student' );
		}
		else {
			if ( $this->getUser()->isAllowed( 'ep-org' ) ) {
				$this->displayNavigation();
			}
			
			if ( $this->subPage === '' ) {
				$this->displayCourses( $student );
			}
			else {
				$this->displayCourse( $student, $this->subPage );
			}
		}
	}

	/**
	 * Display the courses this student is linked to.
	 *
	 * @since 0.1
	 *
	 * @param EPStudent $student
	 */
	protected function displayCourses( EPStudent $student ) {
		$out = $this->getOutput();

		if ( $student->hasCourse() ) {
			if ( $this->getRequest()->getCheck( 'enrolled' ) ) {
				$course = EPCourse::selectRow( null, array( 'id' => $this->getRequest()->getInt( 'enrolled' ) ) );

				if ( $course !== false ) {
					$this->showSuccess( wfMessage(
						'ep-mycourses-enrolled',
						$course->getMasterCourse()->getField( 'name' ),
						$course->getOrg()->getField( 'name' )
					) );
				}
			}

			$currentCourses = $student->getCoursesWithState( 'current' );
			$passedCourses = $student->getCoursesWithState( 'passed' );

			if ( count( $currentCourses ) > 0 ) {
				$out->addHTML( Html::element( 'h2', array(), wfMsg( 'ep-mycourses-current' ) ) );
				$this->displayCoursesList( $currentCourses );
			}

			if ( count( $passedCourses ) > 0 ) {
				$out->addHTML( Html::element( 'h2', array(), wfMsg( 'ep-mycourses-passed' ) ) );
				$this->displayCoursesList( $passedCourses );
			}
		}
		else {
			$out->addWikiMsg( 'ep-mycourses-not-enrolled' );
		}
	}

	/**
	 * Display the provided courses in a table.
	 *
	 * @since 0.1
	 *
	 * @param array $courses
	 */
	protected function displayCoursesList( array /* of EPCourse */ $courses ) {
		$out = $this->getOutput();

		$out->addHTML( Xml::openElement(
			'table',
			array( 'class' => 'wikitable sortable' )
		) );

		$headers = array(
			Html::element( 'th', array(), wfMsg( 'ep-mycourses-header-name' ) ),
			Html::element( 'th', array(), wfMsg( 'ep-mycourses-header-institution' ) ),
		);

		$out->addHTML( '<thead><tr>' . implode( '', $headers ) . '</tr></thead>' );

		$out->addHTML( '<tbody>' );

		foreach ( $courses as /* EPCourse */ $course ) {
			$fields = array();

			$fields[] = Linker::link(
				$this->getTitle( $course->getMasterCourse()->getField( 'name' ) ),
				'<b>' . htmlspecialchars( $course->getMasterCourse()->getField( 'name' ) ) . '</b>'
			);

			$fields[] = Linker::link(
				SpecialPage::getTitleFor( 'Institution', $course->getOrg()->getField( 'name' ) ),
				htmlspecialchars( $course->getOrg()->getField( 'name' ) )
			);

			foreach ( $fields as &$field ) {
				$field = Html::rawElement( 'td', array(), $field );
			}

			$out->addHTML( '<tr>' . implode( '', $fields ) . '</tr>' );
		}

		$out->addHTML( '</tbody>' );
		$out->addHTML( '</table>' );
	}

	/**
	 * Display info for a single course.
	 *
	 * @since 0.1
	 *
	 * @param EPStudent $student
	 * @param string $courseName
	 */
	protected function displayCourse( EPStudent $student, $courseName ) {
		$out = $this->getOutput();

		$course = EPCourse::selectRow( null, array( 'name' => $courseName ) );
		$terms = $student->getCourses( null, array( 'course_id' => $course->getId() ) );

		if ( $course !== false && count( $terms ) > 0 ) {
			$out->addWikiMsg( 'ep-mycourses-show-all' );

			$out->setPageTitle( wfMsgExt(
				'ep-mycourses-course-title',
				'parsemag',
				$courseName,
				$course->getOrg( 'name' )->getField( 'name' )
			) );

			$this->displayCourseSummary( $course, $terms );
		}
		else {
			$this->showError( wfMessage( 'ep-mycourses-no-such-course', $courseName ) );
			$this->displayCourses( $student );
		}
	}

	/**
	 * Display the summary for a course.
	 *
	 * @since 0.1
	 *
	 * @param EPCourse $masterCourse
	 * @param array $courses
	 */
	protected function displayCourseSummary( EPCourse $masterCourse, array /* of EPCourse */ $courses ) {
		$info = array();

		$info['name'] = $masterCourse->getField( 'name' );
		$info['org'] = EPOrg::selectFieldsRow( 'name', array( 'id' => $masterCourse->getField( 'org_id' ) ) );

		foreach ( $info as &$inf ) {
			$inf = htmlspecialchars( $inf );
		}

		$this->displaySummary( $masterCourse, false, $info );
	}
	
	/**
	 * (non-PHPdoc)
	 * @see SpecialEPPage::getDefaultNavigationItems()
	 */
	protected function getDefaultNavigationItems() {
		$items = parent::getDefaultNavigationItems();
		
		unset( $items[wfMsg( 'ep-nav-mycourses' )] );
		
		return $items;
	}

}
