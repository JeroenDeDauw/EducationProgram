<?php

namespace EducationProgram;

use Title;
use Exception;

/**
 * Abstract base class for RevisionedObject that have associated view, edit and history pages.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 *
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
abstract class PageObject extends RevisionedObject {

	/**
	 * @see ORMRow::$table
	 *
	 * @var PageTable
	 */
	protected $table;

	/**
	 * @see RevisionedObject::getIdentifier()
	 */
	public function getIdentifier() {
		return $this->getField( $this->table->getIdentifierField() );
	}

	/**
	 * Returns the title of the page representing the object.
	 *
	 * @return Title
	 */
	public function getTitle() {
		return $this->table->getTitleFor( $this->getIdentifier() );
	}

	/**
	 * Gets a link to the page representing the object.
	 *
	 * @param string $action
	 * @param string $html
	 * @param array $customAttribs
	 * @param array $query
	 *
	 * @return string
	 */
	public function getLink(
		$action = 'view', $html = null, array $customAttribs = [], array $query = []
	) {
		return $this->table->getLinkFor(
			$this->getIdentifier(),
			$action,
			$html,
			$customAttribs,
			$query
		);
	}

	/**
	 * @see ORMRow::save()
	 */
	public function save( $functionName = null ) {
		if ( $this->hasField( $this->table->getIdentifierField() ) &&
			is_null( $this->getTitle() )
		) {
			throw new Exception( 'The title for a PageObject needs to be valid when saving.' );
		}

		$this->setField( 'touched', wfTimestampNow() );

		return parent::save( $functionName );
	}

	/**
	 * @see RevisionedObject::getLogInfo()
	 */
	protected function getLogInfo( $subType ) {
		$title = $this->getTitle();

		if ( is_null( $title ) ) {
			return false;
		} else {
			return [
				'type' => EducationPage::factory( $title )->getLogType(),
				'title' => $title,
			];
		}
	}

	/**
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
	 * @return int
	 */
	public function getTouched() {
		return $this->getField( 'touched' );
	}
}
