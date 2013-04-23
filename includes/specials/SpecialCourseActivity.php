<?php

namespace EducationProgram;

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
 * @ingroup SpecialPage
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class SpecialCourseActivity extends \UnlistedSpecialPage {

	public function __construct() {
		parent::__construct( 'CourseActivity' );
	}

	public function execute( $subPage ) {
		$subPage = $subPage === null ? '' : str_replace( '_', ' ', $subPage );

		$this->getOutput()->setPageTitle( $this->msg( 'ep-viewcourseactivityaction-title', $subPage ) );

		$this->displayCourseActivity( $subPage );
	}

	private function displayCourseActivity( $subPage ) {
		$educationProgram = Extension::globalInstance();

		$courseActivityView = new CourseActivityView(
			$this->getOutput(),
			$this->getLanguage(),
			$educationProgram->newEventStore(),
			$educationProgram->newCourseStore()
		);

		$courseActivityView->displayActivity(
			$subPage,
			$educationProgram->getSettings()->getSetting( 'activityTabMaxAgeInSeconds' )
		);
	}

}
