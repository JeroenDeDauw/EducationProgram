<?php

namespace EducationProgram;

/**
 * Notification type for a student being added to a course. Extends
 * RoleAddNotification to specify student specific keys.
 *
 * @since 0.4 alpha
 *
 * @ingroup EducationProgram
 *
 * @license GNU GPL v2+
 * @author JJ Liu < jie dot squared at gmail dot com >
 */

class StudentAddNotification extends RoleAddNotification {
	const KEY = 'ep-student-add-notification';

	/**
	 * @since 0.4 alpha
	 * @see EducationProgram.INotificationType::getKey()
	 */
	public function getKey() {
		return StudentAddNotification::KEY;
	}

	/**
	 * @since 0.4 alpha
	 * @see EducationProgram.INotificationType::getParameters()
	 */
	public function getParameters() {
		return array_merge( parent::getGenericParameters(),
			array(
				'title-message' => 'ep-student-add-notification-title',
				'email-subject-message' => 'ep-student-add-notification-title-email-subject',
				'email-body-batch-message' => 'ep-student-add-notification-title-email-body',
			)
		);
	}
}