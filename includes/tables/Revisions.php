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
class Revisions extends \ORMTable {

	/**
	 * @see ORMTable::getName()
	 * @since 0.1
	 * @return string
	 */
	public function getName() {
		return 'ep_revisions';
	}

	/**
	 * @see ORMTable::getFieldPrefix()
	 * @since 0.1
	 * @return string
	 */
	public function getFieldPrefix() {
		return 'rev_';
	}

	/**
	 * @see ORMTable::getRowClass()
	 * @since 0.1
	 * @return string
	 */
	public function getRowClass() {
		return 'EducationProgram\EPRevision';
	}

	/**
	 * @see ORMTable::getFields()
	 * @since 0.1
	 * @return array
	 */
	public function getFields() {
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
	 * Create a new revision object for the provided RevisionedObject.
	 * The RevisionedObject should have all it's fields loaded.
	 *
	 * @since 0.1
	 *
	 * @param RevisionedObject $object
	 * @param RevisionAction $revAction
	 *
	 * @return EPRevision
	 */
	public function newFromObject( RevisionedObject $object, RevisionAction $revAction ) {
		$fields = array_merge( $object->getRevisionIdentifiers(), array(
			'user_id' => $revAction->getUser()->getID(),
			'user_text' => $revAction->getUser()->getName(),
			'comment' => $revAction->getComment(),
			'minor_edit' => $revAction->isMinor(),
			'time' => $revAction->getTime(),
			'deleted' => $revAction->isDelete(),
			'data' => $object->toArray()
		) );

		$identifier = $object->getIdentifier();

		if ( !is_null( $identifier ) ) {
			$fields['object_identifier'] = $identifier;
		}

		return new EPRevision( $this, $fields );
	}

	/**
	 * Returns the most recent revision matching the provided conditions.
	 *
	 * @since 0.1
	 *
	 * @param array $conds
	 *
	 * @return EPRevision|bool false
	 */
	public function getLatestRevision( array $conds ) {
		return $this->selectRow(
			null,
			$conds,
			array(
				'ORDER BY' => $this->getPrefixedField( 'id' ) . ' DESC',
			)
		);
	}

}
