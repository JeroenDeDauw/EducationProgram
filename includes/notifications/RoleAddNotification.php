<?php

namespace EducationProgram;

/**
 * Notification type for a user being added to a course
 *
 * @since 0.4 alpha
 *
 * @ingroup EducationProgram
 *
 * @license GNU GPL v2+
 * @author JJ Liu < jie dot squared at gmail dot com >
 */
abstract class RoleAddNotification implements INotificationType {

	/**
	 * Returns the generic parameters that are identical across
	 * all roles for this notification.
	 *
	 * Subclasses should append to this array with role-specific
	 * parameters in their implementation of getParameters().
	 */
	protected function getGenericParameters() {
		return array(
			'primary-link' => array(
				'message' => 'ep-role-add-link-text-view-course',
				'destination' => 'title'
			),
			'group' => 'interactive',

			// The custom message param 'short-title-text' requires a custom
			// notification formatter. See CourseFormatter.
			'title-params' => array( 'agent', 'title', 'short-title-text' ),
			'email-subject-params' => array( 'agent', 'short-title-text' ),
			'email-body-batch-params' => array( 'agent', 'short-title-text' ),
			'icon' => 'ep-added-to-course-icon',
		);
	}

	/**
	 * @since 0.4 alpha
	 * @see EducationProgram.INotificationType::getIconParameters()
	 */
	public function getIconParameters() {
		return array( 'path' => 'EducationProgram/resources/images/added-to-course-notification.png' );
	}

	/**
	 * @since 0.4 alpha
	 * @see EducationProgram.INotificationType::getUsersNotified()
	 */
	public function getUsersNotified( \EchoEvent $event, array &$users ) {

		// Notify the user(s) added to a course
		$usersToAddIds = $event->getExtraParam( 'users' );

		foreach ( $usersToAddIds as $userId ) {
			$users[$userId] = \User::newFromId( $userId );
		}
	}

	/**
	 * Trigger a notification. Specific parameters here:
	 *   'role-add-title' Title The title of the course the user has been added to
	 *   'agent' User The user who added the user to a course
	 *   'users' Users An array of users who were added to a course
	 *
	 * @since 0.4 alpha
	 * @see EducationProgram.INotificationType::trigger()
	 */
	public function trigger( $params ) {
		\EchoEvent::create( array(
			'type' => $this->getKey(),
			'title' => $params['role-add-title'],
			'agent' => $params['agent'],
			'extra' => array (
				// user(s) added to the course
				'users' => $params['users'],
			),
		) );
	}
}
