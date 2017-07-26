<?php

namespace EducationProgram;

/**
 * Repserents a diff between two revisions.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class RevisionDiff {

	protected $changedFields = [];

	protected $isValid = true;

	/**
	 * Creates a diff between an old revision and the current one, for use
	 * with the epcompare action.
	 *
	 * @since 0.5
	 *
	 * @param RevisionedObject $targetObject
	 * @param EPRevision $revision
	 * @param array|null $fields
	 *
	 * @return array
	 */
	public static function newFromCompareRevision(
		RevisionedObject $targetObject,
		EPRevision $revision,
		array $fields = null
	) {
		$sourceObject = $revision->getObject();
		$fields = is_null( $fields ) ? $sourceObject->getFieldNames() : $fields;

		$changedFields = self::compareFields(
			$sourceObject,
			$targetObject,
			$fields
		);

		return new self( $changedFields );
	}

	public static function newFromRestoreRevision(
		RevisionedObject $sourceObject,
		EPRevision $revision,
		array $fields = null
	) {
		$targetObject = $revision->getObject();
		$fields = is_null( $fields ) ? $targetObject->getFieldNames() : $fields;

		$changedFields = self::compareFields(
			$sourceObject,
			$targetObject,
			$fields );

		return new self( $changedFields );
	}

	public static function newFromUndoRevision(
		RevisionedObject $currentObject,
		EPRevision $revision,
		array $fields = null
	) {
		$changedFields = [];

		$targetObject = $revision->getPreviousRevision()->getObject();

		if ( $targetObject !== false ) {
			$sourceObject = $revision->getObject();

			$fields = is_null( $fields ) ? $sourceObject->getFieldNames() : $fields;

			foreach ( $fields as $fieldName ) {
				$sourceHasField = $sourceObject->hasField( $fieldName );
				$targetHasField = $targetObject->hasField( $fieldName );

				if ( $currentObject->getField( $fieldName ) ===
					$sourceObject->getField( $fieldName )
					&&
					( ( $sourceHasField xor $targetHasField )
						||
						$sourceObject->getField( $fieldName ) !==
						$targetObject->getField( $fieldName )
					)
				) {
					$changedFields[$fieldName] = [];

					if ( $sourceHasField ) {
						$changedFields[$fieldName]['source'] =
							$sourceObject->getField( $fieldName );
					}

					if ( $targetHasField ) {
						$changedFields[$fieldName]['target'] =
							$targetObject->getField( $fieldName );
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

	/**
	 * For two revisioned objects, return an associative array with the names
	 * of the changed fields as keys, and as values, an associative array
	 * with the source and target values.
	 *
	 * @param RevisionedObject $sourceObject
	 * @param RevisionedObject $targetObject
	 * @param array $fields
	 * @return array
	 */
	protected static function compareFields(
		RevisionedObject $sourceObject,
		RevisionedObject $targetObject,
		$fields
	) {
		$changedFields = [];

		foreach ( $fields as $fieldName ) {
			$sourceHasField = $sourceObject->hasField( $fieldName );
			$targetHasField = $targetObject->hasField( $fieldName );

			if ( ( $sourceHasField xor $targetHasField )
				|| $sourceObject->getField( $fieldName ) !== $targetObject->getField( $fieldName )
			) {
				$changedFields[$fieldName] = [];

				if ( $sourceHasField ) {
					$changedFields[$fieldName]['source'] =
						$sourceObject->getField( $fieldName );
				}

				if ( $targetHasField ) {
					$changedFields[$fieldName]['target'] =
						$targetObject->getField( $fieldName );
				}
			}
		}

		return $changedFields;
	}

}
