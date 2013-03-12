<?php

namespace EducationProgram\Events;

use DatabaseBase;
use InvalidArgumentException;

/**
 * Service via which EducationProgram events can be saved and queried.
 *
 * Side note:
 * This MySQL implementation of the interface pulls in some global
 * DatabaseBase object. Injecting a connection provider would be better,
 * though sadly enough we do not have such an interface yet.
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
class EventStore {

	/**
	 * @since 0.3
	 *
	 * @var string
	 */
	private $tableName;

	/**
	 * @since 0.3
	 *
	 * @var int
	 */
	private $readConnectionId;

	/**
	 * Constructor.
	 *
	 * @since 0.3
	 *
	 * @param string $tableName
	 * @param int $readConnectionId
	 */
	public function __construct( $tableName, $readConnectionId = DB_SLAVE ) {
		$this->tableName = $tableName;
		$this->readConnectionId = $readConnectionId;
	}

	/**
	 * @since 0.3
	 *
	 * @return DatabaseBase
	 */
	private function getReadConnection() {
		return wfGetDB( $this->readConnectionId );
	}

	/**
	 * @since 0.3
	 *
	 * @return DatabaseBase
	 */
	private function getWriteConnection() {
		return wfGetDB( DB_MASTER );
	}

	/**
	 * Runs the provided event query and returns the matching events.
	 *
	 * @since 0.3
	 *
	 * @param EventQuery $query
	 *
	 * @return Event[]
	 */
	public function query( EventQuery $query ) {
		$db = $this->getReadConnection();

		$conditions = array();

		if ( $query->getCourseIds() !== null ) {
			$conditions['event_course_id'] = $query->getCourseIds();
		}

		if ( $query->getTimeLimit() !== null ) {
			$comparator = $query->getTimeLimitComparator() === EventQuery::COMP_BIGGER ? '>' : '<';
			$conditions[] = 'event_time  ' . $comparator . ' ' . $db->addQuotes( $query->getTimeLimit() );
		}

		$options = array();

		if ( $query->getRowLimit() !== null ) {
			$options['LIMIT'] = $query->getRowLimit();

			$order = $query->getSortOrder() === EventQuery::ORDER_TIME_ASC ? ' ASC' : ' DESC';

			$options['ORDER BY'] = 'event_time' . $order;
		}

		$queryResult = $db->select( $this->tableName, '*', $conditions, __METHOD__, $options );

		$events = array();

		foreach ( $queryResult as $resultRow ) {
			$events[] = $this->eventFromDbResult( $resultRow );
		}

		return $events;
	}

	/**
	 * Constructs and returns an Event object given a result row from the events table.
	 *
	 * @since 0.3
	 *
	 * @param object $resultRow
	 *
	 * @return Event
	 */
	private function eventFromDbResult( $resultRow ) {
		return new Event(
			(int)$resultRow->event_id,
			(int)$resultRow->event_course_id,
			(int)$resultRow->event_user_id,
			$resultRow->event_time,
			$resultRow->event_type,
			unserialize( $resultRow->event_info )
		);
	}

	/**
	 * Inserts a new event into the event store.
	 *
	 * @since 0.3
	 *
	 * @param Event $event
	 *
	 * @return boolean SuccessIndicator
	 * @throws InvalidArgumentException
	 */
	public function insertEvent( Event $event ) {
		if ( $event->getId() !== null ) {
			throw new InvalidArgumentException( 'Can not insert events that already have an ID' );
		}

		$db = $this->getWriteConnection();

		$fields = array(
			'event_course_id' => $event->getCourseId(),
			'event_user_id' => $event->getUserId(),
			'event_time' => $event->getTime(),
			'event_type' => $event->getType(),
			'event_info' => serialize( $event->getInfo() )
		);

		return $db->insert(
			$this->tableName,
			$fields,
			__METHOD__
		) !== false;
	}

}
