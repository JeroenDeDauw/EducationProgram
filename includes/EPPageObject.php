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

	public function getIdentifier() {
		return $this->getField( $this->table->getIdentifierField() );
	}

	/**
	 *
	 *
	 * @since 0.1
	 *
	 * @return Title
	 */
	public function getTitle() {
		return $this->table->getTitleFor( $this->getIdentifier() );
	}
	
	/**
	 * (non-PHPdoc)
	 * @see DBDataObject::save()
	 */
	public function save() {
		if ( $this->hasField( $this->table->getIdentifierField() ) && is_null( $this->getTitle() ) ) {
			throw new MWException( 'The title for a EPPageObject needs to be valid when saving.' );
			return false;
		}
		
		return parent::save();
	}
	
	public function getLink( $action = 'view', $html = null, $customAttribs = array(), $query = array() ) {
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
