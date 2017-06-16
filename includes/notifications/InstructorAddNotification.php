<?php

namespace EducationProgram;

/**
 * Notification type for an instructor being added to a course. Extends
 * RoleAddNotification to specify instructor specific keys.
 *
 * @since 0.4 alpha
 *
 * @ingroup EducationProgram
 *
 * @license GNU GPL v2+
 * @author JJ Liu < jie dot squared at gmail dot com >
 */

class InstructorAddNotification extends RoleAddNotification {

	const KEY = 'ep-instructor-add-notification';

	/**
	 * @since 0.4 alpha
	 * @see EducationProgram.INotificationType::getKey()
	 */
	public function getKey() {
		return InstructorAddNotification::KEY;
	}

}
