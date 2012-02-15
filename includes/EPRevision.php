<?php

/**
 * Class representing a single revision.
 *
 * @since 0.1
 *
 * @file EPRevision.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v3 or later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EPRevision extends DBDataObject {
	
	/**
	 * Cached user object for this revision.
	 *
	 * @since 0.1
	 * @var User|false
	 */
	protected $user = false;

	/**
	 * Return the object as it was at this revision.
	 *
	 * @since 0,1
	 *
	 * @return EPRevisionedObject
	 */
	public function getObject() {
		$class = $this->getField( 'type' ) . 's'; // TODO: refactor made this suck a lot
		return $class::singleton()->newFromArray( $this->getField( 'data' ), true );
	}

	/**
	 * Returns the the object stored in the revision with the provided id,
	 * or false if there is no matching object.
	 *
	 * @since 0.1
	 *
	 * @param integer $revId
	 * @param integer|null $objectId
	 *
	 * @return EPRevisionedObject|false
	 */
	public static function getObjectFromRevId( $revId, $objectId = null ) {
		$conditions = array(
			'id' => $revId
		);

		if ( !is_null( $objectId ) ) {
			$conditions['object_id'] = $objectId;
		}

		$rev = EPRevision::selectRow( array( 'type', 'data' ), $conditions );

		if ( $rev === false ) {
			return false;
		}
		else {
			return $rev->getDataObject();
		}
	}

	/**
	 * Returns the user that authored this revision.
	 *
	 * @since 0.1
	 *
	 * @return User
	 */
	public function getUser() {
		if ( $this->user === false ) {
			$this->user = User::newFromId( $this->loadAndGetField( 'user_id' ) );
		}

		return $this->user;
	}
	
	public static function getLastRevision( array $conditions ) {
		return EPRevision::selectRow(
			null,
			$conditions,
			array(
				'SORT BY' => EPRevision::getPrefixedField( 'time' ),
				'ORDER' => 'DESC',
			)
		);
	}

}
