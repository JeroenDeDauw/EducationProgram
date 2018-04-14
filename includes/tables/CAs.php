<?php

namespace EducationProgram;

/**
 * Class representing the ep_cas table.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 *
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class CAs extends ORMTable {

	public function __construct() {
		$this->fieldPrefix = 'ca_';
	}

	/**
	 * @see ORMTable::getName()
	 *
	 * @return string
	 */
	public function getName() {
		return 'ep_cas';
	}

	/**
	 * @see ORMTable::getRowClass()
	 *
	 * @return string
	 */
	public function getRowClass() {
		return 'EducationProgram\CA';
	}

	/**
	 * @see ORMTable::getFields()
	 *
	 * @return array
	 */
	public function getFields() {
		return [
			'id' => 'id',

			'user_id' => 'int',

			'visible' => 'bool',
			'bio' => 'str',
			'photo' => 'str',
		];
	}

	/**
	 * @see ORMTable::getDefaults()
	 *
	 * @return array
	 */
	public function getDefaults() {
		return [
			'bio' => '',
			'photo' => '',
			'visible' => true,
		];
	}

}
