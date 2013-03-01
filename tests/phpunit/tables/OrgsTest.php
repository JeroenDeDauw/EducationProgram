<?php

namespace EducationProgram\Tests;
use EducationProgram\Orgs;

/**
 * Tests for the EducationProgram\Orgs class.
 *
 * @ingroup EducationProgram
 * @since 0.1
 *
 * @group EducationProgram
 *
 * The database group has as a side effect that temporal database tables are created. This makes
 * it possible to test without poisoning a production database.
 * @group Database
 *
 * Some of the tests takes more time, and needs therefor longer time before they can be aborted
 * as non-functional. The reason why tests are aborted is assumed to be set up of temporal databases
 * that hold the first tests in a pending state awaiting access to the database.
 * @group medium
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class OrgsTest extends \MediaWikiTestCase {

	public function newFromArrayProvider() {
		return array(
			array(
				array(
					'name' => 'foo',
					'city' => 'bar',
					'country' => 'baz',

					'active' => true,
					'course_count' => 42,
					'student_count' => 9001,
					'instructor_count' => 23,
					'ca_count' => 4,
					'oa_count' => 2,
					'courses' => array( 11, 7, 5, 3, 2, 1 ),
				),
				true
			),
			array(
				array(
					'name' => 'foo'
				),
				true
			),
		);
	}

	/**
	 * @dataProvider newFromArrayProvider
	 */
	public function testNewFromArray( array $data, $loadDefaults = false ) {
		$change = Orgs::singleton()->newRow( $data, $loadDefaults );

		foreach ( array_keys( $data ) as $field ) {
			if ( $field === 'name' ) {
				$data[$field] = $GLOBALS['wgLang']->ucfirst( $data[$field] );
			}

			$this->assertEquals( $data[$field], $change->getField( $field ) );
		}


	}

	/**
	 * @dataProvider newFromArrayProvider
	 */
	public function testSaveSelectCountAndDelete( array $data, $loadDefaults = false ) {
		$orgsTable = Orgs::singleton();

		$org = $orgsTable->newRow( $data, $loadDefaults );

		$this->assertTrue( $org->save() );

		$id = $org->getId();

		$this->assertEquals( 1, $orgsTable->count( array( 'id' => $id ) ) );

		$obtainedOrg = $orgsTable->selectRow( null, array( 'id' => $id ) );

		foreach ( array_keys( $data ) as $field ) {
			if ( $field === 'name' ) {
				$data[$field] = $GLOBALS['wgLang']->ucfirst( $data[$field] );
			}

			$this->assertEquals( $data[$field], $obtainedOrg->getField( $field ) );
		}

		$this->assertTrue( $obtainedOrg->remove() );

		$this->assertEquals( 0, $orgsTable->count( array( 'id' => $id ) ) );
	}

}
