<?php



class CoursePage extends EPPage {
	
	protected function getActions() {
		return array(
			'view' => 'ViewCourseAction',
			'edit' => 'EditCourseAction',
			'history' => 'CourseHistoryAction',
			'enroll' => 'CourseEnrollAction',
		);
	}
	
}

