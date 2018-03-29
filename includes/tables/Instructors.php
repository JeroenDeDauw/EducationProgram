<?php

namespace EducationProgram;

/**
 * Class representing the ep_instructors table.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 *
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class Instructors extends ORMTable {

	public function __construct() {
		$this->fieldPrefix = 'instructor_';
	}

	/**
	 * @see ORMTable::getName()
	 *
	 * @return string
	 */
	public function getName() {
		return 'ep_instructors';
	}

	/**
	 * @see ORMTable::getRowClass()
	 *
	 * @return string
	 */
	public function getRowClass() {
		return 'EducationProgram\Instructor';
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
		];
	}

}
