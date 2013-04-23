<?php

namespace EducationProgram\Events;

use IContextSource;
use Language;
use OutputPage;

/**
 * Education Program timeline.
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
 * @since 0.1
 *
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class Timeline {

	/**
	 * List of events to display in this timeline.
	 *
	 * @since 0.1
	 * @var Event[]
	 */
	protected $events;

	/**
	 * @since 0.3
	 * @var OutputPage
	 */
	protected $outputPage;

	/**
	 * @since 0.3
	 * @var Language
	 */
	protected $language;

	/**
	 * Constructor.
	 *
	 * @since 0.1
	 *
	 * @param OutputPage $outputPage
	 * @param Language $language
	 * @param Event[] $events
	 */
	public function __construct( OutputPage $outputPage, Language $language, array $events ) {
		$this->events = $events;
		$this->outputPage = $outputPage;
		$this->language = $language;
	}

	/**
	 * Builds and returns the HTML for the timeline.
	 *
	 * @since 0.1
	 *
	 * @return string
	 */
	public function getHTML() {
		$grouper = new RecentPageEventGrouper();

		$outputPage = $this->outputPage;
		$language = $this->language;

		return implode(
			'<br />',
			array_map(
				function( EventGroup $group ) use ( $outputPage, $language ) {
					return TimelineGroup::newFromEventGroup( $group, $outputPage, $language )->getHTML();
				},
				$grouper->groupEvents( $this->events )
			)
		);
	}

	/**
	 * Displays the timeline.
	 *
	 * @since 0.1
	 */
	public function display() {
		$this->outputPage->addModules( self::getModules() );
		$this->outputPage->addHTML( $this->getHTML() );
	}

	/**
	 * Returns the modules needed for display.
	 *
	 * @since 0.1
	 *
	 * @return array
	 */
	public static function getModules() {
		return array(
			'ep.timeline'
		);
	}

}
