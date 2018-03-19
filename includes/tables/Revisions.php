<?php

namespace EducationProgram;

/**
 * Class representing the ep_revisions table.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class Revisions extends ORMTable {

	public function __construct() {
		$this->fieldPrefix = 'rev_';
	}

	/**
	 * @see ORMTable::getName()
	 *
	 * @return string
	 */
	public function getName() {
		return 'ep_revisions';
	}

	/**
	 * @see ORMTable::getRowClass()
	 *
	 * @return string
	 */
	public function getRowClass() {
		return 'EducationProgram\EPRevision';
	}

	/**
	 * @see ORMTable::getFields()
	 *
	 * @return array
	 */
	public function getFields() {
		return [
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
		];
	}

	/**
	 * Create a new revision object for the provided RevisionedObject.
	 * The RevisionedObject should have all it's fields loaded.
	 *
	 * @param RevisionedObject $object
	 * @param RevisionAction $revAction
	 *
	 * @return EPRevision
	 */
	public function newFromObject( RevisionedObject $object, RevisionAction $revAction ) {
		$fields = array_merge( $object->getRevisionIdentifiers(), [
			'user_id' => $revAction->getUser()->getId(),
			'user_text' => $revAction->getUser()->getName(),
			'comment' => $revAction->getComment(),
			'minor_edit' => $revAction->isMinor(),
			'time' => $revAction->getTime(),
			'deleted' => $revAction->isDelete(),
			'data' => $object->toArray()
		] );

		$identifier = $object->getIdentifier();

		if ( !is_null( $identifier ) ) {
			$fields['object_identifier'] = $identifier;
		}

		return new EPRevision( $this, $fields );
	}

	/**
	 * Returns the most recent revision matching the provided conditions.
	 *
	 * @param array $conds
	 *
	 * @return EPRevision|bool false
	 */
	public function getLatestRevision( array $conds ) {
		return $this->selectRow(
			null,
			$conds,
			[
				'ORDER BY' => $this->getPrefixedField( 'id' ) . ' DESC',
			]
		);
	}

}
