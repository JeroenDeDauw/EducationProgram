<?php

/**
 * Repserents a diff between two revisions.
 *
 * @since 0.1
 *
 * @file EPRevisionDiff.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EPRevisionDiff {

	protected $changedFields = array();

	protected $isValid = true;

	public static function newFromRestoreRevision( EPRevisionedObject $sourceObject, EPRevision $revison, array $fields = null ) {
		$changedFields = array();

		$targetObject = $revison->getObject();
		$fields = is_null( $fields ) ? $targetObject->getFieldNames() : $fields;

		foreach ( $fields as $fieldName ) {
			$sourceHasField = $sourceObject->hasField( $fieldName );
			$targetHasField = $targetObject->hasField( $fieldName );

			if ( ( $sourceHasField XOR $targetHasField )
				|| $sourceObject->getField( $fieldName, null ) !== $targetObject->getField( $fieldName, null ) ) {

				$changedFields[$fieldName] = array();

				if ( $sourceHasField ) {
					$changedFields[$fieldName]['source'] = $sourceObject->getField( $fieldName );
				}

				if ( $targetHasField ) {
					$changedFields[$fieldName]['target'] = $targetObject->getField( $fieldName );
				}
			}
		}

		return new self( $changedFields );
	}

	public static function newFromUndoRevision( EPRevisionedObject $currentObject, EPRevision $revison, array $fields = null ) {
		$changedFields = array();

		$targetObject = $revison->getPreviousRevision()->getObject();

		if ( $targetObject !== false ) {
			$sourceObject = $revison->getObject();

			$fields = is_null( $fields ) ? $sourceObject->getFieldNames() : $fields;

			foreach ( $fields as $fieldName ) {
				$sourceHasField = $sourceObject->hasField( $fieldName );
				$targetHasField = $targetObject->hasField( $fieldName );

				if ( $currentObject->getField( $fieldName, null ) === $sourceObject->getField( $fieldName, null )
					&&
					( 	( $sourceHasField XOR $targetHasField )
						||
						$sourceObject->getField( $fieldName, null ) !== $targetObject->getField( $fieldName, null )
					) ) {

					$changedFields[$fieldName] = array();

					if ( $sourceHasField ) {
						$changedFields[$fieldName]['source'] = $sourceObject->getField( $fieldName );
					}

					if ( $targetHasField ) {
						$changedFields[$fieldName]['target'] = $targetObject->getField( $fieldName );
					}
				}
			}
		}

		$diff = new self( $changedFields );

		$diff->setIsValid( $targetObject !== false );

		return $diff;
	}

	public function __construct( array $changedFields ) {
		$this->changedFields = $changedFields;
	}

	/**
	 * @return array
	 */
	public function getChangedFields() {
		return $this->changedFields;
	}

	public function isValid() {
		return $this->isValid;
	}

	public function setIsValid( $isValid ) {
		$this->isValid = $isValid;
	}

	public function hasChanges() {
		return !empty( $this->changedFields );
	}


}