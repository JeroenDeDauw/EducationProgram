<?php

/**
 * Abstract base class for DBDataObjects with revision history and logging support.
 *
 * @since 0.1
 *
 * @file EPRevisionedObject.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v3 or later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
abstract class EPRevisionedObject extends DBDataObject {
	
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
	 * @var EPRevisionAction|false
	 */
	protected $revAction = false;

	/**
	 * Sets the revision action.
	 *
	 * @since 0.1
	 *
	 * @param EPRevisionAction|false $revAction
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
	 * @return array|false
	 */
	protected function getLogInfo( $subType ) {
		return false;
	}
	
	/**
	 * Store the current version of the object in the revisions table.
	 *
	 * @since 0.1
	 *
	 * @param EPRevisionedObject $object
	 *
	 * @return boolean Success indicator
	 */
	protected function storeRevision( EPRevisionedObject $object ) {
		if ( $this->storeRevisions && $this->revAction !== false ) {
			return EPRevisions::singleton()->newFromObject( $object, $this->revAction )->save();
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
				EPUtils::log( $info );
			}
		}
	}
	
	/**
	 * (non-PHPdoc)
	 * @see DBDataObject::saveExisting()
	 */
	protected function saveExisting() {
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
			$success = parent::saveExisting();

			if ( $success && !$this->inSummaryMode ) {
				$this->onUpdated( $originalObject );
			}
		}

		return $success;
	}

	/**
	 * Gets called after an existing object was updated in the database.
	 * Unless the class is in summary mode @see $this->inSummaryMode
	 *
	 * @since 0.1
	 *
	 * @param EPRevisionedObject $originalObject
	 */
	protected function onUpdated( EPRevisionedObject $originalObject ) {
		$this->storeRevision( $this );
		$this->log( 'update' );
	}
	
	/**
	 * (non-PHPdoc)
	 * @see DBDataObject::insert()
	 */
	protected function insert() {
		$result = parent::insert();

		if ( $result ) {
			$this->storeRevision( $this );
			$this->log( 'add' );
		}

		return $result;
	}
	
	/**
	 * Do logging and revision storage after a removal.
	 * @see DBDataObject::onRemoved()
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
	 * @param EPRevisionAction $revAction
	 * 
	 * @return boolean Success indicator
	 */
	public function revisionedSave( EPRevisionAction $revAction ) {
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
	 * @param EPRevisionAction $revAction
	 * 
	 * @return boolean Success indicator
	 */
	public function revisionedRemove( EPRevisionAction $revAction ) {
		$this->setRevisionAction( $revAction );
		$success = $this->remove();
		$this->setRevisionAction( false );
		return $success;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see DBDataObject::getBeforeRemoveFields()
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
	 * @return EPRevision|false
	 */
	public function getRevisionById( $id ) {
		$objects = $this->getRevisions( 
			array( 'id' => $id ),
			array( 'LIMIT' => 1 )
		);

		return count( $objects ) > 0 ? $objects[0] : false;
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
	 * @return array of EPRevision
	 */
	public function getRevisions( array $conditions = array(), array $options = array() ) {
		return EPRevisions::singleton()->select( null, array_merge(
			array(
				'type' => get_called_class(),
				'object_id' => $this->getId(),
			),
			$conditions
		), $options );
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
	 * @return EPRevision|false
	 */
	public function getLatestRevision( array $conditions = array(), array $options = array() ) {
		$options['ORDER BY'] = EPRevisions::singleton()->getPrefixedField( 'id' ) . ' DESC';

		return EPRevisions::singleton()->selectRow( null, array_merge(
			array(
				'type' => get_called_class(),
				'object_id' => $this->getId(),
			),
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
	 * @param EPRevisionAction $revAction
	 * 
	 * @return boolean Success indicator
	 */
	public function undelete( EPRevisionAction $revAction ) {
		$this->setRevisionAction( $revAction );
		
		$result = parent::insert();

		if ( $result ) {
			$this->log( 'undelete' );
		}
		
		$this->setRevisionAction( false );
		
		return $result;
	}
	
	/**
	 * Restore the object to the provided revisions state.
	 * 
	 * @since 0.1
	 * 
	 * @param EPRevision $revison
	 * @param array|null $fields
	 * 
	 * @return boolean Success indicator
	 */
	public function restoreToRevision( EPRevision $revison, array $fields = null ) {
		$object = $revison->getObject();
		$fields = is_null( $fields ) ? $object->getFieldNames() : $fields;
		
		foreach ( $fields as $fieldName ) {
			$this->restoreField( $fieldName, $object->getField( $fieldName ) );
		}
		
		return true;
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
	 * @param EPRevision $revison
	 * @param array|null $fields
	 *
	 * @return boolean Success indicator
	 */
	public function undoRevision( EPRevision $revison, array $fields = null ) {
		$oldObject = $revison->getPreviousRevision()->getObject();

		if ( $oldObject === false ) {
			return false;
		}

		$newObject = $revison->getObject();

		$fields = is_null( $fields ) ? $newObject->getFieldNames() : $fields;

		foreach ( $fields as $fieldName ) {
			if ( $this->getField( $fieldName ) === $newObject->getField( $fieldName ) ) {
				$this->restoreField( $fieldName, $oldObject->getField( $fieldName ) );
			}
		}

		return true;
	}

	/**
	 * Set a field to the value of the corresponding field in the provided object.
	 *
	 * @since 0.1
	 *
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
