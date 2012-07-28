<?php

/**
 * Student pager, primarily for Special:Students.
 *
 * @since 0.1
 *
 * @file EPStudentPager.php
 * @ingroup EductaionProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EPStudentPager extends EPPager {

	/**
	 * List of user ids mapped to user names and real names, set in doBatchLookups.
	 * The real names will just hold the user name when no real name is set.
	 * user id => array( user name, real name )
	 *
	 * @since 0.1
	 * @var array
	 */
	protected $userNames = array();

	/**
	 * List of user ids with the names of their associated courses.
	 * user id => array( course name 0, ... )
	 *
	 * @since 0.1
	 * @var array
	 */
	protected $courseNames = array();

	/**
	 * Constructor.
	 *
	 * @param IContextSource $context
	 * @param array $conds
	 */
	public function __construct( IContextSource $context, array $conds = array() ) {
		$this->mDefaultDirection = true;

		// when MW 1.19 becomes min, we want to pass an IContextSource $context here.
		parent::__construct( $context, $conds, EPStudents::singleton() );
	}

	/**
	 * (non-PHPdoc)
	 * @see EPPager::getFields()
	 */
	public function getFields() {
		return array(
			'id',
			'user_id',
			'first_enroll',
			'last_active',
			//'active_enroll',
		);
	}

	/**
	 * (non-PHPdoc)
	 * @see TablePager::getRowClass()
	 */
	function getRowClass( $row ) {
		return 'ep-student-row';
	}

	/**
	 * (non-PHPdoc)
	 * @see TablePager::getTableClass()
	 */
	public function getTableClass() {
		return 'TablePager ep-students';
	}

	/**
	 * (non-PHPdoc)
	 * @see EPPager::getFormattedValue()
	 */
	protected function getFormattedValue( $name, $value ) {
		switch ( $name ) {
			case 'user_id':
				if ( array_key_exists( $value, $this->userNames ) ) {
					list( $userName, $realName ) = $this->userNames[$value];
					$displayName = EPSettings::get( 'useStudentRealNames' ) ? $realName : $userName;

					$value = Linker::userLink( $value, $userName, $displayName )
						. EPStudent::getViewLinksFor( $this->getContext(), $value, $userName );
				}
				else {
					wfWarn( 'User id not in $this->userNames in ' . __METHOD__ );
				}
				break;
			case 'first_enroll': case 'last_active':
				$value = htmlspecialchars( $this->getLanguage()->date( $value ) );
				break;
			case 'active_enroll':
				$value = wfMsgHtml( $value === '1' ? 'epstudentpager-yes' : 'epstudentpager-no' );
				break;
			case '_courses_current':
				// TODO
//				$userId = $this->currentObject->getField( 'user_id' );
//
//				if ( array_key_exists( $userId, $this->courseNames ) ) {
//					$value = $this->getLanguage()->pipeList( array_map(
//						function( $courseName ) {
//							return EPCourses::singleton()->getLinkFor( $courseName );
//						},
//						$this->courseNames[$userId]
//					) );
//				}
				break;
		}

		return $value;
	}

	/**
	 * (non-PHPdoc)
	 * @see EPPager::getSortableFields()
	 */
	protected function getSortableFields() {
		return array(
			'id',
			'first_enroll',
			'last_active',
			'active_enroll',
		);
	}

	/**
	 * (non-PHPdoc)
	 * @see EPPager::hasActionsColumn()
	 */
	protected function hasActionsColumn() {
		return false;
	}

	/**
	 * (non-PHPdoc)
	 * @see EPPager::getFieldNames()
	 */
	public function getFieldNames() {
		$fields = parent::getFieldNames();

		unset( $fields['id'] );

		$fields['_courses_current'] = 'current-courses';

		return $fields;
	}

	/**
	 * (non-PHPdoc)
	 * @see IndexPager::doBatchLookups()
	 */
	protected function doBatchLookups() {
		$userIds = array();
		$field = $this->table->getPrefixedField( 'user_id' );

		while( $student = $this->mResult->fetchObject() ) {
			$userIds[] = (int)$student->$field;
		}

		if ( !empty( $userIds ) ) {
			$result = wfGetDB( DB_SLAVE )->select(
				'user',
				array( 'user_id', 'user_name', 'user_real_name' ),
				array( 'user_id' => $userIds ),
				__METHOD__
			);

			while( $user = $result->fetchObject() ) {
				$real = $user->user_real_name === '' ? $user->user_name : $user->user_real_name;
				$this->userNames[$user->user_id] = array( $user->user_name, $real );
			}

			$courseNameField = EPCourses::singleton()->getPrefixedField( 'name' );

			$result = wfGetDB( DB_SLAVE )->select(
				array( 'ep_courses', 'ep_users_per_course' ),
				array( $courseNameField, 'upc_user_id' ),
				array_merge( array(
					'upc_role' => EP_STUDENT,
					'upc_user_id' => $userIds,
				), EPCourses::getStatusConds( 'current', true ) ) ,
				__METHOD__,
				array(),
				array(
					'ep_users_per_course' => array( 'INNER JOIN', array( 'upc_course_id=course_id' ) ),
				)
			);

			while( $courseForUser = $result->fetchObject() ) {
				if ( !array_key_exists( $courseForUser->upc_user_id, $this->courseNames ) ) {
					$this->courseNames[$courseForUser->upc_user_id] = array();
				}

				$this->courseNames[$courseForUser->upc_user_id][] = $courseForUser->$courseNameField;
			}
		}
	}

}
