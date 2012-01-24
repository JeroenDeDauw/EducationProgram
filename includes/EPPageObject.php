<?php

/**
 * Abstract base class for EPDBObjects that have associated view, edit and history pages
 *
 * @since 0.1
 *
 * @file EPPageObject.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v3 or later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
abstract class EPPageObject extends EPDBObject {

	protected static $info = array(
		'EPCourse' => array(
			'pages' => array(
				'view' => 'Course',
				'edit' => 'EditCourse',
				'history' => 'CourseHistory',
			),
			'edit-right' => 'ep-course',
			'identifier' => 'id',
		),
		'EPMC' => array(
			'pages' => array(
				'view' => 'MasterCourse',
				'edit' => 'EditMasterCourse',
				'history' => 'MasterCourseHistory',
			),
			'edit-right' => 'ep-mc',
			'identifier' => 'name',
		),
		'EPOrg' => array(
			'pages' => array(
				'view' => 'Institution',
				'edit' => 'EditInstitution',
				'history' => 'InstitutionHistory',
			),
			'edit-right' => 'ep-org',
			'identifier' => 'name',
		),
	);

	public static function getIdentifierField() {
		return self::$info[get_called_class()]['identifier'];
	}

	public function getIdentifier() {
		return $this->getField( self::$info[get_called_class()]['identifier'] );
	}

	public static function getEditRight() {
		return self::$info[get_called_class()]['edit-right'];
	}

	public static function getTitleText( $action = 'view' ) {
		return self::$info[get_called_class()]['pages'][$action];
	}

	public function getTitle( $action = 'view' ) {
		return SpecialPage::getTitleFor( self::getTitleText( $action ), $this->getIdentifier() );
	}

	public function getLink( $action = 'view' ) {
		return Linker::link(
			self::getTitle( $action ),
			htmlspecialchars( $this->getIdentifier() )
		);
	}

}