<?php

/**
 * Profile page for online ambassadors.
 *
 * @since 0.1
 *
 * @file SpecialOAProfile.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v3 or later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class SpecialOAProfile extends FormSpecialPage {

	/**
	 * Constructor.
	 *
	 * @since 0.1
	 */
	public function __construct() {
		parent::__construct( 'OnlineAmbassadorProfile', 'ep-online', false );
	}

	/**
	 * Main method.
	 *
	 * @since 0.1
	 *
	 * @param string $subPage
	 */
	public function execute( $subPage ) {
		parent::execute( $subPage );
	}

	/**
	 * (non-PHPdoc)
	 * @see FormSpecialPage::getFormFields()
	 * @return array
	 */
	protected function getFormFields() {
		$fields = array();

		$fields['bio'] = array(
			'type' => 'textarea',
			'label-message' => 'ep-oa-profile-bio',
			'required' => true,
			'validation-callback' => function ( $value, array $alldata = null ) {
				return strlen( $value ) < 10 ? wfMsgExt( 'ep-oa-profile-invalid-bio', 'parsemag', 10 ) : true;
			},
			'rows' => 10,
			'id' => 'wpTextbox1',
		);

		$lang = $this->getLanguage();

		$fields['photo'] = array(
			'type' => 'text',
			'label-message' => 'ep-oa-profile-photo',
			'validation-callback' => function ( $value, array $alldata = null ) use ( $lang ) {
				foreach ( EPSettings::get( 'ambassadorPictureDomains' ) as $domain ) {
					$pattern = '@^https?://(([a-z0-9]+)\.)?' . str_replace( '.', '\.', $domain ) . '/.*$@i';

					if ( preg_match( $pattern, $value ) ) {
						return true;
					}
				}

				return wfMsgExt( 'ep-oa-profile-invalid-photo', 'parsemag', $lang->listToText( $domain ) );
			},
		);

		return $fields;
	}

	/**
	 * Gets called after the form is saved.
	 *
	 * @since 0.1
	 */
	public function onSuccess() {
	//	$this->getOutput()->redirect( $this->getReturnToTitle( true )->getLocalURL() );
	}

	/**
	 * Process the form.  At this point we know that the user passes all the criteria in
	 * userCanExecute().
	 *
	 * @param array $data
	 *
	 * @return Bool|Array
	 */
	public function onSubmit( array $data ) {

	}

}