<?php

namespace EducationProgram\Tests\Events;

use EducationProgram\Events\EditEventCreator;
use EducationProgram\CoursePage;
use EducationProgram\Tests\MockSuperUser;

use Page;
use Revision;
use User;
use Title;
use WikiPage;

/**
 * Unit tests for the EducationProgram\Events\EditEventCreator class.
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
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 *
 * @covers \EducationProgram\Events\EditEventCreator
 */
class EditEventCreatorTest extends \PHPUnit\Framework\TestCase {

	public function getEventsForEditProvider() {
		$argLists = [];

		$mainPage = new WikiPage( Title::newMainPage() );

		$argLists[] = [
			$mainPage,
			$mainPage->getRevision(),
			new MockSuperUser()
		];

		$argLists[] = [
			new CoursePage( Title::newFromText( 'Foo/Bar', EP_NS ) ),
			$mainPage->getRevision(),
			new MockSuperUser()
		];

		return $argLists;
	}

	/**
	 * @dataProvider getEventsForEditProvider
	 */
	public function testGetEventsForEdit( Page $article, Revision $rev, User $user ) {
		$eventCreator = new EditEventCreator( new MockUserCourseFinder() );

		$events = $eventCreator->getEventsForEdit( $article, $rev, $user );

		$this->assertInternalType( 'array', $events );
		$this->assertContainsOnlyInstancesOf( 'EducationProgram\Events\Event', $events );
	}

}

class MockUserCourseFinder implements \EducationProgram\UserCourseFinder {

	/**
	 * @see \EducationProgram\UserCourseFinder::getCoursesForUsers
	 *
	 * @since 0.3
	 *
	 * @param int|int[] $userIds
	 * @param int|int[] $roles
	 *
	 * @return int[]
	 */
	public function getCoursesForUsers( $userIds, $roles = [] ) {
		return [ 1, 2, 42, 9001 ];
	}

}
