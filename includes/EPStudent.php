<?php

/**
 * Class representing a single student.
 *
 * @since 0.1
 *
 * @file EPStudent.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v3 or later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EPStudent extends EPDBObject {

	/**
	 * Cached array of the linked EPCourse objects.
	 *
	 * @since 0.1
	 * @var array|false
	 */
	protected $courses = false;

	/**
	 * Cached user object of the user that is this student.
	 *
	 * @since 0.1
	 * @var User|false
	 */
	protected $user = false;

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
			'user_id' => 'id',

			'first_enroll' => 'str', // TS_MW

			'last_active' => 'str', // TS_MW
			'active_enroll' => 'bool',
		);
	}

	/**
	 * Get the student object of a user, or false if there is none.
	 *
	 * @since 0.1
	 *
	 * @param User $user
	 * @param string|array|null $fields
	 *
	 * @return EPStudent|false
	 */
	public static function newFromUser( User $user, $fields = null ) {
		return self::selectRow( $fields, array( 'user_id' => $user->getId() ) );
	}

	/**
	 * Associate the student with the provided courses.
	 *
	 * @since 0.1
	 *
	 * @param array $courses
	 *
	 * @return bool
	 */
	public function associateWithCourses( array /* of EPCourse */ $courses ) {
		$dbw = wfGetDB( DB_MASTER );

		$success = true;

		$dbw->begin();

		foreach ( $courses as /* EPCourse */ $course ) {
			$success = $dbw->insert(
				'ep_students_per_course',
				array(
					'spc_student_id' => $this->getId(),
					'spc_term_id' => $course->getId(),
				)
			) && $success;
		}

		$dbw->commit();

		foreach ( $courses as /* EPCourse */ $course ) {
			EPMC::updateSummaryFields( 'students', array( 'id' => $course->getField( 'mc_id' ) ) );
			EPOrg::updateSummaryFields( 'students', array( 'id' => $course->getField( 'org_id' ) ) );
			EPCourse::updateSummaryFields( 'students', array( 'id' => $this->getId() ) );
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
	 * Returns the master courses this student is linked to (via courses).
	 *
	 * @since 0.1
	 *
	 * @param string|null|array $fields
	 * @param array $conditions
	 * @param array $courseConditions
	 *
	 * @return array of EPMC
	 */
	public function getMasterCourses( $fields = null, array $conditions = array(), array $courseConditions = array() ) {
		$mcIds = array_reduce(
			$this->getCourses( 'course_id', $courseConditions ),
			function( array $ids, EPCourse $term ) {
				$ids[] = $term->getField( 'mc_id' );
				return $ids;
			},
			array()
		);

		if ( count( $mcIds ) < 1 ) {
			return array();
		}

		$conditions['id'] = array_unique( $mcIds );

		return EPMC::select( $fields, $conditions );
	}

	/**
	 * Returns the master courses this student is currently enrolled in.
	 *
	 * @since 0.1
	 *
	 * @param string|null|array $fields
	 * @param array $conditions
	 *
	 * @return array of EPMC
	 */
	public function getCurrentMasterCourses( $fields = null, array $conditions = array() ) {
		$conditions['active'] = 1;
		return $this->getMasterCourses( $fields, $conditions );
	}

	/**
	 * Returns the master courses this student was previously enrolled in.
	 *
	 * @since 0.1
	 *
	 * @param string|null|array $fields
	 * @param array $conditions
	 *
	 * @return array of EPMC
	 */
	public function getPassedMasterCourses( $fields = null, array $conditions = array() ) {
		$conditions['active'] = 0;
		return $this->getMasterCourses( $fields, $conditions );
	}

	/**
	 * Returns the courses this student is enrolled in.
	 *
	 * @since 0.1
	 *
	 * @param string|array|null $fields
	 * @param array $conditions
	 *
	 * @return array of EPCourse
	 */
	protected function doGetCourses( $fields, array $conditions ) {
		$conditions[] = array( array( 'ep_students', 'id' ), $this->getId() );

		return EPCourse::select(
			$fields,
			$conditions,
			array(),
			array(
				'ep_students_per_course' => array( 'INNER JOIN', array( array( array( 'ep_students_per_course', 'course_id' ), array( 'ep_courses', 'id' ) ) ) ),
				'ep_students' => array( 'INNER JOIN', array( array( array( 'ep_students_per_course', 'student_id' ), array( 'ep_students', 'id' ) ) ) )
			)
		);
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
	 * Display a pager with students.
	 *
	 * @since 0.1
	 *
	 * @param IContextSource $context
	 * @param array $conditions
	 */
	public static function displayPager( IContextSource $context, array $conditions = array() ) {
		$pager = new EPStudentPager( $context, $conditions );

		if ( $pager->getNumRows() ) {
			$context->getOutput()->addHTML(
				$pager->getFilterControl() .
					$pager->getNavigationBar() .
					$pager->getBody() .
					$pager->getNavigationBar() .
					$pager->getMultipleItemControl()
			);
		}
		else {
			$context->getOutput()->addHTML( $pager->getFilterControl( true ) );
			$context->getOutput()->addWikiMsg( 'ep-students-noresults' );
		}
	}

	/**
	 * Returns the user that is this student.
	 *
	 * @since 0.1
	 *
	 * @return User
	 */
	public function getUser() {
		if ( $this->user === false ) {
			$this->user = User::newFromId( $this->loadAndGetField( 'user_id' ) );
		}

		return $this->user;
	}

	/**
	 * Returns the display name for the student.
	 *
	 * @since 0.1
	 *
	 * @return String
	 */
	public function getName() {
		return $this->getUser()->getRealName() === '' ? $this->user->getName() : $this->user->getRealName();
	}

}
