<?php

/**
 * Abstract action for undoing a change to an EPPageObject.
 *
 * @since 0.1
 *
 * @file EPUndoAction.php
 * @ingroup EducationProgram
 * @ingroup Action
 *
 * @licence GNU GPL v3+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EPUndoAction extends FormlessAction {

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

		$object = $this->page->getTable()->get( $this->getTitle()->getText() );

		if ( $object === false ) {
			$this->getOutput()->addWikiMsg( $this->prefixMsg( 'none' ), $this->getTitle()->getText() );
			$this->getOutput()->setSubtitle( '' );
		}
		else {
			$req = $this->getRequest();
			
			if ( $req->wasPosted() && $this->getUser()->matchEditToken( $req->getText( 'undoToken' ), $this->getSalt() ) ) {
				$success = $this->doUndo( $object );
				
				if ( $success ) {
					$query = array( 'undid' => '1' ); // TODO: handle
				}
				else {
					$query = array( 'undofailed' => '1' ); // TODO: handle
				}
				
				$this->getOutput()->redirect( $object->getTitle()->getLocalURL( $query ) );
			}
			else {
				$this->displayForm( $object );
			}
		}

		return '';
	}
	
	/**
	 * Does the actual undo action.
	 * 
	 * @since 0.1
	 * 
	 * @return boolean Success indicator
	 */
	protected function doUndo( EPPageObject $object ) {
		$revAction = new EPRevisionAction();
		
		$revAction->setUser( $this->getUser() );
		$revAction->setComment( $this->getRequest()->getText( 'summary', '' ) );
		
		// TODO
		
		return false;
	}

	/**
	 * Display the restoration form for the provided EPPageObject.
	 * 
	 * @since 0.1
	 * 
	 * @param EPPageObject $object
	 */
	protected function displayForm( EPPageObject $object ) {
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
			false,
			array(
				'maxlength' => 250,
				'spellcheck' => true,
			)
		) );

		$out->addHTML( '<br />' );

		$out->addHTML( Html::input(
			'restore',
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

		$out->addHTML( Html::hidden( 'undoToken', $this->getUser()->getEditToken( $this->getSalt() ) ) );

		$out->addHTML( '</form>' );
	}
	
	/**
	 * Returns a salt based on the action and the page name.
	 * 
	 * @since 0.1
	 * 
	 * @return string
	 */
	protected function getSalt() {
		return 'undo' . $this->getTitle()->getLocalURL();
	}

	/**
	 * Returns a prefixed message name.
	 * 
	 * @since 0.1
	 * 
	 * @param string $name
	 * 
	 * @return string
	 */
	protected function prefixMsg( $name ) {
		return strtolower( get_class( $this->page ) ) . '-' . $this->getName() . '-' . $name;
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