<?php

namespace EducationProgram\Tests;

/**
 * Runs the Education Program action pages to make sure they do not contain fatal errors.
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
 * @ingroup EducationProgramTest
 *
 * @group EducationProgram
 * @group Database
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ActionsTest extends \MediaWikiTestCase {

	public function actionProvider() {
		$argLists = [];

		$actions = [
			'edit',
			'view',
			'purge',
			'history',
			'delete',
		];

		foreach ( $actions as $action ) {
			$argLists[] = [
				$action,
				new \EducationProgram\OrgPage( \Title::newFromText( 'University of foo' ) )
			];
			$argLists[] = [
				$action,
				new \EducationProgram\CoursePage(
					\Title::newFromText( 'University of foo/bar baz' )
				)
			];
		}

		return $argLists;
	}

	/**
	 * @dataProvider actionProvider
	 */
	public function testSpecial( $action, \EducationProgram\EducationPage $page ) {
		$context = \RequestContext::getMain();
		$context->setUser( new MockSuperUser() );
		$context->setTitle( $page->getTitle() );

		$action = \Action::factory( $action, $page, $context );
		$action->show();

		$this->assertTrue( true, 'Action was run without errors' );
	}

}
