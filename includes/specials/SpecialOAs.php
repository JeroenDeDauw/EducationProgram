<?php

namespace EducationProgram;

/**
 * Page listing online ambassadors in a pager with filter control.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 *
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class SpecialOAs extends VerySpecialPage {

	public function __construct() {
		parent::__construct( 'OnlineAmbassadors' );
	}

	/**
	 * Main method.
	 *
	 * @param string|null $subPage
	 */
	public function execute( $subPage ) {
		parent::execute( $subPage );

		if ( $this->subPage === '' ) {
			$this->displayNavigation();

			$this->startCache( 3600 );
			$this->addCachedHTML( 'EducationProgram\OA::getPager', $this->getContext() );
		} else {
			$this->getOutput()->redirect(
				\SpecialPage::getTitleFor( 'OnlineAmbassador', $this->subPage )->getLocalURL()
			);
		}
	}

	protected function getGroupName() {
		return 'education';
	}
}
