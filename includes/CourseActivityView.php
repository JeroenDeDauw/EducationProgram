<?php

namespace EducationProgram;

use EducationProgram\Events\EventQuery;
use EducationProgram\Events\EventStore;
use EducationProgram\Events\Timeline;
use EducationProgram\Store\CourseStore;
use Language;
use OutputPage;
use Wikibase\Test\Api\LangAttributeBase;

/**
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
class CourseActivityView {

	protected $outputPage;
	protected $language;
	protected $eventStore;
	protected $courseStore;

	public function __construct( OutputPage $outputPage, Language $language, EventStore $eventStore, CourseStore $courseStore ) {
		$this->outputPage = $outputPage;
		$this->language = $language;
		$this->eventStore = $eventStore;
		$this->courseStore = $courseStore;
	}

	/**
	 * @param int $courseId
	 * @param int $maxAgeInSeconds
	 */
	public function displayActivity( $courseTitle, $maxAgeInSeconds ) {
		try {
			$course = $this->courseStore->getCourseByTitle( $courseTitle );
		}
		catch ( CourseTitleNotFoundException $exception ) {
			$this->displayNotFound();
		}

		if ( isset( $course ) ) {
			$this->displayForCourseId(
				$course->getId(),
				$maxAgeInSeconds
			);
		}
	}

	protected function displayNotFound() {
		$this->outputPage->addWikiMsg( 'ep-viewcourseactivityaction-nosuchcourse' );
	}

	protected function displayForCourseId( $courseId, $maxAgeInSeconds ) {
		$eventQuery = $this->constructQuery( $courseId, $maxAgeInSeconds );

		$events = $this->eventStore->query( $eventQuery );

		$this->displayEvents( $events );
	}

	protected function constructQuery( $courseId, $maxAgeInSeconds ) {
		$eventQuery = new EventQuery();

		$eventQuery->setTimeLimit(
			wfTimestamp( TS_MW, time() - $maxAgeInSeconds ),
			EventQuery::COMP_BIGGER
		);
		$eventQuery->setCourses( array( $courseId ) );

		return $eventQuery;
	}

	protected function displayEvents( array $events ) {
		$view = new Timeline( $this->outputPage, $this->language, $events );
		$view->display();
	}

}
