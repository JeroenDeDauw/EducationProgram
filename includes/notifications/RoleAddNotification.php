<?php

namespace EducationProgram;

/**
 * Notification type for a user being added to a course
 *
 * @since 0.4 alpha
 *
 * @ingroup EducationProgram
 *
 * @license GPL-2.0-or-later
 * @author JJ Liu < jie dot squared at gmail dot com >
 */
abstract class RoleAddNotification implements INotificationType {

	/**
	 * Returns the parameters that are identical across
	 * all roles for this notification.
	 *
	 * Subclasses should append to this array with role-specific
	 * parameters in their implementation of getParameters().
	 */
	public function getParameters() {
		return [
			'presentation-model' => 'EducationProgram\\PresentationModel\\RoleAdd',
			'group' => 'interactive',
			'section' => 'alert',
			'icon' => 'ep-added-to-course-icon',
		];
	}

	/**
	 * @since 0.4 alpha
	 * @see EducationProgram.INotificationType::getIconParameters()
	 */
	public function getIconParameters() {
		return [ 'path' => 'EducationProgram/resources/images/added-to-course-notification.png' ];
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
		\EchoEvent::create( [
			'type' => $this->getKey(),
			'title' => $params['role-add-title'],
			'agent' => $params['agent'],
			'extra' => [
				// user(s) added to the course
				'users' => $params['users'],
			],
		] );
	}
}
