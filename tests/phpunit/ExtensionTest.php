<?php

namespace EducationProgram\Tests;

use EducationProgram\Extension;
use EducationProgram\Settings;

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
class ExtensionTest extends \PHPUnit_Framework_TestCase {

	public function testConstructWithNoSettings() {
		new Extension( new Settings( array() ) );
		$this->assertTrue( true );
	}

	public function testGetCourseStore() {
		$educationProgram = $this->newInstanceFromGlobalSettings();

		$this->assertInstanceOf( 'EducationProgram\Store\CourseStore', $educationProgram->newCourseStore() );
	}

	protected function newInstanceFromGlobalSettings() {
		return new Extension( new Settings( $GLOBALS['egEPSettings'] ) );
	}

}
