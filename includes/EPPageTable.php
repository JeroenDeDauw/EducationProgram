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

	public function getIdentifierField() {
		return static::$info['identifier'];
	}

	public function getEditRight() {
		return static::$info['edit-right'];
	}

	public static function getTitleFor( $identifierValue ) {
		return Title::newFromText(
			$identifierValue,
			static::$info['ns']
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
		return static::$info['list'];
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
	public function getLogInfoForTitle( Title $title ) {
		return array(
			'type' => static::$info['log-type'],
			'title' => $title,
		);
	}

	public static function getTypeForNS( $ns ) {
		foreach ( static::$info as $type => $info ) {
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
			array( static::$info['log-type'] ),
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
