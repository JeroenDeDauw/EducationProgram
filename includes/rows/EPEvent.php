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
	 * @var User|bool false
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

		$info = array(
			'page' => $title->getFullText(),
			'comment' => $revision->getComment(),
			'minoredit' => $revision->isMinor(),
			'parent' => $revision->getParentId()
		);

		if ( MWNamespace::isTalk( $title->getNamespace() ) && !is_null( $revision->getParentId() ) ) {
			$diff = new Diff(
				explode( "\n", Revision::newFromId( $revision->getParentId() )->getText() ),
				explode( "\n", $revision->getText() )
			);

			// Only an order of magnitude more lines then the python equivalent, but oh well... >_>
			// lines = [ diffOp->closing for diffOp in diff->edits if diffOp->type == 'add' ]
			$lines = array_map(
				function( _DiffOp $diffOp ) {
					return $diffOp->closing;
				},
				array_filter(
					$diff->edits,
					function( _DiffOp $diffOp ) {
						return $diffOp->type == 'add';
					}
				)
			);

			$lines = call_user_func_array( 'array_merge', $lines );

			$info['addedlines'] = $lines;
		}

		$fields = array(
			'user_id' => $user->getId(),
			'time' => $revision->getTimestamp(),
			'type' => 'edit-' . $title->getNamespace(),
			'info' => $info,
		);

		return EPEvents::singleton()->newRow( $fields );
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
