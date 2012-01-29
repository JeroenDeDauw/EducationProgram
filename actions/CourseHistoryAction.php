<?php

class CourseHistoryAction extends EPHistoryAction {
	
	
	public function getName() {
		return 'coursehistory';
	}

	protected function getDescription() {
		return wfMsg( 'coursehistory' );
	}

	protected function getItemClass() {
		return 'EPCourse';
	}
	
}