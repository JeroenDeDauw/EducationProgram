<?php

namespace EducationProgram;

use LogicException;

/**
 * Helps to check that an institution can be deleted, and to create an
 * appropriate message if it can't be.
 *
 * These functions are provided through this helper class because they're
 * used in several places (OrgPager, DeleteOrgAction and
 * ApiDeleteEducation).
 *
 * @since 0.4 alpha
 *
 * @ingroup EducationProgram
 *
 * @license GNU GPL v2+
 * @author Andrew Green < agreen@wikimedia.org >
 */
class OrgDeletionHelper {

	/**
	 * @var Org
	 */
	private $org;

	/**
	 * @var \IContextSource
	 */
	private $context;

	/**
	 * @var int
	 */
	private $deletionCheck = OrgDelCheck::NOT_CHECKED;

	/**
	 * @param Org $org The institution that may or may not be deleted
	 *
	 * @param \IContextSource $context The current context
	 */
	public function __construct( Org $org, \IContextSource $context ) {
		$this->org = $org;
		$this->context = $context;
	}

	/**
	 * Determine whether or not the institution can be deleted.
	 *
	 * Note: queries performed here should be optimized/use only summary data,
	 * as this method may be called by OrgPager once per row displayed.
	 *
	 * @return bool
	 */
	public function checkRestrictions() {
		if ( $this->deletionCheck === OrgDelCheck::NOT_CHECKED ) {
			// Is the user allowed to edit this page?
			if ( !$this->context->getUser()->isAllowed( 'ep-org' ) ) {
				$this->deletionCheck = OrgDelCheck::NO_RIGHTS;

			// Does the org have courses?
			} elseif ( $this->org->getField( 'course_count' ) > 0 ) {
				$this->deletionCheck = OrgDelCheck::HAS_COURSES;
			} else {
				$this->deletionCheck = OrgDelCheck::CAN_DELETE;
			}
		}

		return $this->deletionCheck === OrgDelCheck::CAN_DELETE;
	}

	/**
	 * Output a message (via the OutputPage from the context provided in the
	 * constructor) explaining why the institution can't be deleted.
	 */
	public function outputCantDeleteMsg() {
		$msgInfo = $this->getCantDeleteMsgKeyAndParams();

		$this->context->getOutput()->addHTML(
			$this->context->msg( $msgInfo['key'],
			$msgInfo['params'] )->parse() );
	}

	/**
	 * Returns a plain-text message explaining why the institution can't be
	 * deleted.
	 */
	public function getCantDeleteMsg() {
		$msgInfo = $this->getCantDeleteMsgKeyAndParams( true );

		return $this->context->msg( $msgInfo['key'], $msgInfo['params'] );
	}

	/**
	 * Returns a plain-text message explaining why the institution can't be
	 * deleted.
	 */
	public function getCantDeleteMsgPlain() {
		return $this->getCantDeleteMsg()->plain();
	}

	/**
	 * Create an associative array with info (message key and params) for
	 * a message explaining why an institution can't be deleted.
	 *
	 * @param bool $plain
	 *
	 * @return array
	 *
	 * @throws \Exception
	 */
	private function getCantDeleteMsgKeyAndParams( $plain=false ) {
		switch ( $this->deletionCheck ) {
			case OrgDelCheck::NOT_CHECKED:
				throw new \Exception( 'Must check deletion restrictions ' .
					'before getting message.' );
				break;

			case OrgDelCheck::NO_RIGHTS:
				return [
					'key' => 'ep-delete-org-no-rights',
					'params' => []
				];

			case OrgDelCheck::HAS_COURSES:
				if ( $plain ) {
					return [
						'key' => 'ep-delete-org-has-courses-plain',
						'params' => [
							$this->org->getField( 'name' )
						]
					];
				} else {
					return [
						'key' => 'ep-delete-org-has-courses',
						'params' => [
							$this->org->getTitle()->getFullText(),
							$this->org->getField( 'name' )
						]
					];
				}
		}

		throw new LogicException( 'Unexpected deletionCheck value ' . $this->deletionCheck );
	}
}

/**
 * Constants for possible results of check for restrictions.
 */
class OrgDelCheck {
	const NOT_CHECKED = -1;
	const CAN_DELETE = 0;
	const NO_RIGHTS = 1;
	const HAS_COURSES = 2;
}
