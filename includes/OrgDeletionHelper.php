<?php

namespace EducationProgram;

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
 * @licence GNU GPL v2+
 * @author Andrew Green < agreen@wikimedia.org >
 */
class OrgDeletionHelper {

	/**
	 * @since 0.4 alpha
	 *
	 * @var Org
	 */
	protected $org;

	/**
	 * @since 0.4 alpha
	 *
	 * @var \IContextSource
	 */
	protected $context;

	/**
	 * @since 0.4 alpha
	 *
	 * @var OrgDelCheck
	 */
	protected $deletionCheck = OrgDelCheck::NOT_CHECKED;

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
	 * @return boolean
	 */
	public function checkRestrictions() {
		if ( $this->deletionCheck === OrgDelCheck::NOT_CHECKED ) {

			// Is the user allowed to edit this page?
			if ( !$this->context->getUser()->isAllowed( 'ep-org' ) ) {
				$this->deletionCheck = OrgDelCheck::NO_RIGHTS;

			// Does the org have courses?
			} else if ( $this->org->getField( 'course_count' ) > 0 ) {
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
	public function getCantDeleteMsgPlain() {

		$msgInfo = $this->getCantDeleteMsgKeyAndParams( true );

		return $this->context->msg( $msgInfo['key'],
				$msgInfo['params'] )->plain();
	}

	/**
	 * Create an associative array with info (message key and params) for
	 * a message explaining why an institution can't be deleted.
	 *
	 * @param boolean $plain
	 *
	 * @return array
	 *
	 * @throws \MWException
	 */
	private function getCantDeleteMsgKeyAndParams( $plain=false ) {
		switch ( $this->deletionCheck ) {
			case OrgDelCheck::NOT_CHECKED:
				throw new \MWException( 'Must check deletion restrictions ' .
					'before getting message.' );
				break;

			case OrgDelCheck::NO_RIGHTS:
				return array(
					'key' => 'ep-delete-org-no-rights',
					'params' => array()
				);

			case OrgDelCheck::HAS_COURSES:
				if ( $plain ) {

					return array(
						'key' => 'ep-delete-org-has-courses-plain',
						'params' => array(
							$this->org->getField( 'name' )
						)
					);

				} else {

					return array(
						'key' => 'ep-delete-org-has-courses',
						'params' => array(
							$this->org->getTitle()->getFullText(),
							$this->org->getField( 'name' )
						)
					);
				}
		}
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