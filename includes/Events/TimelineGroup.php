<?php

namespace EducationProgram\Events;

use EducationProgram\Settings;
use IContextSource;
use MWException;
use Html;
use Linker;
use Message;
use User;
use Title;
use EducationProgram\Events\Event;

/**
 * Class for displaying a group of Education Program events in a timeline.
 *
 * FIXME: these classes are abusing inheritance.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
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
	 * @param EventGroup $group
	 * @param IContextSource|null $context
	 *
	 * @return mixed
	 * @throws MWException
	 */
	public static function newFromEventGroup( EventGroup $group, IContextSource $context = null ) {
		$type = null;

		/**
		 * @var Event $event
		 */
		foreach ( $group->getEvents() as $event ) {
			if ( is_null( $type ) ) {
				$type = $event->getType();
			}
			elseif ( $type !== $event->getType() ) {
				throw new MWException( 'Got events of different types when trying to build a ' . __CLASS__ );
			}
		}

		$typeMap = array(
			'edit-' . NS_MAIN => '\EducationProgram\Events\EditGroup',
			'edit-' . NS_TALK => '\EducationProgram\Events\EditGroup',
			'edit-' . NS_USER => '\EducationProgram\Events\EditGroup',
			'edit-' . NS_USER_TALK => '\EducationProgram\Events\EditGroup',
		);

		$class = array_key_exists( $type, $typeMap ) ? $typeMap[$type] : '\EducationProgram\Events\UnknownGroup';

		return new $class( $group->getEvents(), $context );
	}

	/**
	 * Constructor.
	 *
	 * @since 0.1
	 *
	 * @param Event[] $events
	 * @param IContextSource|null $context
	 */
	protected function __construct( array $events, IContextSource $context = null ) {
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
			User::newFromId( $event->getUserId() ),
			$this->getLanguage()->time( $event->getTime() ),
			$this->getLanguage()->date( $event->getTime() )

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

		$user = User::newFromId( $event->getUserId() );
		$info = $event->getInfo();

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
			$userIds[] = $event->getUserId();
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

		$info = $this->events[0]->getInfo();
		$type = explode( '-', $this->events[0]->getType() );
		$type = (int)array_pop( $type );

		$keys = array(
			NS_MAIN => 'article',
			NS_TALK => 'talk',
			NS_USER => 'user',
			NS_USER_TALK => 'usertalk',
		);

		// Give grep a chance to find the usages:
		// ep-timeline-users-edit-article, ep-timeline-users-edit-talk, ep-timeline-users-edit-user,
		// ep-timeline-users-edit-usertalk, ep-timeline-users-edit-user-self, ep-timeline-users-edit-usertalk-self,
		// ep-timeline-users-create-article, ep-timeline-users-create-talk, ep-timeline-users-create-user,
		// ep-timeline-users-create-usertalk, ep-timeline-users-create-user-self, ep-timeline-users-create-usertalk-self
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
			&& count( $userIds ) == 1 ) {
			$user = User::newFromName( $subjectText );

			if ( $user instanceof User && $userIds[0] == $user->getId() ) {
				$messageKey .= '-self';
			}
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
