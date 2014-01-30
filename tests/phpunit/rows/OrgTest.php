<?php
namespace EducationProgram\Tests\Rows;

require_once __DIR__ . '/PageObjectTest.php';

use EducationProgram\Orgs;
use EducationProgram\Org;
/**
 * Tests for the EducationProgram\Org class.
 *
 * @ingroup EducationProgramTest
 *
 * @group EducationProgram
 * @group Database
 *
 * @author Andrew Green <agreen at wikimedia dot org>
 */
class OrgTest extends PageObjectTest {

	/**
	 * Returns the name of the subclass of ORMRow that we're testing (in this
	 * case, \EducationProgram\Org).
	 *
	 * @see \ORMRowTest::getRowClass()
	 * @since 0.4 alpha
	 * @return string
	 */
	protected function getRowClass() {
		return "\EducationProgram\Org";
	}

	/**
	 * Returns the table whose rows are represented by the class we're testing.
	 *
	 * @see \ORMRowTest::getTableInstance()
	 * @since 0.4 alpha
	 * @return \EducationProgram\Orgs
	 */
	protected function getTableInstance() {
		return Orgs::singleton();
	}

	/**
	 * Provides an array of arrays containing arguments for the constructor
	 * of the class we're testing (in this case, \EducationProgram\Org).
	 * The test instances we'll use will be created with these arguments.
	 *
	 * @see \ORMRowTest::constructorTestProvider()
	 * @since 0.4 alpha
	 * @return array
	 */
	public function constructorTestProvider() {
		return array ( array (
			array(
				'name' => 'Test Org',
				'city' => 'Test City',
				'country' => 'Test Country',
				'active' => true,
				'course_count' => 0,
				'student_count' => 0,
				'instructor_count' => 0,
				'ca_count' => 0,
				'oa_count' => 0,
				'courses' => array(),
				'last_active_date' => '20140127070312',
			),
			false
		) );
	}

	/**
	 * Provides mock values for mock fields. We override ORMRowTests's
	 * implementation of this method because the value of an Org's name
	 * is always munged to start with a capital, and the superclass
	 * provides a string that doesn't.
	 *
	 * @see \ORMRowTest::getMockValues()
	 * @since 1.20
	 * @return array
	 */
	protected function getMockValues() {
		return array(
				'id' => 1,
				'str' => 'Foobar4645645', // Must start with capital
				'int' => 42,
				'bool' => true,
				'array' => array( 42, 'foobar' ),
		);
	}

	/**
	 * Verifies that if you add an org with the same name as one that already
	 * exists, an \EducationProgram\ErrorPageErrorWithSelflink exception
	 * is thrown.
	 *
	 * @param \EducationProgram\Org $org a test org
	 * @param \EducationProgram\Org $duplicateOrg a test org with the
	 *   same values as $org
	 *
	 * @dataProvider provideSameRaisesExceptionInstances
	 * @expectedException \EducationProgram\ErrorPageErrorWithSelflink
	 */
	public function testSameNameRaisesException(
			Org $org, Org $duplicateOrg ) {

		// Verify that the orgs have the same name.
		$this->assertEquals( $org->getField( 'name' ),
			$duplicateOrg->getField( 'name' ) );

		// Save the first org.
		$org->save();

		// Save the duplicate org. This should throw the exception.
		$duplicateOrg->save();
	}
}