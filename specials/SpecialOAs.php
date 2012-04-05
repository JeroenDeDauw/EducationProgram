<?php

/**
 * Page listing online ambassadors in a pager with filter control.
 *
 * @since 0.1
 *
 * @file SpecialOAs.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class SpecialOAs extends SpecialEPPage {

	/**
	 * Constructor.
	 *
	 * @since 0.1
	 */
	public function __construct() {
		parent::__construct( 'OnlineAmbassadors' );
	}

	/**
	 * Main method.
	 *
	 * @since 0.1
	 *
	 * @param string|null $subPage
	 */
	public function execute( $subPage ) {
		parent::execute( $subPage );

		if ( $this->subPage === '' ) {
			$this->displayNavigation();

			$this->startCache( 3600 );
			$this->addCachedHTML( 'EPOA::getPager', $this->getContext() );
			$this->saveCache();
		}
		else {
			$this->getOutput()->redirect( SpecialPage::getTitleFor( 'OnlineAmbassador', $this->subPage )->getLocalURL() );
		}
	}

}
