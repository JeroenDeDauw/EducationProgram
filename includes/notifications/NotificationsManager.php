<?php

namespace EducationProgram;

use InvalidArgumentException;

/**
 * This class manages setup and generation of Echo notifications for this
 * extension.
 *
 * @since 0.4 alpha
 *
 * @ingroup EducationProgram
 *
 * @license GNU GPL v2+
 * @author Andrew Green < agreen at wikimedia dot org >
 */
class NotificationsManager {

	/**
	 * The string key for this notification category. Must coordinate with
	 * message key 'echo-category-title-{CATEGORY}' in EducationProgram.i18n.php
	 * and the default user options 'echo-subscriptions-web-education-program'
	 * and 'echo-subscriptions-email-education-program' in EducationProgram.php.
	 *
	 * @since 0.4 alpha
	 *
	 * @var string
	 */
	const CATEGORY = 'education-program';

	/**
	 * An associative array. Keys are string keys that designate notification
	 * types. Values are instances of notification type classes (implementing INotificationType).
	 *
	 * @var INotificationType[]
	 */
	private $typesByKey = [];

	/**
	 * Get the string key for the EP notifications category.
	 *
	 * @since 0.4 alpha
	 *
	 * @return string
	 */
	public function getCategoryKey() {
		return self::CATEGORY;
	}

	/**
	 * Register a notification type.
	 *
	 * @since 0.4 alpha
	 *
	 * @param string $typeName The name of an implementation of
	 *   EducationProgram/INotificationType
	 */
	public function registerType( $typeName ) {
		/** @var INotificationType $type */
		$type = new $typeName();
		$this->typesByKey[$type->getKey()] = $type;
	}

	/**
	 * Set up Echo notification types and categories. Called from
	 * onBeforeCreateEchoEvent.
	 *
	 * @see https://www.mediawiki.org/wiki/Extension:Echo/BeforeCreateEchoEvent
	 *
	 * @since 0.4 alpha
	 *
	 * @param array &$notifications
	 * @param array &$notificationCategories
	 * @param array &$icons
	 */
	public function setUpTypesAndCategories(
		array &$notifications,
		array &$notificationCategories,
		array &$icons
	) {
		// register the category
		$notificationCategories[self::CATEGORY] = [
			'tooltip' => 'ep-echo-pref-tooltip',
		];

		foreach ( $this->typesByKey as $typeKey => $type ) {
			$params = $type->getParameters();

			// tell Echo about the notification type
			$notifications[$typeKey] = array_merge(
				$params,
				[
					'category' => self::CATEGORY,
				]
			);

			// Set the icon data if required.
			// Note: we don't check that info with the same key isn't squished
			$iconParams = $type->getIconParameters();
			if ( ( $iconParams !== null ) && ( isset( $params['icon'] ) ) ) {
				$icons[$params['icon']] = $iconParams;
			}
		}
	}

	/**
	 * Determine which users get a notification. Called from
	 * onEchoGetDefaultNotifiedUsers.
	 *
	 * @see https://www.mediawiki.org/wiki/Echo_(Notifications)/Developer_guide
	 *
	 * @since 0.4 alpha
	 *
	 * @param \EchoEvent $event
	 * @param array &$users
	 */
	public function getUsersNotified( \EchoEvent $event, array &$users ) {
		$key = $event->getType();

		if ( isset( $this->typesByKey[$key] ) ) {
			$type = $this->typesByKey[$key];
			$type->getUsersNotified( $event, $users );
		}
	}

	/**
	 * Trigger a notification event of the type indicated by $key.
	 *
	 * @param string $key The string key of the notification type to trigger
	 * @param array $parameters The parameters for INotificationType::trigger()
	 *   (not the same as the parameters for EchoEvent::create()).
	 * @throws InvalidArgumentException
	 */
	public function trigger( $key, array $parameters ) {
		if ( !isset( $this->typesByKey[$key] ) ) {
			throw new InvalidArgumentException( 'No notification type for key ' . $key );
		}

		$type = $this->typesByKey[$key];

		// Checking that Echo is installed. We're assuming that \EchoEvent will
		// be used to trigger the notification, and that this is the only bit of
		// code that risks breakage if Echo is not installed. Safe-ish!
		if ( !class_exists( '\EchoEvent' ) ) {
			trigger_error( 'Tried to send a notification, couldn\'t find ' .
				'the EchoEvent class. Is Echo installed?', E_USER_NOTICE );
		} else {
			$type->trigger( $parameters );
		}
	}
}
