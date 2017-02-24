<?php

namespace EducationProgram;

use Html;
use SpecialPage;
use Xml;

/**
 * Disenrollment page for students.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class SpecialDisenroll extends VerySpecialPage {
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

		$courseName = str_replace( '_', ' ', $this->subPage );

		if ( $courseName === '' ) {
			$this->showWarning( $this->msg( 'ep-disenroll-no-name' ) );
		} else {
			$course = Courses::singleton()->getFromTitle( $courseName );

			if ( $course === false ) {
				$this->showWarning( $this->msg( 'ep-disenroll-invalid-name', $courseName ) );
			} else {
				if ( Student::newFromUser( $this->getUser() )->hasCourse( [ 'id' => $course->getId() ] ) ) {
					$this->executeEnrolled( $course );

				} else {
					$this->showWarning( $this->msg( 'ep-disenroll-not-enrolled' ) );
				}
			}
		}
	}

	/**
	 * @since 0.3
	 *
	 * @param Course $course
	 */
	protected function executeEnrolled( Course $course ) {
		if ( $course->getStatus() === 'current' ) {
			if ( $this->getUser()->isLoggedIn() ) {
				$req = $this->getRequest();

				$editTokenMatches = $this->getUser()->matchEditToken(
					$req->getText( 'disenrollToken' ),
					$this->getPageTitle( $this->subPage )->getLocalURL()
				);

				if ( $req->wasPosted() && $editTokenMatches ) {
					$this->doDisenroll( $course );
				} else {
					$this->getOutput()->setPageTitle(
						$this->msg(
							'ep-disenroll-title',
							$course->getField( 'name' )
						)->text()
					);
					$this->showDisenrollForm( $course );
				}
			} else {
				$this->showLoginLink();
			}
		} else {
			$this->showWarning( $this->msg( 'ep-disenroll-course-passed' ) );
		}
	}

	/**
	 * Show a link to the login page with appropriate returnto argument
	 * when the user is not logged in.
	 *
	 * @since 0.1
	 */
	protected function showLoginLink() {
		$this->getOutput()->addHTML( $this->getLinkRenderer()->makeKnownLink(
			SpecialPage::getTitleFor( 'Userlogin' ),
			$this->msg( 'ep-enroll-login-and-enroll' )->text(),
			[],
			[
				'returnto' => $this->getPageTitle( $this->subPage )->getFullText()
			]
		) );
	}

	/**
	 * Show the disenrollment form for the provdied course.
	 *
	 * @since 0.1
	 *
	 * @param Course $course
	 */
	protected function showDisenrollForm( Course $course ) {
		$out = $this->getOutput();

		$out->addModules( 'ep.disenroll' );

		$target = $this->getPageTitle( $this->subPage )->getLocalURL();

		$out->addWikiMsg( 'ep-disenroll-text', $course->getField( 'name' ) );

		$out->addHTML( Html::openElement(
			'form',
			[
				'method' => 'post',
				'action' => $target,
			]
		) );

		$out->addHTML( '&#160;' . Xml::inputLabel(
			$this->msg( 'ep-disenroll-summary' )->text(),
			'summary',
			'summary',
			65,
			false,
			[
				'maxlength' => 250,
				'spellcheck' => true,
			]
		) );

		$out->addHTML( '<br />' );

		$out->addHTML( Html::input(
			'disenroll',
			$this->msg( 'ep-disenroll-button' )->text(),
			'submit',
			[
				'class' => 'ep-disenroll',
			]
		) );

		$out->addElement(
			'button',
			[
				'class' => 'ep-disenroll-cancel ep-cancel',
				'data-target-url' => $course->getTitle()->getLocalURL(),
			],
			$this->msg( 'ep-disenroll-cancel' )->text()
		);

		$out->addHTML( Html::hidden( 'disenrollToken', $this->getUser()->getEditToken( $target ) ) );

		$out->addHTML( '</form>' );
	}

	/**
	 * Disenroll the user from the provided course.
	 *
	 * @since 0.1
	 *
	 * @param Course $course
	 */
	protected function doDisenroll( Course $course ) {
		$revAction = new RevisionAction();
		$revAction->setUser( $this->getUser() );
		$revAction->setComment( $this->getRequest()->getText( 'summary' ) );

		$success = $course->unenlistUsers(
			$this->getUser()->getId(),
			'student',
			true,
			$revAction
		) !== false;

		if ( $success ) {
			$this->showSuccess( $this->msg( 'ep-disenroll-success', $this->getUser() )->text() );
		} else {
			$this->showError( $this->msg( 'ep-disenroll-fail' ) );
		}

		$this->getOutput()->addWikiMsg(
			'ep-disenroll-returntolink',
			$course->getField( 'name' ),
			$course->getField( 'title' )
		);
	}

	protected function getGroupName() {
		return 'education';
	}
}
