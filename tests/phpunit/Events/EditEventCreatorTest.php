<?php

namespace EducationProgram\Tests\Events;

use EducationProgram\Events\EditEventCreator;
use EducationProgram\CoursePage;

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
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EditEventCreatorTest extends \PHPUnit_Framework_TestCase {

	public function getEventsForEditProvider() {
		$argLists = array();

		$mainPage = new WikiPage( Title::newMainPage() );

		$argLists[] = array(
			$mainPage,
			$mainPage->getRevision(),
			new User()
		);

		$argLists[] = array(
			new CoursePage( Title::newFromText( 'Foo/Bar', EP_NS ) ),
			$mainPage->getRevision(),
			new User()
		);

		return $argLists;
	}

	/**
	 * @dataProvider getEventsForEditProvider
	 */
	public function testGetEventsForEdit( Page $article, Revision $rev, User $user ) {
		$eventCreator = new EditEventCreator();

		$events = $eventCreator->getEventsForEdit( $article, $rev, $user );

		$this->assertInternalType( 'array', $events );
		$this->assertContainsOnlyInstancesOf( 'EducationProgram\Event', $events );
	}

}
