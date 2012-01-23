<?php

/**
 * Base class for history special pages.
 *
 * @since 0.1
 *
 * @file SpecialEPHistory.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v3 or later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
abstract class SpecialEPHistory extends SpecialEPPage {

	protected function displayRevisions( EPDBObject $object ) {
		$conditions = array(
			'type' => get_class( $object ),
		);

		if ( $object->hasIdField() ) {
			$conditions['object_id'] = $object->getId();
		}

		$revisions = EPRevision::select(
			null,
			$conditions
		);

		if ( count( $revisions ) > 0 ) {
			array_unshift( $revisions, EPRevision::newFromObject( $object ) );
			$this->displayRevisionList( $revisions );
		}
		else {
			// TODO
		}
	}

	protected function displayRevisionList( array /* of EPRevision */ $revisions ) {
		foreach ( $revisions as &$revision ) {
			$revision = '<li>' . $this->getRevisionItem( $revision ) . '</li>';
		}

		$this->getOutput()->addHTML( '<ul>' . implode( '', $revisions ) . '</ul>' );
	}

	protected function getRevisionItem( EPRevision $revision ) {
		return $revision->getField( 'time' ) . json_encode( $revision->getField( 'data' ) ); // TODO
	}

}
