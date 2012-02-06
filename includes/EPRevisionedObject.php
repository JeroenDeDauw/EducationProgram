<?php

/**
 * Abstract base class for EPDBObjects with revision history and logging support.
 *
 * @since 0.1
 *
 * @file EPRevisionedObject.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v3 or later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
abstract class EPRevisionedObject extends EPDBObject {
	
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
	 * TODO: add handling for comment, minor edit, ect stuff
	 *
	 * @since 0.1
	 *
	 * @param EPRevision $revision
	 * @param bool $isDelete
	 *
	 * @return boolean Success indicator
	 */
	protected function storeRevision( EPRevision $revision, $isDelete = false ) {
		if ( $this->storeRevisions ) {
			return $revision->save();
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
				$info['subtype'] = $subType;
				EPUtils::log( $info );
			}
		}
	}
	
	/**
	 * Returns the current revision of the object, ie what is in the database.
	 * 
	 * @since 0.1
	 * 
	 * @return EPRevisionedObject
	 */
	protected function getCurrentRevision() {
		static::setReadDb( DB_MASTER );
		$revison = static::selectRow( null, array( 'id' => $this->getId() ) );
		static::setReadDb( DB_SLAVE );
		
		return EPRevision::newFromObject( $revison );
	}
	
	/**
	 * Return if any fields got changed.
	 * 
	 * @since 0.1
	 * 
	 * @param EPRevisionedObject $revision
	 * @param boolean $excludeSummaryFields When set to true, summaty field changes are ignored.
	 * 
	 * @return boolean
	 */
	protected function fieldsChanged( EPRevisionedObject $revision, $excludeSummaryFields = false ) {
		foreach ( $this->fields as $name => $value ) {
			$excluded = $excludeSummaryFields && in_array( $name, $this->getSummaryFields() );
			
			if ( !$excluded && $revision->getField( $name ) !== $value ) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see EPDBObject::saveExisting()
	 */
	protected function saveExisting() {
		$success = parent::saveExisting();

		if ( $success && !$this->inSummaryMode ) {
			$revision = $this->getCurrentRevision();
			
			if ( $this->fieldsChanged( $revision->getObject(), true ) ) {
				$this->storeRevision( $revision );
				$this->log( 'update' );
			}
		}

		return $success;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see EPDBObject::insert()
	 */
	protected function insert() {
		$result = parent::insert();

		if ( $result ) {
			$this->storeRevision( $this->getCurrentRevision() );
			$this->log( 'add' );
		}

		return $result;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see EPDBObject::remove()
	 */
	public function remove() {
		$success = parent::remove();

		if ( $success ) {
			$this->log( 'remove' );
		}

		return $success;
	}
	
}
