<?php

/**
 * Addition and modification interface for master courses.
 *
 * @since 0.1
 *
 * @file SpecialEditMasterCourse.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v3 or later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class SpecialEditMasterCourse extends SpecialEPFormPage {

	/**
	 * Constructor.
	 *
	 * @since 0.1
	 */
	public function __construct() {
		parent::__construct( 'EditMasterCourse', 'ep-mc', 'EPMC', 'MasterCourses' );
	}

	/**
	 * (non-PHPdoc)
	 * @see SpecialEPFormPage::getFormFields()
	 * @return array
	 */
	protected function getFormFields() {
		$fields = parent::getFormFields();

		$fields['name'] = array (
			'type' => 'text',
			'label-message' => 'ep-mc-edit-name',
			'maxlength' => 255,
			'required' => true,
			'validation-callback' => function ( $value, array $alldata = null ) {
				return strlen( $value ) < 5 ? wfMsgExt( 'ep-mc-invalid-name', 'parsemag', 5 ) : true;
			} ,
		);

		$orgOptions = EPOrg::getOrgOptions();

		$fields['org_id'] = array (
			'type' => 'select',
			'label-message' => 'ep-mc-edit-org',
			'required' => true,
			'options' => $orgOptions,
			'validation-callback' => function ( $value, array $alldata = null ) use ( $orgOptions ) {
				return in_array( (int)$value, array_values( $orgOptions ) ) ? true : wfMsg( 'ep-mc-invalid-org' );
			} ,
		);

		$fields['description'] = array (
			'type' => 'textarea',
			'label-message' => 'ep-mc-edit-description',
			'required' => true,
			'validation-callback' => function ( $value, array $alldata = null ) {
				return strlen( $value ) < 10 ? wfMsgExt( 'ep-mc-invalid-description', 'parsemag', 10 ) : true;
			} ,
			'rows' => 10,
			'id' => 'wpTextbox1',
		);

		return $this->processFormFields( $fields );
	}

	/**
	 * (non-PHPdoc)
	 * @see SpecialEPFormPage::getNewData()
	 */
	protected function getNewData() {
		return array(
			'org_id' => $this->getRequest()->getVal( 'neworg' ),
			'name' => $this->getRequest()->getVal( 'newname' ),
		);
	}

}
