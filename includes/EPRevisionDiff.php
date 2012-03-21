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

	public static function newFromUndoRevision( IContextSource $context, EPRevision $revison, array $fields = null ) {
		$changedFields = array();

		$oldObject = $revison->getPreviousRevision()->getObject();

		if ( $oldObject !== false ) {
			$newObject = $revison->getObject();

			$fields = is_null( $fields ) ? $newObject->getFieldNames() : $fields;

			foreach ( $fields as $fieldName ) {
				if ( $this->getField( $fieldName ) === $newObject->getField( $fieldName ) ) {
					$changedFields[$fieldName] = array(  );
					$this->restoreField( $fieldName, $oldObject->getField( $fieldName ) );
				}
			}
		}

		return new self( $context, $changedFields );
	}

	public function __construct( IContextSource $context, array $changedFields ) {
		$this->setContext( $context );
		$this->changedFields = $changedFields;
	}

	public function display() {
		$out = $this->getOutput();

		$out->addHTML( '<table><tr>' );

		$out->addElement( 'th', array(), '' );
		$out->addElement( 'th', array(), $this->msg()->plain( 'ep-diff-old' ) );
		$out->addElement( 'th', array(), $this->msg()->plain( 'ep-diff-new' ) );

		$out->addHTML( '</tr>' );

		foreach ( $this->changedFields as $field => $values ) {
			list( $old, $new ) = $values;

			$out->addHtml( '<tr>' );

			$out->addElement( '<th>', array(), $field );
			$out->addElement( '<td>', array(), $old );
			$out->addElement( '<td>', array(), $new );

			$out->addHtml( '</tr>' );
		}
	}


}