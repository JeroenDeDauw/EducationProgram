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
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class SpecialInstitutions extends VerySpecialPage {

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
			$this->startCache( 3600 );

			$this->displayNavigation();

			if ( $this->getUser()->isAllowed( 'ep-org' ) ) {
				$this->getOutput()->addModules( 'ep.addorg' );
				$this->addCachedHTML( 'EducationProgram\Org::getAddNewControl', $this->getContext() );
			}

			$this->addCachedHTML( 'EducationProgram\OrgPager::getPager', $this->getContext() );
			$this->getOutput()->addModules( OrgPager::getModules() );
		}
		else {
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

}
