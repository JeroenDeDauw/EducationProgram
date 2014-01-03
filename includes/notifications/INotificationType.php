<?php

namespace EducationProgram;

/**
 * A type of notification. Provides information for defining the notification
 * type in Echo, and a means of triggering notifications of this type and
 * channeling them to users.
 *
 * Note: a notification type is *not* a notification category. Notification
 * categories are used in user preferences and can include many types. In the
 * Echo documentation, a notification type is often just called a
 * "notification".
 *
 * @see https://www.mediawiki.org/wiki/Echo_%28Notifications%29/Developer_guide#Defining_a_notification
 *
 * @since 0.4 alpha
 *
 * @ingroup EducationProgram
 *
 * @license GNU GPL v2+
 * @author Andrew Green < agreen at wikimedia dot org >
 */
interface INotificationType {

	/**
	 * Get the string key that designates this type of notification. This is the
	 * key used in $notifications in onBeforeCreateEchoEvent, and as the 'type'
	 * parameter in EchoEvent::create().
	 *
	 * @since 0.4 alpha
	 *
	 * @return string The string key
	 */
	function getKey();

	/**
	 * Get the array of parameters used to define the type of notification in
	 * onBeforeCreateEchoEvent, *except* the 'formatter-class' and 'category'
	 * parameters, which will be provided by the NotificationsManager.
	 *
	 * @since 0.4 alpha
	 *
	 * @return array The associative array of parameters
	 */
	function getParameters();

	/**
	 * Get an array of parameters for the icon that this notification type will
	 * use. Allowed parameters are 'url' and 'path'. This method will be called
	 * by the NotificationsManager to set up the icon in
	 * $wgEchoNotificationIcons.
	 *
	 * The icon key will be the value of the 'icon' parameter provided by
	 * getParameters(). Icon keys should have a unique prefix to prevent
	 * collisions with other icons.
	 *
	 * If you don't want to set up an icon, just return null here.
	 *
	 * @since 0.4 alpha
	 *
	 * @return null|array The associative array of parameters for the icon
	 */
	function getIconParameters();

	/**
	 * Trigger a notification of this type. The parameters used here need not be
	 * the same as those sent to EchoEvent::create(), and will vary from one
	 * notification type to another. Implementations of this method will
	 * probably take higher-level info or objects and process them for the call
	 * to EchoEvent::create().
	 *
	 * Any checks regarding the availability of Notifications and the EchoEvent
	 * class are the responsibility of the caller of this method, not the
	 * implementation.
	 *
	 * @since 0.4 alpha
	 *
	 * @param array $params An associative array of parameters
	 */
	function trigger( $params );

	/**
	 * Set which users will receive a notification event, based on the data in
	 * $event. Pass the list of users in to $users the same way the Echo doc
	 * suggests you do in onEchoGetDefaultNotifiedUsers.
	 *
	 * @since 0.4 alpha
	 *
	 * @param $event EchoEvent
	 * @param $users array
	 */
	function getUsersNotified( \EchoEvent $event, array &$users );
}