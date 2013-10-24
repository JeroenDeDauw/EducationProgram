<?php

namespace EducationProgram;
use User, IContextSource;

/**
 * Object representing a user in a certain role linked to courses.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
abstract class RoleObject extends \ORMRow implements IRole {

	/**
	 * Field for caching the linked user.
	 *
	 * @since 0.1
	 * @var User|bool false
	 */
	protected $user = false;

	/**
	 * Cached array of the linked Course objects.
	 *
	 * @since 0.1
	 * @var Course[]|bool false
	 */
	protected $courses = false;

	/**
	 * Create a new role object from a user id.
	 *
	 * @since 0.1
	 *
	 * @param integer $userId
	 * @param boolean $load If the object should be loaded from the db if it already exists
	 * @param null|array|string $fields Fields to load
	 *
	 * @return RoleObject
	 */
	public static function newFromUserId( $userId, $load = false, $fields = null ) {
		$data = array( 'user_id' => $userId );

		$map = array(
			'EducationProgram\OA' => 'EducationProgram\OAs',
			'EducationProgram\CA' => 'EducationProgram\CAs',
			'EducationProgram\Student' => 'EducationProgram\Students',
			'EducationProgram\Instructor' => 'EducationProgram\Instructors',
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
	 * @return RoleObject
	 */
	public static function newFromUser( User $user, $load = false, $fields = null ) {
		return static::newFromUserId( $user->getId(), $load, $fields );
	}

	/**
	 * Returns the user.
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
	 * Returns the name of the user, possibly using their real name when available.
	 *
	 * @since 0.1
	 *
	 * @return string
	 */
	public function getName() {
		return !Settings::get( 'useStudentRealNames' ) || $this->getUser()->getRealName() === '' ?
			$this->getUser()->getName() : $this->getUser()->getRealName();
	}

	/**
	 * Returns the tool links for this ambassador.
	 *
	 * @since 0.1
	 *
	 * @param IContextSource $context
	 * @param Course|null $course
	 *
	 * @return string
	 */
	public function getToolLinks( IContextSource $context, Course $course = null ) {
		return Utils::getRoleToolLinks( $this, $context, $course );
	}

	/**
	 * Retruns the user link for this ambassador, using their real name when available.
	 *
	 * @since 0.1
	 *
	 * @return string
	 */
	public function getUserLink() {
		return \Linker::userLink(
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
	 * @param RevisionAction|null $revAction
	 *
	 * @return bool Success indicator
	 */
	public function associateWithCourses( array /* of Course */ $courses, RevisionAction $revAction = null ) {
		$success = true;

		$courseIds = array();

		/**
		 * @var Course $course
		 */
		foreach ( $courses as $course ) {
			$courseIds[] = $course->getId();
			$course->setUpdateSummaries( false );

			$success = $course->enlistUsers(
				$this->getField( 'user_id' ),
				$this->getRoleName(),
				true,
				$revAction
			) !== false && $success;

			$course->setUpdateSummaries( true );
		}

		$fieldMap = array(
			'student' => 'student_count',
			'online' => 'oa_count',
			'campus' => 'ca_count',
		);

		$field = $fieldMap[$this->getRoleName()];

		if ( !empty( $courseIds ) ) {
			Orgs::singleton()->updateSummaryFields( $field, array( 'id' => array_unique( $courseIds ) ) );
			Courses::singleton()->updateSummaryFields( $field, array( 'id' => $courseIds ) );
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
	 * @return Course[]
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
		$courseTable = Courses::singleton();

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
	 * Returns the courses this campus ambassador is associated with.
	 *
	 * @since 0.1
	 *
	 * @param string|array|null $fields
	 * @param array $conditions
	 *
	 * @return Course[]
	 */
	protected function doGetCourses( $fields, array $conditions ) {
		return iterator_to_array( Courses::singleton()->getCoursesForUsers(
			$this->getField( 'user_id' ),
			$this->getRoleId(),
			$conditions,
			$fields
		) );
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
	 * @see ORMRow::getUpdateConditions()
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

	/**
	 * Should be called whenever a user gets enrolled in a course.
	 *
	 * @since 0.3
	 *
	 * @param integer $courseId
	 * @param string $role
	 */
	public function onEnrolled( $courseId, $role ) {
		if ( $role === 'student' ) {
			if ( !$this->hasField( 'first_course' ) ) {
				$this->setField( 'first_course', $courseId );
				$this->setField( 'first_enroll', wfTimestampNow() );
			}

			$this->setField( 'last_course', $courseId );
			$this->setField( 'last_enroll', wfTimestampNow() );
			$this->setField( 'last_active', wfTimestampNow() );
			$this->setField( 'active_enroll', true );
		}

		$this->getUser()->setOption( 'ep_showtoplink', true );
		$this->getUser()->saveSettings();
	}

	/**
	 * Convenience method for checking if any RoleObjects in $roleObjectArray
	 * refer to the user with the id $userId.
	 *
	 * @param int $userId
	 * @param RoleObject $roleObjectArray
	 * @return boolean
	 */
	public static function isInRoleObjArray ( $userId, $roleObjectArray ) {
		foreach ( $roleObjectArray as $roleObject ) {
			if ( $userId === $roleObject->getUser()->getId() ) {
				return true;
			}
		}

		return false;
	}
}
