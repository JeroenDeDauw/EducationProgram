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
 * @licence GNU GPL v3+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class OrgPage extends EPPage {
	
	protected static $info = array(
		'ns' => EP_NS_INSTITUTION,
		'actions' => array(
			'view' => false,
			'edit' => 'ep-org',
			'history' => false,
		),
		'edit-right' => 'ep-org',
		'identifier' => 'name',
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
			'history' => 'OrgHistoryAction',
			'delete' => 'EPDeleteAction',
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