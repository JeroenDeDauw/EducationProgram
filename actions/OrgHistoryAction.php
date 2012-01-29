<?php

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