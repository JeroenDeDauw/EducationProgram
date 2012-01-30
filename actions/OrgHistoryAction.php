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
	
	
	public function getName() {
		return 'orghistory';
	}

	protected function getDescription() {
		return wfMsg( 'orghistory' );
	}

	protected function getItemClass() {
		return 'EPOrg';
	}
	
}