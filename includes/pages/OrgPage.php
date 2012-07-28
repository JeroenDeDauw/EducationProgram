<?php

/**
 * Page for interacting with an org.
 *
 * @since 0.1
 *
 * @file OrgPage.php
 * @ingroup EducationProgram
 * @ingroup Page
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class OrgPage extends EPPage {

	protected static $info = array(
		'edit-right' => 'ep-org',
		'list' => 'Institutions',
		'log-type' => 'institution',
	);

	/**
	 * (non-PHPdoc)
	 * @see EPPage::getActions()
	 */
	public function getActions() {
		return array(
			'view' => 'ViewOrgAction',
			'edit' => 'EditOrgAction',
			'history' => 'EPHistoryAction',
			'delete' => 'EPDeleteAction',
			'purge' => 'ViewOrgAction',
		);
	}

	/**
	 * (non-PHPdoc)
	 * @see EPPage::getActions()
	 * @return EPPageTable
	 */
	public function getTable() {
		return EPOrgs::singleton();
	}

}