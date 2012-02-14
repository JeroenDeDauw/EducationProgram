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

	public function getTitle() {
		return $this->table->getTitleFor( $this->getIdentifier() );
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
		return $this->table->getLogInfoForTitle( $this->getTitle() );
	}

}
