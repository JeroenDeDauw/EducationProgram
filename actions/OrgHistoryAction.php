<?php

/**
 * Action to view the history of orgs.
 *
 * @since 0.1
 *
 * @file OrgHistoryAction.php
 * @ingroup EducationProgram
 * @ingroup Action
 *
 * @licence GNU GPL v3+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class OrgHistoryAction extends EPHistoryAction {

	/**
	 * Constructor.
	 *
	 * @since 0.1
	 *
	 * @param Page $page
	 * @param IContextSource $context
	 */
	protected function __construct( Page $page, IContextSource $context = null ) {
		parent::__construct( $page, $context, EPOrgs::singleton() );
	}
	
	public function getName() {
		return 'orghistory';
	}

	protected function getDescription() {
		return Linker::linkKnown(
			SpecialPage::getTitleFor( 'Log' ),
			$this->msg( 'ep-org-history' )->escaped(),
			array(),
			array( 'page' => $this->getTitle()->getPrefixedText() )
		);
	}

}