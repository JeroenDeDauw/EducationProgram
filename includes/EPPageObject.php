<?php

/**
 * Abstract base class for EPRevisionedObject that have associated view, edit and history pages.
 *
 * @since 0.1
 *
 * @file EPPageObject.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v3 or later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
abstract class EPPageObject extends EPRevisionedObject {

	/**
	 * (non-PHPdoc)
	 * @see EPRevisionedObject::getIdentifier()
	 */
	public function getIdentifier() {
		return $this->getField( $this->table->getIdentifierField() );
	}
	
	/**
	 * Returns the title of the page representing the object.
	 *
	 * @since 0.1
	 *
	 * @return Title
	 */
	public function getTitle() {
		return $this->table->getTitleFor( $this->getIdentifier() );
	}
	
	/**
	 * Gets a link to the page representing the object.
	 * 
	 * @since 0.1
	 * 
	 * @param string $action
	 * @param string $html
	 * @param array $customAttribs
	 * @param array $query
	 * 
	 * @return string
	 */
	public function getLink( $action = 'view', $html = null, array $customAttribs = array(), array $query = array() ) {
		return $this->table->getLinkFor(
			$this->getIdentifier(),
			$action,
			$html,
			$customAttribs,
			$query
		);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see DBDataObject::save()
	 */
	public function save() {
		if ( $this->hasField( $this->getIdentifierField() ) && is_null( $this->getTitle() ) ) {
			throw new MWException( 'The title for a EPPageObject needs to be valid when saving.' );
			return false;
		}
		
		return parent::save();
	}

	/**
	 * (non-PHPdoc)
	 * @see EPRevisionedObject::getLogInfo()
	 */
	protected function getLogInfo( $subType ) {
		$title = $this->getTitle();
		
		if ( is_null( $title ) ) {
			return false;
		}
		else {
			return $this->table->getLogInfoForTitle( $this->getTitle() );
		}
	}
	
	/**
	 * (non-PHPdoc)
	 * @see DBDataObject::setField()
	 */
	public function setField( $name, $value ) {
		if ( $name === $this->table->getIdentifierField() ) {
			$value = str_replace( '_', ' ', $value );
		}
		
		parent::setField( $name, $value );
	}

}
