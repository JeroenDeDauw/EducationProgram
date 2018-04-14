<?php

namespace EducationProgram;

/**
 * Class representing a single instructor.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 *
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class Instructor extends RoleObject implements IRole {

	/**
	 * @see IRole::getRoleName
	 */
	public function getRoleName() {
		return 'instructor';
	}

}
