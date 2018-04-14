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
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class OrgPage extends EducationPage {

	protected static $info = [
		'edit-right' => 'ep-org',
		'list' => 'Institutions',
		'log-type' => 'institution',
	];

	/**
	 * @see EducationPage::getActions()
	 */
	public function getActions() {
		return [
			'view' => 'EducationProgram\ViewOrgAction',
			'edit' => 'EducationProgram\EditOrgAction',
			'history' => 'EducationProgram\HistoryAction',
			'delete' => 'EducationProgram\DeleteOrgAction',
			'purge' => 'EducationProgram\ViewOrgAction',
		];
	}

	/**
	 * @see EducationPage::getActions()
	 * @return PageTable
	 */
	public function getTable() {
		return Orgs::singleton();
	}

}
