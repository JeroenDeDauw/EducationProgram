<?php

/**
 * Class representing the ep_courses table.
 *
 * @since 0.1
 *
 * @file EPCourses.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v3 or later
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
			'mc' => 'str',

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
			'mc' => '',

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
	 * @see EPPageTable::getRevertableFields()
	 */
	public function getRevertableFields() {
		return array(
			'org_id',
			'name',
			'mc',
			'start',
			'end',
			'description',
			'token',
			'field',
			'level',
			'term'
		);
	}
	
	public function hasActiveName( $courseName ) {
		$now = wfGetDB( DB_SLAVE )->addQuotes( wfTimestampNow() );

		return $this->has( array(
			'name' => $courseName,
			'end >= ' . $now,
			'start <= ' . $now,
		) );
	}
	
	/**
	 * (non-PHPdoc)
	 * @see EPPageObject::getIdentifierField()
	 */
	public function getIdentifierField() {
		return 'name';
	}
	
	/**
	 * (non-PHPdoc)
	 * @see EPPageObject::getNamespace()
	 */
	public function getNamespace() {
		return EP_NS_COURSE;
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
	 * (non-PHPdoc)
	 * @see EPPageTable::getEditRight()
	 */
	public function getEditRight() {
		return 'ep-course';
	}
	
}
