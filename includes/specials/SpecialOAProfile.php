<?php

namespace EducationProgram;

/**
 * Profile page for online ambassadors.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class SpecialOAProfile extends SpecialAmbassadorProfile {

	/**
	 * Constructor.
	 *
	 * @since 0.1
	 */
	public function __construct() {
		parent::__construct( 'OnlineAmbassadorProfile' );
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
		return 'EducationProgram\OA';
	}

	/**
	 * @see SpecialAmbassadorProfile::userCanAccess()
	 */
	protected function userCanAccess() {
		$user = $this->getUser();
		return $user->isAllowed( 'ep-beonline' )
			|| OA::newFromUser( $user )->hasCourse();
	}

	/**
	 * @see SpecialAmbassadorProfile::getMsgPrefix
	 */
	protected function getMsgPrefix() {
		return 'epoa-';
	}

}