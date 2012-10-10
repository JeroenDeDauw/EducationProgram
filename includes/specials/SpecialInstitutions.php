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
 * @licence GNU GPL v2+
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
	 * @return bool|void
	 */
	public function execute( $subPage ) {
		parent::execute( $subPage );

		if ( $this->subPage === '' ) {
			$this->startCache( 3600 );

			$this->displayNavigation();

			if ( $this->getUser()->isAllowed( 'ep-org' ) ) {
				$this->getOutput()->addModules( 'ep.addorg' );
				$this->addCachedHTML( 'EPOrg::getAddNewControl', $this->getContext() );
			}

			$this->addCachedHTML( 'EPOrgPager::getPager', $this->getContext() );
			$this->getOutput()->addModules( EPOrgPager::getModules() );
		}
		else {
			$this->getOutput()->redirect( EPOrgs::singleton()->getTitleFor( $this->subPage )->getLocalURL() );
		}
	}

	/**
	 * @see SpecialCachedPage::getCacheKey
	 * @return array
	 */
	protected function getCacheKey() {
		// TODO: fix session issue
		$values = $this->getRequest()->getValues();

		$user = $this->getUser();

		$values[] = $user->isAllowed( 'ep-org' );
		$values[] = $user->isAllowed( 'ep-bulkdelorgs' );
		$values[] = $user->getOption( 'ep_bulkdelorgs' );

		return array_merge( $values, parent::getCacheKey() );
	}

}
