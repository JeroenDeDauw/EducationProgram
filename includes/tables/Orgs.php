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
	 * @since 0.4 alpha
	 *
	 * @var boolean
	 */
	protected $read_master_for_summaries = false;

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
			'last_active_date' => 'str', // TS_MW

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
			'last_active_date' => '19700101000000',
		);
	}

	/**
	 * @see PageTable::getRevertibleFields()
	 */
	public function getRevertibleFields() {
		return array_diff(
			array_keys( $this->getFields() ),
			array_merge( array( 'id' ), $this->getSummaryFields() )
		);
	}

	/**
	 * @see \EducationProgram\PageTable::getRevisionedObjectTypeId()
	 * @since 0.4 alpha
	 * @return string
	 */
	public function getRevisionedObjectTypeId() {
		return "EPOrgs";
	}

	/**
	 * @see ORMTable::getSummaryFields()
	 * @since 0.1
	 * @return array
	 */
	public function getSummaryFields() {
		return array(
			'course_count',
			'student_count',
			'instructor_count',
			'oa_count',
			'ca_count',
			'courses',
			'last_active_date',
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

	/**
	 * If true, ensure that DB reads to fetch data for summary fields are done
	 * on DB_MASTER. (This is sometimes necessary to avoid race conditions.)
	 *
	 * @since 0.4 alpha
	 *
	 * @param boolean $read_master_for_summaries
	 */
	public function setReadMasterForSummaries( $read_master_for_summaries ) {
		$this->read_master_for_summaries = $read_master_for_summaries;
	}

	/**
	 * Returns true if we should use the DB_MASTER when for all DB reads when
	 * creating summary fields.
	 *
	 * @since 0.4 alpha
	 *
	 * @return boolean
	 */
	public function getReadMasterForSummaries() {
		return $this->read_master_for_summaries;
	}

	/**
	 * @see ORMTable::updateSummaryFields()
	 */
	public function updateSummaryFields( $summaryFields = null, array $conditions = array() ) {

		// We know that updating summary fields will involve reading data about
		// courses. If $read_master_for_summraies is set, make sure that
		// the Courses table is reading from master. (The superclass will
		// always set this table to read from master when constructing
		// summaries.)

		if ( $this->read_master_for_summaries ) {

			$courses = Courses::singleton();
			$origReadDB = $courses->getReadDb();
			$courses->setReadDb( DB_MASTER );

			parent::updateSummaryFields( $summaryFields, $conditions );

			$courses->setReadDb( $origReadDB );

		} else {
			parent::updateSummaryFields( $summaryFields, $conditions );
		}
	}
}
