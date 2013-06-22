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
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EPRevision extends \ORMRow {

	/**
	 * Cached user object for this revision.
	 *
	 * @since 0.1
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
	 * @since 0,1
	 *
	 * @return RevisionedObject
	 */
	public function getObject() {
		$tableClass = $this->getField( 'type' );

		$map = array(
			'EPCourses' => 'EducationProgram\Courses',
			'EPOrgs' => 'EducationProgram\Orgs',
		);

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
	 * @since 0.1
	 *
	 * @param integer $revId
	 * @param integer|null $objectId
	 *
	 * @return RevisionedObject|bool false
	 */
	public static function getObjectFromRevId( $revId, $objectId = null ) {
		$conditions = array(
			'id' => $revId
		);

		if ( !is_null( $objectId ) ) {
			$conditions['object_id'] = $objectId;
		}

		$rev = Revisions::singleton()->selectRow( array( 'type', 'data' ), $conditions );

		if ( $rev === false ) {
			return false;
		}
		else {
			return $rev->getObject();
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

	/**
	 * Return the previous revision, ie the most recent revision of the object of this revsion
	 * that's older then this revion. If there is none, false is returned.
	 *
	 * @since 0.1
	 *
	 * @return EPRevision|bool false
	 */
	public function getPreviousRevision() {
		return $this->getObject()->getLatestRevision( array(
			'id < ' . wfGetDB( DB_SLAVE )->addQuotes( $this->getId() )
		) );
	}

	/**
	 * Returns if this is the latest revision for the object contained by the revision.
	 *
	 * @since 0.1
	 *
	 * @return boolean
	 */
	public function isLatest() {
		return !$this->table->has( array(
			'type' => $this->getField( 'type' ),
			'object_id' => $this->getField( 'object_id' ),
			'id > ' . wfGetDB( DB_SLAVE )->addQuotes( $this->getId() )
		) );
	}

}
