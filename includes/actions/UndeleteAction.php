<?php

namespace EducationProgram;
use Html, Xml;

/**
 * Action for undoing a change to an PageObject.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 * @ingroup Action
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class UndeleteAction extends Action {

	/**
	 * @see Action::getName()
	 */
	public function getName() {
		return 'epundelete';
	}

	/**
	 * @see Action::getRestriction()
	 */
	public function getRestriction() {
		return $this->page->getEditRight();
	}

	/**
	 * @see Action::getDescription()
	 */
	protected function getDescription() {
		return '';
	}

	/**
	 * @see FormlessAction::onView()
	 */
	public function onView() {
		$this->getOutput()->setPageTitle( $this->getPageTitle() );

		$object = $this->page->getTable()->getFromTitle( $this->getTitle() );

		if ( $object === false ) {
			$revision = Revisions::singleton()->getLatestRevision( array(
				'object_identifier' => $this->getTitle()->getText(),
				'type' => $this->page->getTable()->getRevisionedObjectTypeId(),
			) );

			if ( $revision === false ) {
				$this->getRequest()->setSessionData(
					'epfail',
					$this->msg( $this->prefixMsg( 'failed-norevs' ), $this->getTitle()->getText() )->text()
				);
				$this->getOutput()->redirect( $this->getTitle()->getLocalURL() );
			}
			else {

				// This will check that we can undelete, and will output an
				// appropriate message if we can't.
				$canUndelete = $this->checkAndHandleRestrictions( $revision );

				// If there are no problems, do the undelete or proceed to show
				// the form.
				if ( $canUndelete ) {
					$req = $this->getRequest();

					if ( $req->wasPosted() && $this->getUser()->matchEditToken( $req->getText( 'undeleteToken' ), $this->getSalt() ) ) {
						$success = $this->doUndelete( $revision );

						if ( $success ) {
							$this->getRequest()->setSessionData(
								'epsuccess',
								$this->msg( $this->prefixMsg( 'undeleted' ), $this->getTitle()->getText() )->text()
							);
						}
						else {
							$this->getRequest()->setSessionData(
								'epfail',
								$this->msg( $this->prefixMsg( 'undelete-failed' ), $this->getTitle()->getText() )->text()
							);
						}

						$this->getOutput()->redirect( $this->getTitle()->getLocalURL() );
					}
					else {
						$this->displayForm( $revision );
					}
				}
			}
		}
		else {
			$this->getRequest()->setSessionData(
				'epfail',
				$this->msg( $this->prefixMsg( 'failed-exists' ), $this->getTitle()->getText() )->text()
			);
			$this->getOutput()->redirect( $this->getTitle()->getLocalURL() );
		}

		return '';
	}

	/**
	 * Check that we can perform the requested undeletion. If there are no
	 * problems, do nothing and return true. If there are problems, output
	 * any messages and return false.
	 *
	 * @since 0.4 alpha
	 *
	 * @param EPRevision $revision The latest revision of the object to undelete
	 *
	 * @return boolean
	 *
	 */
	protected function checkAndHandleRestrictions( $revision ) {

		// only worrying about restrictions for courses, specifically
		// checking that the associated institution is not also deleted
		if ( get_class( $this->page ) === 'EducationProgram\CoursePage' ) {

			$undeletionHelper = new CourseUndeletionHelper(
					$revision, $this->context, $this->page );

			if ( $undeletionHelper->checkRestrictions() ) {
				return true;
			} else {
				$undeletionHelper->outputCantUndeleteMsg();
				return false;
			}

		// institutions are always good
		} else {
			return true;
		}
	}

	/**
	 * Does the actual undeletion action.
	 *
	 * @since 0.1
	 *
	 * @param EPRevision $revision
	 *
	 * @return boolean Success indicator
	 */
	protected function doUndelete( EPRevision $revision ) {
		$revAction = new RevisionAction();

		$revAction->setUser( $this->getUser() );
		$revAction->setComment( $this->getRequest()->getText( 'summary', '' ) );

		return $revision->getObject()->undelete( $revAction );
	}

	/**
	 * Display the undeletion form for the provided PageObject.
	 *
	 * @since 0.1
	 *
	 * @param EPRevision $revision
	 */
	protected function displayForm( EPRevision $revision ) {
		$out = $this->getOutput();

		$out->addModules( 'ep.formpage' );

		$object = $revision->getObject();

		$out->addWikiMsg( $this->prefixMsg( 'text' ), $object->getField( 'name' ) );

		$out->addHTML( Html::openElement(
			'form',
			array(
				'method' => 'post',
				'action' => $this->getTitle()->getLocalURL( array( 'action' => 'epundelete' ) ),
			)
		) );

		$out->addHTML( '&#160;' . Xml::inputLabel(
			$this->msg( $this->prefixMsg( 'summary' ) )->text(),
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
			'undelete',
			$this->msg( $this->prefixMsg( 'undelete-button' ) )->text(),
			'submit',
			array(
				'class' => 'ep-undelete',
			)
		) );

		$out->addElement(
			'button',
			array(
				'id' => 'cancelUndeletion',
				'class' => 'ep-undelete-cancel ep-cancel',
				'data-target-url' => $this->getTitle()->getLocalURL(),
			),
			$this->msg( $this->prefixMsg( 'cancel-button' ) )->text()
		);

		$out->addHTML( Html::hidden( 'undeleteToken', $this->getUser()->getEditToken( $this->getSalt() ) ) );

		$out->addHTML( '</form>' );
	}

	/**
	 * Returns the page title.
	 *
	 * @since 0.1
	 *
	 * @return string
	 */
	protected function getPageTitle() {
		return $this->msg(
			$this->prefixMsg( 'title' ),
			$this->getTitle()->getText()
		)->text();
	}
}
