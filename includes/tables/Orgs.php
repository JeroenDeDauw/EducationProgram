<?php

namespace EducationProgram;

/**
 * Class representing the ep_orgs table.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class Orgs extends PageTable {

	/**
	 * @see ORMTable::getName()
	 * @since 0.1
	 * @return string
	 */
	public function getName() {
		return 'ep_orgs';
	}

	/**
	 * @see ORMTable::getFieldPrefix()
	 * @since 0.1
	 * @return string
	 */
	public function getFieldPrefix() {
		return 'org_';
	}

	/**
	 * @see ORMTable::getRowClass()
	 * @since 0.1
	 * @return string
	 */
	public function getRowClass() {
		return 'EducationProgram\Org';
	}

	/**
	 * @see ORMTable::getFields()
	 * @since 0.1
	 * @return array
	 */
	public function getFields() {
		return array(
			'id' => 'id',

			'name' => 'str',
			'city' => 'str',
			'country' => 'str',

			'active' => 'bool',
			'course_count' => 'int',
			'student_count' => 'int',
			'instructor_count' => 'int',
			'ca_count' => 'int',
			'oa_count' => 'int',
			'courses' => 'array',

			'touched' => 'str', // TS_MW
		);
	}

	/**
	 * @see ORMTable::getDefaults()
	 * @since 0.1
	 * @return array
	 */
	public function getDefaults() {
		return array(
			'name' => '',
			'city' => '',
			'country' => '',

			'active' => false,
			'course_count' => 0,
			'student_count' => 0,
			'instructor_count' => 0,
			'ca_count' => 0,
			'oa_count' => 0,
			'courses' => array(),
		);
	}

	/**
	 * @see PageTable::getRevertibleFields()
	 */
	public function getRevertibleFields() {
		return array_diff(
			array_keys( $this->getFields() ),
			array_merge( array( 'id', $this->getSummaryFields() ) )
		);
	}

	/**
	 * @see ORMTable::getSummaryFields()
	 * @since 0.1
	 * @return array
	 */
	public function getSummaryFields() {
		return array(
			'active',
			'course_count',
			'student_count',
			'instructor_count',
			'oa_count',
			'ca_count',
			'courses',
		);
	}

	/**
	 * @see PageObject::getIdentifierField()
	 */
	public function getIdentifierField() {
		return 'name';
	}

	/**
	 * @see PageObject::getNamespace()
	 */
	public function getNamespace() {
		return EP_NS;
	}

	/**
	 * @see PageTable::getEditRight()
	 */
	public function getEditRight() {
		return 'ep-org';
	}

}
