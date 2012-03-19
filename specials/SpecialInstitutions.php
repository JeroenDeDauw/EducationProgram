<?php

/**
 * Page listing all institutions in a pager with filter control.
 * Also has a form for adding new items for those with matching privileges.
 *
 * @since 0.1
 *
 * @file SpecialInstitutions.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v3 or later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class SpecialInstitutions extends SpecialEPPage {

	/**
	 * Constructor.
	 *
	 * @since 0.1
	 */
	public function __construct() {
		parent::__construct( 'Institutions' );
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
			$this->startCache( 3600, $this->getUser()->isAnon() );

			$this->displayNavigation();

			if ( $this->getUser()->isAllowed( 'ep-org' ) ) {
				$this->getOutput()->addModules( 'ep.addorg' );
				$this->addCachedHTML( 'EPOrg::getAddNewControl', array( $this->getContext() ) );
			}

			$this->addCachedHTML( 'EPOrg::getPager', array( $this->getContext() ) );

			$this->saveCache();
		}
		else {
			$this->getOutput()->redirect( Title::newFromText( $this->subPage, EP_NS_INSTITUTION )->getLocalURL() );
		}
	}

	/**
	 * @see SpecialCachedPage::getCacheKey
	 * @return array
	 */
	protected function getCacheKey() {
		$values = $this->getRequest()->getValues();

		if ( array_key_exists( 'action', $values ) && $values['action'] === 'purge' ) {
			unset( $values['action'] );
		}

		return array_merge( $values, parent::getCacheKey() );
	}

}
