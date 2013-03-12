<?php

namespace EducationProgram\Tests\Events;

use EducationProgram\Events\Event;

/**
 * Unit tests for the EducationProgram\Events\Event class.
 *
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
 * @ingroup EducationProgramTest
 *
 * @group EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EventTest extends \PHPUnit_Framework_TestCase {

	public function constructorProvider() {
		$argLists = array();

		$argLists[] = array( 1, 2, 3, '20010115123456', 'type-foobar', array() );
		$argLists[] = array( 42, 9001, 7201010, '20010115123456', 'baz', array( 'o' => 'noez' ) );
		$argLists[] = array( null, 1, 1, '20010115123456', 'spam', array( 'o' => 'noez', 42 ) );

		return $argLists;
	}

	/**
	 * @dataProvider constructorProvider
	 */
	public function testGetId( $id, $courseId, $userId, $time, $type, $info ) {
		$event = new Event( $id, $courseId, $userId, $time, $type, $info );

		$this->assertEquals( $id, $event->getId() );
	}

	/**
	 * @dataProvider constructorProvider
	 */
	public function testGetCourseId( $id, $courseId, $userId, $time, $type, $info ) {
		$event = new Event( $id, $courseId, $userId, $time, $type, $info );

		$this->assertEquals( $courseId, $event->getCourseId() );
	}

	/**
	 * @dataProvider constructorProvider
	 */
	public function testGetUserId( $id, $courseId, $userId, $time, $type, $info ) {
		$event = new Event( $id, $courseId, $userId, $time, $type, $info );

		$this->assertEquals( $userId, $event->getUserId() );
	}

	/**
	 * @dataProvider constructorProvider
	 */
	public function testGetTime( $id, $courseId, $userId, $time, $type, $info ) {
		$event = new Event( $id, $courseId, $userId, $time, $type, $info );

		$this->assertEquals( $time, $event->getTime() );
	}

	/**
	 * @dataProvider constructorProvider
	 */
	public function testGetType( $id, $courseId, $userId, $time, $type, $info ) {
		$event = new Event( $id, $courseId, $userId, $time, $type, $info );

		$this->assertEquals( $type, $event->getType() );
	}

	/**
	 * @dataProvider constructorProvider
	 */
	public function testGetInfo( $id, $courseId, $userId, $time, $type, $info ) {
		$event = new Event( $id, $courseId, $userId, $time, $type, $info );

		$this->assertEquals( $info, $event->getInfo() );
	}

	/**
	 * @dataProvider constructorProvider
	 */
	public function testGetAge( $id, $courseId, $userId, $time, $type, $info ) {
		$event = new Event( $id, $courseId, $userId, $time, $type, $info );

		$this->assertEquals(
			time() - (int)wfTimestamp( TS_UNIX, $time ),
			$event->getAge()
		);
	}

}
