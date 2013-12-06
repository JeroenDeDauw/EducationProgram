<?php

namespace EducationProgram\Events;

use EducationProgram\Settings;
use Language;
use MWException;
use Html;
use Linker;
use Message;
use OutputPage;
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
abstract class TimelineGroup {

	/**
	 * @since 0.1
	 * @var Event[]
	 */
	protected $events;

	/**
	 * @since 0.3
	 * @var OutputPage
	 */
	protected $outputPage;

	/**
	 * @since 0.3
	 * @var Language
	 */
	protected $language;

	/**
	 * Creates a new TimelineGroup from a list of events.
	 * This list needs to be non-empty and contain events with the same type.
	 * If this is not the case, an exception will be thrown.
	 *
	 * FIXME: put in factory/builder
	 * FIXME: do not restrict component interface
	 * FIXME: do not prevent control over component lifecycle
	 *
	 * @since 0.1
	 *
	 * @param EventGroup $group
	 * @param OutputPage $outputPage
	 * @param Language $language
	 *
	 * @return mixed
	 * @throws MWException
	 */
	public static function newFromEventGroup( EventGroup $group, OutputPage $outputPage, Language $language ) {
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

		return new $class( $group->getEvents(), $outputPage, $language );
	}

	protected function __construct( array $events, OutputPage $outputPage, Language $language ) {
		$this->outputPage = $outputPage;
		$this->language = $language;
		$this->events = $events;
	}

	/**
	 * Display the event.
	 *
	 * @since 0.1
	 */
	public function display() {
		$this->outputPage->addHTML( $this->getHTML() );
	}

	/**
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

	protected function newMessage( $key ) {
		$params = func_get_args();
		array_shift( $params );

		$message = new Message( $key, $params );
		return $message->inLanguage( $this->language );
	}

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
		return $this->newMessage(
			'ep-timeline-unknown',
			User::newFromId( $event->getUserId() ),
			$this->language->time( $event->getTime() ),
			$this->language->date( $event->getTime() )

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
				$text = $this->newMessage( 'ep-timeline-cutoff', $text )->plain();
			}
		}
		else {
			$text = trim( $info['comment'] ) === '' ? $this->newMessage( 'ep-timeline-no-summary' )->plain() : $info['comment'];
		}

		$html .= strip_tags(
			$this->outputPage->parseInline( $text ),
			'<a><b><i>'
		);

		$html .= '<br />';

		$html .= '<span class="ep-event-ago">' . $this->newMessage(
			'ep-timeline-ago',
			$this->language->formatDuration( $event->getAge(), array( 'days', 'hours', 'minutes' ) )
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
		$userNames = array();

		foreach ( array_slice( $userIds, 0, Settings::get( 'timelineUserLimit' ) ) as $userId ) {
			$userNames[$userId] = User::newFromId( $userId )->getName();
			$userLinks[] = '<b>' . Linker::userLink( $userId, $userNames[$userId] ) . '</b>';
		}

		$remainingUsers = count( $userIds ) - count( $userLinks );

		if ( !empty( $remainingUsers ) ) {
			$userLinks[] = $this->newMessage(
				'ep-timeline-remaining',
				$this->language->formatNum( $remainingUsers )
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

		if ( in_array( $type, array( NS_USER, NS_USER_TALK ) )
			&& count( $userIds ) == 1 ) {
			$user = User::newFromName( $subjectText );

			if ( $user instanceof User && $userIds[0] == $user->getId() ) {
				$messageKey .= '-self';
			}
		}

		return $this->newMessage(
			$messageKey,
			Message::rawParam( $this->language->listToText( $userLinks ) ),
			$info['page'],
			$subjectText,
			count( $this->events ),
			count( $userIds ),
			implode( '', $userNames )
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
