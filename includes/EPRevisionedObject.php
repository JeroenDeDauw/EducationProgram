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
			return EPRevision::newFromObject( $object, $this->revAction )->save();
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
			static::setReadDb( DB_MASTER );
			$originalObject = static::selectRow( null, array( 'id' => $this->getId() ) );
			static::setReadDb( DB_SLAVE );

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
		$this->storeRevision( $originalObject );
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
	
}