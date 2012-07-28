<?php

/**
 * Class representing the ep_courses table.
 *
 * @since 0.1
 *
 * @file EPCourses.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EPCourses extends EPPageTable {

	/**
	 * (non-PHPdoc)
	 * @see ORMTable::getName()
	 * @since 0.1
	 * @return string
	 */
	public function getName() {
		return 'ep_courses';
	}

	/**
	 * (non-PHPdoc)
	 * @see ORMTable::getFieldPrefix()
	 * @since 0.1
	 * @return string
	 */
	public function getFieldPrefix() {
		return 'course_';
	}

	/**
	 * (non-PHPdoc)
	 * @see ORMTable::getRowClass()
	 * @since 0.1
	 * @return string
	 */
	public function getRowClass() {
		return 'EPCourse';
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

			'org_id' => 'int',
			'name' => 'str',
			'title' => 'str',
			'start' => 'str', // TS_MW
			'end' => 'str', // TS_MW
			'description' => 'str',
			'token' => 'str',
			'students' => 'array',
			'instructors' => 'array',
			'online_ambs' => 'array',
			'campus_ambs' => 'array',
			'field' => 'str',
			'level' => 'str',
			'term' => 'str',
			'lang' => 'str',

			'student_count' => 'int',
			'instructor_count' => 'int',
			'oa_count' => 'int',
			'ca_count' => 'int',
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
			'name' => '',
			'title' => '',
			'start' => wfTimestamp( TS_MW ),
			'end' => wfTimestamp( TS_MW ),
			'description' => '',
			'token' => '',
			'students' => array(),
			'instructors' => array(),
			'online_ambs' => array(),
			'campus_ambs' => array(),
			'field' => '',
			'level' => '',
			'term' => '',
			'lang' => '',

			'student_count' => 0,
			'instructor_count' => 0,
			'oa_count' => 0,
			'ca_count' => 0,
		);
	}

	/**
	 * (non-PHPdoc)
	 * @see ORMTable::getSummaryFields()
	 * @since 0.1
	 * @return array
	 */
	public function getSummaryFields() {
		return array(
			'student_count',
			'instructor_count',
			'oa_count',
			'ca_count',
		);
	}

	/**
	 * (non-PHPdoc)
	 * @see EPPageTable::getRevertibleFields()
	 */
	public function getRevertibleFields() {
		return array(
			'org_id',
			'name',
			'title',
			'start',
			'end',
			'description',
			'token',
			'field',
			'level',
			'term'
		);
	}

	public function hasActiveTitle( $courseTitle ) {
		$now = wfGetDB( DB_SLAVE )->addQuotes( wfTimestampNow() );

		return $this->has( array(
			'title' => $courseTitle,
			'end >= ' . $now,
			'start <= ' . $now,
		) );
	}

	/**
	 * (non-PHPdoc)
	 * @see EPPageObject::getIdentifierField()
	 */
	public function getIdentifierField() {
		return 'title';
	}

	/**
	 * (non-PHPdoc)
	 * @see EPPageObject::getNamespace()
	 */
	public function getNamespace() {
		return EP_NS;
	}

	/**
	 * Get the conditions that will select courses with the provided state.
	 *
	 * @since 0.1
	 *
	 * @param string $state
	 * @param boolean $prefix
	 *
	 * @return array
	 */
	public static function getStatusConds( $state, $prefix = false ) {
		$now = wfGetDB( DB_SLAVE )->addQuotes( wfTimestampNow() );

		$conditions = array();

		switch ( $state ) {
			case 'current':
				$conditions[] = 'end >= ' . $now;
				$conditions[] = 'start <= ' . $now;
				break;
			case 'passed':
				$conditions[] = 'end < ' . $now;
				break;
			case 'planned':
				$conditions[] = 'start > ' . $now;
				break;
		}

		if ( $prefix ) {
			$conditions = self::singleton()->getPrefixedValues( $conditions );
		}

		return $conditions;
	}

	/**
	 * Returns a set of courses for the specified users or roles.
	 *
	 * @since 0.1
	 *
	 * @param array|integer $userIds
	 * @param array|integer $roleIds
	 * @param array $conditions
	 * @param array|string|null $fields
	 * @param array $options
	 *
	 * @return ORMResult
	 */
	public function getCoursesForUsers( $userIds = array(), $roleIds = array(),
										array $conditions = array(), $fields = null, array $options = array() ) {
		$conditions = $this->getPrefixedValues( $conditions );

		if ( $userIds !== array() ) {
			$conditions['upc_user_id'] = (array)$userIds;
		}

		if ( $roleIds !== array() ) {
			$conditions['upc_role'] = (array)$roleIds;
		}

		$options[] = 'DISTINCT';

		$courses = wfGetDB( DB_SLAVE )->select(
			array( 'ep_courses', 'ep_users_per_course' ),
			$this->getPrefixedFields( is_null( $fields ) ? $this->getFieldNames() : (array)$fields ),
			$conditions,
			__METHOD__,
			$options,
			array(
				'ep_users_per_course' => array( 'INNER JOIN', array( 'upc_course_id=course_id' ) ),
			)
		);

		return new ORMResult( $this, $courses );
	}

	/**
	 * @see EPPageTable::getEditRight
	 *
	 * @since 0.1
	 *
	 * @return string
	 */
	public function getEditRight() {
		return 'ep-course';
	}

}
