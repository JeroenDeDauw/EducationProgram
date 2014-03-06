<?php

namespace EducationProgram;

/**
 * Notification type for edits to a course talk page.
 *
 * @since 0.4 alpha
 *
 * @ingroup EducationProgram
 *
 * @license GNU GPL v2+
 * @author Andrew Green < agreen at wikimedia dot org >
 */
class CourseTalkNotification implements INotificationType {

	const KEY = 'ep-course-talk-notification';

	/**
	 * @since 0.4 alpha
	 * @see EducationProgram.INotificationType::getKey()
	 */
	public function getKey() {
		return CourseTalkNotification::KEY;
	}

	/**
	 * @since 0.4 alpha
	 * @see EducationProgram.INotificationType::getParameters()
	 */
	public function getParameters() {
		return array(
			'primary-link' => array(
				'message' => 'ep-course-talk-link-text-view-message',
				'destination' => 'title'
			),
			'secondary-link' => array(
				'message' => 'ep-course-talk-link-text-view-changes',
				'destination' => 'diff'
			),
			'group' => 'interactive',

			// The custom message param 'short-title-text' requires a custom
			// notification formatter. See CourseFormatter.
			'title-message' => 'ep-course-talk-notification-title',
			'title-params' => array( 'agent', 'title', 'short-title-text' ),
			'email-subject-message' => 'ep-course-talk-notification-title-email-subject',
			'email-subject-params' => array( 'agent', 'short-title-text' ),
			'email-body-batch-message' => 'ep-course-talk-notification-title-email-body',
			'email-body-batch-params' => array( 'agent', 'short-title-text' ),
			'icon' => 'ep-course-talk-icon',
		);
	}

	/**
	 * @since 0.4 alpha
	 * @see EducationProgram.INotificationType::getIconParameters()
	 */
	public function getIconParameters() {
		return array( 'path' => 'EducationProgram/resources/images/course-talk-notification.png' );
	}

	/**
	 * @since 0.4 alpha
	 * @see EducationProgram.INotificationType::getUsersNotified()
	 */
	public function getUsersNotified( \EchoEvent $event, array &$users ) {

		// We assume that title is an EP course talk page title.
		$course = Extension::globalInstance()->newCourseStore()
			->getCourseByTitle( $event->getTitle()->getText() );

		// Notify anyone associated with the course (instructors, volunteers
		// or students).
		$roleObjs = array_merge(
			$course->getAllNonStudentRoleObjs(), $course->getStudents() );

		foreach ( $roleObjs as $roleObj ) {
			$user = $roleObj->getUser();
			$users[$user->getId()] = $user;
		}
	}

	/**
	 * Trigger a notification. Specific parameters here:
	 *   'course-talk-title' Title The course talk page title. It is assumed
	 *     that this is the talk page of an EP course. Don't send something that
	 *     isn't.
	 *   'agent' User The user who modified the course talk page
	 *   'revision' Revision The new revision of the course talk page
	 *
	 * @since 0.4 alpha
	 * @see EducationProgram.INotificationType::trigger()
	 */
	public function trigger( $params ) {

		$title = $params['course-talk-title'];

		// Don't send notifications for sub-talk-pages or if if the course
		// doesn't exist. Note: the second check depends on the first one.
		if ( Utils::isCourseSubPage( $title) ||
			!Courses::singleton()->getFromTitle( $title ) ) {
			return;
		}

		$eventParams = array(
			'type' => CourseTalkNotification::KEY,
			'title' => $title,
			'agent' => $params['agent'],
		);

		$revision = $params['revision'];

		if ( $revision ) {
			// 'revid' is used by the EchoBasicFormatter (which
			// CourseFormatter inherits from) to generate the diff
			// destination, which we use in the secondary link.
			$eventParams = array_merge(
				$eventParams, array (
					'extra' => array( 'revid' => $revision->getId() )
				)
			);
		}

		\EchoEvent::create( $eventParams );
	}
}