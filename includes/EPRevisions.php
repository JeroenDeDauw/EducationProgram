<?php

/**
 * Class representing the ep_revisions table.
 *
 * @since 0.1
 *
 * @file EPRevisions.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v3 or later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EPRevisions extends DBTable {

	/**
	 * (non-PHPdoc)
	 * @see DBTable::getDBTable()
	 * @since 0.1
	 * @return string
	 */
	public function getDBTable() {
		return 'ep_revisions';
	}
	
	/**
	 * (non-PHPdoc)
	 * @see DBTable::getFieldPrefix()
	 * @since 0.1
	 * @return string
	 */
	public function getFieldPrefix() {
		return 'rev_';
	}
	
	/**
	 * (non-PHPdoc)
	 * @see DBTable::getDataObjectClass()
	 * @since 0.1
	 * @return string
	 */
	public function getDataObjectClass() {
		return 'EPRevision';
	}
	
	/**
	 * (non-PHPdoc)
	 * @see DBTable::getFieldTypes()
	 * @since 0.1
	 * @return array
	 */
	public function getFieldTypes() {
		return array(
			'id' => 'id',

			'object_id' => 'int',
			'object_identifier' => 'str',
			'user_id' => 'int',
			'type' => 'str',
			'comment' => 'str',
			'user_text' => 'str',
			'minor_edit' => 'bool',
			'time' => 'str', // TS_MW
			'deleted' => 'bool',
			'data' => 'blob',
		);
	}
	
	/**
	 * Create a new revision object for the provided EPRevisionedObject.
	 * The EPRevisionedObject should have all it's fields loaded.
	 *
	 * @since 0.1
	 *
	 * @param DBDataObject $object
	 * @param EPRevisionAction $revAction
	 *
	 * @return EPRevision
	 */
	public function newFromObject( EPRevisionedObject $object, EPRevisionAction $revAction ) {
		$fields = array(
			'object_id' => $object->getId(),
			'user_id' => $revAction->getUser()->getID(),
			'user_text' => $revAction->getUser()->getName(),
			'type' => get_class( $object ),
			'comment' => $revAction->getComment(),
			'minor_edit' => $revAction->isMinor(),
			'time' => $revAction->getTime(),
			'deleted' => $revAction->isDelete(),
			'data' => serialize( $object->toArray() )
		);
		
		$identifier = $object->getIdentifier();
		
		if ( !is_null( $identifier ) ) {
			$fields['object_identifier'] = $identifier;
		}

		return new EPRevision( $this, $fields );
	}
	
}
