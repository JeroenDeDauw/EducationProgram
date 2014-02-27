<?php

namespace EducationProgram;
use Linker, Html;

/**
 * Action for deleting PageObject items.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 * @ingroup Action
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DeleteAction extends Action {
	/**
	 * @see Action::getName()
	 */
	public function getName() {
		return 'delete';
	}

	/**
	 * @see Action::getDescription()
	 */
	protected function getDescription() {
		return $this->msg( 'backlinksubtitle' )->rawParams( Linker::link( $this->getTitle() ) );
	}

	/**
	 * @see Action::getRestriction()
	 */
	public function getRestriction() {
		return $this->page->getEditRight();
	}

	/**
	 * @see FormlessAction::onView()
	 */
	public function onView() {
		$this->getOutput()->setPageTitle( $this->getPageTitle() );

		$object = $this->page->getTable()->getFromTitle( $this->getTitle() );

		if ( $object === false ) {
			$this->getOutput()->addWikiMsg( $this->prefixMsg( 'none' ), $this->getTitle()->getText() );
			$this->getOutput()->setSubtitle( '' );
		}
		else {
			$req = $this->getRequest();

			// This will check that we can delete, and will output an
			// appropriate message if we can't.
			$canDelete = $this->checkAndHandleRestrictions( $object );

			// If there are no problems, proceed to delete or show the form
			if ( $canDelete ) {

				if ( $req->wasPosted() && $this->getUser()->matchEditToken( $req->getText( 'deleteToken' ), $this->getSalt() ) ) {
					$success = $this->doDelete( $object );

					if ( $success ) {
						$title = \SpecialPage::getTitleFor( $this->page->getListPage() );
						$this->getRequest()->setSessionData(
							'epsuccess',
							$this->msg( $this->prefixMsg( 'deleted' ), $this->getTitle()->getText() )->text()
						);
					}
					else {
						$title = $this->getTitle();
						$this->getRequest()->setSessionData(
							'epfail',
							$this->msg(
								$this->prefixMsg( 'delete-failed' ),
								$this->getTitle()->getText(),
								$this->getTitle()->getText()
							)->parse()
						);
					}

					$this->getOutput()->redirect( $title->getLocalURL() );
				}
				else {
					$this->displayForm( $object );
				}
			}
		}

		return '';
	}

	/**
	 * Check that we can perform the requested deletion. If there are no
	 * problems, do nothing and return true. If there are problems, output
	 * an appropriate message and return false.
	 *
	 * This default implementation just returns true; subclasses may override.
	 *
	 * @since 0.4 alpha
	 *
	 * @param PageObject $pageObj The object (so far, Org or Course) to be
	 *   deleted.
	 *
	 * @return boolean
	 *
	 */
	protected function checkAndHandleRestrictions( $pageObj ) {
		return true;
	}

	/**
	 * Does the actual deletion action.
	 *
	 * @since 0.1
	 *
	 * @param PageObject $object
	 *
	 * @return boolean Success indicator
	 */
	protected function doDelete( PageObject $object ) {
		$revAction = new RevisionAction();

		$revAction->setUser( $this->getUser() );
		$revAction->setComment( $this->getRequest()->getText( 'summary', '' ) );
		$revAction->setDelete( true );

		return $object->revisionedRemove( $revAction );
	}

	/**
	 * Display the deletion form for the provided PageObject.
	 *
	 * @since 0.1
	 *
	 * @param PageObject $object
	 */
	protected function displayForm( PageObject $object ) {
		$out = $this->getOutput();

		$out->addModules( 'ep.formpage' );

		$out->addHTML( Html::openElement(
			'div',
			array( 'class' => 'formpageDeleteWarning' )
		) );

		$out->addWikiMsg( $this->prefixMsg( 'text' ), $object->getField( 'name' ) );

		$out->addHTML( Html::closeElement( 'div' ) );

		$out->addHTML( Html::openElement(
			'form',
			array(
				'method' => 'post',
				'action' => $this->getTitle()->getLocalURL( array( 'action' => 'delete' ) ),
			)
		) );

		$out->addHTML( '&#160;' . \Xml::inputLabel(
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
			'delete',
			$this->msg( $this->prefixMsg( 'delete-button' ) )->text(),
			'submit',
			array(
				'class' => 'ep-delete',
			)
		) );

		$out->addElement(
			'button',
			array(
				'id' => 'cancelDelete',
				'class' => 'ep-delete-cancel ep-cancel',
				'data-target-url' => $this->getTitle()->getLocalURL(),
			),
			$this->msg( $this->prefixMsg( 'cancel-button' ) )->text()
		);

		$out->addHTML( Html::hidden( 'deleteToken', $this->getUser()->getEditToken( $this->getSalt() ) ) );

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
