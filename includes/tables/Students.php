<?php

namespace EducationProgram;

/**
 * Class representing the ep_students table.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class Students extends ORMTable {

	public function __construct() {
		$this->fieldPrefix = 'student_';
	}

	/**
	 * @see ORMTable::getName()
	 * @since 0.1
	 * @return string
	 */
	public function getName() {
		return 'ep_students';
	}

	/**
	 * @see ORMTable::getRowClass()
	 * @since 0.1
	 * @return string
	 */
	public function getRowClass() {
		return 'EducationProgram\Student';
	}

	/**
	 * @see ORMTable::getFields()
	 * @since 0.1
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
	 * @since 0.1
	 * @return array
	 */
	public function getSummaryFields() {
		return [
			'last_active',
			'active_enroll',
		];
	}

}
