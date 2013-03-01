<?php

namespace EducationProgram\Tests;

use EducationProgram\UserCourseFinder;

/**
 * Unit tests for the EducationProgram\UserCourseFinder implementing classes.
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
 * @ingroup EducationProgramTest
 *
 * @group EducationProgram
 * @group Database
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
abstract class UserCourseFinderTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @return UserCourseFinder[]
	 */
	abstract public function getInstances();

	public function argumentProvider() {
		$argLists = array();

		$argLists[] = array( 1, array() );
		$argLists[] = array( 1, EP_STUDENT );
		$argLists[] = array( 1, array( EP_INSTRUCTOR, EP_STUDENT ) );
		$argLists[] = array( 1, array( EP_INSTRUCTOR ) );
		$argLists[] = array( array( 1 ), EP_STUDENT );
		$argLists[] = array( array( 1, 2, 3 ), EP_STUDENT );
		$argLists[] = array( array( 1, 2, 3 ), array( EP_STUDENT, EP_INSTRUCTOR ) );
		$argLists[] = array( array(), array( EP_STUDENT, EP_INSTRUCTOR ) );

		return $argLists;
	}

	/**
	 * @dataProvider argumentProvider
	 */
	public function testGetCoursesForUsers( $userIds, $roles = array() ) {
		foreach ( $this->getInstances() as $courseFinder ) {
			$courseIds = $courseFinder->getCoursesForUsers( $userIds, $roles );

			$this->assertInternalType( 'array', $courseIds );
			$this->assertContainsOnly( 'int', $courseIds );
		}
	}

}
