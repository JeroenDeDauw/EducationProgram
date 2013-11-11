<?php

namespace EducationProgram\Tests\Store;

use EducationProgram\Course;
use EducationProgram\Store\CourseStore;

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
 * @ingroup EducationProgramTest
 *
 * @group EducationProgram
 * @group CourseStoreTest
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class CourseStoreTest extends \PHPUnit_Framework_TestCase {

	public function testGetCourseById() {
		$store = $this->newMockStoreForRowSelect();

		$course = $store->getCourseById( 42 );

		$this->assertIsCorrectCourse( $course );
	}

	public function testGetCourseByTitle() {
		$store = $this->newMockStoreForRowSelect();

		$course = $store->getCourseByTitle( 'Foo/Bar' );

		$this->assertIsCorrectCourse( $course );
	}

	protected function newMockStoreForRowSelect() {
		$database = $this->getMockBuilder( 'DatabaseMysql' )
			->disableOriginalConstructor()
			->getMock();

		$database->expects( $this->once() )->method( 'selectRow' )
			->with(
				$this->equalTo( 'in_ur_database' ),
				$this->anything()
			)
			->will( $this->returnValue( $this->getMockResultRow() ) );

		return new CourseStore( 'in_ur_database', $database );
	}

	protected function getMockResultRow() {
		$course = array(
			'course_id' => '42',

			'course_org_id' => '9001',
			'course_name' => 'Master in Angry Birds',
			'course_title' => 'University of Foo/Master in Angry Birds',
			'course_start' => '20130423135535',
			'course_end' => '20130423135536',
			'course_description' => 'In ur courses',
			'course_token' => 'abc',
			'course_students' => serialize( array( 1, 2, 3 ) ),
			'course_instructors' => serialize( array( 4, 5, 6 ) ),
			'course_online_ambs' => serialize( array( 7, 8 ) ),
			'course_campus_ambs' => serialize( array() ),
			'course_field' => 'Leetness',
			'course_level' => 'Over 9000',
			'course_term' => 'Teh future',
			'course_lang' => 'en',

			'course_student_count' => '3',
			'course_instructor_count' => '3',
			'course_oa_count' => '2',
			'course_ca_count' => '0',

			'course_touched' => '20130423135537',
		);

		return (object)$course;
	}

	protected function assertIsCorrectCourse( $course ) {
		/**
		 * @var Course $course
		 */
		$this->assertInstanceOf( 'EducationProgram\Course', $course );
		$this->assertInternalType( 'int', $course->getId() );
		$this->assertEquals( 'en', $course->getField( 'lang' ) );
		$this->assertInternalType( 'array', $course->getField( 'students' ) );
		$this->assertContainsOnly( 'int', $course->getField( 'students' ) );
		$this->assertEquals( array( 7, 8 ), $course->getField( 'online_ambs' ) );
	}

	public function testGetCourseByIdNotFoundBehaviour() {
		$database = $this->getMockBuilder( 'DatabaseMysql' )
			->disableOriginalConstructor()
			->getMock();

		$database->expects( $this->once() )
			->method( 'selectRow' )
			->will( $this->returnValue( false ) );

		$store = new CourseStore( 'in_ur_database', $database );

		$this->setExpectedException( 'EducationProgram\CourseNotFoundException' );

		$store->getCourseById( 1 );
	}

	public function testGetCourseByTitleNotFoundBehaviour() {
		$database = $this->getMockBuilder( 'DatabaseMysql' )
			->disableOriginalConstructor()
			->getMock();

		$database->expects( $this->once() )
			->method( 'selectRow' )
			->will( $this->returnValue( false ) );

		$store = new CourseStore( 'in_ur_database', $database );

		$this->setExpectedException( 'EducationProgram\CourseTitleNotFoundException' );

		$store->getCourseByTitle( 'Foo/Bar' );
	}

}
