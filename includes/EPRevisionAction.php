<?php

/**
 * Class representing a single revision action.
 * This can be any kind of change creating a new revision,
 * such as page creation, edits, deletion and reverts.
 *
 * @since 0.1
 *
 * @file EPRevisionAction.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v3 or later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EPRevisionAction {

	protected $user;
	protected $isMinor = false;
	protected $isDelete = false;
	protected $comment = '';
	protected $time = false;

	/**
	 * @since 0.1
	 * @return boolean
	 */
	public function isMinor() {
		return $this->isMinor;
	}

	/**
	 * @since 0.1
	 * @return boolean
	 */
	public function isDelete() {
		return $this->isDelete;
	}

	/**
	 * @since 0.1
	 * @return string
	 */
	public function getComment() {
		return $this->comment;
	}

	/**
	 * @since 0.1
	 * @return User
	 */
	public function getUser() {
		return $this->user;
	}

	/**
	 * @since 0.1
	 * @return string
	 */
	public function getTime() {
		return $this->time === false ? wfTimestampNow() : $this->time;
	}

	/**
	 * @since 0.1
	 * @param User $user
	 */
	public function setUser( User $user ) {
		$this->user = $user;
	}

	/**
	 * @since 0.1
	 * @param string $comment
	 */
	public function setComment( $comment ) {
		$this->comment = $comment;
	}

	/**
	 * @since 0.1
	 * @param boolean $isDelete
	 */
	public function setDelete( $isDelete ) {
		$this->isDelete = $isDelete;
	}

	/**
	 * @since 0.1
	 * @param boolean $isMinor
	 */
	public function setMinor( $isMinor ) {
		$this->isMinor = $isMinor;
	}

	/**
	 * @since 0.1
	 * @param string $time
	 */
	public function setTime( $time ) {
		$this->time = $time;
	}

}
