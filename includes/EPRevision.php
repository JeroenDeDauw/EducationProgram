<?php

/**
 * Class representing a single revision.
 *
 * @since 0.1
 *
 * @file EPRevision.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v3 or later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EPRevision extends EPDBObject {

	/**
	 * @see parent::__construct
	 *
	 * @since 0.1
	 *
	 * @param array|null $fields
	 * @param bool $loadDefaults
	 */
	public function __construct( $fields = null, $loadDefaults = false ) {
		$this->setStoreRevisions( false );
		parent::__construct( $fields, $loadDefaults );
	}

	/**
	 * @see parent::getFieldTypes
	 *
	 * @since 0.1
	 *
	 * @return array
	 */
	protected static function getFieldTypes() {
		return array(
			'id' => 'id',

			'object_id' => 'id',
			'user_id' => 'id',
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
	 * Create a new revision object for the provided EPDBObject.
	 * The EPDBObject should have all it's fields loaded.
	 *
	 * @since 0.1
	 *
	 * @param EPDBObject $object
	 * @param boolean $deleted
	 *
	 * @return EPRevision
	 */
	public static function newFromObject( EPDBObject $object, $deleted = false ) {
		$fields = array(
			'object_id' => $object->getId(),
			'user_id' => $GLOBALS['wgUser']->getID(), // TODO
			'user_text' => $GLOBALS['wgUser']->getName(), // TODO
			'type' => get_class( $object ),
			'comment' => '', // TODO
			'minor_edit' => false, // TODO
			'time' => wfTimestampNow(),
			'deleted' => $deleted,
			'data' => serialize( $object->toArray() )
		);

		return new static( $fields );
	}


}
