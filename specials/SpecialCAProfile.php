<?php

/**
 * Profile page for campus ambassadors.
 *
 * @since 0.1
 *
 * @file SpecialCAProfile.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v3 or later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class SpecialCAProfile extends SpecialAmbassadorProfile {

	/**
	 * Constructor.
	 *
	 * @since 0.1
	 */
	public function __construct() {
		parent::__construct( 'CampusAmbassadorProfile' );
	}

	/**
	 * (non-PHPdoc)
	 * @see FormSpecialPage::getFormFields()
	 * @return array
	 */
	protected function getFormFields() {
		$fields = parent::getFormFields();



		return $fields;
	}

	/**
	 * (non-PHPdoc)
	 * @see FormSpecialPage::getClassName()
	 */
	protected function getClassName() {
		return 'EPCA';
	}

	/**
	 * (non-PHPdoc)
	 * @see SpecialAmbassadorProfile::userCanAccess()
	 */
	protected function userCanAccess() {
		$user = $this->getUser();
		return $user->isAllowed( 'ep-campus' )
			|| $user->isAllowed( 'ep-becampus' )
			|| EPCA::newFromUser( $user )->hasCourse();
	}

}