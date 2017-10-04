<?php

namespace EducationProgram\Tests\Events;

use EducationProgram\Events\EventQuery;

/**
 * Unit tests for the EducationProgram\Events\EventQuery class.
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
class EventQueryTest extends \PHPUnit_Framework_TestCase {

	public function setCoursesProvider() {
		$argLists = [];

		$argLists[] = [ 1 ];
		$argLists[] = [ 42 ];
		$argLists[] = [ [ 1 ] ];
		$argLists[] = [ [ 42 ] ];
		$argLists[] = [ [ 1, 2, 3, 9001 ] ];

		return $argLists;
	}

	/**
	 * @dataProvider setCoursesProvider
	 *
	 * @param array $courseIds
	 */
	public function testSetCourses( $courseIds ) {
		$query = new EventQuery();
		$query->setCourses( $courseIds );
		$this->assertEquals( (array)$courseIds, $query->getCourseIds() );
	}

	public function timeLimitProvider() {
		$argLists = [];

		$argLists[] = [ '20010115123456', EventQuery::COMP_BIGGER ];
		$argLists[] = [ '20010115123456', EventQuery::COMP_SMALLER ];

		return $argLists;
	}

	/**
	 * @dataProvider timeLimitProvider
	 *
	 * @param string $timeLimit
	 * @param int $comparator
	 */
	public function testSetTimeLimit( $timeLimit, $comparator ) {
		$query = new EventQuery();
		$query->setTimeLimit( $timeLimit, $comparator );

		$this->assertEquals( $timeLimit, $query->getTimeLimit() );
		$this->assertEquals( $comparator, $query->getTimeLimitComparator() );
	}

	public function rowLimitProvider() {
		$argLists = [];

		$argLists[] = [ 1 ];
		$argLists[] = [ 42 ];
		$argLists[] = [ 9001 ];
		$argLists[] = [ 7201010 ];

		return $argLists;
	}

	/**
	 * @dataProvider rowLimitProvider
	 *
	 * @param int $limit
	 */
	public function testSetRowLimit( $limit ) {
		$query = new EventQuery();
		$query->setRowLimit( $limit );
		$this->assertEquals( $limit, $query->getRowLimit() );
	}

	public function sortOrderProvider() {
		$argLists = [];

		$argLists[] = [ EventQuery::ORDER_NONE ];
		$argLists[] = [ EventQuery::ORDER_TIME_ASC ];
		$argLists[] = [ EventQuery::ORDER_TIME_DESC ];

		return $argLists;
	}

	/**
	 * @dataProvider sortOrderProvider
	 *
	 * @param int $order
	 */
	public function testSetSortOrder( $order ) {
		$query = new EventQuery();
		$query->setSortOrder( $order );
		$this->assertEquals( $order, $query->getSortOrder() );
	}

}
