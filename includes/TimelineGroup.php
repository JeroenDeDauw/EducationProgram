<?php

namespace EducationProgram;
use IContextSource, MWException, Html, Linker, Message, User, Title;

/**
 * Class for displaying a group of Education Program events in a timeline.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
abstract class TimelineGroup extends \ContextSource {

	/**
	 * @since 0.1
	 * @var Event[]
	 */
	protected $events;

	/**
	 * Creates a new TimelineGroup from a list of events.
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
	public static function newFromEvents( array /* of Event */ $events, IContextSource $context = null ) {
		if ( empty( $events ) ) {
			throw new MWException( 'Need at least one event to build a ' . __CLASS__ );
		}

		$type = null;

		/**
		 * @var Event $event
		 */
		foreach ( $events as $event ) {
			if ( is_null( $type ) ) {
				$type = $event->getField( 'type' );
			}
			elseif ( $type !== $event->getField( 'type' ) ) {
				throw new MWException( 'Got events of different types when trying to build a ' . __CLASS__ );
			}
		}

		$typeMap = array(
			'edit-' . NS_MAIN => 'EditGroup',
			'edit-' . NS_TALK => 'EditGroup',
			'edit-' . NS_USER => 'EditGroup',
			'edit-' . NS_USER_TALK => 'EditGroup',
		);

		$class = array_key_exists( $type, $typeMap ) ? $typeMap[$type] : 'UnknownGroup';

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
	protected function __construct( array /* of Event */ $events, IContextSource $context = null ) {
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
	 * @return Event[]
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

	/**
	 * Returns the HTML for the groups header.
	 *
	 * @since 0.1
	 *
	 * @return string
	 */
	protected function getHeaderHTML() {
		return '';
	}

	/**
	 * Returns HTML that holds the actual event list.
	 *
	 * @since 0.1
	 *
	 * @return string
	 */
	protected function getClusterHTML() {
		return implode( '', array_map( array( $this, 'getSegment' ), $this->events ) );
	}

	/**
	 * Returns the HTML tag attributes for the groups header element.
	 *
	 * @since 0.1
	 *
	 * @return array
	 */
	protected function getHeaderAttributes() {
		return array(
			'class' => 'ep-timeline-group-header'
		);
	}

	/**
	 * Returns the HTML tag attributes for the groups events container element.
	 *
	 * @since 0.1
	 *
	 * @return array
	 */
	protected function getClusterAttributes() {
		return array(
			'class' => 'ep-timeline-group'
		);
	}

	/**
	 * Returns the HTML representing a single event.
	 *
	 * @since 0.1
	 *
	 * @param Event $event
	 *
	 * @return string
	 */
	protected function getSegment( Event $event ) {
		return Html::rawElement(
			'div',
			$this->getSegmentAttributes( $event ),
			$this->getSegmentHTML( $event )
		);
	}

	/**
	 * Returns the HTML tag attributes for the event container element.
	 *
	 * @since 0.1
	 *
	 * @param Event $event
	 *
	 * @return array
	 */
	protected function getSegmentAttributes( Event $event ) {
		return array(
			'class' => 'ep-event-item',
		);
	}

	/**
	 * Builds and returns the HTML for a single of the event segments.
	 *
	 * @since 0.1
	 *
	 * @param Event $event
	 *
	 * @return string
	 */
	protected abstract function getSegmentHTML( Event $event );

}

/**
 * Represents a group of events that have an unknown type.
 * Displays them as "something happened at that time" :)
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class UnknownGroup extends TimelineGroup {

	/**
	 * Builds and returns the HTML for a single of the event segments.
	 *
	 * @since 0.1
	 *
	 * @param Event $event
	 *
	 * @return string
	 */
	protected function getSegmentHTML( Event $event ) {
		return $this->msg(
			'ep-timeline-unknown',
			$event->getUser()->getName(),
			$this->getLanguage()->time( $event->getField( 'time' ) ),
			$this->getLanguage()->date( $event->getField( 'time' ) )

		)->escaped();
	}

}

/**
 * Represents a group of edit events.
 * Distinguishes between edits to different namespaces.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EditGroup extends TimelineGroup {
	/**
	 * Builds and returns the HTML for a single of the event segments.
	 *
	 * @since 0.1
	 *
	 * @param Event $event
	 *
	 * @return string
	 */
	protected function getSegmentHTML( Event $event ) {
		$html = '';

		$user = $event->getUser();
		$info = $event->getField( 'info' );

		$html .= Linker::userLink( $user->getId(), $user->getName() );

		$html .= '&#160;';

		if ( array_key_exists( 'addedlines', $info ) ) {
			$text = implode( ' ', $info['addedlines'] );

			if ( strlen( $text ) > Settings::get( 'timelineMessageLengthLimit' ) ) {
				$text = substr( $text, 0, Settings::get( 'timelineMessageLengthLimit' ) );
				$text = $this->msg( 'ep-timeline-cutoff', $text )->plain();
			}
		}
		else {
			$text = $info['comment'];
		}

		$html .= strip_tags(
			$this->getOutput()->parseInline( $text ),
			'<a><b><i>'
		);

		$html .= '<br />';

		$html .= '<span class="ep-event-ago">' . $this->msg(
			'ep-timeline-ago',
			$this->getLanguage()->formatDuration( $event->getAge(), array( 'days', 'hours', 'minutes' ) )
		)->escaped() . '</span>';

		return $html;
	}

	/**
	 * Returns the HTML for the groups header.
	 *
	 * @since 0.1
	 *
	 * @return string
	 */
	protected function getHeaderHTML() {
		$userIds = array();

		/**
		 * @var Event $event
		 */
		foreach ( $this->events as $event ) {
			$userIds[] = $event->getField( 'user_id' );
		}

		$userIds = array_unique( $userIds );

		$userLinks = array();

		foreach ( array_slice( $userIds, 0, Settings::get( 'timelineUserLimit' ) ) as $userId ) {
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

/**
 * Represents a group of enlistment events.
 * Enlistment events are people becoming associated as $role with a course, or losing this association.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EnlistGroup extends TimelineGroup {

	/**
	 * Builds and returns the HTML for a single of the event segments.
	 *
	 * @since 0.1
	 *
	 * @param Event $event
	 *
	 * @return string
	 */
	protected function getSegmentHTML( Event $event ) {
		return '';
	}

}
