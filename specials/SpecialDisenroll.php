<?php

/**
 * Disenrollment page for students.
 *
 * @since 0.1
 *
 * @file SpecialDisenroll.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v3 or later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class SpecialDisenroll extends SpecialEPPage {

	/**
	 * @since 0.1
	 * @var EPCourse
	 */
	protected $course;

	/**
	 * @since 0.1
	 * @var string|false
	 */
	protected $token = false;

	/**
	 * Constructor.
	 *
	 * @since 0.1
	 */
	public function __construct() {
		parent::__construct( 'Disenroll', '', false );
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

		$args = explode( '/', $this->subPage, 2 );

		if ( $args[0] === '' ) {
			$this->showWarning( wfMessage(  'ep-disenroll-no-name' ) );
		}
		else {
			$course = EPCourse::get( $args[0] );

			if ( $course === false ) {
				$this->showWarning( wfMessage( 'ep-disenroll-invalid-name' ) );
			}
			else {
				if ( EPStudent::newFromUser( $this->getUser() )->hasCourse( array( 'id' => $course->getId() ) ) ) {
					if ( $course->getStatus() === 'current' ) {
						if ( $this->getUser()->isLoggedIn() ) {
							$req = $this->getRequest();

							if ( $req->wasPosted() && $this->getUser()->matchEditToken( $req->getText( 'disenrollEditToken' ) ) ) {
								$this->doDisenroll( $course );
							}
							else {
								$this->showDisenrollForm( $course );
							}
						}
						else {
							$this->showLoginLink();
						}
					}
					else {
						$this->showWarning( wfMessage( 'ep-disenroll-course-passed' ) );
					}
				}
				else {
					$this->showWarning( wfMessage( 'ep-disenroll-not-enrolled' ) );
				}
			}
		}
	}

	protected function showLoginLink() {
		$this->getOutput()->addHTML( Linker::linkKnown(
			SpecialPage::getTitleFor( 'Userlogin' ),
			wfMsgHtml( 'ep-enroll-login-and-enroll' ),
			array(),
			array(
				'returnto' => $this->getTitle( $this->subPage )->getFullText()
			)
		) );
	}

	protected function showDisenrollForm( EPCourse $course ) {
		// TODO
	}

	protected function doDisenroll( EPCourse $course ) {
		// TODO
	}

}
