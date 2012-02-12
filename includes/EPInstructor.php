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
	 * (non-PHPdoc)
	 * @see DBDataObject::getDBTable()
	 */
	public static function getDBTable() {
		return 'ep_instructors';
	}
	
	/**
	 * (non-PHPdoc)
	 * @see DBDataObject::getFieldPrefix()
	 */
	public static function getFieldPrefix() {
		return 'instructor_';
	}
	
	/**
	 * @see parent::getFieldTypes
	 *
	 * @since 0.1
	 *
	 * @return array
	 */
	protected static function getFieldTypes() {
		return array(
			'id' => 'id',

			'user_id' => 'int',
		);
	}
	
	/**
	 * @since 0.1
	 * @see EPIRole::getRoleName
	 */
	public function getRoleName() {
		return 'instructor';
	}

}
