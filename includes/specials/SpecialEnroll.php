<?php

namespace EducationProgram;

use UserBlockedError;
use Html;
use Xml;
use SpecialPage;

/**
 * Enrollment page for students.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 *
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class SpecialEnroll extends VerySpecialPage {

	/**
	 * @var Course
	 */
	protected $course;

	/**
	 * @var string|bool false
	 */
	protected $token = false;

	public function __construct() {
		// We can not demand ep-enroll here already, since the user might first need to login.
		parent::__construct( 'Enroll', '', false );
	}

	/**
	 * Main method.
	 *
	 * @param string $subPage
	 * @throws UserBlockedError
	 */
	public function execute( $subPage ) {
		if ( $this->getUser()->isBlocked() ) {
			throw new UserBlockedError( $this->getUser()->getBlock() );
		}

		parent::execute( $subPage );

		$args = explode( '/', $this->subPage, 3 );

		$orgName = $args[0];
		$courseName = count( $args ) > 1 ? $args[1] : '';
		$courseTitle = $orgName . '/' . $courseName;
		$token = count( $args ) > 2 ? $args[2] : false;

		if ( $courseTitle === '' ) {
			$this->showWarning( $this->msg( 'ep-enroll-no-id' ) );
		} else {
			/**
			 * @var Course $course
			 */
			$course = Courses::singleton()->getFromTitle( $courseTitle );

			if ( $course === false ) {
				$this->showWarning( $this->msg( 'ep-enroll-invalid-id' ) );
			} elseif ( in_array( $course->getStatus(), [ 'current', 'planned' ] ) ) {
				$this->setPageTitle( $course );

				$tokenIsValid = $course->getField( 'token' ) === '';

				if ( !$tokenIsValid ) {
					if ( $token === false && $this->getRequest()->getCheck( 'wptoken' ) ) {
						$token = $this->getRequest()->getText( 'wptoken' );
					}

					$tokenIsValid = $course->getField( 'token' ) === $token;
					$this->token = $token;
				}

				if ( $tokenIsValid ) {
					$this->showEnrollmentView( $course );
				} else {
					if ( $token !== false ) {
						$this->showWarning( $this->msg( 'ep-enroll-invalid-token' ) );
					}

					$this->showTokenInput();
				}
			} else {
				$this->setPageTitle( $course );

				// Give grep a chance to find the usages:
				// ep-enroll-course-passed, ep-enroll-course-planned
				$this->showWarning( $this->msg( 'ep-enroll-course-' . $course->getStatus() ) );
			}
		}
	}

	/**
	 * Shows the actual enrollment view.
	 * Should only be called after everything checks out, ie the user can enroll in the course.
	 *
	 * @param Course $course
	 */
	protected function showEnrollmentView( Course $course ) {
		$this->course = $course;

		if ( $this->getUser()->isLoggedIn() ) {
			if ( $this->getUser()->isAllowed( 'ep-enroll' ) ) {
				$user = $this->getUser();
				$hasFields = trim( $user->getRealName() ) !== '' &&
					$user->getOption( 'gender' ) !== 'unknown';

				$formFields = $this->getFormFields();

				if ( $hasFields || count( $formFields ) < 3 ) {
					// Defer per T92357
					\DeferredUpdates::addCallableUpdate( function () use ( $course ) {
						$this->doEnroll( $course );
					} );
					$this->onSuccess();
				} else {
					$this->showEnrollmentForm( $formFields );
				}
			} else {
				$this->showWarning( $this->msg( 'ep-enroll-not-allowed' ) );
			}
		} else {
			$this->showSignupLink();
		}
	}

	/**
	 * Show an input for a token.
	 */
	protected function showTokenInput() {
		$out = $this->getOutput();

		$out->addHTML( Html::openElement(
			'form',
			[
				'method' => 'get',
				'action' => $this->getPageTitle( $this->subPage )->getLocalURL(),
			]
		) );

		$out->addHTML( '<fieldset>' );

		$out->addHTML( '<legend>' . $this->msg( 'ep-enroll-add-token' )->escaped() . '</legend>' );

		$out->addHTML( Html::element( 'p', [], $this->msg( 'ep-enroll-add-token-doc' )->text() ) );

		$out->addHTML( '&#160;' .
			Xml::inputLabel( $this->msg( 'ep-enroll-token' )->text(), 'wptoken', 'wptoken' ) );

		$out->addHTML( '&#160;' . Html::input(
			'submittoken',
			$this->msg( 'ep-enroll-submit-token' )->text(),
			'submit'
		) );

		$out->addHTML( '</fieldset></form>' );
	}

	/**
	 * Set the page title.
	 *
	 * @param Course $course
	 */
	protected function setPageTitle( Course $course ) {
		$this->getOutput()->setPageTitle( $this->msg(
			'ep-enroll-title',
			$course->getField( 'name' ),
			$course->getOrg( 'name' )->getField( 'name' )
		)->text() );
	}

	/**
	 * Show links to signup.
	 */
	protected function showSignupLink() {
		$out = $this->getOutput();

		$out->addWikiMsg( 'ep-enroll-login-first' );

		$out->addHTML( '<ul><li>' );

		$subPage = $this->course->getField( 'title' );

		if ( $this->token !== false ) {
			$subPage .= '/' . $this->token;
		}

		$linkRenderer = $this->getLinkRenderer();
		$out->addHTML( $linkRenderer->makeKnownLink(
			SpecialPage::getTitleFor( 'Userlogin' ),
			$this->msg( 'ep-enroll-login-and-enroll' )->text(),
			[],
			[
				'returnto' => $this->getPageTitle( $subPage )->getFullText()
			]
		) );

		$out->addHTML( '</li><li>' );

		$out->addHTML( $linkRenderer->makeKnownLink(
			SpecialPage::getTitleFor( 'Userlogin' ),
			$this->msg( 'ep-enroll-signup-and-enroll' )->text(),
			[],
			[
				'returnto' => $this->getPageTitle( $subPage )->getFullText(),
				'type' => 'signup'
			]
		) );

		$out->addHTML( '</li></ul>' );
	}

	/**
	 * Just enroll the user in the course.
	 *
	 * @param Course $course
	 *
	 * @return bool Success indicator
	 */
	protected function doEnroll( Course $course ) {
		$revAction = new RevisionAction();
		$revAction->setUser( $this->getUser() );
		$revAction->setComment( '' ); // TODO?

		$success = $course->enlistUsers(
			[ $this->getUser()->getId() ],
			'student',
			true,
			$revAction
		) !== false;

		return $success;
	}

	/**
	 * Create and display the enrollment form.
	 *
	 * @param array $formFields
	 */
	protected function showEnrollmentForm( array $formFields ) {
		$this->getOutput()->addWikiMsg( 'ep-enroll-header' );

		$form = new \HTMLForm( $formFields, $this->getContext() );

		$form->setSubmitCallback( [ $this, 'handleSubmission' ] );
		$form->setSubmitText( $this->msg( 'educationprogram-org-submit' )->text() );
		$form->setWrapperLegend( $this->msg( 'ep-enroll-legend' ) );

		if ( $form->show() ) {
			$this->onSuccess();
		}
	}

	/**
	 * Returns the definitions for the fields of the signup form.
	 *
	 * @return array
	 */
	protected function getFormFields() {
		$fields = [];

		$user = $this->getUser();

		$fields['enroll'] = [
			'type' => 'hidden',
			'default' => 1
		];

		if ( Settings::get( 'collectRealName' ) && trim( $user->getRealName() ) === '' ) {
			$fields['realname'] = [
				'type' => 'text',
				'default' => '',
				'label-message' => 'ep-enroll-realname' .
					( Settings::get( 'requireRealName' )
						? ''
						: '-optional'
					),
				'validation-callback' => function ( $value, array $alldata = null ) {
					if ( Settings::get( 'requireRealName' ) && strlen( $value ) < 2 ) {
						return wfMessage( 'ep-enroll-invalid-name' )->numParams( 2 )->text();
					}

					return true;
				}
			];

			if ( Settings::get( 'requireRealName' ) ) {
				$fields['realname'] = true;
			}
		}

		if ( $user->getOption( 'gender' ) === 'unknown' ) {
			$fields['gender'] = [
				'type' => 'select',
				'default' => 'unknown',
				'label-message' => 'ep-enroll-gender',
				'validation-callback' => function ( $value, array $alldata = null ) {
					return in_array( $value, [ 'male', 'female', 'unknown' ] )
						? true
						: wfMessage( 'ep-enroll-invalid-gender' )->text();
				} ,
				'options' => [
					$this->msg( 'gender-male' )->text() => 'male',
					$this->msg( 'gender-female' )->text() => 'female',
					$this->msg( 'gender-unknown' )->text() => 'unknown',
				]
			];
		}

		if ( $this->getRequest()->getCheck( 'wptoken' ) ) {
			$fields['token'] = [
				'type' => 'hidden',
				'default' => $this->getRequest()->getText( 'wptoken' )
			];
		}

		return $fields;
	}

	/**
	 * Process the form.  At this point we know that the user passes all the criteria in
	 * userCanExecute().
	 *
	 * @param array $data
	 *
	 * @return Bool|array
	 */
	public function handleSubmission( array $data ) {
		if ( array_key_exists( 'realname', $data ) ) {
			$this->getUser()->setRealName( $data['realname'] );
		}

		if ( array_key_exists( 'gender', $data ) ) {
			$this->getUser()->setOption( 'gender', $data['gender'] );
		}

		$this->getUser()->saveSettings();

		if ( $this->doEnroll( $this->course ) ) {
			return true;
		} else {
			return []; // TODO
		}
	}

	/**
	 * Gets called after the form is saved.
	 */
	public function onSuccess() {
		$this->getOutput()->redirect(
			SpecialPage::getTitleFor( 'MyCourses' )->getLocalURL( [
				'enrolled' => $this->course->getId()
			] )
		);
	}

	protected function getGroupName() {
		return 'education';
	}
}
