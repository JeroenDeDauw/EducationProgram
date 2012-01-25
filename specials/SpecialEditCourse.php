<?php

/**
 * Addition and modification interface for courses.
 *
 * @since 0.1
 *
 * @file SpecialEditCourse.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v3 or later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class SpecialEditCourse extends SpecialEPFormPage {

	/**
	 * Constructor.
	 *
	 * @since 0.1
	 */
	public function __construct() {
		parent::__construct( 'EditCourse', 'ep-course', 'EPCourse', 'Courses', 'Course' );

		$this->getOutput()->addModules( 'ep.datepicker' );
	}

	/**
	 * (non-PHPdoc)
	 * @see SpecialEPFormPage::getFormFields()
	 * @return array
	 */
	protected function getFormFields() {
		$fields = parent::getFormFields();

		$orgOptions = EPOrg::getOrgOptions();

		$fields['mc_id'] = array (
			'type' => 'select',
			'label-message' => 'ep-course-edit-mastercourse',
			'required' => true,
			'options' => $orgOptions,
			'validation-callback' => function ( $value, array $alldata = null ) use ( $orgOptions ) {
				return in_array( (int)$value, array_values( $orgOptions ) ) ? true : wfMsg( 'ep-course-invalid-course' );
			},
		);

		$fields['token'] = array (
			'type' => 'text',
			'label-message' => 'ep-course-edit-token',
			'maxlength' => 255,
			'required' => true,
			'size' => 20,
			'validation-callback' => function ( $value, array $alldata = null ) {
				$strLen = strlen( $value );
				return ( $strLen !== 0 && $strLen < 2 ) ? wfMsgExt( 'ep-course-invalid-token', 'parsemag', 2 ) : true;
			} ,
		);

		$fields['year'] = array (
			'type' => 'int',
			'label-message' => 'ep-course-edit-year',
			'required' => true,
			'min' => 2000,
			'max' => 9001,
			'size' => 15,
		);

		$fields['start'] = array (
			'class' => 'EPHTMLDateField',
			'label-message' => 'ep-course-edit-start',
			'required' => true,
		);

		$fields['end'] = array (
			'class' => 'EPHTMLDateField',
			'label-message' => 'ep-course-edit-end',
			'required' => true,
		);

		$fields['description'] = array (
			'type' => 'textarea',
			'label-message' => 'ep-course-edit-description',
			'required' => true,
			'validation-callback' => function ( $value, array $alldata = null ) {
				return strlen( $value ) < 10 ? wfMsgExt( 'ep-course-invalid-description', 'parsemag', 10 ) : true;
			} ,
			'rows' => 10,
			'id' => 'wpTextbox1',
		);

		return $this->processFormFields( $fields );
	}

	/**
	 * (non-PHPdoc)
	 * @see SpecialEPFormPage::getTitleConditions()
	 */
	protected function getTitleConditions() {
		return array( 'id' => $this->subPage );
	}

	/**
	 * (non-PHPdoc)
	 * @see SpecialEPFormPage::getNewData()
	 */
	protected function getNewData() {
		return array(
			'mc_id' => $this->getRequest()->getVal( 'newmc' ),
			'year' => $this->getRequest()->getVal( 'newyear' ),
		);
	}

	/**
	 * (non-PHPdoc)
	 * @see SpecialEPFormPage::handleKnownField()
	 */
	protected function handleKnownField( $name, $value ) {
		if ( in_array( $name, array( 'end', 'start' ) ) ) {
			$value = wfTimestamp( TS_MW, strtotime( $value ) );
		}

		return $value;
	}

}
