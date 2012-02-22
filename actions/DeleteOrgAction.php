<?php

/**
 * Page for deleting an institution.
 *
 * @since 0.1
 *
 * @file DeleteOrgAction.php
 * @ingroup EducationProgram
 * @ingroup Action
 *
 * @licence GNU GPL v3+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DeleteOrgAction extends EPDeleteAction {

	/**
	 * Constructor.
	 *
	 * @since 0.1
	 *
	 * @param Page $page
	 * @param IContextSource $context
	 */
	protected function __construct( Page $page, IContextSource $context = null ) {
		parent::__construct( $page, $context, EPOrgs::singleton() );
	}

	public function getName() {
		return 'deleteorg';
	}

	public function getRestriction() {
		return 'ep-org';
	}

}