<?php

namespace EducationProgram;

/**
 * Class representing the ep_cas table.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class CAs extends \ORMTable {

	/**
	 * @see ORMTable::getName()
	 * @since 0.1
	 * @return string
	 */
	public function getName() {
		return 'ep_cas';
	}

	/**
	 * @see ORMTable::getFieldPrefix()
	 * @since 0.1
	 * @return string
	 */
	public function getFieldPrefix() {
		return 'ca_';
	}

	/**
	 * @see ORMTable::getRowClass()
	 * @since 0.1
	 * @return string
	 */
	public function getRowClass() {
		return 'EducationProgram\CA';
	}

	/**
	 * @see ORMTable::getFields()
	 * @since 0.1
	 * @return array
	 */
	public function getFields() {
		return array(
			'id' => 'id',

			'user_id' => 'int',

			'visible' => 'bool',
			'bio' => 'str',
			'photo' => 'str',
		);
	}

	/**
	 * @see ORMTable::getDefaults()
	 * @since 0.1
	 * @return array
	 */
	public function getDefaults() {
		return array(
			'bio' => '',
			'photo' => '',
			'visible' => true,
		);
	}

}
