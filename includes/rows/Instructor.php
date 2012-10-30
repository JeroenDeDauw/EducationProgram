<?php

namespace EducationProgram;

/**
 * Class representing a single instructor.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class Instructor extends RoleObject implements IRole {

	/**
	 * @since 0.1
	 * @see IRole::getRoleName
	 */
	public function getRoleName() {
		return 'instructor';
	}

}
