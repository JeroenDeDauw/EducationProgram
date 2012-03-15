<?php

/**
 * Page listing recent student activity.
 *
 * @since 0.1
 *
 * @file SpecialStudentActivity.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v3 or later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class SpecialStudentActivity extends SpecialEPPage {

	/**
	 * Constructor.
	 *
	 * @since 0.1
	 */
	public function __construct() {
		parent::__construct( 'StudentActivity' );
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

		$this->displayNavigation();

		$out = $this->getOutput();

		$pager = new EPStudentActivityPager( $this->getContext(), array() );

		if ( $pager->getNumRows() ) {
			$out->addHTML(
				$pager->getFilterControl() .
				$pager->getNavigationBar() .
				$pager->getBody() .
				$pager->getNavigationBar() .
				$pager->getMultipleItemControl()
			);
		}
		else {
			$out->addHTML( $pager->getFilterControl( true ) );
			$out->addWikiMsg( 'ep-studentactivity-noresults' );
		}
	}

}


/**
 * Student pager, primarily for Special:Students.
 *
 * @since 0.1
 *
 * @file EPStudentPager.php
 * @ingroup EductaionProgram
 *
 * @licence GNU GPL v3 or later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EPStudentActivityPager extends EPPager {

	/**
	 * List of user ids mapped to user names and real names, set in doBatchLookups.
	 * The real names will just hold the user name when no real name is set.
	 * user id => array( user name, real name )
	 *
	 * @since 0.1
	 * @var array
	 */
	protected $userNames = array();

	/**
	 * List of user ids with the names of their associated courses.
	 * user id => array( course name 0, ... )
	 *
	 * @since 0.1
	 * @var array
	 */
	protected $courseNames = array();

	/**
	 * Constructor.
	 *
	 * @param IContextSource $context
	 * @param array $conds
	 */
	public function __construct( IContextSource $context, array $conds = array() ) {
		$this->mDefaultDirection = true;

		$conds[] = 'last_active > ' . wfGetDB( DB_SLAVE )->addQuotes(
			wfTimestamp( TS_MW, time() - ( EPSettings::get( 'recentActivityLimit' ) ) )
		);

		parent::__construct( $context, $conds, EPStudents::singleton() );
	}

	/**
	 * (non-PHPdoc)
	 * @see EPPager::getFields()
	 */
	public function getFields() {
		return array(
			'id',
			'user_id',
			'last_course',
			'last_active',
		);
	}

	/**
	 * (non-PHPdoc)
	 * @see TablePager::getRowClass()
	 */
	function getRowClass( $row ) {
		return 'ep-studentactivity-row';
	}

	/**
	 * (non-PHPdoc)
	 * @see TablePager::getTableClass()
	 */
	public function getTableClass() {
		return 'TablePager ep-studentactivity';
	}

	/**
	 * (non-PHPdoc)
	 * @see EPPager::getFormattedValue()
	 */
	protected function getFormattedValue( $name, $value ) {
		switch ( $name ) {
			case 'user_id':
				if ( array_key_exists( $value, $this->userNames ) ) {
					list( $userName, $realName ) = $this->userNames[$value];
					$value = Linker::userLink( $value, $userName, $realName ) . Linker::userToolLinks( $value, $userName );
				}
				else {
					wfWarn( 'User id not in $this->userNames in ' . __METHOD__ );
				}
				break;
			case 'first_enroll': case 'last_active':
			$value = htmlspecialchars( $this->getLanguage()->date( $value ) );
			break;
		}

		return $value;
	}

	/**
	 * (non-PHPdoc)
	 * @see EPPager::getSortableFields()
	 */
	protected function getSortableFields() {
		return array(
		);
	}

	/**
	 * (non-PHPdoc)
	 * @see EPPager::hasActionsColumn()
	 */
	protected function hasActionsColumn() {
		return false;
	}

	/**
	 * (non-PHPdoc)
	 * @see IndexPager::getDefaultSort()
	 */
	function getDefaultSort() {
		return 'last_active';
	}

	/**
	 * (non-PHPdoc)
	 * @see EPPager::getFieldNames()
	 */
	public function getFieldNames() {
		$fields = parent::getFieldNames();

		unset( $fields['id'] );

		$fields = wfArrayInsertAfter( $fields, array( 'org_id' => 'org-id' ), 'user_id' );

		return $fields;
	}

	/**
	 * (non-PHPdoc)
	 * @see IndexPager::doBatchLookups()
	 */
	protected function doBatchLookups() {
		$userIds = array();
		$field = $this->table->getPrefixedField( 'user_id' );

		while( $student = $this->mResult->fetchObject() ) {
			$userIds[] = (int)$student->$field;
		}

		if ( !empty( $userIds ) ) {
			$result = wfGetDB( DB_SLAVE )->select(
				'user',
				array( 'user_id', 'user_name', 'user_real_name' ),
				array( 'user_id' => $userIds ),
				__METHOD__
			);

			while( $user = $result->fetchObject() ) {
				$real = $user->user_real_name === '' ? $user->user_name : $user->user_real_name;
				$this->userNames[$user->user_id] = array( $user->user_name, $real );
			}

			$courseNameField = EPCourses::singleton()->getPrefixedField( 'name' );

			// TODO: $this->courseNames[] =
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see EPPager::getMsg()
	 */
	protected function getMsg( $messageKey ) {
		return wfMsg( strtolower( get_called_class() ) . '-' . str_replace( '_', '-', $messageKey ) );
	}

}