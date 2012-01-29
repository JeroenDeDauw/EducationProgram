<?php

class EditCourseAction extends EPEditAction {
	
	
	public function getName() {
		return 'editcourse';
	}

	protected function getDescription() {
		return wfMsg( 'editcourse' );
	}

	public function onView() {
		$this->getOutput()->addModules( array( 'ep.datepicker', 'ep.combobox' ) );

		return parent::onView();
	}
	
	protected function getItemClass() {
		return 'EPCourse';
	}
	
	/**
	 * (non-PHPdoc)
	 * @see SpecialEPFormPage::getFormFields()
	 * @return array
	 */
	protected function getFormFields() {
		$fields = parent::getFormFields();

		$orgOptions = EPOrg::getOrgOptions();

		$fields['name'] = array (
			'type' => 'text',
			'label-message' => 'ep-course-edit-name',
			'required' => true,
		);

		$fields['org_id'] = array (
			'type' => 'select',
			'label-message' => 'ep-course-edit-org',
			'required' => true,
			'options' => $orgOptions,
			'validation-callback' => function ( $value, array $alldata = null ) use ( $orgOptions ) {
				return in_array( (int)$value, array_values( $orgOptions ) ) ? true : wfMsg( 'ep-course-invalid-org' );
			},
		);

		$fields['token'] = array (
			'type' => 'text',
			'label-message' => 'ep-course-edit-token',
			'maxlength' => 255,
			'size' => 20,
			'validation-callback' => function ( $value, array $alldata = null ) {
				$strLen = strlen( $value );
				return ( $strLen !== 0 && $strLen < 2 ) ? wfMsgExt( 'ep-course-invalid-token', 'parsemag', 2 ) : true;
			} ,
		);

		$fields['term'] = array (
			'type' => 'text',
			'label-message' => 'ep-course-edit-term',
			'required' => true,
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

		$fieldFields = EPCourse::selectFields( 'field', array(), array( 'DISTINCT' ) );
		$fieldFields = array_merge( array( '' => '' ), $fieldFields );
		$fields['field'] = array (
			'class' => 'EPHTMLCombobox',
			'label-message' => 'ep-course-edit-field',
			'required' => true,
			'options' => array_combine( $fieldFields, $fieldFields ),
		);

		$levels = EPCourse::selectFields( 'level', array(), array( 'DISTINCT' ) );
		$levels = array_merge( array( '' => '' ), $levels );
		$fields['level'] = array (
			'class' => 'EPHTMLCombobox',
			'label-message' => 'ep-course-edit-level',
			'required' => true,
			'options' => array_combine( $levels, $levels ),
		);

		$langOptions = EPUtils::getLanguageOptions( $this->getLanguage()->getCode() );
		$fields['lang'] = array (
			'type' => 'select',
			'label-message' => 'ep-course-edit-lang',
			'maxlength' => 255,
			'required' => true,
			'options' => $langOptions,
			'validation-callback' => function ( $value, array $alldata = null ) use ( $langOptions ) {
				return in_array( $value, $langOptions ) ? true : wfMsg( 'ep-course-invalid-lang' );
			}
		);

		$mcs = EPCourse::selectFields( 'mc', array(), array( 'DISTINCT' ) );
		$mcs = array_merge( array( '' => '' ), $mcs );
		$fields['mc'] = array (
			'class' => 'EPHTMLCombobox',
			'label-message' => 'ep-course-edit-mc',
			'required' => true,
			'options' => array_combine( $mcs, $mcs ),
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
	 * @see SpecialEPFormPage::getNewData()
	 */
	protected function getNewData() {
		return array(
			'org_id' => $this->getRequest()->getVal( 'neworg' ),
			'name' => wfMsgExt(
				'ep-course-edit-name-format',
				'parsemag',
				$this->getRequest()->getVal( 'newname' ),
				$this->getRequest()->getVal( 'newterm' )
			),
			'term' => $this->getRequest()->getVal( 'newterm' ),
			'mc' => $this->getRequest()->getVal( 'newname' ),
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