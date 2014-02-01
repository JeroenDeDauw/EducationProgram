<?php

namespace EducationProgram;

/**
 * Page for interacting with an org.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 * @ingroup Page
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class OrgPage extends EducationPage {

	protected static $info = array(
		'edit-right' => 'ep-org',
		'list' => 'Institutions',
		'log-type' => 'institution',
	);

	/**
	 * @see EducationPage::getActions()
	 */
	public function getActions() {
		return array(
			'view' => 'EducationProgram\ViewOrgAction',
			'edit' => 'EducationProgram\EditOrgAction',
			'history' => 'EducationProgram\HistoryAction',
			'delete' => 'EducationProgram\DeleteOrgAction',
			'purge' => 'EducationProgram\ViewOrgAction',
		);
	}

	/**
	 * @see EducationPage::getActions()
	 * @return PageTable
	 */
	public function getTable() {
		return Orgs::singleton();
	}

}