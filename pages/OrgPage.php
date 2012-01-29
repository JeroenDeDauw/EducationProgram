<?php


class OrgPage extends EPPage {
	
	protected function getActions() {
		return array(
			'view' => 'ViewOrgAction',
			'edit' => 'EditOrgAction',
			'history' => 'OrgHistoryAction',
		);
	}
	
}