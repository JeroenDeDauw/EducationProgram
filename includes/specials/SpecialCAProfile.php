<?php

namespace EducationProgram;

/**
 * Profile page for campus ambassadors.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
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
	 * @see FormSpecialPage::getFormFields()
	 * @return array
	 */
	protected function getFormFields() {
		$fields = parent::getFormFields();



		return $fields;
	}

	/**
	 * @see FormSpecialPage::getClassName()
	 */
	protected function getClassName() {
		return 'EducationProgram\CA';
	}

	/**
	 * @see SpecialAmbassadorProfile::userCanAccess()
	 */
	protected function userCanAccess() {
		$user = $this->getUser();
		return $user->isAllowed( 'ep-becampus' )
			|| CA::newFromUser( $user )->hasCourse();
	}

	/**
	 * @see SpecialAmbassadorProfile::getMsgPrefix
	 */
	protected function getMsgPrefix() {
		return 'epca-';
	}

}