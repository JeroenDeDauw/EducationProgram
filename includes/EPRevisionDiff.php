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

	public static function newFromUndoRevision( EPRevisionedObject $currentObject, EPRevision $revison, array $fields = null ) {
		$changedFields = array();

		$targetObject = $revison->getPreviousRevision()->getObject();

		if ( $targetObject !== false ) {
			$sourceObject = $revison->getObject();

			$fields = is_null( $fields ) ? $sourceObject->getFieldNames() : $fields;

			foreach ( $fields as $fieldName ) {
				if ( $currentObject->getField( $fieldName ) === $sourceObject->getField( $fieldName )
					&& $sourceObject->getField( $fieldName ) !== $targetObject->getField( $fieldName ) ) {

					$changedFields[$fieldName] = array(
						$sourceObject->getField( $fieldName ),
						$targetObject->getField( $fieldName )
					);
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
			list( $old, $new ) = $values;

			$out->addHtml( '<tr>' );

			$out->addElement( 'th', array(), $field );
			$out->addElement( 'td', array(), $old );
			$out->addElement( 'td', array(), $new );

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