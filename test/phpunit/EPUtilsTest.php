<?php

/**
 * Tests for the EPUtils class.
 *
 * @ingroup EducationProgram
 * @since 0.1
 *
 * @group EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EPUtilsTest extends MediaWikiTestCase {

	public function keyPrefixProvider() {
		return array(
			array( 'key', 'value', 'key - value' ),
			array( ' key   ', ' value ', ' key    -  value ' ),
			array( '- key 2 -', '- value 2 -', '- key 2 - - - value 2 -' ),
		);
	}

	/**
	 * Tests @see EPUtils::getKeyPrefixedValues
	 * @dataProvider keyPrefixProvider
	 */
	public function testGetKeyPrefixedValues( $key, $value, $expected ) {
		$this->assertEquals(
			array( $expected => $key ),
			EPUtils::getValuesAppendedKeys( array( $key => $value ) )
		);
	}

}
