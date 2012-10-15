<?php

/**
 * Special page listing the courses relevant to the current user.
 * There are the courses the user is either enrolled in, an ambassador for or instructor for.
 * When a subpage param is provided, and it's a valid course
 * name, info for that course is shown.
 *
 * @since 0.1
 *
 * @file SpecialManageCourses.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class SpecialManageCourses extends SpecialEPPage {
	/**
	 * Constructor.
	 *
	 * @since 0.1
	 */
	public function __construct() {
		parent::__construct( 'ManageCourses' );
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
				$course = EPCourses::singleton()->selectRow( null, array( 'title' => $this->subPage ) );

				if ( $course === false ) {
					// TODO high
				}
				else {
					$this->displayCourse( $course );
				}
			}
		}
		else {
			$this->getOutput()->addHTML( Linker::linkKnown(
				SpecialPage::getTitleFor( 'Userlogin' ),
				$this->msg( 'ep-mycourses-login-first' )->escaped(),
				array(),
				array(
					'returnto' => $this->getTitle( $this->subPage )->getFullText()
				)
			) );
		}
	}

	/**
	 * Displays the navigation menu.
	 *
	 * @since 0.1
	 */
	protected function displayNavigation() {
		$menu = new EPMenu( $this->getContext() );
		$menu->setItemFunction( function( array $items ) {
			unset( $items['ep-nav-mycourses'] );
			return $items;
		} );
		$menu->display();
	}

	/**
	 * Display the courses relevant to the current user.
	 *
	 * @since 0.1
	 */
	protected function displayCourses() {
		$this->displayRoleAssociation( 'EPStudent' );

		$this->displayRoleAssociation( 'EPInstructor' );

		$this->displayRoleAssociation( 'EPOA' );

		$this->displayRoleAssociation( 'EPCA' );
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
	 * @param $class string The name of the EPIRole implementing class
	 */
	protected function displayRoleAssociation( $class ) {
		$user = $this->getUser();
		$userRole = $class::newFromUser( $user );
		$courses = $userRole->getCourses( array( 'id', 'name', 'title', 'org_id', 'students' ) );

		switch ( $class ) {
			case 'EPStudent':
				$isAllowed = true;
				break;
			case 'EPInstructor':
				$isAllowed = $user->isAllowed( 'ep-beinstructor' ) || $user->isAllowed( 'ep-instructor' );
				break;
			case 'EPOA':
				$isAllowed = $user->isAllowed( 'ep-beonline' ) || $user->isAllowed( 'ep-online' );
				break;
			case 'EPCA':
				$isAllowed = $user->isAllowed( 'ep-becampus' ) || $user->isAllowed( 'ep-campus' );
				break;
		}

		if ( !empty( $courses ) ) {
			// @todo FIXME: Add full text of all used message keys here for grepping
			//              and transparancy purposes.
			$message = $this->msg( 'ep-mycourses-courses-' . strtolower( $class ) )
				->numParams( count( $courses ) )->params( $this->getUser()->getName() )->text();
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
		elseif ( $isAllowed ) {
			$this->getOutput()->addWikiMsg( 'ep-mycourses-nocourses-' . strtolower( $class ), $this->getUser()->getName() );
		}
	}

	/**
	 * Display a list of courses, each as a h3 section with the student/article table in it.
	 *
	 * @param array $courses
	 */
	protected function displayCourseTables( array $courses ) {
		$out = $this->getOutput();

		/**
		 * @var EPCourse $course
		 */
		foreach ( $courses as  $course ) {
			$out->addElement( 'h3', array(), $course->getField( 'name' ) );

			$out->addHTML(
				$this->msg( 'ep-mycourses-course-org-links' )
					->rawParams( $course->getLink(), $course->getOrg()->getLink() )
					->escaped()
			);

			$studentIds = $course->getField( 'students' );

			if ( $studentIds !== array() ) {
				$pager = new EPArticleTable(
					$this->getContext(),
					array( 'user_id' => $studentIds ),
					array( 'course_id' => $course->getId() )
				);

				if ( $pager->getNumRows() > 0 ) {
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

		$out->addHTML(
			$this->msg( 'ep-mycourses-enrolledin' )->rawParams(
				$course->getLink(),
				$course->getOrg()->getLink()
			)->escaped()
		);

		$out->addWikiMsg( 'ep-mycourses-articletable' );

		$pager = new EPArticleTable(
			$this->getContext(),
			array( 'user_id' => $this->getUser()->getId() ),
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
		/**
		 * @var EPCourse $course
		 */
		foreach ( $courses as $course ) {
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
