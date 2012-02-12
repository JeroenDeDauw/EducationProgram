<?php

/**
 * Action to edit a course.
 *
 * @since 0.1
 *
 * @file EditCourseAction.php
 * @ingroup EducationProgram
 * @ingroup Action
 *
 * @licence GNU GPL v3+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EditCourseAction extends EPEditAction {
	
	/**
	 * (non-PHPdoc)
	 * @see Action::getName()
	 */
	public function getName() {
		return 'editcourse';
	}

	/**
	 * (non-PHPdoc)
	 * @see Action::getDescription()
	 */
	protected function getDescription() {
		return wfMsgHtml( $this->isNew() ? 'ep-addcourse' : 'ep-editcourse' );
	}

	/**
	 * (non-PHPdoc)
	 * @see Action::getRestriction()
	 */
	public function getRestriction() {
		return 'ep-course';
	}
	
	/**
	 * (non-PHPdoc)
	 * @see EPEditAction::onView()
	 */
	public function onView() {
		$this->getOutput()->addModules( array( 'ep.datepicker', 'ep.combobox' ) );

		$c = $this->getItemClass(); // Yeah, this is needed in PHP 5.3 >_>
		
		if ( !$this->isNewPost() && !$c::hasIdentifier( $this->getTitle()->getText() ) ) {
			$c::displayDeletionLog(
				$this->getContext(),
				'ep-' . strtolower( $this->getName() ) . '-deleted' 
			);

			list( $name, $term ) = $this->titleToNameAndTerm( $this->getTitle()->getText() );
			
			EPCourse::displayAddNewRegion(
				$this->getContext(),
				array(
					'name' => $this->getRequest()->getText(
						'newname',
						$name
					),
					'term' => $this->getRequest()->getText(
						'newterm',
						$term
					),
				)
			);

			$this->isNew = true;
			$this->getOutput()->setSubtitle( $this->getDescription() );
			$this->getOutput()->setPageTitle( $this->getPageTitle() );
			
			return '';
		}
		else {
			return parent::onView();
		}
	}

	/**
	 * Parse a course title to name and term.
	 *
	 * @since 0.1
	 *
	 * @param string $titleText
	 *
	 * @return array
	 */
	protected function titleToNameAndTerm( $titleText ) {
		$term = '';

		$matches = array();
		preg_match( '/(.*)\((.*)\)/', $titleText, $matches );

		if ( count( $matches ) == 3 && trim( $matches[1] ) !== '' && $matches[2] !== '' ) {
			$name = trim( $matches[1] );
			$term = trim( $matches[2] );
		}

		return array( $name, $term );
	}

	/**
	 * (non-PHPdoc)
	 * @see EPEditAction::getItemClass()
	 * @return string
	 */
	protected function getItemClass() {
		return 'EPCourse';
	}
	
	/**
	 * (non-PHPdoc)
	 * @see EPEditAction::getFormFields()
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
		
		$mcs = EPCourse::selectFields( 'mc', array(), array( 'DISTINCT' ) );
		
		if ( $this->getRequest()->getCheck( 'newname' ) ) {
			$newName = $this->getRequest()->getText( 'newname' );
			$mcs = array_merge( array( $newName => $newName ), $mcs );
		}
		else {
			$mcs = array_merge( array( '' => '' ), $mcs );
		}
		
		
		$fields['mc'] = array (
			'class' => 'EPHTMLCombobox',
			'label-message' => 'ep-course-edit-mc',
			'required' => true,
			'options' => array_combine( $mcs, $mcs ),
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
	 * @see EPEditAction::getNewData()
	 */
	protected function getNewData() {
		$data = parent::getNewData();

		if ( $this->isNewPost() ) {
			$data['org_id'] = $this->getRequest()->getVal( 'neworg' );

			$data['mc'] = $data['name'];

			$data['name'] = wfMsgExt(
				'ep-course-edit-name-format',
				'parsemag',
				$data['name'],
				$this->getRequest()->getVal( 'newterm' )
			);
			
			$data['term'] = $this->getRequest()->getVal( 'newterm' );
		}

		return $data;
	}

	/**
	 * (non-PHPdoc)
	 * @see EPEditAction::handleKnownField()
	 */
	protected function handleKnownField( $name, $value ) {
		if ( in_array( $name, array( 'end', 'start' ) ) ) {
			$value = wfTimestamp( TS_MW, strtotime( $value. ' UTC' ) );
		}

		return $value;
	}
	
}