<?php

/**
 * Special page listing the courses relevant to the current user.
 * There are the courses the user is either enrolled in, an ambassador for or instructor for.
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

		if ( $this->getUser()->isLoggedIn() ) {
			if ( $this->getUser()->isAllowed( 'ep-org' ) ) {
				$this->displayNavigation();
			}
			
			if ( $this->subPage === '' ) {
				$this->displayCourses();
			}
			else {
				$course = EPCourses::singleton()->selectRow( null, array( 'name' => $this->subPage ) );
				
				if ( $course === false ) {
					// TODO
				}
				else {
					$this->displayCourse( $course );
				}
			}		
		}
		else {
			$this->getOutput()->addHTML( Linker::linkKnown(
				SpecialPage::getTitleFor( 'Userlogin' ),
				wfMsgHtml( 'ep-mycourses-login-first' ),
				array(),
				array(
					'returnto' => $this->getTitle( $this->subPage )->getFullText()
				)
			) );
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see SpecialEPPage::getDefaultNavigationItems()
	 */
	protected function getDefaultNavigationItems() {
		$items = parent::getDefaultNavigationItems();
		
		if ( $this->subPage === '' ) {
			unset( $items[wfMsg( 'ep-nav-mycourses' )] );			
		}
		
		return $items;
	}

	/**
	 * Display the courses relevant to the current user.
	 *
	 * @since 0.1
	 */
	protected function displayCourses() {
		if ( $this->getRequest()->getCheck( 'enrolled' ) ) {
			EPStudents::singleton()->setReadDb( DB_MASTER );
			
			$course = EPCourses::singleton()->selectRow( null, array( 'id' => $this->getRequest()->getInt( 'enrolled' ) ) );
			
			if ( $course !== false && in_array( $this->getUser()->getId(), $course->getField( 'students' ) ) ) {
				$this->showSuccess( wfMessage(
					'ep-mycourses-enrolled',
					array(
						Message::rawParam( $course->getLink() ),
						Message::rawParam( $course->getOrg()->getLink() )
					)
				) );
			}
		}

		$this->displayRoleAssociation( 'EPStudent' );
		
		if ( $this->getUser()->isAllowed( 'ep-instructor' ) ) {
			$this->displayRoleAssociation( 'EPInstructor' );
		}
		
		if ( $this->getUser()->isAllowed( 'ep-online' ) ) {
			$this->displayRoleAssociation( 'EPOA' );
		}
		
		if ( $this->getUser()->isAllowed( 'ep-campus' ) ) {
			$this->displayRoleAssociation( 'EPCA' );
		}
	}

	/**
	 * Display the courses the user is enrolled in.
	 *
	 * @since 0.1
	 * 
	 * @param array $courses
	 */
	protected function displayEnrollment( array $courses ) {
		if ( count( $courses ) == 1 ) {
			$this->displayCourse( $courses[0] );
		}
		else {
			$this->displayCourseList( $courses );
		}
	}

	/**
	 * Display the courses the current user is associated with in the role
	 * of which the class name is provided.
	 *
	 * @since 0.1
	 *
	 * @param $class The name of the EPIRole implementing class
	 */
	protected function displayRoleAssociation( $class ) {
		$userRole = $class::newFromUser( $this->getUser() );
		$courses = $userRole->getCourses( array( 'id', 'name', 'org_id' ) );

		if ( count( $courses ) > 0 ) {
			$message = wfMsgExt( 'ep-mycourses-courses-' . strtolower( $class ), 'parsemag', count( $courses ), $this->getUser()->getName() );
			$this->getOutput()->addElement( 'h2', array(), $message );

			if ( $class == 'EPStudent' ) {
				$this->displayEnrollment( $courses );
			}
			elseif ( $class == 'EPInstructor' ) {
				$this->displayCourseTables( $courses );
			}
			else {
				$this->displayCoursePager( $courses, $class );
			}
		}
		else {
			$this->getOutput()->addWikiMsg( 'ep-mycourses-nocourses-' . strtolower( $class ) );
		}
	}

	/**
	 * Display a list of courses, each as a h3 section with the student/article table in it.
	 *
	 * @param array $courses
	 */
	protected function displayCourseTables( array $courses ) {
		$out = $this->getOutput();

		foreach ( $courses as /* EPCourse */ $course ) {
			$out->addElement( 'h3', array(), $course->getField( 'name' ) );

			$out->addHTML( $this->msg(
				'ep-mycourses-course-org-links',
				array(
					Message::rawParam( $course->getLink() ),
					Message::rawParam( $course->getOrg()->getLink() )
				)
			)->text() );

			$pager = new EPArticleTable(
				$this->getContext(),
				array( 'id' => $this->getUser()->getId() ),
				array( 'course_id' => $course->getId() )
			);

			if ( $pager->getNumRows() ) {
				$out->addHTML(
					$pager->getFilterControl() .
					$pager->getNavigationBar() .
					$pager->getBody() .
					$pager->getNavigationBar() .
					$pager->getMultipleItemControl()
				);
			}
		}
	}

	/**
	 * Display enrollment info for a single course.
	 *
	 * @since 0.1
	 *
	 * @param EPCourse $course
	 */
	protected function displayCourse( EPCourse $course ) {
		$out = $this->getOutput();
		
		$out->addHTML( $this->msg(
			'ep-mycourses-enrolledin',
			array(
				Message::rawParam( $course->getLink() ),
				Message::rawParam( $course->getOrg()->getLink() )
			)
		)->text() );

		$out->addWikiMsg( 'ep-mycourses-articletable' );

		$pager = new EPArticleTable(
			$this->getContext(),
			array( 'id' => $this->getUser()->getId() ),
			array(
				'course_id' => $course->getId(),
				'user_id' => $this->getUser()->getId(),
			)
		);

		$pager->setShowStudents( false );

		if ( $pager->getNumRows() ) {
			$out->addHTML(
				$pager->getFilterControl() .
				$pager->getNavigationBar() .
				$pager->getBody() .
				$pager->getNavigationBar() .
				$pager->getMultipleItemControl()
			);
		}
	}

	/**
	 * Display enrollment info for a list of courses.
	 *
	 * @since 0.1
	 *
	 * @param array $courses
	 */
	protected function displayCourseList( array $courses ) {
		foreach ( $courses as /* EPCourse */ $course ) {
			$this->getOutput()->addElement( 'h3', array(), $course->getField( 'name' ) );
			$this->displayCourse( $course );
		}
	}

	/**
	 * Display a course pager with the provided courses.
	 *
	 * @since 0.1
	 *
	 * @param array $courses
	 * @param string $class
	 */
	protected function displayCoursePager( array $courses, $class ) {
		$out = $this->getOutput();

		$courseIds = array_map(
			function( EPCourse $course ) {
				return $course->getId();
			},
			$courses
		);

		$pager = new EPCoursePager( $this->getContext(), array( 'id' => $courseIds ), true );

		$pager->setFilterPrefix( $class );
		$pager->setEnableFilter( count( $courses ) > 1 );

		if ( $pager->getNumRows() ) {
			$out->addHTML(
				$pager->getFilterControl() .
				$pager->getNavigationBar() .
				$pager->getBody() .
				$pager->getNavigationBar()
			);
		}
		else {
			$out->addHTML( $pager->getFilterControl() );
			$out->addWikiMsg( 'ep-courses-noresults' );
		}
	}

}
