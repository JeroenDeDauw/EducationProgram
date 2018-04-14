<?php

namespace EducationProgram\Tests;

use EducationProgram\Utils;

/**
 * Tests for the Utils class.
 *
 * @ingroup EducationProgramTest
 * @since 0.1
 *
 * @group EducationProgram
 * @group Database
 *
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 *
 * @covers \EducationProgram\Utils
 */
class UtilsTest extends \MediaWikiTestCase {

	public function keyPrefixProvider() {
		return [
			[ 'key', 'value', 'key - value' ],
			[ ' key   ', ' value ', ' key    -  value ' ],
			[ '- key 2 -', '- value 2 -', '- key 2 - - - value 2 -' ],
		];
	}

	/**
	 * Tests @see Utils::getKeyPrefixedValues
	 * @dataProvider keyPrefixProvider
	 */
	public function testGetKeyPrefixedValues( $key, $value, $expected ) {
		$this->assertEquals(
			[ $expected => $key ],
			Utils::getValuesAppendedKeys( [ $key => $value ] )
		);
	}

	public function articleContentProvider() {
		return [
			[ 'Foo bar', 'baz bah' ],
			[ 'Foobar', false ],
		];
	}

	/**
	 * @dataProvider articleContentProvider
	 */
	public function testGetArticleContent( $pageName, $textContent ) {
		if ( $textContent !== false ) {
			$wikiPage = \WikiPage::factory( \Title::newFromText( $pageName ) );
			$wikiPage->doEditContent( new \WikitextContent( $textContent ), 'test' );
		}

		$content = Utils::getArticleContent( $pageName );
		$expected = $textContent === false ? '' : $textContent;

		$this->assertEquals( $expected, $content );
	}

}
