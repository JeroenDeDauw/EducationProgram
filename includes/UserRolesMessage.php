<?php

namespace EducationProgram;

use Html;

/**
 * Class for generating a message about a user's role(s) in courses (as a
 * student, instructor or volunteer). Currently this message appears on
 * Special:Contributions.
 *
 * Use the maxCoursesInUserRolesMessage setting to set the maximum number of
 * courses to be mentioned in the message.
 *
 * @since 0.4 alpha
 *
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Andrew Green < agreen at wikimedia dot org >
 */
class UserRolesMessage {

	/**
	 * An array of arrays. Each inner array contains a rolename and an array of
	 * courses that the user participates in in that role.
	 *
	 * @since 0.4 alpha
	 * @var array
	 */
	protected $rolesAndCourses;

	/**
	 * Message keys that vary depending on which role we're dealing with.
	 *
	 * @since 0.4 alpha
	 * @var array
	 */
	protected $messageKeysForRoles = array(
		'student' => array(
			'main' => 'ep-user-roles-message-main-student',
			'main-many' => 'ep-user-roles-message-main-many-student',
			'rolename' => 'ep-user-roles-message-rolename-student',
		),
		'instructor' => array(
			'main' => 'ep-user-roles-message-main-instructor',
			'main-many' => 'ep-user-roles-message-main-many-instructor',
			'rolename' => 'ep-user-roles-message-rolename-instructor',
		),
		'online' => array(
			'main' => 'ep-user-roles-message-main-online',
			'main-many' => 'ep-user-roles-message-main-many-online',
			'rolename' => 'ep-user-roles-message-rolename-online',
		),
		'campus' => array(
			'main' => 'ep-user-roles-message-main-campus',
			'main-many' => 'ep-user-roles-message-main-many-campus',
			'rolename' => 'ep-user-roles-message-rolename-campus',
		),
	);

	/**
	 * The id of the user about whom we'll display a message
	 *
	 * @since 0.4 alpha
	 * @var int
	 */
	protected $userid;

	/**
	 * the \OutputPage to send output to
	 *
	 * @since 0.4 alpha
	 * @var \OutputPage
	 */
	protected $out;

	/**
	 * Contructor
	 *
	 * @since 0.4 alpha
	 *
	 * @param int $userid the id of the user about whom we'll display a message
	 * @param \OutputPage $out used for output
	 */
	public function __construct( $userid, \OutputPage $out ) {
		$this->userid = $userid;
		$this->out = $out;
	}

	/**
	 * Do some preparatory work. You must call this method before calling
	 * userHasRoles() or output().
	 */
	public function prepare() {

		// classes of table objects, ordered by priority, for describing
		// a user's partcipation in the corrsponding roles
		$orderedTableClasses = array(
			'EducationProgram\CAs',
			'EducationProgram\OAs',
			'EducationProgram\Instructors',
			'EducationProgram\Students'
		);

		// create an array that contains role objects or false (for roles the
		// user doesn't have)
		$orderedRoleObjs = array();

		foreach ( $orderedTableClasses as $tableClass ) {
			$orderedRoleObjs[] = $tableClass::singleton()
				->selectRow( null, array( 'user_id' => $this->userid ) );
		}

		// Create an array of arrays. Each inner array will contain a rolename
		// and an array of courses that the user participates in in that role.
		$this->rolesAndCourses = array();

		foreach ( $orderedRoleObjs as $roleObj ) {
			if ( $roleObj ) {
				$courses = $roleObj->getCourses(
						array( 'name', 'title', 'term'),
						Courses::getStatusConds( 'current' ) );

				if ( count ( $courses ) > 0) {
					$this->rolesAndCourses[] = array(
						'role' => $roleObj->getRoleName(),
						'courses' => $courses );
				}
			}
		}
	}

	/**
	 * Does the user have any roles in EducationProgram courses?
	 *
	 * Note: you must call prepare() before calling this method. Unless you know
	 * that the user has roles, call this method before calling output().
	 *
	 * @return boolean true if the user has roles, false if not
	 */
	public function userHasRoles() {
		return ( count( $this->rolesAndCourses ) > 0 );
	}

