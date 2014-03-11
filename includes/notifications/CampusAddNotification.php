<?php

namespace EducationProgram;

/**
 * Notification type for a campus volunteer being added to a course. Extends
 * RoleAddNotification to specify campus volunteer specific keys.
 *
 * @since 0.4 alpha
 *
 * @ingroup EducationProgram
 *
 * @license GNU GPL v2+
 * @author JJ Liu < jie dot squared at gmail dot com >
 */

class CampusAddNotification extends RoleAddNotification {
	const KEY = 'ep-campus-add-notification';

	/**
	 * @since 0.4 alpha
	 * @see EducationProgram.INotificationType::getKey()
	 */
	public function getKey() {
		return CampusAddNotification::KEY;
	}

	/**
	 * @since 0.4 alpha
	 * @see EducationProgram.INotificationType::getParameters()
	 */
	public function getParameters() {
		return array_merge( parent::getGenericParameters(),
			array(
				'title-message' => 'ep-campus-add-notification-title',
				'email-subject-message' => 'ep-campus-add-notification-title-email-subject',
				'email-body-batch-message' => 'ep-campus-add-notification-title-email-body',
			)
		);
	}
}