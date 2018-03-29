<?php

namespace EducationProgram\Tests\Events;

use EducationProgram\Events\EventStore;
use EducationProgram\Events\EventQuery;
use EducationProgram\Events\Event;

/**
 * Unit tests for the EducationProgram\Events\EventStore class.
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
 * @covers \EducationProgram\Events\EventStore
 */
class EventStoreTest extends \MediaWikiTestCase {

	public function getStore() {
		return new EventStore( 'ep_events' );
	}

	public function setUp() {
		parent::setUp();

		wfGetDB( DB_MASTER )->delete( 'ep_events', '*' );

		$events = [];

		$events[] = new Event(
			null,
			900001,
			1,
			'20010115123456',
			'foobar',
			[ 'baz' ]
		);

		$events[] = new Event(
			null,
			900002,
			1,
			'20010115123457',
			'foobar',
			[ 'bah' ]
		);

		$events[] = new Event(
			null,
			900001,
			1,
			'20110115123457',
			'foobar',
			[ 'spam' ]
		);

		$events[] = new Event(
			null,
			900001,
			2,
			'20110115123457',
			'foobar',
			[ 'hax' ]
		);

		$events[] = new Event(
			null,
			900001,
			1,
			'20110115123457',
			'nyan',
			[ '~=[,,_,,]:3', 42, [ 'o_O' ] ]
		);

		foreach ( $events as $event ) {
			$this->getStore()->insertEvent( $event );
		}
	}

	public function queryProvider() {
		$argLists = [];

		$query = new EventQuery();
		$query->setCourses( 900001 );

		$argLists[] = [ $query, 4 ];

		$query = new EventQuery();
		$query->setCourses( 900002 );

		$argLists[] = [ $query, 1 ];

		$query = new EventQuery();
		$query->setCourses( 900003 );

		$argLists[] = [ $query, 0 ];

		$query = new EventQuery();
		$query->setCourses( [ 900001, 900002, 900003 ] );

		$argLists[] = [ $query, 5 ];

		$query = new EventQuery();
		$query->setCourses( 900001 );
		$query->setRowLimit( 2 );

		$argLists[] = [ $query, 2 ];

		$query = new EventQuery();
		$query->setCourses( 900001 );
		$query->setRowLimit( 2 );
		$query->setSortOrder( EventQuery::ORDER_TIME_ASC );

		$argLists[] = [ $query, 2 ];

		$query = new EventQuery();
		$query->setCourses( 900001 );
		$query->setTimeLimit( '20050115123457', EventQuery::COMP_BIGGER );

		$argLists[] = [ $query, 3 ];

		$query = new EventQuery();
		$query->setCourses( 900001 );
		$query->setTimeLimit( '20050115123457', EventQuery::COMP_SMALLER );

		$argLists[] = [ $query, 1 ];

		return $argLists;
	}

	/**
	 * @dataProvider queryProvider
	 *
	 * @param EventQuery $query
	 * @param int $expectedCount
	 */
	public function testQuery( EventQuery $query, $expectedCount ) {
		$events = $this->getStore()->query( $query );

		$this->assertInternalType( 'array', $events );
		$this->assertContainsOnlyInstancesOf( 'EducationProgram\Events\Event', $events );

		$this->assertCount( $expectedCount, $events );
	}

	public function eventProvider() {
		$events = [];

		$events[] = new Event(
			null,
			900011,
			4242,
			'20110115123457',
			'foobar',
			[ 'hax' ]
		);

		$events[] = new Event(
			null,
			900012,
			31337,
			'20110115123457',
			'nyan',
			[ '~=[,,_,,]:3', 42, [ 'o_O' ] ]
		);

		return $this->arrayWrap( $events );
	}

	/**
	 * @dataProvider eventProvider
	 *
	 * @param Event $event
	 */
	public function testInsertEvent( Event $event ) {
		$this->assertTrue( $this->getStore()->insertEvent( $event ), 'insertEvent returned true' );
	}

}
