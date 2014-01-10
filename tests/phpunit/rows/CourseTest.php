<?php

namespace EducationProgram\Tests\Rows;

require_once __DIR__ . '/PageObjectTest.php';

use EducationProgram\Courses;
use EducationProgram\Course;
/**
 * Tests for the EducationProgram\Course class.
 *
 * @ingroup EducationProgramTest
 *
 * @group EducationProgram
 * @group Database
 *
 * @author Andrew Green <agreen at wikimedia dot org>
 */
class CourseTest extends PageObjectTest {

	/**
	 * Returns the name of the subclass of ORMRow that we're testing (in this
	 * case, \EducationProgram\Course).
	 *
	 * @see \ORMRowTest::getRowClass()
	 * @since 0.4 alpha
	 * @return string
	 */
	protected function getRowClass() {
		return "\EducationProgram\Course";
	}

	/**
	 * Returns the table whose rows are represented by the class we're testing.
	 *
	 * @see \ORMRowTest::getTableInstance()
	 * @since 0.4 alpha
	 * @return \EducationProgram\Courses
	 */
	protected function getTableInstance() {
		return Courses::singleton();
	}

	/**
	 * Provides an array of arrays containing arguments for the constructor
	 * of the class we're testing (in this case, \EducationProgram\Course).
	 * The test instances we'll use will be created with these arguments.
	 *
	 * @see \ORMRowTest::constructorTestProvider()
	 * @since 0.4 alpha
	 * @return array
	 */
	public function constructorTestProvider() {
		return array ( array (
			array(
				'org_id' => 1,
				'name' => 'Test Course',
				'title' => 'Test Org/Test Course (Test term)',
				'start' => '20001008000000',
				'end' => '20501008000000',
				'description' => 'Test course description',
				'token' => 'testtoken',
				'students' => array(),
				'instructors' => array(),
				'online_ambs' => array(),
				'campus_ambs' => array(),
				'field' => 'Test Feild',
				'level' => 'Test level',
				'term' => 'Test term',
				'lang' => 'en',
				'student_count' => 0,
				'instructor_count' => 0,
				'oa_count' => 0,
				'ca_count' => 0,
			),
			false
		) );
	}

	/**
	 * Verifies that if you add a course with the same title as one that already
	 * exists, an \EducationProgram\ErrorPageErrorWithSelflink exception
	 * is thrown.
	 *
	 * @param \EducationProgram\Course $course a test course
	 * @param \EducationProgram\Course $duplicateCourse a test course with the
	 *   same values as $course
	 *
	 * @dataProvider provideSameRaisesExceptionInstances
	 * @expectedException \EducationProgram\ErrorPageErrorWithSelflink
	 */
	public function testSameTitleRaisesException(
			Course $course, Course $duplicateCourse ) {

		// Verify that the courses have the same title.
		$this->assertEquals( $course->getField( 'title' ),
			$duplicateCourse->getField( 'title' ) );

		// Save the first course.
		$course->save();

		// Save the duplicate course. This should throw the exception.
		$duplicateCourse->save();
	}
}