<?php

namespace EducationProgram\Tests\Rows;

abstract class PageObjectTest extends \ORMRowTest {
	/**
	 * Provides pairs of duplicate instances for testing the addition of an
	 * instance with the same name/title as one that already exists.
	 *
	 * @since 0.4 alpha
	 * @return array
	 */
	public function provideSameRaisesExceptionInstances() {
		$instances1 = $this->instanceProvider();
		$instances2 = $this->instanceProvider();
		$pairsOfDupes = array();

		for ( $i = 0; $i < count( $instances1 ); $i++ ) {
			$pairsOfDupes[] = array ( $instances1[$i][0], $instances2[$i][0] );
		}

		return $pairsOfDupes;
	}

	/**
	 * Clears out test data for subsequent tests.
	 */
	protected function tearDown() {
		$this->getTableInstance()->delete( array() );
		parent::tearDown();
	}
}