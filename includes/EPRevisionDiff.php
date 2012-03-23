<?php

/**
 * Utility for visualizing diffs between two revisions.
 *
 * @since 0.1
 *
 * @file EPRevisionDiff.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v3 or later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EPRevisionDiff extends ContextSource {

	protected $context;

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

	public function display() {
		$out = $this->getOutput();

		$out->addHTML( '<table class="wikitable sortable"><tr>' );

		$out->addElement( 'th', array(), '' );
		$out->addElement( 'th', array(), $this->msg( 'ep-diff-old' )->plain() );
		$out->addElement( 'th', array(), $this->msg( 'ep-diff-new' )->plain() );

		$out->addHTML( '</tr>' );

		foreach ( $this->changedFields as $field => $values ) {
			$out->addHtml( '<tr>' );

			$source = array_key_exists( 'source', $values ) ? $values['source'] : '';
			$target = array_key_exists( 'target', $values ) ? $values['target'] : '';

			$out->addElement( 'th', array(), $field );
			$out->addElement( 'td', array(), $source );
			$out->addElement( 'td', array(), $target );

			$out->addHtml( '</tr>' );
		}

		$out->addHTML( '</table>' );
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