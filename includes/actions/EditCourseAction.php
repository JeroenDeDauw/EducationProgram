<?php

namespace EducationProgram;
use Page, IContextSource;

/**
 * Action to edit a course.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 * @ingroup Action
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EditCourseAction extends EditAction {
	/**
	 * Constructor.
	 *
	 * @since 0.1
	 *
	 * @param Page $page
	 * @param IContextSource $context
	 */
	public function __construct( Page $page, IContextSource $context = null ) {
		parent::__construct( $page, $context, Courses::singleton() );
	}

	/**
	 * @see Action::getName()
	 */
	public function getName() {
		return 'edit';
	}

	/**
	 * Returns the base string for generating message keys, which vary based
	 * on whether one has attempted to create a new course or edit an
	 * existing one.
	 *
	 * @return string
	 */
	protected function getMessageKeyBase() {
		return $this->isNew() ? 'ep-addcourse' : 'ep-editcourse';
	}

	/**
	 * @see Action::getRestriction()
	 */
	public function getRestriction() {
		return 'ep-course';
	}

	/**
	 * @see EditAction::onView()
	 */
	public function onView() {
		$out = $this->getOutput();

		$out->addModules( array( 'ep.datepicker', 'ep.combobox' ) );

		$identifier = Courses::normalizeTitle( $this->getTitle()->getText() );

		if ( !$this->isNewPost() && !$this->table->hasIdentifier( $identifier ) ) {

			// Get the most recent revision here, so we can check that the
			// institution hasn't been deleted before displaying undelete
			// info.
			$latestRevision = Revisions::singleton()->getLatestRevision(
				array(
					'object_identifier' => $identifier,
					'type' => Courses::singleton()->getRevisionedObjectTypeId(),
				)
			);

			// Make sure there are indeed previous revisions to undelete
			if ( $latestRevision !== false ) {

				// use CourseUndeletionHelper to check restrictions on
				// undeletion and display a message if we can't undelete.
				$undeletionHelper = new CourseUndeletionHelper(
					$latestRevision, $this->context, $this->page );

				if ( $undeletionHelper->checkRestrictions() ) {
					$this->displayUndeletionLink();
				} else {
					$undeletionHelper->outputCantUndeleteMsg();
				}

				$this->displayDeletionLog();
			}

			list( $name, $term ) = $this->titleToNameAndTerm( $this->getTitle()->getText() );

			$out->addHTML( Course::getAddNewRegion(
				$this->getContext(),
				array(
					'name' => $this->getRequest()->getText(
						'newname',
						$this->getLanguage()->ucfirst( $name )
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
	 * @see EditAction::getFormFields()
	 * @return array
	 */
	protected function getFormFields() {
		$fields = parent::getFormFields();

		$orgOptions = Orgs::singleton()->selectFields( array( 'name', 'id' ) );

		$messageMethod = array( $this, 'msg' );

		$fields['title'] = array(
			'type' => 'text',
			'label-message' => 'ep-course-edit-title',
			'help-message' => 'ep-course-help-title',
			'required' => true,
			'validation-callback' => function( $value, array $allData = null ) use ( $messageMethod ) {
				if ( strpos( $value, '/' ) === false ) {
					return true;
				}

				return call_user_func( $messageMethod, 'ep-course-no-slashes' )->text();
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
			'class' => 'EducationProgram\HTMLCombobox',
			'label-message' => 'ep-course-edit-name',
			'help-message' => 'ep-course-help-name',
			'required' => true,
			'options' => array_combine( $names, $names ),
		);

		$fields['description'] = array(
			'type' => 'textarea',
			'label-message' => 'ep-course-edit-description',
			'required' => true,
			'validation-callback' => function( $value, array $alldata = null ) use ( $messageMethod ) {
				if ( strlen( $value ) < 10 ) {
					return call_user_func( $messageMethod, 'ep-course-invalid-description', 10 )->text();
				}

				return true;
			} ,
			'rows' => 15,
			'cols' => 200,
			'id' => 'wpTextbox1',
		);

		$fields['org_id'] = array(
			'type' => 'select',
			'label-message' => 'ep-course-edit-org',
			'required' => true,
			'options' => $orgOptions,
			'validation-callback' => function( $value, array $allData = null ) use ( $orgOptions, $messageMethod ) {
				if ( in_array( (int)$value, array_values( $orgOptions ) ) ) {
					return true;
				}

				return call_user_func( $messageMethod, 'ep-course-invalid-org' )->text();
			},
		);

		$fields['token'] = array(
			'type' => 'text',
			'label-message' => 'ep-course-edit-token',
			'help-message' => 'ep-course-help-token',
			'maxlength' => 255,
			'size' => 20,
			'validation-callback' => function( $value, array $alldata = null ) use ( $messageMethod ) {
				$strLen = strlen( $value );

				if ( $strLen !== 0 && $strLen < 2 ) {
					return call_user_func( $messageMethod, 'ep-course-invalid-token', 2 )->text();
				}

				return true;
			},
		);

		$fieldFields = $this->table->selectFields( 'term', array(), array( 'DISTINCT' ) );
		$fieldFields = array_merge( array( '' => '' ), $fieldFields );
		$fields['term'] = array(
			'class' => 'EducationProgram\HTMLCombobox',
			'label-message' => 'ep-course-edit-term',
			'required' => true,
			'options' => array_combine( $fieldFields, $fieldFields ),
		);

		$fields['start'] = array(
			'class' => 'EducationProgram\HTMLDateField',
			'label-message' => 'ep-course-edit-start',
			'required' => true,
		);

		$fields['end'] = array(
			'class' => 'EducationProgram\HTMLDateField',
			'label-message' => 'ep-course-edit-end',
			'required' => true,
		);

		$fieldFields = $this->table->selectFields( 'field', array(), array( 'DISTINCT' ) );
		$fieldFields = array_merge( array( '' => '' ), $fieldFields );
		$fields['field'] = array(
			'class' => 'EducationProgram\HTMLCombobox',
			'label-message' => 'ep-course-edit-field',
			'required' => true,
			'options' => array_combine( $fieldFields, $fieldFields ),
		);

		$levels = $this->table->selectFields( 'level', array(), array( 'DISTINCT' ) );
		$levels = array_merge( array( '' => '' ), $levels );
		$fields['level'] = array(
			'class' => 'EducationProgram\HTMLCombobox',
			'label-message' => 'ep-course-edit-level',
			'required' => true,
			'options' => array_combine( $levels, $levels ),
		);

		$langOptions = Utils::getLanguageOptions( $this->getLanguage()->getCode() );
		$fields['lang'] = array(
			'type' => 'select',
			'label-message' => 'ep-course-edit-lang',
			'maxlength' => 255,
			'required' => true,
			'options' => $langOptions,
			'validation-callback' => function( $value, array $allData = null ) use ( $langOptions, $messageMethod ) {
				if ( in_array( $value, $langOptions ) ) {
					return true;
				}

				return call_user_func( $messageMethod, 'ep-course-invalid-lang' )->text();
			},
		);

		return $this->processFormFields( $fields );
	}

	/**
	 * @see EditAction::getNewData()
	 */
	protected function getNewData() {
		$data = parent::getNewData();

		$name = Courses::normalizeTitle( $data['name'] );

		$data['title'] = $name;

		if ( $this->isNewPost() ) {
			$data['org_id'] = $this->getRequest()->getVal( 'neworg' );

			$data['title'] = $this->msg(
				'ep-course-edit-name-format',
				$name,
				$this->getRequest()->getVal( 'newterm' )
			)->text();

			$data['term'] = $this->getRequest()->getVal( 'newterm' );

			$data['description'] = $this->getDefaultDescription( array(
				'institutionid' => $data['org_id'],
				'name' => $name,
				'title' => $name,
				'term' => $data['term'],
			) );

			$data['lang'] = $GLOBALS['wgContLang']->getCode();
		}
		else {
			unset( $data['name'] );
		}

		return $data;
	}

	/**
	 * @see EditAction::handleKnownField()
	 */
	protected function handleKnownField( $name, $value ) {
		if ( in_array( $name, array( 'end', 'start' ) ) ) {
			$value = wfTimestamp( TS_MW, strtotime( $value . ' UTC' ) );
		}

		if ( $name === 'token' ) {
			$value = trim( $value );
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
		$primaryPage = Settings::get( 'courseDescPage' );
		$orgPage = Settings::get( 'courseOrgDescPage' );

		$orgTitle = Orgs::singleton()->selectFieldsRow( 'name', array( 'id' => $data['institutionid'] ) );

		$content = false;

		if ( $orgTitle !== false ) {
			$orgPage = str_replace(
				array( '$1', '$2' ),
				array( $orgTitle, $primaryPage ),
				$orgPage
			);

			$content = Utils::getArticleContent( $orgPage );
		}

		if ( $content === false || $content === '' ) {
			$content = Utils::getArticleContent( $primaryPage );
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
	 * @see EditAction::getTitleField
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
		$prefix = Orgs::singleton()->selectFieldsRow( 'name', array( 'id' => $orgId ) ) . '/';

		if ( strpos( $courseName, $prefix ) !== 0 ) {
			$courseName = $prefix . $courseName;
		}

		return $courseName;
	}

	/**
	 * @see EditAction::handleKnownFields
	 *
	 * @since 0.2
	 *
	 * @param array $fields
	 */
	protected function handleKnownFields( array &$fields ) {
		$fields['title'] = $this->getPrefixedTitle( $fields['title'], $fields['org_id'] );
	}

	/**
	 * @see EditAction::getDefaultFromItem
	 *
	 * @since 0.2
	 *
	 * @param PageObject $item
	 * @param string $name
	 *
	 * @return mixed
	 */
	protected function getDefaultFromItem( PageObject $item, $name ) {
		$value = $item->getField( $name );

		if ( $name === 'title' ) {
			$value = explode( '/', $value, 2 );
			$value = $this->getLanguage()->ucfirst( array_pop( $value ) );
		}

		return $value;
	}
}
