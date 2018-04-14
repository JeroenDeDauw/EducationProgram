<?php

namespace EducationProgram;

use User;

/**
 * Class representing a single revision.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 *
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EPRevision extends ORMRow {

	/**
	 * Cached user object for this revision.
	 *
	 * @var User|bool false
	 */
	protected $user = false;

	/**
	 * @see ORMRow::getTable()
	 */
	public function getTable() {
		return Revisions::singleton();
	}

	/**
	 * Return the object as it was at this revision.
	 *
	 * @return RevisionedObject
	 */
	public function getObject() {
		$tableClass = $this->getField( 'type' );

		$map = [
			'EPCourses' => 'EducationProgram\Courses',
			'EPOrgs' => 'EducationProgram\Orgs',
		];

		$tableClass = $map[$tableClass];

		$data = $this->getField( 'data' );

		// Workaround for bug 49971
		if ( is_string( $data ) ) {
			$data = unserialize( $data );
		}

		return $tableClass::singleton()->newRow( $data, true );
	}

	/**
	 * Returns the the object stored in the revision with the provided id,
	 * or false if there is no matching object.
	 *
	 * @param int $revId
	 * @param int|null $objectId
	 *
	 * @return RevisionedObject|bool false
	 */
	public static function getObjectFromRevId( $revId, $objectId = null ) {
		$conditions = [
			'id' => $revId
		];

		if ( !is_null( $objectId ) ) {
			$conditions['object_id'] = $objectId;
		}

		$rev = Revisions::singleton()->selectRow( [ 'type', 'data' ], $conditions );

		if ( $rev === false ) {
			return false;
		} else {
			return $rev->getObject();
		}
	}

	/**
	 * Returns the user that authored this revision.
	 *
	 * @return User
	 */
	public function getUser() {
		if ( $this->user === false ) {
			$id = $this->loadAndGetField( 'user_id' );
			if ( $id ) {
				$this->user = User::newFromId( $id );
			} else {
				$this->user = User::newFromName( $this->loadAndGetField( 'user_text' ), false );
			}
		}

		return $this->user;
	}

	/**
	 * Return the previous revision, ie the most recent revision of the object of this revsion
	 * that's older then this revion. If there is none, false is returned.
	 *
	 * @return EPRevision|bool false
	 */
	public function getPreviousRevision() {
		return $this->getObject()->getLatestRevision( [
			'id < ' . wfGetDB( DB_REPLICA )->addQuotes( $this->getId() )
		] );
	}

	/**
	 * Returns if this is the latest revision for the object contained by the revision.
	 *
	 * @return bool
	 */
	public function isLatest() {
		return !$this->table->has( [
			'type' => $this->getField( 'type' ),
			'object_id' => $this->getField( 'object_id' ),
			'id > ' . wfGetDB( DB_REPLICA )->addQuotes( $this->getId() )
		] );
	}

}
