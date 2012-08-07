<?php

/**
 * Action to edit an org.
 *
 * @since 0.1
 *
 * @file EditOrgAction.php
 * @ingroup EducationProgram
 * @ingroup Action
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EditOrgAction extends EPEditAction {

	/**
	 * Constructor.
	 *Re
	 * @since 0.1
	 *
	 * @param Page $page
	 * @param IContextSource $context
	 */
	protected function __construct( Page $page, IContextSource $context = null ) {
		parent::__construct( $page, $context, EPOrgs::singleton() );
	}

	/**
	 * (non-PHPdoc)
	 * @see Action::getName()
	 */
	public function getName() {
		return 'edit';
	}

	/**
	 * (non-PHPdoc)
	 * @see Action::getDescription()
	 */
	protected function getDescription() {
		return wfMsgHtml( $this->isNew() ? 'ep-addorg' : 'ep-editorg' );
	}

	/**
	 * (non-PHPdoc)
	 * @see Action::getRestriction()
	 */
	public function getRestriction() {
		return 'ep-org';
	}

	/**
	 * (non-PHPdoc)
	 * @see EPEditAction::getFormFields()
	 * @return array
	 */
	protected function getFormFields() {
		$fields = parent::getFormFields();

		$fields['name'] = array(
			'type' => 'text',
			'label-message' => 'educationprogram-org-edit-name',
			'maxlength' => 255,
			'required' => true,
			'validation-callback' => function( $value, array $alldata = null ) {
				return strlen( $value ) < 2 ? wfMsg( 'educationprogram-org-invalid-name' ) : true;
			},
			'validation-callback' => function( $value, array $alldata = null ) {
				return in_string( '/', $value ) ? wfMsg( 'ep-org-no-slashes' ) : true;
			},
		);

		$fields['city'] = array(
			'type' => 'text',
			'label-message' => 'educationprogram-org-edit-city',
			'validation-callback' => function( $value, array $alldata = null ) {
				return $value !== '' && strlen( $value ) < 2 ? wfMsg( 'educationprogram-org-invalid-city' ) : true;
			},
		);

		if ( !in_array( $this->getRequest()->getText( 'wpitem-country', '' ), EPSettings::get( 'citylessCountries' ) ) ) {
			$fields['city']['required'] = true;
		}

		$fields['country'] = array(
			'type' => 'select',
			'label-message' => 'educationprogram-org-edit-country',
			'maxlength' => 255,
			'required' => true,
			'options' => EPUtils::getCountryOptions( $this->getLanguage()->getCode() ),
			'validation-callback' => array( $this, 'countryIsValid' ),
		);

		return $this->processFormFields( $fields );
	}

	/**
	 * Returns true if the country value is valid or an error message if it's not.
	 *
	 * @since 0.1
	 *
	 * @param string $value
	 * @param array $alldata
	 *
	 * @return string|true
	 */
	public function countryIsValid( $value, array $alldata = null ) {
		$countries = array_keys( CountryNames::getNames( $this->getLanguage()->getCode() ) );

		return in_array( $value, $countries ) ? true : wfMsg( 'educationprogram-org-invalid-country' );
	}

	/**
	 * @see EPEditAction::getTitleField
	 *
	 * @since 0.2
	 *
	 * @return string
	 */
	protected function getTitleField() {
		return 'name';
	}

}
