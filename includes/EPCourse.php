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
			'org_id' => 'id',

			'name' => 'str',
			'start' => 'str', // TS_MW
			'end' => 'str', // TS_MW
			'description' => 'str',
			'token' => 'str',
			'online_ambs' => 'array',
			'campus_ambs' => 'array',
			'field' => 'str',
			'level' => 'str',
			'term' => 'str',
			'lang' => 'str',
			'mc' => 'str',
		
			'students' => 'int',
		);
	}

	/**
	 * (non-PHPdoc)
	 * @see EPDBObject::getDefaults()
	 */
	public static function getDefaults() {
		return array(
			'name' => '',
			'start' => wfTimestamp( TS_MW ),
			'end' => wfTimestamp( TS_MW ),
			'description' => '',
			'token' => '',
			'online_ambs' => array(),
			'campus_ambs' => array(),
			'field' => '',
			'level' => '',
			'term' => '',
			'lang' => '',
			'mc' => '',

			'students' => 0,
		);
	}

	/**
	 * Returns the students enrolled in this course.
	 *
	 * @since 0.1
	 *
	 * @param string|array|null $fields
	 * @param array $conditions
	 *
	 * @return array of EPStudent
	 */
	protected function doGetStudents( $fields, array $conditions ) {
		$conditions[] = array( array( 'ep_courses', 'id' ), $this->getId() );

		return EPStudent::select(
			$fields,
			$conditions,
			array(),
			array(
				'ep_students_per_course' => array( 'INNER JOIN', array( array( array( 'ep_students_per_course', 'student_id' ), array( 'ep_students', 'id' ) ) ) ),
				'ep_courses' => array( 'INNER JOIN', array( array( array( 'ep_students_per_course', 'course_id' ), array( 'ep_courses', 'id' ) ) ) )
			)
		);
	}

	/**
	 * Returns the students enrolled in this course.
	 * Caches the result when no conditions are provided and all fields are selected.
	 *
	 * @since 0.1
	 *
	 * @param string|array|null $fields
	 * @param array $conditions
	 *
	 * @return array of EPStudent
	 */
	public function getStudents( $fields = null, array $conditions = array() ) {
		if ( count( $conditions ) !== 0 ) {
			return $this->doGetStudents( $fields, $conditions );
		}

		if ( $this->students === false ) {
			$students = $this->doGetStudents( $fields, $conditions );

			if ( is_null( $fields ) ) {
				$this->students = $students;
			}

			return $students;
		}
		else {
			return $this->students;
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see EPDBObject::loadSummaryFields()
	 */
	public function loadSummaryFields( $summaryFields = null ) {
		if ( is_null( $summaryFields ) ) {
			$summaryFields = array( 'org_id', 'students' );
		}
		else {
			$summaryFields = (array)$summaryFields;
		}

		$fields = array();

		if ( in_array( 'org_id', $summaryFields ) ) {
			$fields['org_id'] = $this->getCourse( 'org_id' )->getField( 'org_id' );
		}
		
		if ( in_array( 'students', $summaryFields ) ) {
			$fields['students'] = wfGetDB( DB_SLAVE )->select(
				'ep_students_per_course',
				'COUNT(*) AS rowcount',
				array( 'spc_course_id' => $this->getId() )
			);

			$fields['students'] = $fields['students']->fetchObject()->rowcount;
		}

		$this->setFields( $fields );
	}

	/**
	 * (non-PHPdoc)
	 * @see EPDBObject::insertIntoDB()
	 */
	protected function insertIntoDB() {
		$success = parent::insertIntoDB();

		if ( $success && $this->updateSummaries ) {
			EPOrg::updateSummaryFields( array( 'courses', 'active' ), array( 'id' => $this->getField( 'org_id' ) ) );
		}

		return $success;
	}

	/**
	 * (non-PHPdoc)
	 * @see EPDBObject::removeFromDB()
	 */
	public function removeFromDB() {
		$id = $this->getId();

		if ( $this->updateSummaries ) {
			$this->loadFields( array( 'org_id' ) );
			$orgId = $this->getField( 'org_id' );
		}

		$success = parent::removeFromDB();

		if ( $success && $this->updateSummaries ) {
			EPOrg::updateSummaryFields( array( 'courses', 'students', 'active' ), array( 'id' => $orgId ) );
		}

		if ( $success ) {
			$success = wfGetDB( DB_MASTER )->delete( 'ep_students_per_course', array( 'spc_course_id' => $id ) ) && $success;
		}

		return $success;
	}

	/**
	 * (non-PHPdoc)
	 * @see EPDBObject::updateInDB()
	 */
	protected function updateInDB() {
		if ( $this->updateSummaries ) {
			$oldOrgId = $this->hasField( 'org_id' ) ? self::selectFieldsRow( 'org_id', array( 'id' => $this->getId() ) ) : false;
		}

		$success = parent::updateInDB();

		if ( $this->updateSummaries && $success ) {
			if ( $oldOrgId !== false && $oldOrgId !== $this->getField( 'org_id' ) ) {
				$conds = array( 'id' => array( $oldOrgId, $this->getField( 'org_id' ) ) );
				EPOrg::updateSummaryFields( array( 'courses', 'students', 'active' ), $conds );
			}
		}

		return $success;
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
	 */
	public static function displayPager( IContextSource $context, array $conditions = array() ) {
		$pager = new EPCoursePager( $context, $conditions );

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
				'action' => self::getTitleFor( 'NAME' )->getLocalURL(),
			)
		) );

		$out->addHTML( '<fieldset>' );

		$out->addHTML( '<legend>' . wfMsgHtml( 'ep-courses-addnew' ) . '</legend>' );

		$out->addHTML( Html::element( 'p', array(), wfMsg( 'ep-courses-namedoc' ) ) );

		$out->addHTML( Html::element( 'label', array( 'for' => 'neworg' ), wfMsg( 'ep-courses-neworg' ) ) );

		$select = new XmlSelect(
			'neworg',
			'neworg',
			array_key_exists( 'org', $args ) ? $args['org'] : false
		);

		$select->addOptions( EPOrg::getOrgOptions() );
		$out->addHTML( $select->getHTML() );

		$out->addHTML( '&#160;' . Xml::inputLabel( wfMsg( 'ep-courses-newname' ), 'newname', 'newname', 20 ) );

		$out->addHTML( '&#160;' . Xml::inputLabel( wfMsg( 'ep-courses-newterm' ), 'newterm', 'newterm', 10 ) );

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
				$this->instructors[] = EPInstructor::newFromId( $userId );
			}
		}

		return $this->instructors;
	}

	/**
	 * (non-PHPdoc)
	 * @see EPDBObject::setField()
	 */
	public function setField( $name, $value ) {
		if ( $name === 'instructors' ) {
			$this->instructors = false;
		}

		parent::setField( $name, $value );
	}

	/**
	 * Adds a number of instructors to this course,
	 * by default also saving the course and only
	 * logging the adittion of the instructors.
	 *
	 * @since 0.1
	 *
	 * @param array|integer $newInstructors
	 * @param string $message
	 * @param boolean $save
	 * @param boolean $log
	 *
	 * @return boolean Success indicator
	 */
	public function addInstructors( $newInstructors, $message = '', $save = true, $log = true ) {
		$instructors = $this->getField( 'instructors' );
		$addedInstructors = array();

		foreach ( (array)$newInstructors as $userId ) {
			if ( !is_integer( $userId ) ) {
				throw new MWException( 'Provided user id is not an integer' );
			}

			if ( !in_array( $userId, $instructors ) ) {
				$instructors[] = $userId;
				$addedInstructors[] = $userId;
			}
		}

		if ( count( $addedInstructors ) > 0 ) {
			$this->setField( 'instructors', $instructors );

			$success = true;

			if ( $save ) {
				$this->disableLogging();
				$success = $this->writeToDB();
				$this->enableLogging();
			}

			if ( $success && $log ) {
				$this->logInstructorChange( 'add', $addedInstructors, $message );
			}

			return $success;
		}
		else {
			return true;
		}
	}

	/**
	 * Remove a number of instructors to this course,
	 * by default also saving the course and only
	 * logging the removal of the instructors.
	 *
	 * @since 0.1
	 *
	 * @param array|integer $sadInstructors
	 * @param string $message
	 * @param boolean $save
	 * @param boolean $log
	 *
	 * @return boolean Success indicator
	 */
	public function removeInstructors( $sadInstructors, $message = '', $save = true, $log = true ) {
		$removedInstructors = array();
		$remaimingInstructors = array();
		$sadInstructors = (array)$sadInstructors;

		foreach ( $this->getField( 'instructors' ) as $userId ) {
			if ( in_array( $userId, $sadInstructors ) ) {
				$removedInstructors[] = $userId;
			}
			else {
				$remaimingInstructors[] = $userId;
			}
		}

		if ( count( $removedInstructors ) > 0 ) {
			$this->setField( 'instructors', $remaimingInstructors );

			$success = true;

			if ( $save ) {
				$this->disableLogging();
				$success = $this->writeToDB();
				$this->enableLogging();
			}

			if ( $success && $log ) {
				$this->logInstructorChange( 'remove', $removedInstructors, $message );
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
	 * @param array $instructors
	 * @param string $message
	 */
	protected function logInstructorChange( $action, array $instructors, $message ) {
		$names = array();

		foreach ( $instructors as $userId ) {
			$names[] = EPInstructor::newFromId( $userId )->getName();
		}

		$info = array(
			'type' => 'instructor',
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

}
