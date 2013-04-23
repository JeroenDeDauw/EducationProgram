<?php

namespace EducationProgram\Tests\Events;

use EducationProgram\Events\Event;
use EducationProgram\Events\RecentPageEventGrouper;

/**
 * Unit tests for the EducationProgram\Events\RecentPageEventGrouper class.
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
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class RecentPageEventGrouperTest extends \PHPUnit_Framework_TestCase {

	public function testGroupEventsWithEmptyArray() {
		$grouper = new RecentPageEventGrouper();

		$groupedEvents = $grouper->groupEvents( array() );

		$this->assertInternalType( 'array', $groupedEvents );
		$this->assertCount( 0, $groupedEvents );
	}

	/**
	 * @dataProvider eventsWithDifferentPagesProvider
	 */
	public function testGroupEventsWithDifferentPages( array $events ) {
		$grouper = new RecentPageEventGrouper();

		$groupedEvents = $grouper->groupEvents( $events );
		$this->assertInternalType( 'array', $groupedEvents );
		$this->assertContainsOnlyInstancesOf( 'EducationProgram\Events\EventGroup', $groupedEvents );

		$this->assertSameSize( $events, $groupedEvents );
	}

	public function eventsWithDifferentPagesProvider() {
		return array(
			array( array(
				new Event( 1, 2, 3, 1337, 'Nyan!', array( 'page' => 'Nyan' ) ),
			) ),

			array( array(
				new Event( 1, 2, 3, 1337, 'Nyan!', array( 'page' => 'Foo' ) ),
				new Event( 4, 5, 6, 31337, 'Nyan!', array( 'page' => 'Bar' ) ),
				new Event( 7, 8, 9, 7201010, 'Nyan!', array( 'page' => 'Baz' ) ),
			) ),

			array( array(
				new Event( 1, 2, 3, 1337, 'Nyan!', array( 'page' => 'Foo' ) ),
				new Event( 4, 5, 6, 31337, 'Nyan!', array() ),
				new Event( 7, 8, 9, 7201010, 'Nyan!', array() ),
			) ),

			array( array(
				new Event( 1, 2, 3, 1337, 'Nyan!', array( 'page' => 'Foo' ) ),
				new Event( 4, 5, 6, 31337, 'Nyan!', array( 'page' => 'Foo', 'parent' => null ) ),
			) ),
		);
	}

	/**
	 * @dataProvider eventsWithTheSamePagesProvider
	 */
	public function testGroupEventsWithTheSamePages( array $events ) {
		$grouper = new RecentPageEventGrouper();

		$groupedEvents = $grouper->groupEvents( $events );
		$this->assertInternalType( 'array', $groupedEvents );
		$this->assertContainsOnlyInstancesOf( 'EducationProgram\Events\EventGroup', $groupedEvents );

		$this->assertCount( 1, $groupedEvents );
	}

	public function eventsWithTheSamePagesProvider() {
		return array(
			array( array(
				new Event( 1, 2, 3, 1337, 'Nyan!', array( 'page' => 'Nyan' ) ),
			) ),

			array( array(
				new Event( 1, 2, 3, 1337, 'Nyan!', array( 'page' => 'Nyan' ) ),
				new Event( 4, 5, 6, 31337, 'Nyan!', array( 'page' => 'Nyan' ) ),
				new Event( 7, 8, 9, 7201010, 'Nyan!', array( 'page' => 'Nyan' ) ),
			) ),

			array( array(
				new Event( 1, 2, 3, 1337, 'Nyan!', array( 'page' => 'Nyan' ) ),
				new Event( 4, 5, 6, 31337, 'Nyan!', array( 'page' => 'Nyan', 'parent' => 42 ) ),
				new Event( 7, 8, 9, 7201010, 'Nyan!', array( 'page' => 'Nyan', 'parent' => 1 ) ),
			) ),
		);
	}

	public function testGroupEventsGroupSorting() {
		$grouper = new RecentPageEventGrouper();

		$events = array(
			new Event( 1, 2, 3, 1337, 'Nyan!', array( 'page' => 'Nyan' ) ),
			new Event( 2, 8, 9, 7201010, 'Nyan!', array( 'page' => 'Nyan' ) ),
			new Event( 3, 5, 6, 31337, 'Nyan!', array( 'page' => 'Nyan' ) ),

			new Event( 4, 2, 3, 10003, 'Onoez!', array( 'page' => 'Onoez' ) ),
			new Event( 5, 8, 9, 10001, 'Onoez!', array( 'page' => 'Onoez' ) ),
			new Event( 6, 5, 6, 10002, 'Onoez!', array( 'page' => 'Onoez' ) ),
		);

		$groupedEvents = $grouper->groupEvents( $events );

		$nyanGroup = $groupedEvents[0];
		$onoezGroup = $groupedEvents[1];

		$this->assertEquals(
			array(
				2,
				3,
				1
			),
			$this->eventsToIds( $nyanGroup->getEvents() )
		);

		$this->assertEquals(
			array(
				4,
				6,
				5
			),
			$this->eventsToIds( $onoezGroup->getEvents() )
		);
	}

	private function eventsToIds( array $events ) {
		return array_map(
			function( Event $event ) {
				return $event->getId();
			},
			$events
		);
	}

}