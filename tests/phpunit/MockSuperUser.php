<?php

namespace EducationProgram\Tests;

/**
 * Just call SuperUser when you need to be able to do everything.
 *
 * Stolen from Translate extension.>
 */
class MockSuperUser extends \User {
	public function getId() {
		return 666;
	}

	public function getName() {
		return 'SuperUser';
	}

	public function isAllowed( $right = '' ) {
		return true;
	}
}
