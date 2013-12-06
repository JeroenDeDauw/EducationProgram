<?php

namespace EducationProgram;
use Page, IContextSource;

/**
 * Action to edit an org.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 * @ingroup Action
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EditOrgAction extends EditAction {
	/**
	 * Constructor.
	 *
	 * @since 0.1
	 *
	 * @param Page $page
	 * @param IContextSource $context
	 */
	public function __construct( Page $page, IContextSource $context = null ) {
		parent::__construct( $page, $context, Orgs::singleton() );
	}

	/**
	 * @see Action::getName()
	 */
	public function getName() {
		return 'edit';
	}

	/**
	 * Returns the base string for generating message keys, which vary based
	 * on whether one has attempted to create a new institution or edit an
	 * existing one.
	 *
	 * @see EditAction::getMessageKeyBase()
	 * @return string
	 */
	protected function getMessageKeyBase() {
		return $this->isNew() ? 'ep-addorg' : 'ep-editorg';
	}

	/**
	 * @see Action::getRestriction()
	 */
	public function getRestriction() {
		return 'ep-org';
	}

	/**
	 * @see EditAction::getFormFields()
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
				if ( strlen( $value ) < 2 ) {
					return wfMessage( 'educationprogram-org-invalid-name', 2 )->text();
				}

				if ( strpos( $value, '/' ) !== false ) {
					return wfMessage( 'ep-org-no-slashes' )->text();
				}

				return true;
			},
		);

		$fields['city'] = array(
			'type' => 'text',
			'label-message' => 'educationprogram-org-edit-city',
			'validation-callback' => function( $value, array $alldata = null ) {
				return $value !== '' && strlen( $value ) < 2 ? wfMessage( 'educationprogram-org-invalid-city', 2 )->text() : true;
			},
		);

		if ( !in_array( $this->getRequest()->getText( 'wpitem-country', '' ), Settings::get( 'citylessCountries' ) ) ) {
			$fields['city']['required'] = true;
		}

		$fields['country'] = array(
			'type' => 'select',
			'label-message' => 'educationprogram-org-edit-country',
			'maxlength' => 255,
			'required' => true,
			'options' => Utils::getCountryOptions( $this->getLanguage()->getCode() ),
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
	 * @return string|bool true
	 */
	public function countryIsValid( $value, array $alldata = null ) {
		$countries = array_keys( \CountryNames::getNames( $this->getLanguage()->getCode() ) );

		return in_array( $value, $countries ) ? true : $this->msg( 'educationprogram-org-invalid-country' )->text();
	}

	/**
	 * @see EditAction::getTitleField
	 *
	 * @since 0.2
	 *
	 * @return string
	 */
	protected function getTitleField() {
		return 'name';
	}
}
