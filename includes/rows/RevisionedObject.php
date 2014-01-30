<?php

namespace EducationProgram;
use IORMRow;
use ORMResult;

/**
 * Abstract base class for ORMRows with revision history and logging support.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
abstract class RevisionedObject extends \ORMRow {
	/**
	 * If the object should log changes.
	 * Can be changed via disableLogging and enableLogging.
	 *
	 * @since 0.1
	 * @var bool
	 */
	protected $log = true;

	/**
	 * If the object should store old revisions.
	 *
	 * @since 0.1
	 * @var bool
	 */
	protected $storeRevisions = true;

	/**
	 *
	 * @since 0.1
	 * @var RevisionAction|bool false
	 */
	protected $revAction = false;

	/**
	 * Sets the revision action.
	 *
	 * @since 0.1
	 *
	 * @param RevisionAction|bool $revAction false
	 */
	protected function setRevisionAction( $revAction ) {
		$this->revAction = $revAction;
	}

	/**
	 * Sets the value for the @see $storeRevisions field.
	 *
	 * @since 0.1
	 *
	 * @param boolean $store
	 */
	public function setStoreRevisions( $store ) {
		$this->storeRevisions = $store;
	}

	/**
	 * Sets the value for the @see $log field.
	 *
	 * @since 0.1
	 */
	public function enableLogging() {
		$this->log = true;
	}

	/**
	 * Sets the value for the @see $log field.
	 *
	 * @since 0.1
	 */
	public function disableLogging() {
		$this->log = false;
	}

	/**
	 * Returns the info for the log entry or false if no entry should be created.
	 *
	 * @since 0.1
	 *
	 * @param string $subType
	 *
	 * @return array|bool false
	 */
	protected function getLogInfo( $subType ) {
		return false;
	}

	/**
	 * Store the current version of the object in the revisions table.
	 *
	 * @since 0.1
	 *
	 * @param RevisionedObject $object
	 *
	 * @return boolean Success indicator
	 */
	protected function storeRevision( RevisionedObject $object ) {
		if ( $this->storeRevisions && $this->revAction !== false ) {
			return Revisions::singleton()->newFromObject( $object, $this->revAction )->save();
		}

		return true;
	}

	/**
	 * Log an action.
	 *
	 * @since 0.1
	 *
	 * @param string $subType
	 */
	protected function log( $subType ) {
		if ( $this->log ) {
			$info = $this->getLogInfo( $subType );

			if ( $info !== false ) {
				if ( $this->revAction !== false ) {
					$info['user'] = $this->revAction->getUser();
					$info['comment'] = $this->revAction->getComment();
				}

				$info['subtype'] = $subType;
				Utils::log( $info );
			}
		}
	}

	public function save( $functionName = null ) {
		if ( $this->hasIdField() ) {
			return $this->saveExisting( $functionName );
		} else {
			return $this->insert( $functionName );
		}
	}

	/**
	 * @see ORMRow::saveExisting()
	 */
	protected function saveExisting( $functionName = null ) {
		if ( !$this->inSummaryMode ) {
			$this->table->setReadDb( DB_MASTER );
			$originalObject = $this->table->selectRow( null, array( 'id' => $this->getId() ) );
			$this->table->setReadDb( DB_SLAVE );

			if ( $originalObject === false ) {
				return false;
			}
		}

		$success = true;

		if ( $this->inSummaryMode || $this->fieldsChanged( $originalObject, true ) ) {
			$success = parent::saveExisting( $functionName );

			if ( $success && !$this->inSummaryMode ) {
				$this->onUpdated( $originalObject );
			}
		}

		return $success;
	}

	/**
	 * Return if any fields got changed.
	 *
	 * @since 1.20
	 *
	 * @param IORMRow $object
	 * @param boolean|array $excludeSummaryFields
	 *  When set to true, summary field changes are ignored.
	 *  Can also be an array of fields to ignore.
	 *
	 * @return boolean
	 */
	protected function fieldsChanged( IORMRow $object, $excludeSummaryFields = false ) {
		$exclusionFields = array();

		if ( $excludeSummaryFields !== false ) {
			$exclusionFields = is_array( $excludeSummaryFields ) ? $excludeSummaryFields : $this->table->getSummaryFields();
		}

		foreach ( $this->fields as $name => $value ) {
			$excluded = $excludeSummaryFields && in_array( $name, $exclusionFields );

			if ( !$excluded && $object->getField( $name ) !== $value ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Gets called after an existing object was updated in the database.
	 * Unless the class is in summary mode @see $this->inSummaryMode
	 *
	 * @since 0.1
	 *
	 * @param RevisionedObject $originalObject
	 */
	protected function onUpdated( RevisionedObject $originalObject ) {
		$this->storeRevision( $this );
		$this->log( 'update' );
	}

	/**
	 * Gets called after an object was undeleted. Implementing empty here
	 * so that subclasses can override or not as needed.
	 *
	 * @since 0.4 alpha
	 */
	protected function onUndeleted() { }

	/**
	 * @see ORMRow::insert()
	 */
	protected function insert( $functionName = null, array $options = null ) {
		$result = parent::insert( $functionName, $options );

		if ( $result ) {
			$this->storeRevision( $this );
			$this->log( 'add' );
		}

		return $result;
	}

	/**
	 * Do logging and revision storage after a removal.
	 * @see ORMRow::onRemoved()
	 *
	 * @since 0.1
	 */
	protected function onRemoved() {
		$this->storeRevision( $this );
		$this->log( 'remove' );
		parent::onRemoved();
	}

	public function getIdentifier() {
		return null;
	}

	/**
	 * Save the object using the provided revision action info for logging and revision storage.
	 * PHP does not support method overloading, else this would be just "save" :/
	 *
	 * @since 0.1
	 *
	 * @param RevisionAction $revAction
	 *
	 * @return boolean Success indicator
	 */
	public function revisionedSave( RevisionAction $revAction ) {
		$this->setRevisionAction( $revAction );
		$success = $this->save();
		$this->setRevisionAction( false );
		return $success;
	}

	/**
	 * Remove the object using the provided revision action info for logging and revision storage.
	 * PHP does not support method overloading, else this would be just "remove" :/
	 *
	 * @since 0.1
	 *
	 * @param RevisionAction $revAction
	 *
	 * @return boolean Success indicator
	 */
	public function revisionedRemove( RevisionAction $revAction ) {
		$this->setRevisionAction( $revAction );
		$success = $this->remove();
		$this->setRevisionAction( false );
		return $success;
	}

	/**
	 * @see ORMRow::getBeforeRemoveFields()
	 */
	protected function getBeforeRemoveFields() {
		return null;
	}

	/**
	 * Get the revision with the provided id for this object.
	 * Returns false if there is no revision with this id for this object.
	 *
	 * @since 0.1
	 *
	 * @param integer $id
	 *
	 * @return EPRevision|bool false
	 */
	public function getRevisionById( $id ) {
		$objects = $this->getRevisions(
			array( 'id' => $id ),
			array( 'LIMIT' => 1 )
		);

		return $objects->isEmpty() ? false : $objects->current();
	}

	/**
	 * Returns the revisions of the object matching the provided conditions.
	 * If you set the type or object_id fields, other revisions might be returned as well.
	 *
	 * @since 0.1
	 *
	 * @param array $conditions
	 * @param array $options
	 *
	 * @return ORMResult
	 */
	public function getRevisions( array $conditions = array(), array $options = array() ) {
		return Revisions::singleton()->select( null, array_merge(
			$this->getRevisionIdentifiers(),
			$conditions
		), $options );
	}

	/**
	 * @since 0.3
	 *
	 * @return string
	 */
	protected abstract function getTypeId();

	public function getRevisionIdentifiers() {
		$identifiers = array(
			'type' => $this->getTypeId()
		);

		if ( $this->hasIdField() ) {
			$identifiers['object_id'] = $this->getId();
		}

		return $identifiers;
	}

	/**
	 * Returns the most recently stored revision for this object
	 * matching the provided contions or false if there is none.
	 *
	 * @since 0.1
	 *
	 * @param array $conditions
	 * @param array $options
	 *
	 * @return EPRevision|bool false
	 */
	public function getLatestRevision( array $conditions = array(), array $options = array() ) {
		$options['ORDER BY'] = Revisions::singleton()->getPrefixedField( 'id' ) . ' DESC';

		return Revisions::singleton()->selectRow( null, array_merge(
			$this->getRevisionIdentifiers(),
			$conditions
		), $options );
	}

	/**
	 * Undeletes ab object by inserting the current object.
	 * Only call this method when the object does not exist in
	 * it's database table and has the current version in the revision table.
	 *
	 * @since 0.1
	 *
	 * @param RevisionAction $revAction
	 *
	 * @return boolean Success indicator
	 */
	public function undelete( RevisionAction $revAction ) {
		$this->setRevisionAction( $revAction );

		$result = parent::insert();

		if ( $result ) {
			$this->log( 'undelete' );
		}

		$this->setRevisionAction( false );

		$this->onUndeleted();

		return $result;
	}

	/**
	 * Restore the object to the provided revisions state.
	 *
	 * @since 0.1
	 *
	 * @param EPRevision $revision
	 * @param array|null $fields
	 *
	 * @return boolean Success indicator
	 */
	public function restoreToRevision( EPRevision $revision, array $fields = null ) {
		$diff = $this->getRestoreDiff( $revision, $fields );
		$this->applyDiff( $diff );
		return $diff->isValid();
	}

	/**
	 * Get a diff for the changes that will happen when retoring to the provided revision.
	 *
	 * @since 0.1
	 *
	 * @param EPRevision $revision
	 * @param array|null $fields
	 *
	 * @return RevisionDiff
	 */
	public function getRestoreDiff( EPRevision $revision, array $fields = null ) {
		$fields = is_null( $fields ) ? $this->table->getRevertibleFields() : $fields;
		return RevisionDiff::newFromRestoreRevision( $this, $revision, $fields );
	}

	/**
	 * Undo the changes of a single revision to this object.
	 * Changes are compared on field level. If a field is no
	 * longer the same as in the revision being undone, it
	 * will not be reverted.
	 *
	 * At some point we might want to have more fine grained
	 * reverts for text fields.
	 *
	 * @since 0.1
	 *
	 * @param EPRevision $revision
	 * @param array|null $fields
	 *
	 * @return boolean Success indicator
	 */
	public function undoRevision( EPRevision $revision, array $fields = null ) {
		$diff = $this->getUndoDiff( $revision, $fields );
		$this->applyDiff( $diff );
		return $diff->isValid();
	}

	public function applyDiff( RevisionDiff $diff ) {
		foreach ( $diff->getChangedFields() as $fieldName => $values ) {
			if ( array_key_exists( 'target', $values ) ) {
				$this->restoreField( $fieldName, $values['target'] );
			}
			else {
				$this->removeField( $fieldName );
			}
		}
	}

	/**
	 * Get a diff for the changes that will happen when undoing the provided revision.
	 *
	 * @since 0.1
	 *
	 * @param EPRevision $revision
	 * @param array|null $fields
	 *
	 * @return RevisionDiff
	 */
	public function getUndoDiff( EPRevision $revision, array $fields = null ) {
		$fields = is_null( $fields ) ? $this->table->getRevertibleFields() : $fields;
		return RevisionDiff::newFromUndoRevision( $this, $revision, $fields );
	}

	/**
	 * Set a field to the value of the corresponding field in the provided object.
	 *
	 *
	 * @since 0.1
	 * @param string $fieldName
	 * @param mixed $newValue
	 */
	protected function restoreField( $fieldName, $newValue ) {
		$this->setField( $fieldName, $newValue );
	}

	/**
	 * Retore the object to a revision with the provided id.
	 *
	 * @since 0.1
	 *
	 * @param integer $revId
	 * @param array|null $fields
	 *
	 * @return boolean Success indicator
	 */
	public function restoreToRevisionId( $revId, array $fields = null ) {
		$revision = $this->getRevisionById( $revId );
		return $revision === false ? false : $this->restoreToRevision( $revision, $fields );
	}

	/**
	 * Undo the changes of the revision with the provided id to this object.
	 *
	 * @since 0.1
	 *
	 * @param integer $revId
	 * @param array|null $fields
	 *
	 * @return boolean Success indicator
	 */
	public function undoRevisionId( $revId, array $fields = null ) {
		$revision = $this->getRevisionById( $revId );
		return $revision === false ? false : $this->undoRevision( $revision, $fields );
	}
}
