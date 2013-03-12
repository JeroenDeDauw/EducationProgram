<?php

namespace EducationProgram\Events;

use InvalidArgumentException;

/**
 * Specifies the selection criteria and options for a EventStore query.
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
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EventQuery {

	const COMP_BIGGER = 0;
	const COMP_SMALLER = 1;

	const ORDER_NONE = 0;
	const ORDER_TIME_ASC = 1;
	const ORDER_TIME_DESC = 2;

	/**
	 * @var int[]
	 */
	private $courseIds = array();

	/**
	 * @var string|null
	 */
	private $timeLimit = null;

	/**
	 * @var int|null
	 */
	private $timeLimitComparator = null;

	/**
	 * @var int|null
	 */
	private $limit = null;

	/**
	 * @var int|null
	 */
	private $order = null;

	/**
	 * Sets the ids of the courses to which the events should be relevant.
	 *
	 * @since 0.3
	 *
	 * @param int|int[] $courseIds
	 * @throws InvalidArgumentException
	 */
	public function setCourses( $courseIds ) {
		$courseIds = (array)$courseIds;

		foreach ( $courseIds as $courseId ) {
			if ( !is_int( $courseId ) ) {
				throw new InvalidArgumentException( 'Course ids need to be integers' );
			}
		}

		$this->courseIds = $courseIds;
	}

	/**
	 * Sets a time limit all events should be older or newer then,
	 * depending on the provided comparator.
	 *
	 * @since 0.3
	 *
	 * @param string $time TS_MW
	 * @param int $comparator
	 *
	 * @throws InvalidArgumentException
	 */
	public function setTimeLimit( $time, $comparator ) {
		if ( !is_string( $time ) ) {
			throw new InvalidArgumentException( '$time needs to be a TS_MW string' );
		}

		if ( !is_int( $comparator ) ) {
			throw new InvalidArgumentException( '$comparator needs to be an integer' );
		}

		$this->timeLimit = $time;
		$this->timeLimitComparator = $comparator;
	}

	/**
	 * Sets the query limit.
	 *
	 * @since 0.3
	 *
	 * @param int $limit
	 *
	 * @throws InvalidArgumentException
	 */
	public function setRowLimit( $limit ) {
		if ( !is_int( $limit ) ) {
			throw new InvalidArgumentException( '$limit needs to be an integer' );
		}

		if ( $limit <= 0 ) {
			throw new InvalidArgumentException( '$limit needs to be bigger than 0' );
		}

		$this->limit = $limit ;
	}

	/**
	 * Sets the query sort order.
	 *
	 * @since 0.3
	 *
	 * @param int $order
	 *
	 * @throws InvalidArgumentException
	 */
	public function setSortOrder( $order ) {
		if ( !is_int( $order ) ) {
			throw new InvalidArgumentException( '$order needs to be an integer' );
		}

		$this->order = $order;
	}

	/**
	 * Gets the ids of the courses to which the events should be relevant.
	 *
	 * @since 0.3
	 *
	 * @return int[]
	 */
	public function getCourseIds() {
		return $this->courseIds;
	}

	/**
	 * Returns the time limit.
	 * Returned as string in TS_MW format or null if there is no such limit.
	 *
	 * @since 0.3
	 *
	 * @return string|null
	 */
	public function getTimeLimit() {
		return $this->timeLimit;
	}

	/**
	 * Returns the time limit comparator.
	 *
	 * @since 0.3
	 *
	 * @return int|null
	 */
	public function getTimeLimitComparator() {
		return $this->timeLimitComparator;
	}

	/**
	 * Returns the query row limit.
	 *
	 * @since 0.3
	 *
	 * @return int|null
	 */
	public function getRowLimit() {
		return $this->limit;
	}

	/**
	 * Returns the query sort order.
	 *
	 * @since 0.3
	 *
	 * @return int|null
	 */
	public function getSortOrder() {
		return $this->order;
	}

}
