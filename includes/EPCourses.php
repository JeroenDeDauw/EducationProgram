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
	 * @see DBTable::getDBTable()
	 * @since 0.1
	 * @return string
	 */
	public function getDBTable() {
		return 'ep_courses';
	}
	
	/**
	 * (non-PHPdoc)
	 * @see DBTable::getFieldPrefix()
	 * @since 0.1
	 * @return string
	 */
	public function getFieldPrefix() {
		return 'course_';
	}
	
	/**
	 * (non-PHPdoc)
	 * @see DBTable::getDataObjectClass()
	 * @since 0.1
	 * @return string
	 */
	public function getDataObjectClass() {
		return 'EPCourse';
	}
	
	/**
	 * (non-PHPdoc)
	 * @see DBTable::getFieldTypes()
	 * @since 0.1
	 * @return array
	 */
	public function getFieldTypes() {
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
	 * @see DBTable::getDefaults()
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
	 * @see DBTable::getSummaryFields()
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
	
}
