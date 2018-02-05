<?php

namespace EducationProgram\Tests;

use EducationProgram\Course;
use EducationProgram\CourseActivityView;
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
 * @ingroup EducationProgramTest
 *
 * @group EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 *
 * @covers \EducationProgram\CourseActivityView
 */
class CourseActivityViewTest extends \PHPUnit_Framework_TestCase {

	public function testDisplayActivity() {
		$outputPage = $this->getMockBuilder( 'OutputPage' )
			->disableOriginalConstructor()->getMock();

		$outputPage->expects( $this->atLeastOnce() )->method( 'addHTML' );

		$language = $this->getMock( 'Language' );

		$eventStore = $this->getMockBuilder( 'EducationProgram\Events\EventStore' )
			->disableOriginalConstructor()->getMock();

		$eventStore->expects( $this->once() )->method( 'query' )->will( $this->returnValue( [] ) );

		$courseStore = $this->getMockBuilder( 'EducationProgram\Store\CourseStore' )
			->disableOriginalConstructor()->getMock();

		$courseStore->expects( $this->once() )
			->method( 'getCourseByTitle' )
			->with( $this->equalTo( 'Foo/Bar' ) )
			->will( $this->returnValue(
				$this->getMockCourse()
			) );

		$activityView = new CourseActivityView( $outputPage, $language, $eventStore, $courseStore );

		$activityView->displayActivity( 'Foo/Bar', 31337 );
	}

	protected function getMockCourse() {
		return new Course(
			Courses::singleton(),
			[
				'id' => 42,

				'org_id' => 9001,
				'name' => 'Master in Angry Birds',
				'title' => 'University of Foo/Master in Angry Birds',
				'start' => '20130423135535',
				'end' => '20130423135536',
				'description' => 'In ur courses',
				'token' => 'abc',
				'students' => [ 1, 2, 3 ],
				'instructors' => [ 4, 5, 6 ],
				'online_ambs' => [ 7, 8 ],
				'campus_ambs' => [],
				'field' => 'Leetness',
				'level' => 'Over 9000',
				'term' => 'Teh future',
				'lang' => 'en',

				'student_count' => 3,
				'instructor_count' => 3,
				'oa_count' => 2,
				'ca_count' => 0,

				'touched' => 20130423135537,
			]
		);
	}

}
