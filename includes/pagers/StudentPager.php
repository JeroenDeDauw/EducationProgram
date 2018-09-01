<?php

namespace EducationProgram;

use IContextSource;

/**
 * Student pager, primarily for Special:Students.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 *
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class StudentPager extends EPPager {

	/**
	 * List of user ids mapped to user names and real names, set in doBatchLookups.
	 * The real names will just hold the user name when no real name is set.
	 * user id => array( user name, real name )
	 *
	 * @var array
	 */
	protected $userNames = [];

	/**
	 * List of user ids with the names of their associated courses.
	 * user id => array( course name 0, ... )
	 *
	 * @var array
	 */
	protected $courseTitles = [];

	/**
	 * @param IContextSource $context
	 * @param array $conds
	 */
	public function __construct( IContextSource $context, array $conds = [] ) {
		$this->mDefaultDirection = true;

		// when MW 1.19 becomes min, we want to pass an IContextSource $context here.
		parent::__construct( $context, $conds, Students::singleton() );
	}

	/**
	 * @see Pager::getFields()
	 */
	public function getFields() {
		return [
			'id',
			'user_id',
			'first_enroll',
			'last_active',
			// 'active_enroll',
		];
	}

	/**
	 * @see TablePager::getRowClass()
	 */
	function getRowClass( $row ) {
		return 'ep-student-row';
	}

	/**
	 * @see TablePager::getTableClass()
	 */
	public function getTableClass() {
		return 'TablePager ep-students';
	}

	/**
	 * @see Pager::getFormattedValue()
	 */
	protected function getFormattedValue( $name, $value ) {
		switch ( $name ) {
			case 'user_id':
				if ( array_key_exists( $value, $this->userNames ) ) {
					list( $userName, $realName ) = $this->userNames[$value];
					$displayName = Settings::get( 'useStudentRealNames' ) ? $realName : $userName;

					$retValue = \Linker::userLink( $value, $userName, $displayName )
						. Student::getViewLinksFor( $this->getContext(), $value, $userName );
				} else {
					wfWarn( 'User id not in $this->userNames in ' . __METHOD__ );
				}
				break;
			case 'first_enroll': case 'last_active':
				$retValue = htmlspecialchars( $this->getLanguage()->date( $value ) );
				break;
			case 'active_enroll':
				$msgKey = $value === '1' ? 'epstudentpager-yes' : 'epstudentpager-no';
				$retValue = $this->msg( $msgKey )->escaped();
				break;
			case '_courses_current':
				$userId = $this->currentObject->getField( 'user_id' );

				if ( array_key_exists( $userId, $this->courseTitles ) ) {
					$retValue = $this->getLanguage()->pipeList( array_map(
						function ( $courseTitle ) {
							$titleParts = explode( '/', $courseTitle, 2 );
							return Courses::singleton()->getLinkFor(
								$courseTitle,
								'view',
								htmlspecialchars( array_pop( $titleParts ) )
							);
						},
						$this->courseTitles[$userId]
					) );
				}
				break;
		}

		return $retValue;
	}

	/**
	 * @see Pager::getSortableFields()
	 */
	protected function getSortableFields() {
		return [
			'id',
			'first_enroll',
			'last_active',
			'active_enroll',
		];
	}

	/**
	 * @see EPPager::hasActionsColumn()
	 */
	protected function hasActionsColumn() {
		return false;
	}

	/**
	 * @see Pager::getFieldNames()
	 */
	public function getFieldNames() {
		$fields = parent::getFieldNames();

		unset( $fields['id'] );

		$fields['_courses_current'] = 'current-courses';

		return $fields;
	}

	/**
	 * @see IndexPager::doBatchLookups()
	 */
	protected function doBatchLookups() {
		$userIds = [];
		$field = $this->table->getPrefixedField( 'user_id' );

		foreach ( $this->mResult as $student ) {
			$userIds[] = (int)$student->$field;
		}

		if ( !empty( $userIds ) ) {
			$result = wfGetDB( DB_REPLICA )->select(
				'user',
				[ 'user_id', 'user_name', 'user_real_name' ],
				[ 'user_id' => $userIds ],
				__METHOD__
			);

			foreach ( $result as $user ) {
				$real = $user->user_real_name === '' ? $user->user_name : $user->user_real_name;
				$this->userNames[$user->user_id] = [ $user->user_name, $real ];
			}

			$courseNameField = Courses::singleton()->getPrefixedField( 'title' );

			$result = wfGetDB( DB_REPLICA )->select(
				[ 'ep_courses', 'ep_users_per_course' ],
				[ $courseNameField, 'upc_user_id' ],
				array_merge( [
					'upc_role' => EP_STUDENT,
					'upc_user_id' => $userIds,
				], Courses::getStatusConds( 'current', true ) ),
				__METHOD__,
				[],
				[
					'ep_users_per_course' => [ 'INNER JOIN', [ 'upc_course_id=course_id' ] ],
				]
			);

			foreach ( $result as $courseForUser ) {
				if ( !array_key_exists( $courseForUser->upc_user_id, $this->courseTitles ) ) {
					$this->courseTitles[$courseForUser->upc_user_id] = [];
				}

				$this->courseTitles[$courseForUser->upc_user_id][] = $courseForUser->$courseNameField;
			}
		}
	}

}
