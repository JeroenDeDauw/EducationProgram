<?php

/**
 * Page listing campus ambassadors in a pager with filter control.
 *
 * @since 0.1
 *
 * @file SpecialCAs.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class SpecialCAs extends SpecialEPPage {

	/**
	 * Constructor.
	 *
	 * @since 0.1
	 */
	public function __construct() {
		parent::__construct( 'CampusAmbassadors' );
	}

	/**
	 * Main method.
	 *
	 * @since 0.1
	 *
	 * @param string|null $subPage
	 * @return bool|void
	 */
	public function execute( $subPage ) {
		parent::execute( $subPage );

		if ( $this->subPage === '' ) {
			$this->displayNavigation();

			$this->startCache( 3600 );
			$this->addCachedHTML( 'EPCA::getPager', $this->getContext() );
		}
		else {
			$this->getOutput()->redirect( SpecialPage::getTitleFor( 'CampusAmbassador', $this->subPage )->getLocalURL() );
		}
	}

}
