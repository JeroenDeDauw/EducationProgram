<?php

namespace EducationProgram;

use User;
use IContextSource;

/**
 * Interface for classes representing a user in a certain role.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 *
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
interface IRole {

	/**
	 * Returns the user.
	 *
	 * @return User
	 */
	public function getUser();

	/**
	 * Returns name of the user.
	 *
	 * @return string
	 */
	public function getName();

	/**
	 * @return string
	 */
	public function getUserLink();

	/**
	 * Returns the tool links for this user.
	 *
	 * @param IContextSource $context
	 * @param Course|null $course
	 *
	 * @return string
	 */
	public function getToolLinks( IContextSource $context, Course $course = null );

	/**
	 * Returns a short name for the role.
	 *
	 * @return string
	 */
	public function getRoleName();

}
