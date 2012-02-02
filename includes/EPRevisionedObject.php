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
	 * @param bool $isDelete
	 *
	 * @return boolean Success indicator
	 */
	protected function storeRevision( EPRevisionedObject $revision, $isDelete = false ) {
		if ( $this->storeRevisions ) {
			$revison->setStoreRevisions( false );
			return $revison->writeToDB();
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
		
		return EPRevision::newFromObject( $revison, $isDelete );;
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
	 * @see EPDBObject::updateInDB()
	 */
	protected function updateInDB() {
		$success = parent::updateInDB();

		if ( $success && !$this->inSummaryMode ) {
			$revision = $this->getCurrentRevision();
			
			if ( $this->fieldsChanged( $revision, true ) ) {
				$this->storeRevision( $revision );
				$this->log( 'update' );
			}
		}

		return $success;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see EPDBObject::insertIntoDB()
	 */
	protected function insertIntoDB() {
		$result = parent::insertIntoDB();

		if ( $result ) {
			$this->storeRevision( $this );
			$this->log( 'add' );
		}

		return $result;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see EPDBObject::removeFromDB()
	 */
	public function removeFromDB() {
		$success = parent::removeFromDB();

		if ( $success ) {
			$this->log( 'remove' );
		}

		return $success;
	}
	
}
