<?php

/**
 * Class representing the ep_oas table.
 *
 * @since 0.1
 *
 * @file EPOAs.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EPOAs extends ORMTable {

	/**
	 * (non-PHPdoc)
	 * @see ORMTable::getName()
	 * @since 0.1
	 * @return string
	 */
	public function getName() {
		return 'ep_oas';
	}

	/**
	 * (non-PHPdoc)
	 * @see ORMTable::getFieldPrefix()
	 * @since 0.1
	 * @return string
	 */
	public function getFieldPrefix() {
		return 'oa_';
	}

	/**
	 * (non-PHPdoc)
	 * @see ORMTable::getRowClass()
	 * @since 0.1
	 * @return string
	 */
	public function getRowClass() {
		return 'EPOA';
	}

	/**
	 * (non-PHPdoc)
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
	 * (non-PHPdoc)
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
