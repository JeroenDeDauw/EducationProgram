<?php

namespace EducationProgram\Tests\Events;

use EducationProgram\Events\Event;
use EducationProgram\Events\EventGroup;

/**
 * Unit tests for the EducationProgram\Events\EventGroup class.
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
 * @group Database
 *
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 *
 * @covers \EducationProgram\Events\EventGroup
 */
class EventGroupTest extends \PHPUnit\Framework\TestCase {

	public function eventsProvider() {
		return [
			[ [
				new Event( 1, 2, 3, 1337, 'Nyan!', [] ),
			] ],

			[ [
				new Event( 1, 2, 3, 1337, 'Nyan!', [] ),
				new Event( 4, 5, 6, 31337, 'Nyan!', [] ),
				new Event( 7, 8, 9, 7201010, 'Nyan!', [] ),
			] ],
		];
	}

	/**
	 * @dataProvider eventsProvider
	 */
	public function testConstructor( array $events ) {
		$eventGroup = new EventGroup( $events );

		$obtainedEvents = $eventGroup->getEvents();

		$this->assertInternalType( 'array', $obtainedEvents );
		$this->assertContainsOnlyInstancesOf( 'EducationProgram\Events\Event', $obtainedEvents );
		$this->assertEquals( $events, $obtainedEvents );
	}

	public function testGetLastEventTime() {
		$events = [
			new Event( 1, 2, 3, 1337, 'Nyan!', [] ),
			new Event( 4, 5, 6, 31337, 'Nyan!', [] ),
			new Event( 7, 8, 9, 7201010, 'Nyan!', [] ),
			new Event( 4, 5, 6, 123, 'Nyan!', [] ),
		];

		$eventGroup = new EventGroup( $events );

		$lastEventTime = $eventGroup->getLatestEventTime();

		$this->assertInternalType( 'int', $lastEventTime );
		$this->assertEquals( 7201010, $lastEventTime );
	}

	public function testConstructorWithEmptyArray() {
		$this->setExpectedException( 'InvalidArgumentException' );
		new EventGroup( [] );
	}

}
