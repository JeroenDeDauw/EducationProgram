<?php

/**
 * Action for undoing a change to an EPPageObject.
 *
 * @since 0.1
 *
 * @file EPUndeleteAction.php
 * @ingroup EducationProgram
 * @ingroup Action
 *
 * @licence GNU GPL v3+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EPUndeleteAction extends EPAction {

	/**
	 * (non-PHPdoc)
	 * @see Action::getName()
	 */
	public function getName() {
		return 'epundelete';
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
		return '';
	}

	/**
	 * (non-PHPdoc)
	 * @see FormlessAction::onView()
	 */
	public function onView() {
		$this->getOutput()->setPageTitle( $this->getPageTitle() );

		$object = $this->page->getTable()->get( $this->getTitle()->getText() );

		if ( $object === false ) {
			$revision = EPRevisions::singleton()->getLatestRevision( array(
				'object_identifier' => $this->getTitle()->getText(),
				'type' => $this->page->getTable()->getDataObjectClass(),
 			) );
			
			if ( $revision === false ) {
				$query = array( 'undeletefailed' => 'norevs' ); // TODO: handle
				$this->getOutput()->redirect( $this->getTitle()->getLocalURL( $query ) );
			}
			else {
				$req = $this->getRequest();
				
				if ( $req->wasPosted() && $this->getUser()->matchEditToken( $req->getText( 'undeleteToken' ), $this->getSalt() ) ) {
					$success = $this->doUndelete( $revision );
					
					if ( $success ) {
						$query = array( 'undeleted' => '1' ); // TODO: handle
					}
					else {
						$query = array( 'undeletefailed' => 'fail' ); // TODO: handle
					}
					
					$this->getOutput()->redirect( $this->getTitle()->getLocalURL( $query ) );
				}
				else {
					$this->displayForm( $revision );
				}
			}
		}
		else {
			$query = array( 'undeletefailed' => 'exists' ); // TODO: handle
			$this->getOutput()->redirect( $this->getTitle()->getLocalURL( $query ) );
		}
		
		return '';
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
		$revAction = new EPRevisionAction();
		
		$revAction->setUser( $this->getUser() );
		$revAction->setComment( $this->getRequest()->getText( 'summary', '' ) );
		
		return $revision->getObject()->undelete( $revAction );
	}

	/**
	 * Display the undeletion form for the provided EPPageObject.
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
			wfMsg( $this->prefixMsg( 'summary' ) ),
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
			wfMsg( $this->prefixMsg( 'undelete-button' ) ),
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
			wfMsg( $this->prefixMsg( 'cancel-button' ) )
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
		return wfMsgExt(
			$this->prefixMsg( 'title' ),
			'parsemag',
			$this->getTitle()->getText()
		);
	}

}