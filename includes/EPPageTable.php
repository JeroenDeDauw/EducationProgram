<?php

/**
 * Abstract base class DBDataObjects that have associated page views.
 *
 * @since 0.1
 *
 * @file EPPageTable.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v3 or later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
abstract class EPPageTable extends DBTable {

	protected static $info = array(
		'EPCourses' => array(
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
			'log-type' => 'course',
		),
		'EPOrgs' => array(
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
		),
	);

	public function getIdentifierField() {
		return self::$info[get_called_class()]['identifier'];
	}

	public function getEditRight() {
		return self::$info[get_called_class()]['edit-right'];
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

	public function hasIdentifier( $identifier ) {
		return $this->has( array( $this->getIdentifierField() => $identifier ) );
	}

	public function get( $identifier, $fields = null ) {
		return static::selectRow( $fields, array( $this->getIdentifierField() => $identifier ) );
	}

	public function getListPage() {
		return self::$info[get_called_class()]['list'];
	}

	/**
	 * Delete all objects matching the provided condirions.
	 *
	 * @since 0.1
	 *
	 * @param EPRevisionAction $revAction
	 * @param array $conditions
	 *
	 * @return boolean
	 */
	public function deleteAndLog( EPRevisionAction $revAction, array $conditions ) {
		$objects = $this->select(
			null,
			$conditions
		);

		$success = true;

		if ( count( $objects ) > 0 ) {
			$revAction->setDelete( true );

			foreach ( $objects as /* EPPageObject */ $object ) {
				$success = $object->revisionedRemove( $revAction ) && $success;
			}
		}

		return $success;
	}

	/**
	 * (non-PHPdoc)
	 * @see EPRevisionedObject::getLogInfo()
	 */
	protected function getLogInfoForTitle( Title $title ) {
		return array(
			'type' => self::$info[get_called_class()]['log-type'],
			'title' => $title,
		);
	}

	public static function getTypeForNS( $ns ) {
		foreach ( self::$info as $type => $info ) {
			if ( $info['ns'] === $ns ) {
				return $type;
			}
		}

		throw new MWException( 'Unknown EPPageObject ns' );
	}

	public static function getLatestRevForTitle( Title $title, $conditions = array() ) {
		$conds = array(
			'type' => self::getTypeForNS( $title->getNamespace() ),
			'object_identifier' => $title->getText(),
		);

		return EPRevision::getLastRevision( array_merge( $conds, $conditions ) );
	}

	public static function displayDeletionLog( IContextSource $context, $messageKey ) {
		$out = $context->getOutput();

		LogEventsList::showLogExtract(
			$out,
			array( self::$info[get_called_class()]['log-type'] ),
			$context->getTitle(),
			'',
			array(
				'lim' => 10,
				'conds' => array( 'log_action' => 'remove' ),
				'showIfEmpty' => false,
				'msgKey' => array( $messageKey )
			)
		);
	}

}
