<?php

namespace EducationProgram;

/**
 * Page listing all institutions in a pager with filter control.
 * Also has a form for adding new items for those with matching privileges.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 *
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class SpecialInstitutions extends VerySpecialPage {

	public function __construct() {
		parent::__construct( 'Institutions' );
	}

	/**
	 * Main method.
	 *
	 * @param string|null $subPage
	 */
	public function execute( $subPage ) {
		parent::execute( $subPage );

		if ( $this->subPage === '' ) {
			$this->startCache( 3600 );

			$this->displayNavigation();

			if ( $this->getUser()->isAllowed( 'ep-org' ) ) {
				$this->getOutput()->addModules( 'ep.addorg' );
				$this->addCachedHTML( 'EducationProgram\Org::getAddNewControl', $this->getContext() );
			}

			$this->addCachedHTML( 'EducationProgram\OrgPager::getPager', $this->getContext() );
			$this->getOutput()->addModules( OrgPager::getModules() );
		} else {
			$this->getOutput()->redirect( Orgs::singleton()->getTitleFor( $this->subPage )->getLocalURL() );
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

	protected function getGroupName() {
		return 'education';
	}
}
