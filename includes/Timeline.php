<?php

namespace EducationProgram;
use IContextSource;

/**
 * Education Program timeline.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class Timeline extends \ContextSource {

	/**
	 * List of events to display in this timeline.
	 *
	 * @since 0.1
	 * @var Event[]
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
	public function __construct( IContextSource $context, array /* of Event */ $events ) {
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
				return TimelineGroup::newFromEvents( $group['events'] )->getHTML();
			}, $this->getSortedGroups() )
		);
	}

	/**
	 * Groups the events by affected page and sorts the groups by
	 * the last change they contain, most recent first.
	 *
	 * @since 0.1
	 *
	 * @return Event[]
	 */
	protected function getSortedGroups() {
		$groups = array();

		foreach ( $this->events as $event ) {
			$eventInfo = $event->getField( 'info' );

			if ( array_key_exists( 'page', $eventInfo ) ) {
				$groupId = $eventInfo['page'] . '|';
				$groupId .= array_key_exists( 'parent', $eventInfo ) && is_null( $eventInfo['parent'] ) ? 'create' : 'edit';

				if ( array_key_exists( $groupId, $groups ) ) {
					$groups[$groupId]['events'][] = $event;

					if ( $event->getField( 'time' ) > $groups[$groupId]['time'] ) {
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

		$out->addModules( self::getModules() );
		$out->addHTML( $this->getHTML() );
	}

	/**
	 * Returns the modules needed for display.
	 *
	 * @since 0.1
	 *
	 * @return array
	 */
	public static function getModules() {
		return array(
			'ep.timeline'
		);
	}

}
