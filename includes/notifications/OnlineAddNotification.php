<?php

namespace EducationProgram;

/**
 * Notification type for an online volunteer being added to a course. Extends
 * RoleAddNotification to specify online volunteer specific keys.
 *
 * @since 0.4 alpha
 *
 * @ingroup EducationProgram
 *
 * @license GPL-2.0-or-later
 * @author JJ Liu < jie dot squared at gmail dot com >
 */
class OnlineAddNotification extends RoleAddNotification {

	const KEY = 'ep-online-add-notification';

	/**
	 * @since 0.4 alpha
	 * @see EducationProgram.INotificationType::getKey()
	 */
	public function getKey() {
		return self::KEY;
	}

}
