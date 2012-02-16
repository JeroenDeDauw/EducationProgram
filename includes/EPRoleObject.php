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
	 * @param null|array|string $fields
	 * 
	 * @return EPRoleObject
	 */
	public static function newFromUserId( $userId, $fields = null ) {
		$data = array( 'user_id' => $userId );
		
		$map = array(
			'EPOA' => 'EPOAs',
			'EPCA' => 'EPCAs',
			'EPStudent' => 'EPStudents',
			'EPInstructor' => 'EPInstructors',
		); // TODO: this is lame
		
		$class = $map[get_called_class()];
		$table = $class::singleton();
		
		$userRole = $table->selectRow( null, $data );
		return $userRole === false ? new static( $table, $data, true ) : $userRole;
	}
	
	/**
	 * Create a new instructor object from a User object.
	 * 
	 * @since 0.1
	 * 
	 * @param User $user
	 * @param null|array|string $fields
	 * 
	 * @return EPRoleObject
	 */
	public static function newFromUser( User $user, $fields = null ) {
		return static::newFromUserId( $user->getId(), $fields );
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

		foreach ( $courses as /* EPCourse */ $course ) {
			$courseIds[] = $course->getId();
			$course->setUpdateSummaries( false );
			$course->enlistUsers( $this->getField( 'user_id' ), $this->getRoleName() );
			$course->setUpdateSummaries( true );
		}

		$fieldMap = array(
			'student' => 'student_count',
			'online' => 'oa_count',
			'campus' => 'ca_count',
		);

		$field = $fieldMap[$this->getRoleName()];

		if ( count( $courseIds ) > 0 ) {
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
	 * Get the courses with a certain state.
	 * States can be 'current', 'passed' and 'planned'
	 *
	 * @since 0.1
	 *
	 * @param string $state
	 * @param array|null $fields
	 * @param array $conditions
	 *
	 * @return array of EPCourse
	 */
	public function getCoursesWithState( $state, $fields = null, array $conditions = array() ) {
		$now = wfGetDB( DB_SLAVE )->addQuotes( wfTimestampNow() );

		switch ( $state ) {
			case 'passed':
				$conditions[] = 'end < ' . $now;
				break;
			case 'planned':
				$conditions[] = 'start > ' . $now;
				break;
			case 'current':
				$conditions[] = 'end >= ' . $now;
				$conditions[] = 'start <= ' . $now;
				break;
		}

		return $this->getCourses( $fields, $conditions );
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
		return count( $this->getCourses( 'id', $conditions ) ) > 0;
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
			array(
				'upc_role' => $this->getRoleId(),
				'upc_user_id' => $this->getField( 'user_id' ),
			),
			__METHOD__,
			array(),
			array(
				'ep_users_per_course' => array( 'INNER JOIN', array( 'upc_course_id=course_id' ) ),
			)
		);
		
		$courses = array();
		
		foreach ( $result as $course ) {
			$courses[] = EPCourses::singleton()->newFromDBResult( $course );
		}
		
		return $courses;
	}
	
	protected function getRoleId() {
		$map = array(
			'campus' => EP_CA,
			'online' => EP_OA,
			'instructor' => EP_INSTRUCTOR,
			'student' => EP_STUDENT,
		);

		return $map[$this->getRoleName()];
	}
	
}