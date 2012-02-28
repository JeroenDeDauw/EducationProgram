<?php

/**
 * Abstract action class holding common EP functionality.
 *
 * @since 0.1
 *
 * @file EPAction.php
 * @ingroup EducationProgram
 * @ingroup Action
 *
 * @licence GNU GPL v3+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
abstract class EPAction extends FormlessAction {
	
	/**
	 * Display a warning that the page has been deleted together with the first
	 * few items from its deletion log.
	 * 
	 * @since 0.1
	 */
	public function displayDeletionLog() {
		$out = $this->getOutput();
		
		LogEventsList::showLogExtract(
			$out,
			array( $this->page->getLogType() ),
			$this->getTitle(),
			'',
			array(
				'lim' => 10,
				'conds' => array( 'log_action' => 'remove' ),
				'showIfEmpty' => false,
				'msgKey' => array( $this->prefixMsg( 'deleted' )  )
			)
		);
	}
	
	/**
	 * Display an undeletion link if the user is alloed to undelete and 
	 * if there are any previous revions that can be used to undelete.
	 * 
	 * @since 0.1
	 */
	public function displayUndeletionLink() {
		if ( $this->getUser()->isAllowed( $this->page->getEditRight() ) ) {
			$revisionCount = EPRevisions::singleton()->count( array(
				'object_identifier' => $this->getTitle()->getText()
			) );
			
			if ( $revisionCount > 0 ) {
				$this->getOutput()->addHTML( $this->msg(
					$this->prefixMsg( 'undelete-revisions' ),
					Message::rawParam( Linker::linkKnown(
						$this->getTitle(),
						$this->msg( $this->prefixMsg( 'undelete-link' ) )->numParams( $revisionCount )->escaped(),
						array(),
						array( 'action' => 'epundelete' )
					) )
				)->text() );
			}
		}
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
	 * Returns a salt based on the action and the page name.
	 * 
	 * @since 0.1
	 * 
	 * @return string
	 */
	protected function getSalt() {
		return get_class( $this->page ) . $this->getTitle()->getLocalURL();
	}
		
}
