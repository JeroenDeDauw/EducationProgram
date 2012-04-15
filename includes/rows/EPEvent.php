<?php

/**
 * Class representing a single Education Program event.
 *
 * @since 0.1
 *
 * @file EPEvent.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EPEvent extends ORMRow {

	/**
	 * Field for caching the linked user.
	 *
	 * @since 0.1
	 * @var User|false
	 */
	protected $user = false;

	/**
	 * Create a new edit event from a revision.
	 *
	 * @since 0.1
	 *
	 * @param Revision $revision
	 * @param User $user
	 *
	 * @return EPEvent
	 */
	public static function newFromRevision( Revision $revision, User $user ) {
		$title = $revision->getTitle();

		$fields = array(
			'user_id' => $user->getId(),
			'time' => $revision->getTimestamp(),
			'type' => 'edit-' . $title->getNamespace(),
			'info' => array(
				'page' => $title->getFullText(),
				'comment' => $revision->getComment(),
				'minoredit' => $revision->isMinor(),
			),
		);

		return EPEvents::singleton()->newFromArray( $fields );
	}

	/**
	 * Returns an event display object for visualizing the event.
	 *
	 * @since 0.1
	 *
	 * @return EPEventDisplay
	 */
	public function getEventDisplay() {
		$typeMap = array(
			'edit-' . NS_MAIN => 'EPEditEvent',
			'edit-' . NS_TALK => 'EPEditEvent',
			'edit-' . NS_USER => 'EPEditEvent',
			'edit-' . NS_USER_TALK => 'EPEditEvent',
		);

		$class = array_key_exists( $this->getField( 'type' ), $typeMap ) ? $typeMap[$this->getField( 'type' )] : 'EPUnknownEvent';

		return new $class( $this );
	}

	/**
	 * Returns the user that made the event.
	 *
	 * @since 0.1
	 *
	 * @return User
	 */
	public function getUser() {
		if ( $this->user === false ) {
			$this->user = User::newFromId( $this->getField( 'user_id' ) );
		}

		return $this->user;
	}

	/**
	 * Returns the age of the event in seconds.
	 *
	 * @since 0.1
	 *
	 * @return integer
	 */
	public function getAge() {
		return time() - (int)wfTimestamp( TS_UNIX, $this->getField( 'time' ) );
	}

}

/**
 * Class for displaying a single Education Program event.
 * This used used by EPTimeline which creates regions in which this content is put.
 *
 * @since 0.1
 *
 * @file EPEvent.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
abstract class EPEventDisplay extends ContextSource {

	/**
	 * @since 0.1
	 * @var EPEvent
	 */
	protected $event;

	/**
	 * Constructor.
	 *
	 * @since 0.1
	 *
	 * @param EPEvent $event
	 * @param IContextSource|null $context
	 */
	public function __construct( EPEvent $event, IContextSource $context = null ) {
		if ( !is_null( $context ) ) {
			$this->setContext( $context );
		}

		$this->event = $event;
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
	 * Returns the event.
	 *
	 * @since 0.1
	 *
	 * @return EPEvent
	 */
	public function getEvent() {
		return $this->event;
	}

	/**
	 * Builds and returns the HTML for the event.
	 *
	 * @since 0.1
	 *
	 * @return string
	 */
	public function getHTML() {
		return Html::rawElement(
			'div',
			$this->getDivAttributes(),
			$this->getInnerHTML()
		);
	}

	protected function getDivAttributes() {
		return array(
			'class' => 'ep-event-item',
		);
	}

	/**
	 * Builds and returns the HTML for the event.
	 *
	 * @since 0.1
	 *
	 * @return string
	 */
	protected abstract function getInnerHTML();

}

class EPUnknownEvent extends EPEventDisplay {

	protected function getInnerHTML() {
		return $this->msg(
			'ep-event-unknown',
			$this->event->getUser()->getName(),
			$this->getLanguage()->timeanddate( $this->event->getField( 'time' ) )

		)->escaped();
	}

}

class EPEditEvent extends EPEventDisplay {

	protected function getInnerHTML() {
		$html = '';

		$user = $this->event->getUser();
		$info = $this->event->getField( 'info' );

		$html .= Linker::userLink( $user->getId(), $user->getName() );

		$html .= '&#160;';

		$html .= $this->getOutput()->parseInline( $info['comment'] );

		$html .= '<br />';

		$html .= '<span class="ep-event-ago">' . $this->msg(
			'ep-event-ago',
			EPUtils::formatDuration( $this->event->getAge(), array( 'days', 'hours', 'minutes' ) )
		)->escaped() . '</span>';

		return $html;
	}

}

class EPEnlistEvent extends EPEventDisplay {

	protected function getInnerHTML() {
		return '';
	}

}