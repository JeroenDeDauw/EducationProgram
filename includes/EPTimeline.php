<?php

/**
 * Education Program timeline.
 *
 * @since 0.1
 *
 * @file EPTimeline.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EPTimeline extends ContextSource {

	/**
	 * List of events to display in this timeline.
	 *
	 * @since 0.1
	 * @var array
	 */
	protected $events;

	/**
	 * Constructor.
	 *
	 * @since 0.1
	 *
	 * @param IContextSource $context
	 * @param array $events
	 */
	public function __construct( IContextSource $context, array /* of EPEvent */ $events ) {
		$this->setContext( $context );
		$this->events = $events;
	}


	/**
	 * Builds and returns the HTML for the timeline.
	 *
	 * @since 0.1
	 *
	 * @return string
	 */
	public function getHTML() {
		$groups = $this->getSortedGroups();

		$clusters = array();

		foreach ( $groups as $group ) {
			$segments = array();
			$userIds = array();

			foreach ( $group['events'] as /* EPEvent */ $event ) {
				$segments[] = $event->getEventDisplay()->getHTML();
				$userIds[] = $event->getField( 'user_id' );
			}

			$userIds = array_unique( $userIds );

			$userLinks = array();

			foreach ( array_slice( $userIds, 0, EPSettings::get( 'timelineUserLimit' ) ) as $userId ) {
				$userLinks[] = '<b>' . Linker::userLink( $userId, User::newFromId( $userId )->getName() ) . '</b>';
			}

			$remainingUsers = count( $userIds ) - count( $userLinks );

			$language = $this->getLanguage();

			if ( !empty( $remainingUsers ) ) {
				$userLinks[] = $this->msg(
					'ep-timeline-remaining',
					$language->formatNum( $remainingUsers )
				)->escaped();
			}

			$cluster = '';

			// TODO: this is evil, event display class should be group display
			$info = $group['events'][0]->getField( 'info' );
			$type = $group['events'][0]->getField( 'type' );

			$cluster .= $this->msg(
				'ep-timeline-users-' . $type
			)->rawParams(
				$language->listToText( $userLinks ),
				'<b>' . Linker::link( Title::newFromText( $info['page'] ) ) . '</b>'
			)->escaped() . '<br />';

			$cluster .= implode( '', $segments );

			$clusters[] = Html::rawElement(
				'div',
				array( 'class' => 'ep-timeline-group ep-timeline-' . $type ),
				$cluster
			);
		}

		return implode( '<br />', $clusters );
	}

	/**
	 * Groups the events by affected page and sorts the groups by
	 * the last change they contain, most recent first.
	 *
	 * @since 0.1
	 *
	 * @return array of EPEvent
	 */
	protected function getSortedGroups() {
		$groups = array();

		foreach ( $this->events as /* EPEvent */ $event ) {
			$eventInfo = $event->getField( 'info' );

			if ( array_key_exists( 'page', $eventInfo ) ) {
				if ( array_key_exists( $eventInfo['page'], $groups ) ) {
					$groups[$eventInfo['page']]['events'][] = $event;

					if ( $event->getField( 'time' ) > $groups[$eventInfo['page']]['time'] ) {
						$groups[$eventInfo['page']]['time'] = $event->getField( 'time' );
					}
				}
				else {
					$groups[$eventInfo['page']] = array(
						'time' => $event->getField( 'time' ),
						'events' => array( $event ),
					);
				}
			}
			else {
				$groups[] = array(
					'time' => $event->getField( 'time' ),
					'events' => array( $event ),
				);
			}
		}

		$groupTimes = array();

		foreach ( $groups as $index => $group ) {
			$groupTimes[$index] = $group['time'];
		}

		arsort( $groupTimes );

		$sortedGroups = array();

		foreach ( $groupTimes as $groupIndex => $time ) {
			$sortedGroups[] = $groups[$groupIndex];
		}

		return $sortedGroups;
	}

	/**
	 * Displays the timeline.
	 *
	 * @since 0.1
	 */
	public function display() {
		$out = $this->getOutput();

		$out->addModules( 'ep.timeline' );
		$out->addHTML( $this->getHTML() );
	}

}
