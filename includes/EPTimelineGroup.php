<?php

/**
 * Class for displaying a group of Education Program events in a timeline.
 *
 * @since 0.1
 *
 * @file EPTimelineGroup.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
abstract class EPTimelineGroup extends ContextSource {

	/**
	 * @since 0.1
	 * @var array of EPEvent
	 */
	protected $events;

	/**
	 * Creates a new EPTimelineGroup from a list of events.
	 * This list needs to be non-empty and contain events with the same type.
	 * If this is not the case, an exception will be thrown.
	 *
	 * @since 0.1
	 *
	 * @param array $events
	 * @param IContextSource|null $context
	 *
	 * @return mixed
	 * @throws MWException
	 */
	public static function newFromEvents( array /* of EPEvent */ $events, IContextSource $context = null ) {
		if ( empty( $events ) ) {
			throw new MWException( 'Need at least one event to build a ' . __CLASS__ );
		}

		$type = null;

		foreach ( $events as /* EPEvent */ $event ) {
			if ( is_null( $type ) ) {
				$type = $event->getField( 'type' );
			}
			elseif ( $type !== $event->getField( 'type' ) ) {
				throw new MWException( 'Got events of different types when trying to build a ' . __CLASS__ );
			}
		}

		$typeMap = array(
			'edit-' . NS_MAIN => 'EPEditGroup',
			'edit-' . NS_TALK => 'EPEditGroup',
			'edit-' . NS_USER => 'EPEditGroup',
			'edit-' . NS_USER_TALK => 'EPEditGroup',
		);

		$class = array_key_exists( $type, $typeMap ) ? $typeMap[$type] : 'EPUnknownGroup';

		return new $class( $events, $context );
	}

	/**
	 * Constructor.
	 *
	 * @since 0.1
	 *
	 * @param array $events
	 * @param IContextSource|null $context
	 */
	protected function __construct( array /* of EPEvent */ $events, IContextSource $context = null ) {
		if ( !is_null( $context ) ) {
			$this->setContext( $context );
		}

		$this->events = $events;
	}

	/**
	 * Display the event.
	 *
	 * @since 0.1
	 */
	public function display() {
		$this->getOutput()->addHTML( $this->getHTML() );
	}

	/**
	 * Returns the events.
	 *
	 * @since 0.1
	 *
	 * @return array of EPEvent
	 */
	public function getEvents() {
		return $this->events;
	}

	/**
	 * Builds and returns the HTML for the event.
	 *
	 * @since 0.1
	 *
	 * @return string
	 */
	public function getHTML() {
		return
			Html::rawElement(
				'span',
				$this->getHeaderAttributes(),
				$this->getHeaderHTML()
			) .
			Html::rawElement(
				'div',
				$this->getClusterAttributes(),
				$this->getClusterHTML()
			);
	}

	protected function getHeaderHTML() {
		return '';
	}

	protected function getClusterHTML() {
		return implode( '', array_map( array( $this, 'getSegment' ), $this->events ) );
	}

	protected function getHeaderAttributes() {
		return array(
			'class' => 'ep-timeline-group-header'
		);
	}

	protected function getClusterAttributes() {
		return array(
			'class' => 'ep-timeline-group'
		);
	}

	protected function getSegment( EPEvent $event ) {
		return Html::rawElement(
			'div',
			$this->getSegmentAttributes( $event ),
			$this->getSegmentHTML( $event )
		);
	}

	protected function getSegmentAttributes( EPEvent $event ) {
		return array(
			'class' => 'ep-event-item',
		);
	}

	/**
	 * Builds and returns the HTML for a single of the event segments.
	 *
	 * @since 0.1
	 *
	 * @param EPEvent $event
	 *
	 * @return string
	 */
	protected abstract function getSegmentHTML( EPEvent $event );

}

class EPUnknownGroup extends EPTimelineGroup {

	protected function getSegmentHTML( EPEvent $event ) {
		return $this->msg(
			'ep-timeline-unknown',
			$event->getUser()->getName(),
			$this->getLanguage()->timeanddate( $event->getField( 'time' ) )

		)->escaped();
	}

}

class EPEditGroup extends EPTimelineGroup {

	protected function getSegmentHTML( EPEvent $event ) {
		$html = '';

		$user = $event->getUser();
		$info = $event->getField( 'info' );

		$html .= Linker::userLink( $user->getId(), $user->getName() );

		$html .= '&#160;';

		$html .= $this->getOutput()->parseInline( $info['comment'] );

		$html .= '<br />';

		$html .= '<span class="ep-event-ago">' . $this->msg(
			'ep-timeline-ago',
			EPUtils::formatDuration( $event->getAge(), array( 'days', 'hours', 'minutes' ) )
		)->escaped() . '</span>';

		return $html;
	}

	protected function getHeaderHTML() {
		$userIds = array();

		foreach ( $this->events as /* EPEvent */ $event ) {
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

		$info = $this->events[0]->getField( 'info' );
		$type = explode( '-', $this->events[0]->getField( 'type' ) );
		$type = (int)array_pop( $type );

		$keys = array(
			NS_MAIN => 'article',
			NS_TALK => 'talk',
			NS_USER => 'user',
			NS_USER_TALK => 'usertalk',
		);

		$isNew = array_key_exists( 'parent', $info ) && is_null( $info['parent'] );
		$messageKey = 'ep-timeline-users-' . ( $isNew ? 'create' : 'edit' ) . '-' . $keys[$type];

		$subjectText = Title::newFromText( $info['page'] )->getSubjectPage()->getText();

		$messageArgs = array(
			Message::rawParam( $language->listToText( $userLinks ) ),
			$info['page'],
			$subjectText,
			count( $this->events ),
			count( $userIds ),
		);

		if ( in_array( $type, array( NS_USER, NS_USER_TALK ) )
			&& count( $userIds ) == 1 && $userIds[0] == User::newFromName( $subjectText )->getId() ) {
			$messageKey .= '-self';
		}

		return $this->msg(
			$messageKey,
			$messageArgs
		)->parse() . '<br />';
	}

}

class EPEnlistGroup extends EPTimelineGroup {

	protected function getSegmentHTML( EPEvent $event ) {
		return '';
	}

}