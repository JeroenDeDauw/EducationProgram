<?php

namespace EducationProgram;

/**
 * Helps to check that a course can be undeleted, and to create an appropriate
 * message if it can't be.
 *
 * These functions are provided through this helper class because they are
 * required by classes on different branches of the Action hierarchy
 * (EditCourseAction and UndeleteAction). They are specific enough to course
 * undeletion that putting them in the Action superclass would be ugly.
 *
 * @since 0.4 alpha
 *
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Andrew Green < agreen@wikimedia.org >
 */
 class CourseUndeletionHelper {

	/**
	 * @since 0.4 alpha
	 *
	 * @var EPRevision
	 */
	protected $revision;

	/**
	 * @since 0.4 alpha
	 *
	 * @var \IContextSource
	 */
	protected $context;

	/**
	 * @since 0.4 alpha
	 *
	 * @var EducationPage
	 */
	protected $educationPage;

	/**
	 * @since 0.4 alpha
	 *
	 * @var CourseUndelCheck
	 */
	protected $undeletionCheck = CourseUndelCheck::NOT_CHECKED;

	/**
	 * @since 0.4 alpha
	 *
	 * @var int
	 */
	protected $deletedOrgId;

	/**
	 * @param EPRevision $revision the latest revision of the course that may or
	 *   may not be undeleted
	 *
	 * @param \IContextSource $context the current context
	 *
	 * @param EducationPage $educationPage the EducationPage object for the
	 *   course that may or may not be undeleted
	 */
	public function __construct( EPRevision $revision,
		\IContextSource $context, EducationPage $educationPage ) {

		$this->revision = $revision;
		$this->context = $context;
		$this->educationPage = $educationPage;
	}

	/**
	 * Determine whether or not the course can be undeleted.
	 *
	 * @return boolean
	 */
	public function checkRestrictions() {

		// Is the user allowed to edit this page?
		if ( !$this->context->getUser()->isAllowed(
			$this->educationPage->getEditRight() ) ) {

			$this->undeletionCheck = CourseUndelCheck::NO_RIGHTS;
			return false;
		}

		$deletedCourse = $this->revision->getObject();
		$this->deletedOrgId = $deletedCourse->getField( 'org_id' );

		$org = Orgs::singleton()->selectRow( null,
			array( 'id' => $this->deletedOrgId ) );

		if ( $org === false ) {
			$this->undeletionCheck = CourseUndelCheck::ORG_DELETED;
			return false;
		}

		$this->undeletionCheck = CourseUndelCheck::CAN_UNDELETE;
		return true;
	}

	/**
	 * Output a message (via the OutputPage from the context provided in the
	 * constructor) explaining why the course can't be undeleted.
	 */
	public function outputCantUndeleteMsg() {
		switch ( $this->undeletionCheck ) {
			case CourseUndelCheck::NOT_CHECKED:
				throw new \MWException( 'Must check undeletion restrictions ' .
					'before outputting message.' );
				break;

			case CourseUndelCheck::NO_RIGHTS:

				// Output a message explaining that the user doesn't have
				// permission to undelete a course.
				$this->context->getOutput()->addHTML(
					$this->context->msg( 'ep-undelete-course-no-rights' )->
					escaped()
				);

				break;

			case CourseUndelCheck::ORG_DELETED:

				// Output a message explaining that the institution
				// must be undeleted before the course is undeleted.

				// First get the latest revision of the deleted org.
				// If we're here, we can assume that deletedOrgId has been set.
				$deletedOrgRev = Revisions::singleton()->getLatestRevision(
					array(
						'object_id' => $this->deletedOrgId,
						'type' => Orgs::singleton()->getRevisionedObjectTypeId(),
					)
				);

				// Check that we actually got a revision.
				if ( $deletedOrgRev !== false ) {

					// Get an object for the deleted org and get some info.
					$deletedOrg = $deletedOrgRev->getObject();
					$deletedOrgTitle = $deletedOrg->getTitle();
					$deletedOrgName = $deletedOrg->getField( 'name' );

					// To prevent the restore link from showing as a redlink, we
					// include it as if it were external and use the plainlinks
					// css class.
					$html = \Html::openElement(
						'span',
						array( 'class' => 'plainlinks' ) );

					$html .= $this->context->msg(
						'ep-undelete-course-org-deleted',
						$deletedOrgTitle,
						$deletedOrgName,
						$deletedOrgTitle->getFullURL()
					)->parse();

					$html .= \Html::closeElement( 'span' );

					$this->context->getOutput()->addHTML( $html );

				// If we didn't get a revision, something is quite wrong.
				} else {
					throw new \MWException( 'Couldn\'t find a revision for ' .
						'deleted institution id ' .
						$this->deletedOrgId );
				}

				break;
		}
	}
}

/**
 * Constants for possible results of check for restrictions.
 */
class CourseUndelCheck {
	const NOT_CHECKED = -1;
	const CAN_UNDELETE = 0;
	const NO_RIGHTS = 1;
	const ORG_DELETED = 2;
}