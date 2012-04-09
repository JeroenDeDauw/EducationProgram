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

	public function getEventContext() {
		$typeMap = array(
			'edit-' . NS_MAIN => 'EPEditEvent',
			'edit-' . NS_TALK => 'EPEditEvent',
			'edit-' . NS_USER => 'EPEditEvent',
			'edit-' . NS_USER_TALK => 'EPEditEvent',
		);

		$class = $typeMap[$this->getField( 'type' )];

		return new $class( $this );
	}

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

abstract class EPEventContext extends ContextSource {

	protected $event;

	public function __construct( EPEvent $event, IContextSource $context = null ) {
		if ( !is_null( $context ) ) {
			$this->setContext( $context );
		}

		$this->event = $event;
	}

	public function display() {
		$this->getOutput()->addHTML( $this->getHTML() );
	}

	public abstract function getHTML();

}

class EPEditEvent extends EPEventContext {

	public function getHTML() {
		$html = '';

		$user = $this->event->getUser();
		$info = $this->event->getField( 'info' );

		$html .= Linker::userLink( $user->getId(), $user->getName() );

		$html .= '&#160;';

		$html .= htmlspecialchars( $info['comment'] );

		$html .= '<br />';

		$html .= EPUtils::formatDuration( $this->event->getAge(), array( 'days', 'hours', 'minutes' ) );

		return $html;
	}

}