<?php

namespace EducationProgram\Events;

use User;

/**
 * Class representing a single Education Program event.
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
class Event {

	private $eventId;
	private $courseId;
	private $userId;
	private $time;
	private $type;
	private $info;

	/**
	 * Constructor.
	 *
	 * @since 0.3
	 *
	 * @param int|null $eventId
	 * @param int $courseId
	 * @param int $userId
	 * @param string $time TS_MW
	 * @param string $type
	 * @param array $info
	 */
	public function __construct( $eventId, $courseId, $userId, $time, $type, array $info ) {
		$this->eventId = $eventId;
		$this->courseId = $courseId;
		$this->userId = $userId;
		$this->time = $time;
		$this->type = $type;
		$this->info = $info;
	}

	/**
	 * Returns the id of the event.
	 *
	 * @since 0.3
	 *
	 * @return int|null
	 */
	public function getId() {
		return $this->eventId;
	}

	/**
	 * Returns the id of the course for which the event is relevant.
	 *
	 * @since 0.3
	 *
	 * @return int
	 */
	public function getCourseId() {
		return $this->courseId;
	}

	/**
	 * Returns the id of the User that made the event.
	 *
	 * @since 0.3
	 *
	 * @return int
	 */
	public function getUserId() {
		return $this->userId;
	}

	/**c
	 * Returns the time at which the event occurred.
	 * The time is a string formatted as TS_MW.
	 *
	 * @since 0.3
	 *
	 * @return string TS_MW
	 */
	public function getTime() {
		return $this->time;
	}

	/**
	 * Returns the string identifier for the events type.
	 *
	 * @since 0.3
	 *
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Returns the events type specific info.
	 *
	 * @since 0.3
	 *
	 * @return string
	 */
	public function getInfo() {
		return $this->info;
	}

	/**
	 * Returns the age of the event in seconds.
	 *
	 * @since 0.3
	 *
	 * @return integer
	 */
	public function getAge() {
		return time() - (int)wfTimestamp( TS_UNIX, $this->time );
	}

}
