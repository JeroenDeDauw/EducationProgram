<?php

/**
 * Class representing a single instructor.
 *
 * @since 0.1
 *
 * @file EPInstructor.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v3 or later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EPInstructor extends EPRoleObject implements EPIRole {

	/**
	 * @since 0.1
	 * @see EPIRole::getRoleName
	 */
	public function getRoleName() {
		return 'instructor';
	}

}
