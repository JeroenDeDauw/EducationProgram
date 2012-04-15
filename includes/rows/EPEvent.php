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
				'parent' => $revision->getParentId()
			),
		);

		return EPEvents::singleton()->newFromArray( $fields );
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