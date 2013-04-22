<?php

namespace EducationProgram\Events;

use IContextSource;

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
class Timeline extends \ContextSource {

	/**
	 * List of events to display in this timeline.
	 *
	 * @since 0.1
	 * @var Event[]
	 */
	protected $events;

	/**
	 * Constructor.
	 *
	 * @since 0.1
	 *
	 * @param IContextSource $context
	 * @param Event[] $events
	 */
	public function __construct( IContextSource $context, array $events ) {
		$this->setContext( $context );
		$this->events = $events;
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

		return implode(
			'<br />',
			array_map(
				function( EventGroup $group ) {
					return TimelineGroup::newFromEventGroup( $group )->getHTML();
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
		$out = $this->getOutput();

		$out->addModules( self::getModules() );
		$out->addHTML( $this->getHTML() );
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
