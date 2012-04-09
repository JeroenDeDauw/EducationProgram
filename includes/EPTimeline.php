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
		$groups = array();

		foreach ( $this->events as $index => /* EPEvent */ $event ) {
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

		$segments = array();

		foreach ( $groupTimes as $groupIndex => $time ) {
			$group = $groups[$groupIndex];


		}

		foreach ( $this->events as /* EPEvent */ $event ) {
			$segments[] = $event->getEventDisplay()->getHTML();
		}

		return implode( '<br />', $segments ); // TODO
	}

	/**
	 * Displays the timeline.
	 *
	 * @since 0.1
	 */
	public function display() {
		$this->getOutput()->addHTML( $this->getHTML() );
	}

}
