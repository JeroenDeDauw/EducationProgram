<?php

namespace EducationProgram\Tests;
use SpecialPage;

/**
 * Runs the Education Program special pages to make sure they do not contain fatal errors.
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
class SpecialsTest extends \MediaWikiTestCase {

	public function specialProvider() {
		$specials = array(
			'Courses',
			'Articles',
			'CampusAmbassadors',
			'Disenroll',
			'EducationProgram',
			'Enroll',
			'Institutions',
			'ManageCourses',
			'MyCourses',
			'OnlineAmbassadorProfile',
			'OnlineAmbassadors',
			'Student',
			'StudentActivity',
			'Students',
		);

		$argLists = array();

		foreach ( $specials as $special ) {
			if ( array_key_exists( $special, $GLOBALS['wgSpecialPages'] ) ) {
				$specialPage = \SpecialPageFactory::getPage( $special );
				$context = \RequestContext::newExtraneousContext( $specialPage->getPageTitle() );

				$specialPage->setContext( clone $context );
				$argLists[] = array( clone $specialPage );

				$context->setUser( new MockSuperUser() );
				$specialPage->setContext( $context );
				$argLists[] = array( $specialPage );
			}
		}

		return $argLists;
	}

	/**
	 * @dataProvider specialProvider
	 */
	public function testSpecial( \SpecialPage $specialPage ) {
		try {
			$specialPage->execute( '' );
		}
		catch ( \Exception $ex ) {
			if ( !( $ex instanceof \PermissionsError ) && !( $ex instanceof \ErrorPageError ) ) {
				throw $ex;
			}
		}

		$this->assertTrue( true, 'SpecialPage was run without errors' );
	}

}
