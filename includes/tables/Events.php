<?php

namespace EducationProgram;

/**
 * Class representing the ep_events table.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class Events extends ORMTable {

	public function __construct() {
		$this->fieldPrefix = 'event_';
	}

	/**
	 * @see ORMTable::getName()
	 * @since 0.1
	 * @return string
	 */
	public function getName() {
		return 'ep_events';
	}

	/**
	 * @see ORMTable::getRowClass()
	 * @since 0.1
	 * @return string
	 */
	public function getRowClass() {
		return 'ORMRow';
	}

	/**
	 * @see ORMTable::getFields()
	 * @since 0.1
	 * @return array
	 */
	public function getFields() {
		return array(
			'id' => 'id',

			'course_id' => 'int',
			'user_id' => 'int',
			'time' => 'str', // TS_MW
			'type' => 'str',
			'info' => 'blob',
		);
	}

}
