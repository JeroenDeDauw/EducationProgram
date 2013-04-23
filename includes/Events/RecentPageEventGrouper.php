<?php

namespace EducationProgram\Events;

/**
 * Groups the events by affected page and sorts the groups by
 * the last change they contain, most recent first.
 *
 * Edits and page creations are kept in distinct groups.
 *
 * Events within a group are ordered as well, newest first.
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
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class RecentPageEventGrouper implements EventGrouper {

	/**
	 * @see EventGrouper::groupEvents
	 *
	 * @since 0.3
	 *
	 * @param Event[] $events
	 *
	 * @return EventGroup[]
	 */
	public function groupEvents( array $events ) {
		$groups = $this->getEventsGroupedByPage( $events );

		return $this->getGroupsSortedByTime( $groups );
	}

	/**
	 * @param Event[] $events
	 *
	 * @return EventGroup[]
	 */
	private function getEventsGroupedByPage( array $events ) {
		$groups = array();

		foreach ( $events as $event ) {
			if ( array_key_exists( 'page', $event->getInfo() ) ) {
				$this->addPageEventToGroups( $groups, $event );
			}
			else {
				$groups[] = array( $event );
			}
		}

		$groupObjects = array();

		foreach ( $groups as $events ) {
			$events = $this->getEventsSortedByTime( $events );
			$groupObjects[] = new EventGroup( $events );
		}

		return $groupObjects;
	}

	private function addPageEventToGroups( array &$groups, Event $event ) {
		$eventInfo = $event->getInfo();

		$groupId = $eventInfo['page'] . '|';
		$groupId .= array_key_exists( 'parent', $eventInfo ) && is_null( $eventInfo['parent'] ) ? 'create' : 'edit';

		if ( !array_key_exists( $groupId, $groups ) ) {
			$groups[$groupId] = array();
		}

		$groups[$groupId][] = $event;
	}

	/**
	 * @param EventGroup[] $groups
	 *
	 * @return EventGroup[]
	 */
	private function getGroupsSortedByTime( array $groups ) {
		$groupTimes = array();

		foreach ( $groups as $index => $group ) {
			$groupTimes[$index] = $group->getLatestEventTime();
		}

		arsort( $groupTimes );

		$sortedGroups = array();

		foreach ( $groupTimes as $groupIndex => $time ) {
			$sortedGroups[] = $groups[$groupIndex];
		}

		return $sortedGroups;
	}

	/**
	 * @param Event[] $groups
	 *
	 * @return Event[]
	 */
	private function getEventsSortedByTime( array $events ) {
		$eventTimes = array();

		foreach ( $events as $index => $event ) {
			$eventTimes[$index] = $event->getTime();
		}

		arsort( $eventTimes );

		$sortedEvents = array();

		foreach ( $eventTimes as $groupIndex => $time ) {
			$sortedEvents[] = $events[$groupIndex];
		}

		return $sortedEvents;
	}

}
