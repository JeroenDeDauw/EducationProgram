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
abstract class EPRestoreAction extends FormlessAction {

	/**
	 * (non-PHPdoc)
	 * @see Action::getName()
	 */
	public function getName() {
		return 'eprestore';
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
			
			if ( $req->wasPosted() && $this->getUser()->matchEditToken( $req->getText( 'restoreToken' ), $this->getSalt() ) ) {
				$success = $this->doRestore( $object );
				
				if ( $success ) {
					$query = array( 'restored' => '1' ); // TODO: handle
				}
				else {
					$query = array( 'delfailed' => '1' ); // TODO: handle
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
	 * Does the actual restore action.
	 * 
	 * @since 0.1
	 * 
	 * @return boolean Success indicator
	 */
	protected function doRestore( EPPageObject $object ) {
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

		$out->addWikiMsg( $this->prefixMsg( 'text' ), $object->getField( 'name' ) );

		$out->addHTML( Html::openElement(
			'form',
			array(
				'method' => 'post',
				'action' => $this->getTitle()->getLocalURL( array( 'action' => 'restore' ) ),
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
				'target-url' => $this->getTitle()->getLocalURL(),
			),
			wfMsg( $this->prefixMsg( 'cancel-button' ) )
		);

		$out->addHTML( Html::hidden( 'restoreToken', $this->getUser()->getEditToken( $this->getSalt() ) ) );

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
		return 'restore' . $this->getTitle()->getLocalURL();
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