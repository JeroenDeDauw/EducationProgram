<?php

/**
 * Abstract base class DBDataObjects that have associated page views.
 *
 * @since 0.1
 *
 * @file EPPageTable.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v3 or later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
abstract class EPPageTable extends DBTable {

	/**
	 * Returns the field use to identify this object, ie the part used as page title.
	 * 
	 * @since 0.1
	 * 
	 * @return string
	 */
	public abstract function getIdentifierField();
	
	/**
	 * Returns the namespace in which objects of this type reside.
	 * 
	 * @since 0.1
	 * 
	 * @return integer
	 */
	public abstract function getNamespace();
	
	/**
	 * Returns the name of the fields that can be changed
	 * when doing a revert or restoring to a previous revision.
	 * 
	 * @since 0.1
	 * 
	 * @return array
	 */
	public abstract function getRevertableFields();
	
	/**
	 * Returns the right needed to edit items in this table.
	 * 
	 * @since 0.1
	 * 
	 * @return string
	 */
	public abstract function getEditRight();
	
	public function hasIdentifier( $identifier ) {
		return $this->has( array( $this->getIdentifierField() => $identifier ) );
	}

	public function get( $identifier, $fields = null ) {
		return $this->selectRow( $fields, array( $this->getIdentifierField() => $identifier ) );
	}

	/**
	 * Delete all objects matching the provided condirions.
	 *
	 * @since 0.1
	 *
	 * @param EPRevisionAction $revAction
	 * @param array $conditions
	 *
	 * @return boolean
	 */
	public function deleteAndLog( EPRevisionAction $revAction, array $conditions ) {
		$objects = $this->select(
			null,
			$conditions
		);

		$success = true;

		if ( !empty( $objects ) ) {
			$revAction->setDelete( true );

			foreach ( $objects as /* EPPageObject */ $object ) {
				$success = $object->revisionedRemove( $revAction ) && $success;
			}
		}

		return $success;
	}

	/**
	 * Construct a new title for an object of this type with the provided identifier value.
	 * 
	 * @since 0.1
	 * 
	 * @param string $identifierValue
	 * 
	 * @return Title
	 */
	public function getTitleFor( $identifierValue ) {
		return Title::newFromText(
			$identifierValue,
			$this->getNamespace()
		);
	}

	/**
	 * Returns a link to the page representing the object of this type with the provided identifier value.
	 * 
	 * @since 0.1
	 * 
	 * @param string $identifierValue
	 * @param string $action
	 * @param string $html
	 * @param array $customAttribs
	 * @param array $query
	 * 
	 * @return string
	 */
	public function getLinkFor( $identifierValue, $action = 'view', $html = null, array $customAttribs = array(), array $query = array() ) {
		if ( $action !== 'view' ) {
			$query['action'] = $action;
		}

		// Linker has no hook that allows us to figure out if the page actually exists :(
		// FIXME: now it does
		return Linker::linkKnown( 
			$this->getTitleFor( $identifierValue, $action ),
			is_null( $html ) ? htmlspecialchars( $identifierValue ) : $html,
			$customAttribs,
			$query
		);
	}

}
