<?php

namespace EducationProgram;

use EducationProgram\Events\Timeline;

/**
 * Action for viewing a course.
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
 * @ingroup Action
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ViewCourseActivityAction extends \FormlessAction {

	public function getName() {
		return 'epcourseactivity';
	}

	public function onView() {
		$educationProgram = Extension::globalInstance();

		$courseActivityView = new CourseActivityView(
			$this->getOutput(),
			$this->getLanguage(),
			$educationProgram->newEventStore(),
			$educationProgram->newCourseStore()
		);

		$courseActivityView->displayActivity(
			$this->getTitle()->getText(),
			$educationProgram->getSettings()->getSetting( 'activityTabMaxAgeInSeconds' )
		);
	}

	/**
	 * @see Action::getDescription()
	 */
	protected function getDescription() {
		return '';
	}

	/**
	 * Returns the page title.
	 *
	 * @since 0.1
	 *
	 * @return string
	 */
	protected function getPageTitle() {
		return $this->msg(
			'ep-viewcourseactivityaction-title',
			$this->getTitle()->getText()
		)->text();
	}

}
