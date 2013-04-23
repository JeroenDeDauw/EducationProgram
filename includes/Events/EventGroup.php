<?php

namespace EducationProgram\Events;

use InvalidArgumentException;

/**
 * Collection of events. Ordered. Immutable.
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
class EventGroup {

	private $events;

	/**
	 * @param Event[] $events
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct( array $events ) {
		if ( $events === array() ) {
			throw new InvalidArgumentException( 'Cannot construct an EventGroup with no events' );
		}

		$this->events = $events;
	}

	/**
	 * @return Event[]
	 */
	public function getEvents() {
		return $this->events;
	}

	/**
	 * @return int Unix timestamp
	 */
	public function getLatestEventTime() {
		$latestEventTime = 0;

		foreach ( $this->events as $event ) {
			$currentEventTime = (int)wfTimestamp( TS_UNIX, $event->getTime() );

			if ( $currentEventTime > $latestEventTime ) {
				$latestEventTime = $currentEventTime;
			}
		}

		return $latestEventTime;
	}

}
