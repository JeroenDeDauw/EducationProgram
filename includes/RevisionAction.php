<?php

namespace EducationProgram;

use User;

/**
 * Class representing a single revision action.
 * This can be any kind of change creating a new revision,
 * such as page creation, edits, deletion and reverts.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 *
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class RevisionAction {

	protected $user;
	protected $isMinor = false;
	protected $isDelete = false;
	protected $comment = '';
	protected $time = false;

	/**
	 * @return bool
	 */
	public function isMinor() {
		return $this->isMinor;
	}

	/**
	 * @return bool
	 */
	public function isDelete() {
		return $this->isDelete;
	}

	/**
	 * @return string
	 */
	public function getComment() {
		return $this->comment;
	}

	/**
	 * @return User
	 */
	public function getUser() {
		return $this->user;
	}

	/**
	 * @return string
	 */
	public function getTime() {
		return $this->time === false ? wfTimestampNow() : $this->time;
	}

	/**
	 * @param User $user
	 */
	public function setUser( User $user ) {
		$this->user = $user;
	}

	/**
	 * @param string $comment
	 */
	public function setComment( $comment ) {
		$this->comment = $comment;
	}

	/**
	 * @param bool $isDelete
	 */
	public function setDelete( $isDelete ) {
		$this->isDelete = $isDelete;
	}

	/**
	 * @param bool $isMinor
	 */
	public function setMinor( $isMinor ) {
		$this->isMinor = $isMinor;
	}

	/**
	 * @param string $time
	 */
	public function setTime( $time ) {
		$this->time = $time;
	}

}
