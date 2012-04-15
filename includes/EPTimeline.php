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
		return implode(
			'<br />',
			array_map( function( array $group ) {
				return EPEventDisplay::newFromEvents( $group['events'] )->getHTML();
			}, $this->getSortedGroups() )
		);
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
				$groupId = $eventInfo['page'] . '|';
				$groupId .=

				if ( array_key_exists( $eventInfo['page'], $groups ) ) {
					$groups[$groupId]['events'][] = $event;

					if ( $event->getField( 'time' ) > $groups[$eventInfo['page']]['time'] ) {
						$groups[$groupId]['time'] = $event->getField( 'time' );
					}
				}
				else {
					$groups[$groupId] = array(
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
