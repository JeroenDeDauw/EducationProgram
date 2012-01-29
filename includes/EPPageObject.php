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
			'ns' => EP_NS_COURSE,
			'actions' => array(
				'view' => false,
				'edit' => 'ep-course',
				'history' => false,
				'enroll' => 'ep-enroll',
			),
			'edit-right' => 'ep-course',
			'identifier' => 'name',
			'list' => 'Courses',
		),
		'EPOrg' => array(
			'ns' => EP_NS_INSTITUTION,
			'actions' => array(
				'view' => false,
				'edit' => 'ep-org',
				'history' => false,
			),
			'edit-right' => 'ep-org',
			'identifier' => 'name',
			'list' => 'Institutions',
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

	public function getTitle() {
		return Title::newFromText(
			$this->getIdentifier(),
			self::$info[get_called_class()]['ns']
		);
	}

	public function getLink( $action = 'view', $html = null, $customAttribs = array(), $query = array() ) {
		return self::getLinkFor(
			$this->getIdentifier(),
			$action,
			$html,
			$customAttribs,
			$query
		);
	}

	public static function getTitleFor( $identifierValue ) {
		return Title::newFromText(
			$identifierValue,
			self::$info[get_called_class()]['ns']
		);
	}

	public static function getLinkFor( $identifierValue, $action = 'view', $html = null, $customAttribs = array(), $query = array() ) {
		if ( $action !== 'view' ) {
			$query['action'] = $action;
		}
		
		return Linker::linkKnown( // Linker has no hook that allows us to figure out if the page actually exists :(
			self::getTitleFor( $identifierValue, $action ),
			is_null( $html ) ? htmlspecialchars( $identifierValue ) : $html,
			$customAttribs,
			$query
		);
	}
	
	public static function hasIdentifier( $identifier ) {
		return static::has( array( static::getIdentifierField() => $identifier ) );
	}
	
	public static function get( $identifier, $fields = null ) {
		return static::selectRow( $fields, array( static::getIdentifierField() => $identifier ) );
	}
	
	public static function getListPage() {
		return self::$info[get_called_class()]['list'];
	}

}