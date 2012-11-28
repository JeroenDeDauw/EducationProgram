<?php

namespace EducationProgram;
use SpecialPage;

/**
 * Page listing campus ambassadors in a pager with filter control.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class SpecialCAs extends VerySpecialPage {

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
	 */
	public function execute( $subPage ) {
		parent::execute( $subPage );

		if ( $this->subPage === '' ) {
			$this->displayNavigation();

			$this->startCache( 3600 );
			$this->addCachedHTML( 'EducationProgram\CA::getPager', $this->getContext() );
		}
		else {
			$this->getOutput()->redirect( SpecialPage::getTitleFor( 'CampusAmbassador', $this->subPage )->getLocalURL() );
		}
	}

}
