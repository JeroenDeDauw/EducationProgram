<?php

namespace EducationProgram;

/**
 * Class representing the ep_students table.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 *
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class Students extends ORMTable {

	public function __construct() {
		$this->fieldPrefix = 'student_';
	}

	/**
	 * @see ORMTable::getName()
	 *
	 * @return string
	 */
	public function getName() {
		return 'ep_students';
	}

	/**
	 * @see ORMTable::getRowClass()
	 *
	 * @return string
	 */
	public function getRowClass() {
		return 'EducationProgram\Student';
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
			'first_enroll' => 'str', // TS_MW
			'first_course' => 'int',
			'last_enroll' => 'str', // TS_MW
			'last_course' => 'int',
			'last_active' => 'str', // TS_MW
			'active_enroll' => 'bool',
		];
	}

	/**
	 * @see ORMTable::getSummaryFields()
	 *
	 * @return array
	 */
	public function getSummaryFields() {
		return [
			'last_active',
			'active_enroll',
		];
	}

}
