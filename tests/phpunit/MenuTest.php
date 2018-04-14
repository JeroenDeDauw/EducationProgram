<?php

namespace EducationProgram\Tests;

use EducationProgram\Menu;

/**
 * Tests for the EducationProgram\Menu class.
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
 * @since 0.4
 *
 * @ingroup EducationProgramTest
 *
 * @group EducationProgram
 *
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 *
 * @covers \EducationProgram\Menu
 */
class MenuTest extends \MediaWikiTestCase {

	public function testConstructor() {
		$menu = new Menu( \RequestContext::getMain() );

		$this->assertInternalType( 'string', $menu->getHTML() );
	}

	public function testSetItemFunction() {
		$menu = new Menu( \RequestContext::getMain() );

		$menu->setItemFunction( function ( array $items ) {
			unset( $items['ep-nav-mycourses'] );
			return $items;
		} );

		$this->assertInternalType( 'string', $menu->getHTML() );
	}

}
