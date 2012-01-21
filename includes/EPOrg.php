<?php

/**
 * Class representing a single organization/institution.
 *
 * @since 0.1
 *
 * @file EPOrg.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v3 or later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EPOrg extends EPDBObject {

	/**
	 * Cached array of the linked EPMC objects.
	 *
	 * @since 0.1
	 * @var array|false
	 */
	protected $mcs = false;

	/**
	 * Cached array of the linked EPCourse objects.
	 *
	 * @since 0.1
	 * @var array|false
	 */
	protected $courses = false;

	/**
	 * @see parent::getFieldTypes
	 *
	 * @since 0.1
	 *
	 * @return array
	 */
	protected static function getFieldTypes() {
		return array(
			'id' => 'id',

			'name' => 'str',
			'city' => 'str',
			'country' => 'str',

			'active' => 'bool',
			'courses' => 'int',
			'mcs' => 'int',
			'students' => 'int',
		);
	}

	/**
	 * (non-PHPdoc)
	 * @see EPDBObject::getDefaults()
	 */
	public static function getDefaults() {
		return array(
			'name' => '',
			'city' => '',
			'country' => '',

			'active' => false,
			'courses' => 0,
			'mcs' => 0,
			'students' => 0,
		);
	}

	/**
	 * (non-PHPdoc)
	 * @see EPDBObject::getLogInfo()
	 */
	protected function getLogInfo( $subType ) {
		return array(
			'type' => 'institution',
			'title' => $this->getTitle(),
		);
	}

	/**
	 * (non-PHPdoc)
	 * @see EPDBObject::loadSummaryFields()
	 */
	public function loadSummaryFields( $summaryFields = null ) {
		if ( is_null( $summaryFields ) ) {
			$summaryFields = array( 'courses', 'mcs', 'students', 'active' );
		}
		else {
			$summaryFields = (array)$summaryFields;
		}

		$fields = array();

		if ( in_array( 'courses', $summaryFields ) ) {
			$fields['courses'] = EPCourse::count( array( 'org_id' => $this->getId() ) );
		}

		if ( in_array( 'mcs', $summaryFields ) ) {
			$fields['mcs'] = EPMC::count( array( 'org_id' => $this->getId() ) );
		}

		$dbr = wfGetDB( DB_SLAVE );

		if ( in_array( 'students', $summaryFields ) ) {
			$courseIds = EPCourse::selectFields( 'id', array( 'org_id' => $this->getId() ) );

			if ( count( $courseIds ) > 0 ) {
				$fields['students'] = $dbr->select(
					'ep_students_per_course',
					'COUNT(*) AS rowcount',
					array( 'spc_course_id' => $courseIds )
				);

				$fields['students'] = $fields['students']->fetchObject()->rowcount;
			}
			else {
				$fields['students'] = 0;
			}
		}

		if ( in_array( 'active', $summaryFields ) ) {
			$now = wfGetDB( DB_SLAVE )->addQuotes( wfTimestampNow() );

			$fields['active'] = EPCourse::has( array(
				'org_id' => $this->getId(),
				'end >= ' . $now,
				'start <= ' . $now,
			) );
		}

		$this->setFields( $fields );
	}

	/**
	 * (non-PHPdoc)
	 * @see EPDBObject::removeFromDB()
	 */
	public function removeFromDB() {
		$id = $this->getId();
		$this->loadFields( array( 'name' ) );

		$success = parent::removeFromDB();

		if ( $success ) {
			$success = wfGetDB( DB_MASTER )->delete( 'ep_cas_per_org', array( 'cpo_org_id' => $id ) ) && $success;

			foreach ( EPMC::select( 'id', array( 'org_id' => $id ) ) as /* EPMC */ $masterCourse ) {
				$success = $masterCourse->removeFromDB() && $success;
			}
		}

		return $success;
	}

	/**
	 * Returns a list of orgs in an array that can be fed to select inputs.
	 *
	 * @since 0.1
	 *
	 * @param array|null $orgs
	 *
	 * @return array
	 */
	public static function getOrgOptions( array /* EPOrg */ $orgs = null ) {
		$options = array();

		if ( is_null( $orgs ) ) {
			$orgs = EPOrg::select( array( 'name', 'id' ) );
		}
		
		foreach ( $orgs as /* EPOrg */ $org ) {
			$options[$org->getField( 'name' )] = $org->getId();
		}

		return $options;
	}

	/**
	 * Adds a control to add a new org to the provided context.
	 * Adittional arguments can be provided to set the default values for the control fields.
	 *
	 * @since 0.1
	 *
	 * @param IContextSource $context
	 * @param array $args
	 *
	 * @return boolean
	 */
	public static function displayAddNewControl( IContextSource $context, array $args = array() ) {
		if ( !$context->getUser()->isAllowed( 'ep-org' ) ) {
			return false;
		}

		$out = $context->getOutput();

		$out->addHTML( Html::openElement(
			'form',
			array(
				'method' => 'post',
				'action' => SpecialPage::getTitleFor( 'EditInstitution' )->getLocalURL(),
			)
		) );

		$out->addHTML( '<fieldset>' );

		$out->addHTML( '<legend>' . wfMsgHtml( 'ep-institutions-addnew' ) . '</legend>' );

		$out->addHTML( Html::element( 'p', array(), wfMsg( 'ep-institutions-namedoc' ) ) );

		$out->addHTML( Xml::inputLabel(
			wfMsg( 'ep-institutions-newname' ),
			'newname',
			'newname',
			false,
			array_key_exists( 'name', $args ) ? $args['name'] : false
		) );

		$out->addHTML( '&#160;' . Html::input(
			'addneworg',
			wfMsg( 'ep-institutions-add' ),
			'submit'
		) );

		$out->addHTML( Html::hidden( 'isnew', 1 ) );

		$out->addHTML( '</fieldset></form>' );

		return true;
	}

	/**
	 * Display a pager with courses.
	 *
	 * @since 0.1
	 *
	 * @param IContextSource $context
	 * @param array $conditions
	 */
	public static function displayPager( IContextSource $context, array $conditions = array() ) {
		$pager = new EPOrgPager( $context, $conditions );

		if ( $pager->getNumRows() ) {
			$context->getOutput()->addHTML(
				$pager->getFilterControl() .
				$pager->getNavigationBar() .
				$pager->getBody() .
				$pager->getNavigationBar() .
				$pager->getMultipleItemControl()
			);
		}
		else {
			$context->getOutput()->addHTML( $pager->getFilterControl( true ) );
			$context->getOutput()->addWikiMsg( 'ep-institutions-noresults' );
		}
	}

	/**
	 * Retruns the master courses linked to this org.
	 *
	 * @since 0.1
	 *
	 * @param array|null $fields
	 *
	 * @return array of EPMC
	 */
	public function getMasterCourses( array $fields = null ) {
		if ( $this->mcs === false ) {
			$this->mcs = EPMC::select( $fields, array( 'org_id' => $this->getId() ) );
		}

		return $this->mcs;
	}

	/**
	 * Retruns the courses linked to this org.
	 *
	 * @since 0.1
	 *
	 * @param array|null $fields
	 *
	 * @return array of EPCourse
	 */
	public function getCourses( array $fields = null ) {
		if ( $this->courses === false ) {
			$this->courses = EPCourse::select( $fields, array( 'org_id' => $this->getId() ) );
		}

		return $this->courses;
	}

	/**
	 * Get the title of Special:Institution/name.
	 *
	 * @since 0.1
	 *
	 * @return Title
	 */
	public function getTitle() {
		return SpecialPage::getTitleFor( 'Institution', $this->getField( 'name' ) );
	}

	/**
	 * Get a link to Special:Institution/name.
	 *
	 * @since 0.1
	 *
	 * @return string
	 */
	public function getLink() {
		return Linker::linkKnown(
			$this->getTitle(),
			htmlspecialchars( $this->getField( 'name' ) )
		);
	}

}
