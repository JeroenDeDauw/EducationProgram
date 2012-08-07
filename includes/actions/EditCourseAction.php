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
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EditCourseAction extends EPEditAction {

	/**
	 * Constructor.
	 *
	 * @since 0.1
	 *
	 * @param Page $page
	 * @param IContextSource $context
	 */
	protected function __construct( Page $page, IContextSource $context = null ) {
		parent::__construct( $page, $context, EPCourses::singleton() );
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
		$out = $this->getOutput();

		$out->addModules( array( 'ep.datepicker', 'ep.combobox' ) );

		if ( !$this->isNewPost() && !$this->table->hasIdentifier( $this->getTitle()->getText() ) ) {
			$this->displayUndeletionLink();
			$this->displayDeletionLog();

			list( $name, $term ) = $this->titleToNameAndTerm( $this->getTitle()->getText() );

			$out->addHTML( EPCourse::getAddNewRegion(
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
			) );

			$out->addModules( 'ep.addcourse' );

			$this->isNew = true;
			$out->setSubtitle( $this->getDescription() );
			$out->setPageTitle( $this->getPageTitle() );

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
		// TODO: put all in one regex
		// TODO: unit test
		$titleText = explode( '/', $titleText, 2 );
		$titleText = array_pop( $titleText );

		$matches = array();
		preg_match( '/(.*)\((.*)\)/', $titleText, $matches );

		if ( count( $matches ) == 3 && trim( $matches[1] ) !== '' && $matches[2] !== '' ) {
			$name = trim( $matches[1] );
			$term = trim( $matches[2] );
		}
		else {
			$name = $titleText;
			$term = '';
		}

		return array( $name, $term );
	}

	/**
	 * (non-PHPdoc)
	 * @see EPEditAction::getFormFields()
	 * @return array
	 */
	protected function getFormFields() {
		$fields = parent::getFormFields();

		$orgOptions = EPOrgs::singleton()->selectFields( array( 'name', 'id' ) );

		$fields['title'] = array(
			'type' => 'text',
			'label-message' => 'ep-course-edit-title',
			'help-message' => 'ep-course-help-title',
			'required' => true,
			'validation-callback' => function( $value, array $alldata = null ) {
				return in_string( '/', $value ) ? wfMsg( 'ep-course-no-slashes' ) : true;
			},
		);

		$names = $this->table->selectFields( 'name', array(), array( 'DISTINCT' ) );

		if ( $this->getRequest()->getCheck( 'newname' ) ) {
			$newName = $this->getRequest()->getText( 'newname' );
			$names = array_merge( array( $newName => $newName ), $names );
		}
		else {
			$names = array_merge( array( '' => '' ), $names );
		}

		$fields['name'] = array(
			'class' => 'EPHTMLCombobox',
			'label-message' => 'ep-course-edit-name',
			'help-message' => 'ep-course-help-name',
			'required' => true,
			'options' => array_combine( $names, $names ),
		);

		$fields['org_id'] = array(
			'type' => 'select',
			'label-message' => 'ep-course-edit-org',
			'required' => true,
			'options' => $orgOptions,
			'validation-callback' => function( $value, array $alldata = null ) use ( $orgOptions ) {
				return in_array( (int)$value, array_values( $orgOptions ) ) ? true : wfMsg( 'ep-course-invalid-org' );
			},
		);

		$fields['token'] = array(
			'type' => 'text',
			'label-message' => 'ep-course-edit-token',
			'help-message' => 'ep-course-help-token',
			'maxlength' => 255,
			'size' => 20,
			'validation-callback' => function( $value, array $alldata = null ) {
				$strLen = strlen( $value );
				return ( $strLen !== 0 && $strLen < 2 ) ? wfMsgExt( 'ep-course-invalid-token', 'parsemag', 2 ) : true;
			} ,
		);

		$fieldFields = $this->table->selectFields( 'term', array(), array( 'DISTINCT' ) );
		$fieldFields = array_merge( array( '' => '' ), $fieldFields );
		$fields['term'] = array(
			'class' => 'EPHTMLCombobox',
			'label-message' => 'ep-course-edit-term',
			'required' => true,
			'options' => array_combine( $fieldFields, $fieldFields ),
		);

		$fields['start'] = array(
			'class' => 'EPHTMLDateField',
			'label-message' => 'ep-course-edit-start',
			'required' => true,
		);

		$fields['end'] = array(
			'class' => 'EPHTMLDateField',
			'label-message' => 'ep-course-edit-end',
			'required' => true,
		);

		$fieldFields = $this->table->selectFields( 'field', array(), array( 'DISTINCT' ) );
		$fieldFields = array_merge( array( '' => '' ), $fieldFields );
		$fields['field'] = array(
			'class' => 'EPHTMLCombobox',
			'label-message' => 'ep-course-edit-field',
			'required' => true,
			'options' => array_combine( $fieldFields, $fieldFields ),
		);

		$levels = $this->table->selectFields( 'level', array(), array( 'DISTINCT' ) );
		$levels = array_merge( array( '' => '' ), $levels );
		$fields['level'] = array(
			'class' => 'EPHTMLCombobox',
			'label-message' => 'ep-course-edit-level',
			'required' => true,
			'options' => array_combine( $levels, $levels ),
		);

		$langOptions = EPUtils::getLanguageOptions( $this->getLanguage()->getCode() );
		$fields['lang'] = array(
			'type' => 'select',
			'label-message' => 'ep-course-edit-lang',
			'maxlength' => 255,
			'required' => true,
			'options' => $langOptions,
			'validation-callback' => function( $value, array $alldata = null ) use ( $langOptions ) {
				return in_array( $value, $langOptions ) ? true : wfMsg( 'ep-course-invalid-lang' );
			}
		);

		$fields['description'] = array(
			'type' => 'textarea',
			'label-message' => 'ep-course-edit-description',
			'required' => true,
			'validation-callback' => function( $value, array $alldata = null ) {
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

		$data['title'] = $data['name'];

		if ( $this->isNewPost() ) {
			$data['org_id'] = $this->getRequest()->getVal( 'neworg' );

			$data['title'] = wfMsgExt(
				'ep-course-edit-name-format',
				'parsemag',
				$data['name'],
				$this->getRequest()->getVal( 'newterm' )
			);

			$data['term'] = $this->getRequest()->getVal( 'newterm' );

			$data['description'] = $this->getDefaultDescription( array(
				'institutionid' => $data['org_id'],
				'name' => $data['name'],
				'title' => $data['name'],
				'term' => $data['term'],
			) );
		}
		else {
			unset( $data['name'] );
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

	/**
	 * Returns the description to use as default, based
	 * on the courseDescPage and courseOrgDescPage settings.
	 *
	 * @since 0.1
	 *
	 * @param array $data
	 *
	 * @return string
	 */
	protected function getDefaultDescription( array $data ) {
		$primaryPage = EPSettings::get( 'courseDescPage' );
		$orgPage = EPSettings::get( 'courseOrgDescPage' );

		$orgTitle = EPOrgs::singleton()->selectFieldsRow( 'name', array( 'id' => $data['institutionid'] ) );

		$content = false;

		if ( $orgTitle !== false ) {
			$orgPage = str_replace(
				array( '$1', '$2' ),
				array( $orgTitle, $primaryPage ),
				$orgPage
			);

			$content = EPUtils::getArticleContent( $orgPage );
		}

		if ( $content === false ) {
			$content = EPUtils::getArticleContent( $primaryPage );
		}

		if ( $content === false ) {
			$content = '';
		}
		else {
			if ( $orgTitle !== false ) {
				$data['institution'] = $orgTitle;
			}

			$content = str_replace(
				array_map( function( $name ) {
					return '{{{' . $name . '}}}';
				}, array_keys( $data ) ),
				$data,
				$content
			);
		}

		return $content;
	}

	/**
	 * @see EPEditAction::getTitleField
	 *
	 * @since 0.2
	 *
	 * @return string
	 */
	protected function getTitleField() {
		return 'title';
	}

	/**
	 * @since 0.2
	 *
	 * @param string $courseName
	 * @param string|integer $orgId
	 *
	 * @return string
	 */
	protected function getPrefixedTitle( $courseName, $orgId ) {
		$prefix = EPOrgs::singleton()->selectFieldsRow( 'name', array( 'id' => $orgId ) ) . '/';

		if ( strpos( $courseName, $prefix ) !== 0 ) {
			$courseName = $prefix . $courseName;
		}

		return $courseName;
	}

	/**
	 * @see EPEditAction::handleKnownFields
	 *
	 * @since 0.2
	 *
	 * @param array $fields
	 */
	protected function handleKnownFields( array &$fields ) {
		$fields['title'] = $this->getPrefixedTitle( $fields['title'], $fields['org_id'] );
	}

	/**
	 * @see EPEditAction::getDefaultFromItem
	 *
	 * @since 0.2
	 *
	 * @param EPPageObject $item
	 * @param string $name
	 *
	 * @return mixed
	 */
	protected function getDefaultFromItem( EPPageObject $item, $name ) {
		$value = $item->getField( $name );

		if ( $name === 'title' ) {
			$value = explode( '/', $value, 2 );
			$value = array_pop( $value );
		}

		return $value;
	}

}
