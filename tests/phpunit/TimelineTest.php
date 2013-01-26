<?php

namespace EducationProgram\Test;
use EducationProgram\Timeline;

/**
 * Tests for the EducationProgram\Timeline class.
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
 * @file
 * @since 0.4
 *
 * @ingroup EducationProgramTest
 *
 * @group EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class TimelineTest extends \MediaWikiTestCase {

	public function constructorProvider() {
		$argLists = array();

		$argLists[] = array( \RequestContext::getMain(), array() );

		return $argLists;
	}

	/**
	 * @dataProvider constructorProvider
	 *
	 * @param \IContextSource $context
	 * @param array $events
	 */
	public function testConstructor( \IContextSource $context, array $events ) {
		$timeline = new Timeline( $context, $events );

		$this->assertInternalType( 'string', $timeline->getHTML() );
	}

}
