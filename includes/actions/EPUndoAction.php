<?php

/**
 * Action for undoing a change to an EPPageObject.
 *
 * @since 0.1
 *
 * @file EPUndoAction.php
 * @ingroup EducationProgram
 * @ingroup Action
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EPUndoAction extends EPAction {

	/**
	 * (non-PHPdoc)
	 * @see Action::getName()
	 */
	public function getName() {
		return 'epundo';
	}

	/**
	 * (non-PHPdoc)
	 * @see Action::getRestriction()
	 */
	public function getRestriction() {
		return $this->page->getEditRight();
	}

	/**
	 * (non-PHPdoc)
	 * @see Action::getDescription()
	 */
	protected function getDescription() {
		return $this->msg( 'backlinksubtitle' )->rawParams( Linker::link( $this->getTitle() ) );
	}

	/**
	 * (non-PHPdoc)
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

			$success = false;

			if ( $req->getCheck( 'revid' ) ) {
				$revision = EPRevisions::singleton()->selectRow( null, array( 'id' => $req->getInt( 'revid' ) ) );

				if ( $revision !== false ) {
					if ( $req->wasPosted() && $this->getUser()->matchEditToken( $req->getText( 'undoToken' ), $this->getSalt() ) ) {
						$success = $this->doUndo( $object, $revision );
					}
					else {
						$diff = $object->getUndoDiff( $revision );

						if ( $diff->isValid() ) {
							if ( $diff->hasChanges() ) {
								$diffTable = new EPDiffTable( $this->getContext(), $diff );
								$diffTable->display();

								$this->displayForm( $object, $revision );
							}
							else {
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
				}
				else {
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
	 * @param EPPageObject $object
	 * @param EPRevision $revision
	 *
	 * @return boolean Success indicator
	 */
	protected function doUndo( EPPageObject $object, EPRevision $revision ) {
		$success = $object->undoRevision( $revision );

		if ( $success ) {
			$revAction = new EPRevisionAction();

			$revAction->setUser( $this->getUser() );
			$revAction->setComment( $this->getRequest()->getText( 'summary', '' ) );

			$success = $object->revisionedSave( $revAction );
		}

		return $success;
	}

	/**
	 * Display the undo revision form for the provided EPPageObject.
	 *
	 * @since 0.1
	 *
	 * @param EPPageObject $object
	 * @param EPRevision $revision
	 */
	protected function displayForm( EPPageObject $object, EPRevision $revision ) {
		$out = $this->getOutput();

		$out->addModules( 'ep.formpage' );

		$out->addWikiMsg( $this->prefixMsg( 'text' ), $object->getField( 'name' ) );

		$out->addHTML( Html::openElement(
			'form',
			array(
				'method' => 'post',
				'action' => $this->getTitle()->getLocalURL( array( 'action' => 'epundo' ) ),
			)
		) );

		$out->addHTML( '&#160;' . Xml::inputLabel(
			wfMsg( $this->prefixMsg( 'summary' ) ),
			'summary',
			'summary',
			65,
			wfMsgExt(
				$this->prefixMsg( 'summary-value' ),
				'parsemag',
				$this->getLanguage()->date( $revision->getField( 'time' ) ),
				$revision->getUser()->getName(),
				$this->getLanguage()->time( $revision->getField( 'time' ) )
			),
			array(
				'maxlength' => 250,
				'spellcheck' => true,
			)
		) );

		$out->addHTML( '<br />' );

		$out->addHTML( Html::input(
			'undo',
			wfMsg( $this->prefixMsg( 'undo-button' ) ),
			'submit',
			array(
				'class' => 'ep-undo',
			)
		) );

		$out->addElement(
			'button',
			array(
				'id' => 'cancelRestore',
				'class' => 'ep-undo-cancel ep-cancel',
				'data-target-url' => $this->getTitle()->getLocalURL(),
			),
			wfMsg( $this->prefixMsg( 'cancel-button' ) )
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
		return wfMsgExt(
			$this->prefixMsg( 'title' ),
			'parsemag',
			$this->getTitle()->getText()
		);
	}

}