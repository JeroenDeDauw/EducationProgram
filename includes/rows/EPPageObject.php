<?php

/**
 * Abstract base class for EPRevisionedObject that have associated view, edit and history pages.
 *
 * @since 0.1
 *
 * @file EPPageObject.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
abstract class EPPageObject extends EPRevisionedObject {
	/**
	 * @see ORMRow::$table
	 * @var EPPageTable
	 */
	protected $table;

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
	 * @see ORMRow::save()
	 */
	public function save( $functionName = null ) {
		if ( $this->hasField( $this->table->getIdentifierField() ) && is_null( $this->getTitle() ) ) {
			throw new MWException( 'The title for a EPPageObject needs to be valid when saving.' );
		}

		$this->setField( 'touched', wfTimestampNow() );

		return parent::save( $functionName );
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
			return array(
				'type' => EPPage::factory( $title )->getLogType(),
				'title' => $title,
			);
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see ORMRow::setField()
	 */
	public function setField( $name, $value ) {
		if ( $name === $this->table->getIdentifierField() ) {
			$value = str_replace( '_', ' ', $value );
		}

		parent::setField( $name, $value );
	}

	/**
	 * @since 0.2
	 *
	 * @return integer
	 */
	public function getTouched() {
		return $this->getField( 'touched' );
	}
}
