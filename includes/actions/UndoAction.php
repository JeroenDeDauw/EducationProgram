<?php

namespace EducationProgram;

use Html;
use Xml;

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
class UndoAction extends Action {

	/**
	 * @see Action::getName()
	 */
	public function getName() {
		return 'epundo';
	}

	public function doesWrites() {
		return true;
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
		return $this->msg( 'backlinksubtitle' )->rawParams( \Linker::link( $this->getTitle() ) );
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
		} else {
			$req = $this->getRequest();

			$success = false;

			if ( $req->getCheck( 'revid' ) ) {
				$revision = Revisions::singleton()->selectRow( null, [ 'id' => $req->getInt( 'revid' ) ] );

				if ( $revision !== false ) {
					if ( $req->wasPosted() && $this->getUser()->matchEditToken( $req->getText( 'undoToken' ), $this->getSalt() ) ) {
						$success = $this->doUndo( $object, $revision );
					} else {
						$diff = $object->getUndoDiff( $revision );

						if ( $diff->isValid() ) {
							if ( $diff->hasChanges() ) {
								$diffTable = new DiffTable( $this->getContext(), $diff );
								$diffTable->display();

								$this->displayForm( $object, $revision );
							} else {
								// TODO
							}

							$success = null;
						}
					}
				}
			}

			if ( !is_null( $success ) ) {
				if ( $success ) {
					$this->getRequest()->setSessionData(
						'epsuccess',
						$this->msg( $this->prefixMsg( 'undid' ), $this->getTitle()->getText() )->text()
					);
				} else {
					$this->getRequest()->setSessionData(
						'epfail',
						$this->msg( $this->prefixMsg( 'undo-failed' ), $this->getTitle()->getText() )->text()
					);
				}

				$this->getOutput()->redirect( $object->getTitle()->getLocalURL() );
			}
		}

		return '';
	}

	/**
	 * Does the actual undo action.
	 *
	 * @since 0.1
	 *
	 * @param PageObject $object
	 * @param EPRevision $revision
	 *
	 * @return boolean Success indicator
	 */
	protected function doUndo( PageObject $object, EPRevision $revision ) {
		$success = $object->undoRevision( $revision );

		if ( $success ) {
			$revAction = new RevisionAction();

			$revAction->setUser( $this->getUser() );
			$revAction->setComment( $this->getRequest()->getText( 'summary', '' ) );

			$success = $object->revisionedSave( $revAction );
		}

		return $success;
	}

	/**
	 * Display the undo revision form for the provided PageObject.
	 *
	 * @since 0.1
	 *
	 * @param PageObject $object
	 * @param EPRevision $revision
	 */
	protected function displayForm( PageObject $object, EPRevision $revision ) {
		$out = $this->getOutput();

		$out->addModules( 'ep.formpage' );

		$out->addWikiMsg( $this->prefixMsg( 'text' ), $object->getField( 'name' ) );

		$out->addHTML( Html::openElement(
			'form',
			[
				'method' => 'post',
				'action' => $this->getTitle()->getLocalURL( [ 'action' => 'epundo' ] ),
			]
		) );

		$out->addHTML( '&#160;' . Xml::inputLabel(
			$this->msg( $this->prefixMsg( 'summary' ) )->text(),
			'summary',
			'summary',
			65,
			$this->msg(
				$this->prefixMsg( 'summary-value' ),
				$this->getLanguage()->date( $revision->getField( 'time' ) ),
				$revision->getUser()->getName(),
				$this->getLanguage()->time( $revision->getField( 'time' ) )
			)->text(),
			[
				'maxlength' => 250,
				'spellcheck' => true,
			]
		) );

		$out->addHTML( '<br />' );

		$out->addHTML( Html::input(
			'undo',
			$this->msg( $this->prefixMsg( 'undo-button' ) )->text(),
			'submit',
			[
				'class' => 'ep-undo',
			]
		) );

		$out->addElement(
			'button',
			[
				'id' => 'cancelRestore',
				'class' => 'ep-undo-cancel ep-cancel',
				'data-target-url' => $this->getTitle()->getLocalURL(),
			],
			$this->msg( $this->prefixMsg( 'cancel-button' ) )->text()
		);

		$out->addHTML( Html::hidden( 'revid', $this->getRequest()->getInt( 'revid' ) ) );
		$out->addHTML( Html::hidden( 'undoToken', $this->getUser()->getEditToken( $this->getSalt() ) ) );

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
