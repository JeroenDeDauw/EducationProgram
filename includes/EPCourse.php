<?php

/**
 * Class representing a single course.
 *
 * @since 0.1
 *
 * @file EPCourse.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v3 or later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EPCourse extends EPPageObject {

	/**
	 * Field for caching the linked org.
	 *
	 * @since 0.1
	 * @var EPOrg|false
	 */
	protected $org = false;

	/**
	 * Cached array of the linked EPStudent objects.
	 *
	 * @since 0.1
	 * @var array|false
	 */
	protected $students = false;

	/**
	 * Field for caching the instructors.
	 *
	 * @since 0.1
	 * @var {array of EPInstructor}|false
	 */
	protected $instructors = false;
	
	/**
	 * Field for caching the online ambassaords.
	 *
	 * @since 0.1
	 * @var {array of EPOA}|false
	 */
	protected $oas = false;
	
		/**
	 * Field for caching the campus ambassaords.
	 *
	 * @since 0.1
	 * @var {array of EPCA}|false
	 */
	protected $cas = false;

	/**
	 * Returns a list of statuses a term can have.
	 * Keys are messages, values are identifiers.
	 *
	 * @since 0.1
	 *
	 * @return array
	 */
	public static function getStatuses() {
		return array(
			wfMsg( 'ep-course-status-passed' ) => 'passed',
			wfMsg( 'ep-course-status-current' ) => 'current',
			wfMsg( 'ep-course-status-planned' ) => 'planned',
		);
	}

	/**
	 * Returns the message for the provided status identifier.
	 *
	 * @since 0.1
	 *
	 * @param string $status
	 *
	 * @return string
	 */
	public static function getStatusMessage( $status ) {
		static $map = null;

		if ( is_null( $map ) ) {
			$map = array_flip( self::getStatuses() );
		}

		return $map[$status];
	}

	protected static $countMap = array(
		'student_count' => 'students',
		'instructor_count' => 'instructors',
		'oa_count' => 'online_ambs',
		'ca_count' => 'campus_ambs',
	);

	/**
	 * (non-PHPdoc)
	 * @see DBDataObject::loadSummaryFields()
	 */
	public function loadSummaryFields( $summaryFields = null ) {
		if ( is_null( $summaryFields ) ) {
			$summaryFields = array( 'org_id' );
		}
		else {
			$summaryFields = (array)$summaryFields;
		}

		$fields = array();

		if ( in_array( 'org_id', $summaryFields ) ) {
			$fields['org_id'] = $this->getField( 'org_id' );
		}

		$this->setFields( $fields );
	}

	/**
	 * (non-PHPdoc)
	 * @see DBDataObject::insert()
	 */
	protected function insert() {
		$success = parent::insert();

		if ( $success && $this->updateSummaries ) {
			EPOrg::updateSummaryFields( array( 'course_count', 'active' ), array( 'id' => $this->getField( 'org_id' ) ) );
		}

		return $success;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see EPRevisionedObject::onRemoved()
	 */
	protected function onRemoved() {
		if ( $this->updateSummaries ) {
			EPOrg::updateSummaryFields( null, array( 'id' => $this->getField( 'org_id' ) ) );
		}

		wfGetDB( DB_MASTER )->delete( 'ep_users_per_course', array( 'upc_course_id' => $this->getId() ) );

		parent::onRemoved();
	}

	/**
	 * (non-PHPdoc)
	 * @see EPRevisionedObject::onUpdated()
	 */
	protected function onUpdated( EPRevisionedObject $originalCourse ) {
		$newUsers = array();
		$changedSummaries = array();

		$roleMap = array(
			'online_ambs' => EP_OA,
			'campus_ambs' => EP_CA,
			'students' => EP_STUDENT,
			'instructors' => EP_INSTRUCTOR,
		);

		$countMap = array_flip( self::$countMap );

		$dbw = wfGetDB( DB_MASTER );

		foreach ( array( 'online_ambs', 'campus_ambs', 'students', 'instructors' ) as $usersField ) {
			if ( $this->hasField( $usersField ) && $originalCourse->getField( $usersField ) !== $this->getField( $usersField ) ) {
				$removedIds = array_diff( $originalCourse->getField( $usersField ), $this->getField( $usersField ) );
				$addedIds = array_diff( $this->getField( $usersField ), $originalCourse->getField( $usersField ) );

				foreach ( $addedIds as $addedId ) {
					$newUsers[] = array(
						'upc_course_id' => $this->getId(),
						'upc_user_id' => $addedId,
						'upc_role' => $roleMap[$usersField],
					);
				}

				if ( !empty( $removedIds ) || !empty( $addedIds ) ) {
					$changedSummaries[] = $countMap[$usersField];
				}

				if ( count( $removedIds ) > 0 ) {
					$dbw->delete( 'ep_users_per_course', array(
						'upc_course_id' => $this->getId(),
						'upc_user_id' => $removedIds,
						'upc_role' => $roleMap[$usersField],
					) );
				}
			}
		}

		if ( count( $newUsers ) > 0 ) {
			$dbw->begin();

			foreach ( $newUsers as $userLink ) {
				$dbw->insert( 'ep_users_per_course', $userLink );
			}

			$dbw->commit();
		}

		if ( $this->updateSummaries ) {
			if ( $this->hasField( 'org_id' ) && $originalCourse->getField( 'org_id' ) !== $this->getField( 'org_id' ) ) {
				$conds = array( 'id' => array( $originalCourse->getField( 'org_id' ), $this->getField( 'org_id' ) ) );
				EPOrg::updateSummaryFields( null, $conds );
			}
			else if ( !empty( $changedSummaries ) ) {
				EPOrg::updateSummaryFields( $changedSummaries, array( 'id' => $originalCourse->getField( 'org_id' ) ) );
			}
		}

		parent::onUpdated( $originalCourse );
	}

	/**
	 * (non-PHPdoc)
	 * @see DBDataObject::save()
	 */
	public function save() {
		if ( $this->hasField( 'name' ) ) {
			$this->setField( 'name', $GLOBALS['wgLang']->ucfirst( $this->getField( 'name' ) ) );
		}

		foreach ( array( 'student_count', 'instructor_count', 'oa_count', 'ca_count' ) as $summaryField ) {
			$field = self::$countMap[$summaryField];
			if ( $this->hasField( $field ) ) {
				$this->setField( $summaryField, count( $this->getField( $field ) ) );
			}
		}

		return parent::save();
	}

	/**
	 * Returns the org associated with this term.
	 *
	 * @since 0.1
	 *
	 * @param string|array|null $fields
	 *
	 * @return EPOrg
	 */
	public function getOrg( $fields = null ) {
		if ( $this->org === false ) {
			$this->org = EPOrg::selectRow( $fields, array( 'id' => $this->loadAndGetField( 'org_id' ) ) );
		}

		return $this->org;
	}

	/**
	 * Display a pager with terms.
	 *
	 * @since 0.1
	 *
	 * @param IContextSource $context
	 * @param array $conditions
	 * @param boolean $readOnlyMode
	 * @param string|false $filterPrefix
	 */
	public static function displayPager( IContextSource $context, array $conditions = array(), $readOnlyMode = false, $filterPrefix = false ) {
		$pager = new EPCoursePager( $context, $conditions, $readOnlyMode );

		if ( $filterPrefix !== false ) {
			$pager->setFilterPrefix( $filterPrefix );
		}
		
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
			$context->getOutput()->addWikiMsg( 'ep-courses-noresults' );
		}
	}

	/**
	 * Adds a control to add a term org to the provided context.
	 * Additional arguments can be provided to set the default values for the control fields.
	 *
	 * @since 0.1
	 *
	 * @param IContextSource $context
	 * @param array $args
	 *
	 * @return boolean
	 */
	public static function displayAddNewControl( IContextSource $context, array $args ) {
		if ( !$context->getUser()->isAllowed( 'ep-course' ) ) {
			return false;
		}

		$out = $context->getOutput();
		
		$out->addModules( 'ep.addcourse' );

		$out->addHTML( Html::openElement(
			'form',
			array(
				'method' => 'post',
				'action' => self::getTitleFor( 'NAME_PLACEHOLDER' )->getLocalURL( array( 'action' => 'edit' ) ),
			)
		) );

		$out->addHTML( '<fieldset>' );

		$out->addHTML( '<legend>' . wfMsgHtml( 'ep-courses-addnew' ) . '</legend>' );

		$out->addElement( 'p', array(), wfMsg( 'ep-courses-namedoc' ) );

		$out->addElement( 'label', array( 'for' => 'neworg' ), wfMsg( 'ep-courses-neworg' ) );

		$select = new XmlSelect(
			'neworg',
			'neworg',
			array_key_exists( 'org', $args ) ? $args['org'] : false
		);

		$select->addOptions( EPOrg::getOrgOptions() );
		$out->addHTML( $select->getHTML() );

		$out->addHTML( '&#160;' . Xml::inputLabel(
			wfMsg( 'ep-courses-newname' ),
			'newname',
			'newname',
			20,
			array_key_exists( 'name', $args ) ? $args['name'] : false
		) );

		$out->addHTML( '&#160;' . Xml::inputLabel(
			wfMsg( 'ep-courses-newterm' ),
			'newterm',
			'newterm',
			10,
			array_key_exists( 'term', $args ) ? $args['term'] : false
		) );

		$out->addHTML( '&#160;' . Html::input(
			'addnewcourse',
			wfMsg( 'ep-courses-add' ),
			'submit',
			array(
				'disabled' => 'disabled',
				'class' => 'ep-course-add',
			)
		) );

		$out->addHTML( Html::hidden( 'isnew', 1 ) );

		$out->addHTML( '</fieldset></form>' );

		return true;
	}

	/**
	 * Adds a control to add a new term to the provided context
	 * or show a message if this is not possible for some reason.
	 *
	 * @since 0.1
	 *
	 * @param IContextSource $context
	 * @param array $args
	 */
	public static function displayAddNewRegion( IContextSource $context, array $args = array() ) {
		if ( EPOrg::has() ) {
			EPCourse::displayAddNewControl( $context, $args );
		}
		elseif ( $context->getUser()->isAllowed( 'ep-course' ) ) {
			$context->getOutput()->addWikiMsg( 'ep-courses-addorgfirst' );
		}
	}

	/**
	 * Gets the amount of days left, rounded up to the nearest integer.
	 *
	 * @since 0.1
	 *
	 * @return integer
	 */
	public function getDaysLeft() {
		$timeLeft = (int)wfTimestamp( TS_UNIX, $this->getField( 'end' ) ) - time();
		return (int)ceil( $timeLeft / ( 60 * 60 * 24 ) );
	}

	/**
	 * Gets the amount of days since term start, rounded up to the nearest integer.
	 *
	 * @since 0.1
	 *
	 * @return integer
	 */
	public function getDaysPassed() {
		$daysPassed = time() - (int)wfTimestamp( TS_UNIX, $this->getField( 'start' ) );
		return (int)ceil( $daysPassed / ( 60 * 60 * 24 ) );
	}

	/**
	 * Returns the status of the course.
	 *
	 * @since 0.1
	 *
	 * @return string
	 */
	public function getStatus() {
		if ( $this->getDaysLeft() <= 0 ) {
			$status = 'passed';
		}
		elseif ( $this->getDaysPassed() <= 0 ) {
			$status = 'planned';
		}
		else {
			$status = 'current';
		}

		return $status;
	}

	/**
	 * Returns the students as a list of EPStudent objects.
	 *
	 * @since 0.1
	 *
	 * @return array of EPStudent
	 */
	public function getStudents() {
		if ( $this->students === false ) {
			$this->students = array();

			foreach ( $this->getField( 'students' ) as $userId ) {
				$this->students[] = EPStudent::newFromUserId( $userId );
			}
		}

		return $this->students;
	}

	/**
	 * Returns the instructors as a list of EPInstructor objects.
	 *
	 * @since 0.1
	 *
	 * @return array of EPInstructor
	 */
	public function getInstructors() {
		if ( $this->instructors === false ) {
			$this->instructors = array();

			foreach ( $this->getField( 'instructors' ) as $userId ) {
				$this->instructors[] = EPInstructor::newFromUserId( $userId );
			}
		}

		return $this->instructors;
	}
	
	/**
	 * Returns the campus ambassadors as a list of EPCA objects.
	 *
	 * @since 0.1
	 *
	 * @return array of EPCA
	 */
	public function getCampusAmbassadors() {
		if ( $this->cas === false ) {
			$this->cas = array();

			foreach ( $this->getField( 'campus_ambs' ) as $userId ) {
				$this->cas[] = EPCA::newFromUserId( $userId );
			}
		}

		return $this->cas;
	}
	
	/**
	 * Returns the online ambassadors as a list of EPOA objects.
	 *
	 * @since 0.1
	 *
	 * @return array of EPOA
	 */
	public function getOnlineAmbassadors() {
		if ( $this->oas === false ) {
			$this->oas = array();

			foreach ( $this->getField( 'online_ambs' ) as $userId ) {
				$this->oas[] = EPOA::newFromUserId( $userId );
			}
		}

		return $this->oas;
	}
	
	/**
	 * Returns the users that have a certain role as list of EPIRole objects.
	 * 
	 * @since 0.1
	 * 
	 * @param string $roleName
	 * 
	 * @return array of EPIRole
	 * @throws MWException
	 */
	public function getUserWithRole( $roleName ) {
		switch ( $roleName ) {
			case 'instructor':
				return $this->getInstructors();
				break;
			case 'online':
				return $this->getOnlineAmbassadors();
				break;
			case 'campus':
				return $this->getCampusAmbassadors();
				break;
			case 'student':
				return $this->getStudents();
				break;
		}
		
		throw new MWException( 'Invalid role name: ' . $roleName );
	}

	/**
	 * (non-PHPdoc)
	 * @see DBDataObject::setField()
	 */
	public function setField( $name, $value ) {
		if ( $name === 'instructors' ) {
			$this->instructors = false;
		}

		parent::setField( $name, $value );
	}

	/**
	 * Adds a role for a number of users to this course,
	 * by default also saving the course and only
	 * logging the adittion of the users/roles.
	 *
	 * @since 0.1
	 *
	 * @param array|integer $newUsers
	 * @param string $role
	 * @param string $message
	 * @param boolean $save
	 * @param boolean $log
	 *
	 * @return boolean Success indicator
	 */
	public function enlistUsers( $newUsers, $role, $message = '', $save = true, $log = true ) {
		$roleMap = array(
			'student' => 'students',
			'campus' => 'campus_ambs',
			'online' => 'online_ambs',
			'instructor' => 'instructors',
		);

		$field = $roleMap[$role];
		$users = $this->getField( $field );
		$addedUsers = array();

		foreach ( (array)$newUsers as $userId ) {
			if ( !is_integer( $userId ) ) {
				throw new MWException( 'Provided user id is not an integer' );
			}
			
			if ( !in_array( $userId, $users ) ) {
				$users[] = $userId;
				$addedUsers[] = $userId;
			}
		}

		if ( count( $addedUsers ) > 0 ) {
			$this->setField( $field, $users );

			$success = true;

			if ( $save ) {
				$this->disableLogging();
				$success = $this->save();
				$this->enableLogging();
			}

			if ( $success && $log ) {
				$this->logRoleChange( 'add', $role, $addedUsers, $message );
			}

			return $success;
		}
		else {
			return true;
		}
	}

	/**
	 * Remove the role for a number of users for this course,
	 * by default also saving the course and only
	 * logging the role changes.
	 *
	 * @since 0.1
	 *
	 * @param array|integer $sadUsers
	 * @param string $role
	 * @param string $message
	 * @param boolean $save
	 * @param boolean $log
	 *
	 * @return boolean Success indicator
	 */
	public function unenlistUsers( $sadUsers, $role, $message = '', $save = true, $log = true ) {
		$removedUser = array();
		$remaimingUsers = array();
		$sadUsers = (array)$sadUsers;

		$roleMap = array(
			'student' => 'students',
			'campus' => 'campus_ambs',
			'online' => 'online_ambs',
			'instructor' => 'instructors',
		);

		$field = $roleMap[$role];
		
		foreach ( $this->getField( $field ) as $userId ) {
			if ( in_array( $userId, $sadUsers ) ) {
				$removedUser[] = $userId;
			}
			else {
				$remaimingUsers[] = $userId;
			}
		}

		if ( count( $removedUser ) > 0 ) {
			$this->setField( $field, $remaimingUsers );

			$success = true;

			if ( $save ) {
				$this->disableLogging();
				$success = $this->save();
				$this->enableLogging();
			}

			if ( $success && $log ) {
				$this->logRoleChange( 'remove', $role, $removedUser, $message );
			}

			return $success;
		}
		else {
			return true;
		}
	}

	/**
	 * Log a change of the instructors of the course.
	 *
	 * @since 0.1
	 *
	 * @param string $action
	 * @param string $role
	 * @param array $users
	 * @param string $message
	 */
	protected function logRoleChange( $action, $role, array $users, $message ) {
		$names = array();

		$classes = array(
			'instructor' => 'EPInstructor',
			'campus' => 'EPCA',
			'online' => 'EPOA',
			'student' => 'EPStudent',
		);
		
		$class = $classes[$role];
		
		foreach ( $users as $userId ) {
			$names[] = $class::newFromUserId( $userId )->getName();
		}

		$info = array(
			'type' => $role,
			'subtype' => $action,
			'title' => $this->getTitle(),
			'parameters' => array(
				'4::instructorcount' => count( $names ),
				'5::instructors' => $GLOBALS['wgLang']->listToText( $names )
			),
		);

		if ( $message !== '' ) {
			$info['comment'] = $message;
		}

		EPUtils::log( $info );
	}

	public static function hasActiveName( $courseName ) {
		$now = wfGetDB( DB_SLAVE )->addQuotes( wfTimestampNow() );

		return self::has( array(
			'name' => $courseName,
			'end >= ' . $now,
			'start <= ' . $now,
		) );
	}
	
}