	/**
	 * Generate the message and output it via the \OutputPage instance sent in
	 * with the constructor.
	 *
	 * Call this method at the point during page output where the message
	 * should be inserted.
	 *
	 * Before calling this method, call prepare() and check that there is some
	 * output via userHasRoles();
	 *
	 * @throws MWException exception if there's no message to output
	 */
	public function output() {
		// sanity check
		if ( !$this->userHasRoles() ) {
			throw new \MWException( 'Can\'t produce user role message output if ' .
				'user doesn\'t have any roles' );
		}

		// We'll make a detailed message only for the first role (first element
		// in $this->rolesAndCourses).

		// first set up some values we'll need
		$mainRole = $this->rolesAndCourses[0]['role'];
		$mainRoleCourses = $this->rolesAndCourses[0]['courses'];
		$user = \User::newFromId( $this->userid );
		$userName = $user->getName();

		// load required css snippet
		$this->out->addModules( 'ep.userrolesmessage' );

		// open enclosing div
		$this->out->addHTML( Html::openElement( 'div',
			array( 'class' => 'userrolesmessage' ) ) );

		// Choose the message to display depending on the number of courses for
		// the first role. No matter, the following chunk of code should give us
		// a valid $mainMessageHTML ready for output.

		$maxCourses = Extension::globalInstance()->getSettings()
			->getSetting( 'maxCoursesInUserRolesMessage' );

		$mainRoleCoursesCount = count( $mainRoleCourses );

		if ( $mainRoleCoursesCount <= $maxCourses ) {

			// create the main message with the list of courses

			// make an array of wikitext links to courses and their talk pages
			$courseLinks = array();

			foreach ( $mainRoleCourses as $course ) {

				$title = $course->getTitle();

				$msg = $this->out->msg(
					'ep-user-roles-message-course-link-for-list',
					$title->getFullText(),
					$course->getName(),
					$title->getTalkPage()->getFullText()
				);

				$courseLinks[] = $msg->plain();
			}

			$mainMessageHTML = $this->out->msg(
				$this->messageKeysForRoles[$mainRole]['main'],
				$user->getUserPage()->getFullText(),
				$userName,
				$this->out->getLanguage()->listToText( $courseLinks ),
				$mainRoleCoursesCount
			)->parse();

		} else {

			// create the main message with only the number of courses

			$mainMessageParams = array(
				$user->getUserPage()->getFullText(),
				$userName,
				$mainRoleCoursesCount
			);

			// If we're talking about a student role, include a link to the
			// student's profile.
			if ( $mainRole === 'student' ) {
				$mainMessageParams[] =
					\SpecialPage::getTitleFor( 'Student', $userName );
			}

			$mainMessageHTML = $this->out->msg(
				$this->messageKeysForRoles[$mainRole]['main-many'],
				$mainMessageParams
			)->parse();
		}

		$this->out->addHTML( $mainMessageHTML );

		// If there were any additional roles, we'll summarize them with
		// a phrase like, "She is also an instructor."
		if ( count( $this->rolesAndCourses ) > 1 ) {

			// Make an array of remaining rolenames.

			// Set up some local vars to use in the following closure, since
			// PHP > 5.4 doesn't suport $this in closures.
			$messageKeysForRoles = $this->messageKeysForRoles;
			$out = $this->out;

			$remainingRolenames = array_map(
				function( $roleAndCourse ) use ($messageKeysForRoles, $out) {

					$msgKey =
						$messageKeysForRoles[$roleAndCourse['role']]['rolename'];

					return $out->msg( $msgKey )->plain();
				},
				array_slice( $this->rolesAndCourses, 1 ) );

			$remainingRolenamesList =
				$this->out->getLanguage()->listToText( $remainingRolenames );

			// create and output the message
			$this->out->addHTML( ' ' .
				$this->out->msg(
					'ep-user-roles-message-additional',
					$userName,
					$remainingRolenamesList
				)->escaped()
			);
		}

		// close enclosing div
		$this->out->addHTML( Html::closeElement( 'div' ) );
	}
}