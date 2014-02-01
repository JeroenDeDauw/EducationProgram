<?php

namespace EducationProgram;

/**
 * Action for deleting Org items.
 *
 * @since 0.4 alpha
 *
 * @ingroup EducationProgram
 * @ingroup Action
 *
 * @licence GNU GPL v2+
 * @author Andrew Green < agreen@wikimedia.org >
 */
class DeleteOrgAction extends DeleteAction {

	/**
	 * Check that we can perform the requested deletion. If there are no
	 * problems, do nothing and return true. If there are problems, output
	 * an appropriate message and return false.
	 *
	 * @see DeleteAction::checkAndHandleRestrictions
	 *
	 * @since 0.4 alpha
	 *
	 * @param Org $org The Org to be deleted.
	 *
	 * @return boolean
	 *
	 */
	protected function checkAndHandleRestrictions( $org ) {
		$deletionHelper = new OrgDeletionHelper( $org, $this->context );

		if ( $deletionHelper->checkRestrictions() ) {
			return true;
		} else {
			$deletionHelper->outputCantDeleteMsg();
			return false;
		}
	}
}
