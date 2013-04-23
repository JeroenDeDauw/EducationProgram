<?php

namespace EducationProgram\Tests;

use EducationProgram\CourseActivityView;

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
 * @ingroup EducationProgramTest
 *
 * @group EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class CourseActivityViewTest extends \PHPUnit_Framework_TestCase {

	public function testDisplayActivity() {
		$outputPage = $this->getMockBuilder( 'OutputPage' )
			->disableOriginalConstructor()->getMock();

		$language = $this->getMock( 'Language' );

		$eventStore = $this->getMockBuilder( 'EducationProgram\Events\EventStore' )
			->disableOriginalConstructor()->getMock();

		$eventStore->expects( $this->once() )->method( 'query' )->will( $this->returnValue( array() ) );

		$outputPage->expects( $this->atLeastOnce() )->method( 'addHTML' );

		$activityView = new CourseActivityView( $outputPage, $language, $eventStore );

		$activityView->displayActivity( 42, 31337 );
	}

}
