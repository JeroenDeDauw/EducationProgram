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
 * @license GPL-2.0-or-later
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
	 * @return bool
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
