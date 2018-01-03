<?php

namespace EducationProgram;

/**
 * Class representing the ep_oas table.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class OAs extends ORMTable {

	public function __construct() {
		$this->fieldPrefix = 'oa_';
	}

	/**
	 * @see ORMTable::getName()
	 *
	 * @return string
	 */
	public function getName() {
		return 'ep_oas';
	}

	/**
	 * @see ORMTable::getRowClass()
	 *
	 * @return string
	 */
	public function getRowClass() {
		return 'EducationProgram\OA';
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
