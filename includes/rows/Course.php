<?php

namespace EducationProgram;
use IContextSource, Xml, Html, IORMTable, MWException;

/**
 * Class representing a single course.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class Course extends PageObject {
	/**
	 * Field for caching the linked org.
	 *
	 * @since 0.1
	 * @var Org|bool false
	 */
	protected $org = false;

	/**
	 * Cached array of the linked Student objects.
	 *
	 * @since 0.1
	 * @var Student[]|bool false
	 */
	protected $students = false;

	/**
	 * Field for caching the instructors.
	 *
	 * @since 0.1
	 * @var Instructor[]|bool false
	 */
	protected $instructors = false;

	/**
	 * Field for caching the online ambassadors.
	 *
	 * @since 0.1
	 * @var OA[]|bool false
	 */
	protected $oas = false;

	/**
	 * Field for caching the campus ambassadors.
	 *
	 * @since 0.1
	 * @var CA[]| bool false
	 */
	protected $cas = false;

	/**
	 * Maps role-related field names and numeric constants for the
	 * ep_users_per_course table.
	 *
	 * @since 0.4 alpha
	 *
	 * @var array
	 */
	protected $fieldsAndUPCConstants = array(
		'online_ambs' => EP_OA,
		'campus_ambs' => EP_CA,
		'students' => EP_STUDENT,
		'instructors' => EP_INSTRUCTOR,
	);

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
			wfMessage( 'ep-course-status-passed' )->text() => 'passed',
			wfMessage( 'ep-course-status-current' )->text() => 'current',
			wfMessage( 'ep-course-status-planned' )->text() => 'planned',
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
	 * @see ORMRow::loadSummaryFields()
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
	 * @see ORMRow::insert()
	 */
	protected function insert( $functionName = null, array $options = null ) {
		$success = parent::insert( $functionName, $options );

		if ( $success && $this->updateSummaries ) {

			// these are the only org summary fields that could need updating
			$this->updateOrgSummaryFields( null,
					array( 'course_count', 'last_active_date' ) );
		}

		return $success;
	}

	/**
	 * @see RevisionedObject::onRemoved()
	 */
	protected function onRemoved() {
		if ( $this->updateSummaries ) {
			$this->updateOrgSummaryFields();
		}

		// get rid of all fields linked to this course in ep_users_per_course
		$this->upcPurge();

		parent::onRemoved();
	}

	/**
	 * @see RevisionedObject::onUpdated()
	 */
	protected function onUpdated( RevisionedObject $originalCourse ) {

		// add and remove rows from ep_users_per_course as needed
		$newUserIdsAndRoles = array();
		$dbm = wfGetDB( DB_MASTER );

		foreach ( array( 'online_ambs', 'campus_ambs', 'students', 'instructors' ) as $usersField ) {
			if ( $this->hasField( $usersField ) && $originalCourse->getField( $usersField ) !== $this->getField( $usersField ) ) {
				$removedIds = array_diff( $originalCourse->getField( $usersField ), $this->getField( $usersField ) );
				$addedIds = array_diff( $this->getField( $usersField ), $originalCourse->getField( $usersField ) );

				// prepare data for rows to add
				foreach ( $addedIds as $addedId ) {
					$newUserIdsAndRoles[] = array(
						'user_id' => $addedId,
						'role' => $usersField,
					);
				}

				// remove rows as required
				if ( !empty( $removedIds ) ) {
					$dbm->delete( 'ep_users_per_course', array(
						'upc_course_id' => $this->getId(),
						'upc_user_id' => $removedIds,
						'upc_role' => $this->fieldsAndUPCConstants[$usersField],
					) );
				}
			}
		}

		// add the rows to ep_users_per_course
		if ( !empty( $newUserIdsAndRoles ) ) {
			$this->upcAdd( $newUserIdsAndRoles );
		}

		// update summary data for this course's institution
		if ( $this->updateSummaries ) {

			$this->updateOrgSummaryFields( $originalCourse->getField( 'org_id' ) );

			if ( $this->hasField( 'org_id' ) &&
				$originalCourse->getField( 'org_id' ) !==
				$this->getField( 'org_id' ) ) {

				$this->updateOrgSummaryFields();
			}
		}

		parent::onUpdated( $originalCourse );
	}

	/**
	 * @see EducationProgram\RevisionedObject::onUndeleted()
	 */
	protected function onUndeleted() {

		// re-create rows to ep_users_per_course table
		$this->upcAdd();

		// update summary fields for the related institution
		$this->updateOrgSummaryFields();

		// Note: it seems that summary fields in Courses table will be restored via
		// the revision.

		parent::onUndeleted();
	}

	/**
	 * Update the summary fields for this course's institution, or for the
	 * institution with the provided $org_id.
	 *
	 * @since 0.4 alpha
	 *
	 * @param array $fields
	 * @param int $org_id
	 */
	protected function updateOrgSummaryFields( $org_id=null, $fields=null ) {
		if ( is_null( $org_id ) ) {
			$org_id = $this->getField( 'org_id' );
		}

		// make sure Orgs uses up-to-date info
		$previousRMFSValue = Orgs::singleton()->getReadMasterForSummaries();
		Orgs::singleton()->setReadMasterForSummaries( true );
		Orgs::singleton()->updateSummaryFields( $fields, array( 'id' => $org_id ) );
		Orgs::singleton()->setReadMasterForSummaries( $previousRMFSValue );
	}

	/**
	 * Purge and re-create rows in ep_users_per_course related to this course
	 */
	public function rebuildUPCRows() {
		$this->upcPurge();
		$this->upcAdd();
	}

	/**
	 * Add rows for this course to ep_users_per_course, using data in
	 * $userIdsAndRoles. If no parameter is provided, we add rows to
	 * that table for all students, volunteers and instructors
	 * associated with this course.
	 *
	 * @since 0.4 alpha
	 *
	 * @param array $userIdsAndRoles An array of associative arrays with data on
	 *   users and roles to add; each inner array has two keys: user_id and
	 *   role.
	 */
	protected function upcAdd( $userIdsAndRoles=null ) {

		// if no param, build the data from this course's user fields
		if ( is_null( $userIdsAndRoles ) ) {

			$userIdsAndRoles = array();

			foreach ( array( 'online_ambs', 'campus_ambs', 'students',
				 'instructors' ) as $usersField ) {

				$addedIds = $this->getField( $usersField );

				foreach ( $addedIds as $addedId ) {
					$userIdsAndRoles[] = array(
						'user_id' => $addedId,
						'role' => $usersField,
					);
				}
			}
		}

		// add the rows as necessary
		if ( !empty( $userIdsAndRoles ) ) {
			$addData = array();

			foreach ( $userIdsAndRoles as $userIdAndRole ) {
				$addData[] = array(
					'upc_course_id' => $this->getId(),
					'upc_user_id' => $userIdAndRole['user_id'],
					'upc_role' => $this->
						fieldsAndUPCConstants[$userIdAndRole['role']],
					'upc_time' => wfTimestampNow(),
				);
			}

			$dbw = wfGetDB( DB_MASTER );
			$dbw->begin();

			foreach ( $addData as $item ) {
				$dbw->insert( 'ep_users_per_course', $item );
			}

			$dbw->commit();
		}
	}

	/**
	 * Purge rows in ep_users_per_course related to this course.
	 *
	 * @since 0.4 alpha
	 */
	protected function upcPurge() {
		wfGetDB( DB_MASTER )
			->delete( 'ep_users_per_course',
			array( 'upc_course_id' => $this->getId() ) );
	}

	/**
	 * @see ORMRow::save()
	 */
	public function save( $functionName = null ) {

		// Check if we're attempting to create a course that already exists.
		// This can happen if the user clicks the "Submit" button of the
		// second form for course creation more than once.
		if ( !$this->hasIdField() ) {
			$title = $this->getField( 'title' );

			if ( $this->table->has( array( 'title' => $title ) ) ) {

				$pageTitle = $this->getTitle(); // title of the Wiki page
				throw new ErrorPageErrorWithSelflink( 'ep-err-course-exists-title',
						'ep-err-course-exists-text', $pageTitle, $title );
			}
		}

		if ( $this->hasField( 'name' ) ) {
			$this->setField( 'name', $GLOBALS['wgLang']->ucfirst( $this->getField( 'name' ) ) );
		}

		foreach ( array( 'student_count', 'instructor_count', 'oa_count', 'ca_count' ) as $summaryField ) {
			$field = self::$countMap[$summaryField];
			if ( $this->hasField( $field ) ) {
				$this->setField( $summaryField, count( $this->getField( $field ) ) );
			}
		}

		return parent::save( $functionName );
	}

	/**
	 * Returns the org associated with this term.
	 *
	 * @since 0.1
	 *
	 * @param string|array|null $fields
	 *
	 * @return Org
	 */
	public function getOrg( $fields = null ) {
		if ( $this->org === false ) {
			$this->org = Orgs::singleton()->selectRow( $fields, array( 'id' => $this->loadAndGetField( 'org_id' ) ) );
		}

		return $this->org;
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
	 * @return string
	 */
	public static function getAddNewControl( IContextSource $context, array $args ) {
		if ( !$context->getUser()->isAllowed( 'ep-course' ) ) {
			return '';
		}

		$html = '';

		$html .= Html::openElement(
			'form',
			array(
				'method' => 'post',
				'action' => Courses::singleton()->getTitleFor( 'NAME_PLACEHOLDER' )->getLocalURL( array( 'action' => 'edit' ) ),
			)
		);

		$html .= '<fieldset>';

		$html .= '<legend>' . $context->msg( 'ep-courses-addnew' )->escaped() . '</legend>';

		$html .= '<p>' . $context->msg( 'ep-courses-namedoc' )->escaped() . '</p>';

		$html .= Html::element( 'label', array( 'for' => 'neworg' ), $context->msg( 'ep-courses-neworg' )->plain() );

		$select = new \XmlSelect(
			'neworg',
			'neworg',
			array_key_exists( 'org', $args ) ? $args['org'] : false
		);

		$orgs = Orgs::singleton()->selectFields( array( 'id', 'name' ) );
		natcasesort( $orgs );
		$orgs = array_flip( $orgs );

		$select->addOptions( $orgs );
		$html .= $select->getHTML();

		$html .= '&#160;' . Xml::inputLabel(
			$context->msg( 'ep-courses-newname' )->plain(),
			'newname',
			'newname',
			20,
			array_key_exists( 'name', $args ) ? $args['name'] : false
		);

		$html .= '&#160;' . Xml::inputLabel(
			$context->msg( 'ep-courses-newterm' )->plain(),
			'newterm',
			'newterm',
			10,
			array_key_exists( 'term', $args ) ? $args['term'] : false
		);

		$html .= '&#160;' . Html::input(
			'addnewcourse',
			$context->msg( 'ep-courses-add' )->plain(),
			'submit',
			array(
				'disabled' => 'disabled',
				'class' => 'ep-course-add',
			)
		);

		$html .= Html::hidden( 'isnew', 1 );

		$html .= '</fieldset></form>';

		return $html;
	}

	/**
	 * Adds a control to add a new term to the provided context
	 * or show a message if this is not possible for some reason.
	 *
	 * @since 0.1
	 *
	 * @param IContextSource $context
	 * @param array $args
	 *
	 * @return string
	 */
	public static function getAddNewRegion( IContextSource $context, array $args = array() ) {
		if ( Orgs::singleton()->has() ) {
			return Course::getAddNewControl( $context, $args );
		}
		else {
			return $context->msg( 'ep-courses-addorgfirst' )->parse();
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
	 * Returns the students as a list of Student objects.
	 *
	 * @since 0.1
	 *
	 * @return Student[]
	 */
	public function getStudents() {
		return $this->getRoleList( 'students', 'EducationProgram\Students', 'students' );
	}

	/**
	 * Returns the instructors as a list of Instructor objects.
	 *
	 * @since 0.1
	 *
	 * @return Instructor[]
	 */
	public function getInstructors() {
		return $this->getRoleList( 'instructors', 'EducationProgram\Instructors', 'instructors' );
	}

	/**
	 * Returns the users that have the specified role.
	 *
	 * @since 0.1
	 *
	 * @param string $fieldName Name of the role field.
	 * @param string $tableName Name of the table class in which this role is kept track of.
	 * @param string $classField Name of the field in which the list is cached in this class.
	 *
	 * @return IRole[]
	 */
	protected function getRoleList( $fieldName, $tableName, $classField ) {
		if ( $this->$classField === false ) {
			$userIds = $this->getField( $fieldName );

			if ( empty( $userIds ) ) {
				$this->$classField = array();
			}
			else {
				/**
				 * @var IORMTable $table
				 */
				$table = $tableName::singleton();

				$this->$classField = $table->select(
					null,
					array( 'user_id' => $userIds )
				);

				$this->$classField = iterator_to_array( $this->$classField );

				// At this point we will have all users that actually have an entry in the role table.
				// But it's possible they do not have such an entry, so create new objects for those.

				$addedIds = array();

				foreach ( $this->$classField as /* EPRole */ $userInRole ) {
					$addedIds[] = $userInRole->getField( 'user_id' );
				}

				foreach ( array_diff( $userIds, $addedIds ) as $remainingId ) {
					array_push( $this->$classField, $table->newRow( array( 'user_id' => $remainingId ) ) );
				}
			}
		}

		return $this->$classField;
	}

	/**
	 * Returns the campus ambassadors as a list of CA objects.
	 *
	 * @since 0.1
	 *
	 * @return CA[]
	 */
	public function getCampusAmbassadors() {
		return $this->getRoleList( 'campus_ambs', 'EducationProgram\CAs', 'cas' );
	}

	/**
	 * Returns the role objects for all roles other than student (i.e.,
	 * instructors, campus ambassadors and online ambassadors).
	 */
	public function getAllNonStudentRoleObjs() {
		return array_merge(
			$this->getInstructors(),
			$this->getCampusAmbassadors(),
			$this->getOnlineAmbassadors() );
	}

	/**
	 * Returns the online ambassadors as a list of OA objects.
	 *
	 * @since 0.1
	 *
	 * @return OA[]
	 */
	public function getOnlineAmbassadors() {
		return $this->getRoleList( 'online_ambs', 'EducationProgram\OAs', 'oas' );
	}

	/**
	 * Returns the users that have a certain role as list of IRole objects.
	 *
	 * @since 0.1
	 *
	 * @param string $roleName
	 *
	 * @return IRole[]
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
	 * @see ORMRow::setField()
	 */
	public function setField( $name, $value ) {
		switch ( $name ) {
			case 'name':
				$value = str_replace( '_', ' ', $value );
				break;
			case 'instructors':
				$this->instructors = false;
				break;
			case 'students':
				$this->students = false;
				break;
			case 'oas':
				$this->oas = false;
				break;
			case 'cas':
				$this->cas = false;
				break;
		}

		parent::setField( $name, $value );
	}

	/**
	 * Returns the key of the notification for adding a specific role
	 * to a course
	 *
	 * @param string $role
	 *
	 * @return string
	 */
	public function getAddNotificationKey( $role ) {
		switch ( $role ) {
			case 'instructor':
				return 'ep-instructor-add-notification';
				break;
			case 'online':
				return 'ep-online-add-notification';
				break;
			case 'campus':
				return 'ep-campus-add-notification';
				break;
			case 'student':
				return 'ep-student-add-notification';
				break;
		}
	}

	/**
	 * Adds a role for a number of users to this course,
	 * by default also saving the course and only
	 * logging the addition of the users/roles.
	 *
	 * @since 0.1
	 *
	 * @param array|integer $newUserIds
	 * @param string $role
	 * @param boolean $save
	 * @param RevisionAction|null $revAction
	 * @param array &$addedUserIds Reference to an array, for info sending
	 *    a list of the ids of users that were actually enlisted. (That is,
	 *    $newUserIds minus any that were already enlisted in this role.)
	 *
	 * @return integer|bool false The amount of enlisted users or false on failiure
	 */
	public function enlistUsers( $newUserIds, $role, $save = true,
			RevisionAction $revAction = null, &$addedUserIds = null ) {
		$roleMap = array(
			'student' => 'students',
			'campus' => 'campus_ambs',
			'online' => 'online_ambs',
			'instructor' => 'instructors',
		);

		$field = $roleMap[$role];
		$userIds = $this->getField( $field );
		$usersToAddIds = array_diff( (array) $newUserIds, $userIds );

		if ( empty( $usersToAddIds ) ) {
			return 0;
		}
		else {
			$this->setField( $field, array_merge( $userIds, $usersToAddIds ) );

			$success = true;

			if ( $save ) {
				$this->disableLogging();
				$success = $this->save();
				$this->enableLogging();
			}

			if ( $success ) {

				// If we are enrolling students, then add them to the Students
				// table. There are tables for other roles, but of them, only
				// the tables for volunteers are used, and it's not necessary
				// for those tables to be filled automatically.
				if ( $role === 'student' ) {

					foreach ( $usersToAddIds as $userId ) {
						$student = Student::newFromUserId( $userId, true, 'id' );
						$student->onEnrolled( $this->getId(), $role );
						$student->save();
					}
				}

				// If we have a value for $addedUserIds, pass in
				// the ids of users that were enlisted.
				if ( $addedUserIds !== null ) {
					$addedUserIds = $usersToAddIds;
				}
			}

			if ( $success && !is_null( $revAction ) ) {
				$action = count( $usersToAddIds ) == 1 && $revAction->getUser()->getId() === $usersToAddIds[0] ? 'selfadd' : 'add';

				// trigger notification(s) if a user added someone
				// other than him/herself to a course
				if ( $action === 'add' ) {
					Extension::globalInstance()->getNotificationsManager()->trigger(
						$this->getAddNotificationKey( $role ),
						array(
							'role-add-title' => $this->getTitle(),
							'agent' => $revAction->getUser(),
							'users' => $usersToAddIds,
						)
					);
				}

				$this->logRoleChange( $action, $role, $usersToAddIds, $revAction->getComment() );
			}

			return $success ? count( $usersToAddIds ) : false;
		}
	}

	/**
	 * Remove the role for a number of users for this course,
	 * by default also saving the course and only
	 * logging the role changes.
	 *
	 * TODO: move into its own use case
	 *
	 * @since 0.1
	 *
	 * @param array|integer $sadUsers
	 * @param string $role
	 * @param boolean $save
	 * @param RevisionAction|null $revAction
	 *
	 * @return integer|bool false The amount of unenlisted users or false on failiure
	 */
	public function unenlistUsers( $sadUsers, $role, $save = true, RevisionAction $revAction = null ) {
		$sadUsers = (array)$sadUsers;

		$roleMap = array(
			'student' => 'students',
			'campus' => 'campus_ambs',
			'online' => 'online_ambs',
			'instructor' => 'instructors',
		);

		$field = $roleMap[$role];

		$removedUsers = array_intersect( $sadUsers, $this->getField( $field ) );

		if ( empty( $removedUsers ) ) {
			return 0;
		}
		else {
			$this->setField( $field, array_diff( $this->getField( $field ), $sadUsers ) );

			if ( $role === 'student' ) {
				// Get rid of the articles asscoaite associations with the student.
				// No revisioning is implemented here, so this cannot be undone.
				// Might want to add revisioning or just add a 'deleted' flag at some point.

				Extension::globalInstance()->newArticleStore()->deleteArticleByCourseAndUsers(
					$this->getId(),
					$removedUsers
				);
			}

			$success = true;

			if ( $save ) {
				$this->disableLogging();
				$success = $this->save();
				$this->enableLogging();
			}

			if ( $success && !is_null( $revAction ) ) {
				$action = count( $removedUsers ) == 1 && $revAction->getUser()->getId() === $removedUsers[0] ? 'selfremove' : 'remove';
				$this->logRoleChange( $action, $role, $removedUsers, $revAction->getComment() );
			}

			return $success ? count( $removedUsers ) : false;
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
			'instructor' => 'EducationProgram\Instructor',
			'campus' => 'EducationProgram\CA',
			'online' => 'EducationProgram\OA',
			'student' => 'EducationProgram\Student',
		);

		$class = $classes[$role];

		foreach ( $users as $userId ) {
			$names[] = $class::newFromUserId( $userId )->getName();
		}

		$info = array(
			'type' => $role,
			'subtype' => $action,
			'title' => $this->getTitle(),
		);

		if ( in_array( $action, array( 'add', 'remove' ) ) ) {
			$info['parameters'] = array(
				'4::usercount' => count( $names ),
				'5::users' => $names
			);
		}

		if ( $message !== '' ) {
			$info['comment'] = $message;
		}

		Utils::log( $info );
	}

	/**
	 * @see EPRevionedObject::restoreField()
	 */
	protected function restoreField( $fieldName, $newValue ) {
		if ( $fieldName !== 'org_id'
			|| Orgs::singleton()->has( array( 'id' => $newValue ) ) ) {
			parent::restoreField( $fieldName, $newValue );
		}
	}

	/**
	 * @see PageObject::getLink
	 *
	 * @since 0.2
	 *
	 * @param string $action
	 * @param string $html
	 * @param array $customAttribs
	 * @param array $query
	 *
	 * @return string
	 */
	public function getLink( $action = 'view', $html = null, array $customAttribs = array(), array $query = array() ) {
		return parent::getLink(
			$action,
			is_null( $html ) ? htmlspecialchars( $this->getField( 'name' ) ) : $html,
			$customAttribs,
			$query
		);
	}

	/**
	 * Gets the course's name, i.e. the "name" part in this standard format:
	 * institution/name (term)
	 *
	 * @since 0.4 alpha
	 *
	 * @returns string
	 */
	// TODO: Logic related to formatting the course title in the
	// "institution/name (term)" format is peppered all around the codebase
	// (in ep.addcourse.js, EditCourseAction.php, Courses.php).
	public function getName() {
		return $this->getField( 'name' );
	}

	/**
	 * @see RevisionedObject::getTypeId
	 *
	 * @since 0.3
	 *
	 * @return string
	 */
	protected function getTypeId() {
		return $this->table->getRevisionedObjectTypeId();
	}
}
