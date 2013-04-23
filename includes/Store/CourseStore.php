<?php

namespace EducationProgram\Store;

use DatabaseBase;
use EducationProgram\Course;
use EducationProgram\CourseNotFoundException;
use EducationProgram\CourseTitleNotFoundException;
use EducationProgram\Courses;

/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @since 0.3
 *
 * @file
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class CourseStore {

	/**
	 * @var string
	 */
	private $tableName;

	/**
	 * @var DatabaseBase
	 */
	private $readDatabase;

	public function __construct( $tableName, DatabaseBase $readDatabase ) {
		$this->readDatabase = $readDatabase;
		$this->tableName = $tableName;
	}

	/**
	 * @since 0.3
	 *
	 * @param int $courseId
	 *
	 * @return Course
	 * @throws CourseNotFoundException
	 */
	public function getCourseById( $courseId ) {
		$result = $this->readDatabase->selectRow(
			$this->tableName,
			$this->getReadFields(),
			array(
				'course_id' => $courseId
			),
			__METHOD__
		);

		if ( !is_object( $result ) ) {
			throw new CourseNotFoundException( $courseId );
		}

		return $this->newCourseFromRow( $result );
	}

	/**
	 * @since 0.3
	 *
	 * @param string $courseTitle
	 *
	 * @return Course
	 * @throws CourseTitleNotFoundException
	 */
	public function getCourseByTitle( $courseTitle ) {
		$result = $this->readDatabase->selectRow(
			$this->tableName,
			$this->getReadFields(),
			array(
				'course_title' => $courseTitle
			),
			__METHOD__
		);

		if ( !is_object( $result ) ) {
			throw new CourseTitleNotFoundException( $courseTitle );
		}

		return $this->newCourseFromRow( $result );
	}

	/**
	 * @since 0.3
	 *
	 * @return string[]
	 */
	protected function getReadFields() {
		return array(
			'course_id',

			'course_org_id',
			'course_name',
			'course_title',
			'course_start', // TS_MW
			'course_end', // TS_MW
			'course_description',
			'course_token',
			'course_students',
			'course_instructors',
			'course_online_ambs',
			'course_campus_ambs',
			'course_field',
			'course_level',
			'course_term',
			'course_lang',

			'course_student_count',
			'course_instructor_count',
			'course_oa_count',
			'course_ca_count',

			'course_touched',
		);
	}

	/**
	 * Constructs and returns a new Course given a result row.
	 *
	 * @since 0.3
	 *
	 * @param object $row
	 *
	 * @return Course
	 */
	protected function newCourseFromRow( $row ) {
		$fields = array();

		foreach ( (array)$row as $fieldName => $fieldValue ) {
			$fields[substr( $fieldName, 7 )] = $fieldValue;
		}

		$fields['students'] = unserialize( $fields['students'] );
		$fields['instructors'] = unserialize( $fields['instructors'] );
		$fields['online_ambs'] = unserialize( $fields['online_ambs'] );
		$fields['campus_ambs'] = unserialize( $fields['campus_ambs'] );

		$fields['id'] = (int)$fields['id'];
		$fields['student_count'] = (int)$fields['student_count'];
		$fields['instructor_count'] = (int)$fields['instructor_count'];
		$fields['oa_count'] = (int)$fields['oa_count'];
		$fields['ca_count'] = (int)$fields['ca_count'];

		return new Course(
			$courseTable = Courses::singleton(),
			$fields
		);
	}

}