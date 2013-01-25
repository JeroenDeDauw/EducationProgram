<?php

namespace EducationProgram;
use User, IContextSource;

/**
 * Interface for classes representing a user in a certain role.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
interface IRole {

	/**
	 * Returns the user.
	 *
	 * @since 0.1
	 *
	 * @return User
	 */
	public function getUser();

	/**
	 * Returns name of the user.
	 *
	 * @since 0.1
	 *
	 * @return string
	 */
	public function getName();

	/**
	 * Retruns the user link.
	 *
	 * @since 0.1
	 *
	 * @return string
	 */
	public function getUserLink();

	/**
	 * Returns the tool links for this user.
	 *
	 * @since 0.1
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
	 * @since 0.1
	 *
	 * @return string
	 */
	public function getRoleName();

}
