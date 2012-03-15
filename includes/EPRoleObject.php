<?php

/**
 * Object representing a user in a certain role linked to courses.
 *
 * @since 0.1
 *
 * @file EPRoleObject.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v3 or later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
abstract class EPRoleObject extends DBDataObject implements EPIRole {
	
	/**
	 * Field for caching the linked user.
	 *
	 * @since 0.1
	 * @var User|false
	 */
	protected $user = false;
	
	/**
	 * Cached array of the linked EPCourse objects.
	 *
	 * @since 0.1
	 * @var array|false
	 */
	protected $courses = false;

	/**
	 * Create a new instructor object from a user id.
	 * 
	 * @since 0.1
	 * 
	 * @param integer $userId
	 * @param boolean $load If the object should be loaded from the db if it already exists
	 * @param null|array|string $fields Fields to load
	 *
	 * @return EPRoleObject
	 */
	public static function newFromUserId( $userId, $load = false, $fields = null ) {
		$data = array( 'user_id' => $userId );
		
		$map = array(
			'EPOA' => 'EPOAs',
			'EPCA' => 'EPCAs',
			'EPStudent' => 'EPStudents',
			'EPInstructor' => 'EPInstructors',
		); // TODO: this is lame
		
		$class = $map[get_called_class()];
		$table = $class::singleton();
		
		$userRole = $load ? $table->selectRow( $fields, $data ) : false;
		
		if ( $userRole === false ) {
			return new static( $table, $data, true );
		}
		else {
			$userRole->setFields( $data );
			return $userRole;
		}
	}
	
	/**
	 * Create a new instructor object from a User object.
	 * 
	 * @since 0.1
	 * 
	 * @param User $user
	 * @param boolean $load If the object should be loaded from the db if it already exists
	 * @param null|array|string $fields Fields to load
	 *
	 * @return EPRoleObject
	 */
	public static function newFromUser( User $user, $load = false, $fields = null ) {
		return static::newFromUserId( $user->getId(), $load, $fields );
	}
	
	/**
	 * Returns the user that this instructor is.
	 * 
	 * @since 0.1
	 * 
	 * @return User
	 */
	public function getUser() {
		if ( $this->user === false ) {
			$this->user = User::newFromId( $this->getField( 'user_id' ) );
		}
		
		return $this->user;
	}
	
	/**
	 * Returns the name of the instroctor, using their real name when available.
	 * 
	 * @since 0.1
	 * 
	 * @return string
	 */
	public function getName() {
		return $this->getUser()->getRealName() === '' ? $this->getUser()->getName() : $this->getUser()->getRealName();
	}
	
	/**
	 * Returns the tool links for this ambassador.
	 * 
	 * @since 0.1
	 * 
	 * @param IContextSource $context
	 * @param EPCourse|null $course
	 * 
	 * @return string
	 */
	public function getToolLinks( IContextSource $context, EPCourse $course = null ) {
		return EPUtils::getRoleToolLinks( $this, $context, $course );
	}

	/**
	 * Retruns the user link for this ambassador, using their real name when available.
	 * 
	 * @since 0.1
	 * 
	 * @return string
	 */
	public function getUserLink() {
		return Linker::userLink(
			$this->getUser()->getId(),
			$this->getUser()->getName(),
			$this->getName()
		);
	}
	
	/**
	 * Associate the user with the provided courses.
	 *
	 * @since 0.1
	 *
	 * @param array $courses
	 *
	 * @return bool Success indicator
	 */
	public function associateWithCourses( array /* of EPCourse */ $courses ) {
		$success = true;

		$courseIds = array();
		
		foreach ( $courses as /* EPCourse */ $course ) {
			$courseIds[] = $course->getId();
			$course->setUpdateSummaries( false );
			$success = $course->enlistUsers( $this->getField( 'user_id' ), $this->getRoleName() ) !== false && $success;
			$course->setUpdateSummaries( true );
		}

		$fieldMap = array(
			'student' => 'student_count',
			'online' => 'oa_count',
			'campus' => 'ca_count',
		);

		$field = $fieldMap[$this->getRoleName()];

		if ( !empty( $courseIds ) ) {
			EPOrgs::singleton()->updateSummaryFields( $field, array( 'id' => array_unique( $courseIds ) ) );
			EPCourses::singleton()->updateSummaryFields( $field, array( 'id' => $courseIds ) );
		}

		return $success;
	}

	/**
	 * Returns the courses this student is enrolled in.
	 * Caches the result when no conditions are provided and all fields are selected.
	 *
	 * @since 0.1
	 *
	 * @param string|array|null $fields
	 * @param array $conditions
	 *
	 * @return array of EPCourse
	 */
	public function getCourses( $fields = null, array $conditions = array() ) {
		if ( count( $conditions ) !== 0 ) {
			return $this->doGetCourses( $fields, $conditions );
		}

		if ( $this->courses === false ) {
			$courses = $this->doGetCourses( $fields, $conditions );

			if ( is_null( $fields ) ) {
				$this->courses = $courses;
			}

			return $courses;
		}
		else {
			return $this->courses;
		}
	}

	/**
	 * Returns if the student has any course matching the provided conditions.
	 *
	 * @since 0.1
	 *
	 * @param array $conditions
	 *
	 * @return boolean
	 */
	public function hasCourse( array $conditions = array() ) {
		$courseTable = EPCourses::singleton();

		return wfGetDB( DB_SLAVE )->select(
			array( 'ep_courses', 'ep_users_per_course' ),
			$courseTable->getPrefixedField( 'id' ),
			array_merge( array(
				'upc_role' => $this->getRoleId(),
				'upc_user_id' => $this->getField( 'user_id' ),
			), $courseTable->getPrefixedValues( $conditions ) ),
			__METHOD__,
			array(
				'LIMIT' => 1
			),
			array(
				'ep_users_per_course' => array( 'INNER JOIN', array( 'upc_course_id=course_id' ) ),
			)
		)->numRows() > 0;
	}
	
	/**
	 * Returns the courses this campus ambassdor is associated with.
	 *
	 * @since 0.1
	 *
	 * @param string|array|null $fields
	 * @param array $conditions
	 *
	 * @return array of EPCourse
	 */
	protected function doGetCourses( $fields, array $conditions ) {
		$courseTable = EPCourses::singleton();
		
		$result = wfGetDB( DB_SLAVE )->select(
			array( 'ep_courses', 'ep_users_per_course' ),
			$courseTable->getPrefixedFields( is_null( $fields ) ? $courseTable->getFieldNames() : (array)$fields ),
			array_merge( array(
				'upc_role' => $this->getRoleId(),
				'upc_user_id' => $this->getField( 'user_id' ),
			), $courseTable->getPrefixedValues( $conditions ) ),
			__METHOD__,
			array(),
			array(
				'ep_users_per_course' => array( 'INNER JOIN', array( 'upc_course_id=course_id' ) ),
			)
		);
		
		$courses = array();
		
		foreach ( $result as $course ) {
			$courses[] = $courseTable->newFromDBResult( $course );
		}
		
		return $courses;
	}

	/**
	 * Returns the role ID for the object by looking it up
	 * in a map using it's name.
	 *
	 * @since 0.1
	 *
	 * @return integer, part of EP_ enum.
	 */
	protected function getRoleId() {
		$map = array(
			'campus' => EP_CA,
			'online' => EP_OA,
			'instructor' => EP_INSTRUCTOR,
			'student' => EP_STUDENT,
		);

		return $map[$this->getRoleName()];
	}

	/**
	 * @see DBDataObject::getUpdateConditions()
	 *
	 * Always adding the user ID to the list of consitions,
	 * even when not loaded yet (a new query will be done),
	 * so that it's not possible to update an existing user
	 * with a wrong user ID.
	 *
	 * @since 0.1
	 *
	 * @return array
	 */
	protected function getUpdateConditions() {
		$conds = parent::getUpdateConditions();

		$conds['user_id'] = $this->loadAndGetField( 'user_id' );

		return $conds;
	}
	
}