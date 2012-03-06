<?php

/**
 * Abstract action for restoring an EPPageObject to a previous revision.
 *
 * @since 0.1
 *
 * @file EPRestoreAction.php
 * @ingroup EducationProgram
 * @ingroup Action
 *
 * @licence GNU GPL v3+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EPRestoreAction extends EPAction {

	/**
	 * (non-PHPdoc)
	 * @see Action::getName()
	 */
	public function getName() {
		return 'eprestore';
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

		$object = $this->page->getTable()->get( $this->getTitle()->getText() );

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
					if ( $req->wasPosted() && $this->getUser()->matchEditToken( $req->getText( 'restoreToken' ), $this->getSalt() ) ) {
						$success = $this->doRestore( $object, $revision );
					}
					else {
						$this->displayForm( $object, $revision );
						$success = null;
					}
				}
			}

			if ( !is_null( $success ) ) {
				if ( $success ) {
					$query = array( 'restored' => '1' ); // TODO: handle
				}
				else {
					$query = array( 'restorefailed' => '1' ); // TODO: handle
				}

				$this->getOutput()->redirect( $object->getTitle()->getLocalURL( $query ) );
			}
		}

		return '';
	}
	
	/**
	 * Does the actual restore action.
	 * 
	 * @since 0.1
	 *
	 * @param EPPageObject $object
	 * @param EPRevision $revision
	 * 
	 * @return boolean Success indicator
	 */
	protected function doRestore( EPPageObject $object, EPRevision $revision ) {
		$success = $object->restoreToRevision( $revision, $object->getTable()->getRevertableFields() );
		
		if ( $success ) {
			$revAction = new EPRevisionAction();
		
			$revAction->setUser( $this->getUser() );
			$revAction->setComment( $this->getRequest()->getText( 'summary', '' ) );
			
			$success = $object->revisionedSave( $revAction );
		}
		
		return $success;
	}

	/**
	 * Display the restoration form for the provided EPPageObject.
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
				'action' => $this->getTitle()->getLocalURL( array( 'action' => 'eprestore' ) ),
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
				$this->getLanguage()->timeanddate( $revision->getField( 'time' ) ),
				$revision->getUser()->getName()
			),
			array(
				'maxlength' => 250,
				'spellcheck' => true,
			)
		) );

		$out->addHTML( '<br />' );

		$out->addHTML( Html::input(
			'restore',
			wfMsg( $this->prefixMsg( 'restore-button' ) ),
			'submit',
			array(
				'class' => 'ep-restore',
			)
		) );

		$out->addElement(
			'button',
			array(
				'id' => 'cancelRestore',
				'class' => 'ep-restore-cancel ep-cancel',
				'data-target-url' => $this->getTitle()->getLocalURL(),
			),
			wfMsg( $this->prefixMsg( 'cancel-button' ) )
		);
		
		$out->addHTML( Html::hidden( 'revid', $this->getRequest()->getInt( 'revid' ) ) );
		$out->addHTML( Html::hidden( 'restoreToken', $this->getUser()->getEditToken( $this->getSalt() ) ) );

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
