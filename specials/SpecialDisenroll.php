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
			$course = EPCourses::singleton()->get( $args[0] );

			if ( $course === false ) {
				$this->showWarning( wfMessage( 'ep-disenroll-invalid-name', $subPage ) );
			}
			else {
				if ( EPStudent::newFromUser( $this->getUser() )->hasCourse( array( 'id' => $course->getId() ) ) ) {
					if ( $course->getStatus() === 'current' ) {
						if ( $this->getUser()->isLoggedIn() ) {
							$req = $this->getRequest();

							$editTokenMatches = $this->getUser()->matchEditToken(
								$req->getText( 'disenrollToken' ),
								$this->getTitle( $this->subPage )->getLocalURL()
							);

							if ( $req->wasPosted() && $editTokenMatches ) {
								$this->doDisenroll( $course );
							}
							else {
								$this->getOutput()->setPageTitle( wfMsgExt( 'ep-disenroll-title', 'parsemag', $course->getField( 'mc' ) ) );
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

	/**
	 * Show a link to the login page with appropriate returnto argument
	 * when the user is not logged in.
	 *
	 * @since 0.1
	 */
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

	/**
	 * Show the disenrollment form for the provdied course.
	 *
	 * @since 0.1
	 *
	 * @param EPCourse $course
	 */
	protected function showDisenrollForm( EPCourse $course ) {
		$out = $this->getOutput();

		$out->addModules( 'ep.disenroll' );

		$target = $this->getTitle( $this->subPage )->getLocalURL();

		$out->addWikiMsg( 'ep-disenroll-text', $course->getField( 'name' ) );

		$out->addHTML( Html::openElement(
			'form',
			array(
				'method' => 'post',
				'action' => $target,
			)
		) );

		$out->addHTML( '&#160;' . Xml::inputLabel(
			wfMsg( 'ep-disenroll-summary' ),
			'summary',
			'summary',
			65,
			false,
			array(
				'maxlength' => 250,
				'spellcheck' => true,
			)
		) );

		$out->addHTML( '<br />' );

		$out->addHTML( Html::input(
			'disenroll',
			wfMsg( 'ep-disenroll-button' ),
			'submit',
			array(
				'class' => 'ep-disenroll',
			)
		) );

		$out->addElement(
			'button',
			array(
				'class' => 'ep-disenroll-cancel',
				'target-url' => $course->getTitle()->getLocalURL(),
			),
			wfMsg( 'ep-disenroll-cancel' )
		);

		$out->addHTML( Html::hidden( 'disenrollToken', $this->getUser()->getEditToken( $target ) ) );

		$out->addHTML( '</form>' );
	}

	/**
	 * Disenroll the user from the provided course.
	 *
	 * @since 0.1
	 *
	 * @param EPCourse $course
	 */
	protected function doDisenroll( EPCourse $course ) {
		$success = $course->unenlistUsers(
			$this->getUser()->getId(),
			'student',
			$this->getRequest()->getText( 'summary' )
		);

		if ( $success ) {
			$this->showSuccess( wfMessage( 'ep-disenroll-success' ) );
		}
		else {
			$this->showError( wfMessage( 'ep-disenroll-fail' ) );
		}

		$this->getOutput()->addWikiMsg( 'ep-disenroll-returnto', $course->getField( 'name' ) );
	}

}
