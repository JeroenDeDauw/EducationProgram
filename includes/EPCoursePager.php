<?php

/**
 * Course pager.
 *
 * @since 0.1
 *
 * @file EPCoursePager.php
 * @ingroup EductaionProgram
 *
 * @licence GNU GPL v3 or later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EPCoursePager extends EPPager {

	/**
	 * Constructor.
	 *
	 * @param IContextSource $context
	 * @param array $conds
	 */
	public function __construct( IContextSource $context, array $conds = array() ) {
		parent::__construct( $context, $conds, 'EPCourse' );
	}

	/**
	 * (non-PHPdoc)
	 * @see EPPager::getFields()
	 */
	public function getFields() {
		return array(
			'id',
			'mc_id',
			'year',
			'start',
			'end',
			'students',
		);
	}

	/**
	 * (non-PHPdoc)
	 * @see TablePager::getRowClass()
	 */
	function getRowClass( $row ) {
		return 'ep-course-row';
	}

	/**
	 * (non-PHPdoc)
	 * @see TablePager::getTableClass()
	 */
	public function getTableClass() {
		return 'TablePager ep-courses';
	}

	/**
	 * (non-PHPdoc)
	 * @see EPPager::getFormattedValue()
	 */
	public function getFormattedValue( $name, $value ) {
		switch ( $name ) {
			case 'id':
				$value = Linker::linkKnown(
					SpecialPage::getTitleFor( 'Course', $value ),
					htmlspecialchars( $this->getLanguage()->formatNum( $value, true ) )
				);
				break;
			case 'mc_id':
				$value = EPMC::selectRow( 'name', array( 'id' => $value ) )->getField( 'name' );

				$value = Linker::linkKnown(
					SpecialPage::getTitleFor( 'MasterCourse', $value ),
					htmlspecialchars( $value )
				);
				break;
			case 'year':
				$value = htmlspecialchars( $this->getLanguage()->formatNum( $value, true ) );
				break;
			case 'start': case 'end':
				$value = htmlspecialchars( $this->getLanguage()->date( $value ) );
				break;
			case '_status':
				$value = htmlspecialchars( EPCourse::getStatusMessage( $this->currentObject->getStatus() ) );
			case 'students':
				$value = htmlspecialchars( $this->getLanguage()->formatNum( $value ) );
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
			'id',
			'year',
			'start',
			'end',
			'students',
		);
	}

	/**
	 * (non-PHPdoc)
	 * @see EPPager::getFieldNames()
	 */
	public function getFieldNames() {
		$fields = parent::getFieldNames();

		if ( array_key_exists( 'mc_id', $this->conds ) && array_key_exists( 'org_id', $fields ) ) {
			unset( $fields['org_id'] );
		}

		$fields = wfArrayInsertAfter( $fields, array( '_status' => 'status' ), 'students' );

		return $fields;
	}

	/**
	 * (non-PHPdoc)
	 * @see EPPager::getFilterOptions()
	 */
	protected function getFilterOptions() {
		$options = array();

		if ( !array_key_exists( 'mc_id', $this->conds ) ) {
			$options['mc_id'] = array(
				'type' => 'select',
				'options' => array_merge(
					array( '' => '' ),
					EPMC::getMasterCourseOptions( EPMC::select( array( 'name', 'id' ) ) )
				),
				'value' => '',
				'datatype' => 'int',
			);

			$options['org_id'] = array(
				'type' => 'select',
				'options' => array_merge(
					array( '' => '' ),
					EPOrg::getOrgOptions( EPOrg::select( array( 'name', 'id' ) ) )
				),
				'value' => '',
				'datatype' => 'int',
			);
		}

		$years = EPCourse::selectFields( 'year', array(), array( 'DISTINCT' ), array(), true );
		asort( $years, SORT_NUMERIC );
		$years = array_merge( array( '' ), $years );
		$years = array_combine( $years, $years );

		$options['year'] = array(
			'type' => 'select',
			'options' => $years,
			'value' => '',
		);

		$options['status'] = array(
			'type' => 'select',
			'options' => array_merge(
				array( '' => '' ),
				EPCourse::getStatuses()
			),
			'value' => 'current',
		);

		return $options;
	}

	/**
	 * (non-PHPdoc)
	 * @see EPPager::getControlLinks()
	 */
	protected function getControlLinks( EPDBObject $item ) {
		$links = parent::getControlLinks( $item );

		$links[] = $value = Linker::linkKnown(
			SpecialPage::getTitleFor( 'Course', $item->getId() ),
			wfMsgHtml( 'view' )
		);

		if ( $this->getUser()->isAllowed( 'ep-term' ) ) {
			$links[] = $value = Linker::linkKnown(
				SpecialPage::getTitleFor( 'EditCourse', $item->getId() ),
				wfMsgHtml( 'edit' )
			);

			$links[] = $this->getDeletionLink( 'course', $item->getId() );
		}

		return $links;
	}

	/**
	 * (non-PHPdoc)
	 * @see EPPager::getMultipleItemActions()
	 */
	protected function getMultipleItemActions() {
		$actions = parent::getMultipleItemActions();

		if ( $this->getUser()->isAllowed( 'ep-course' ) ) {
			$actions[wfMsg( 'ep-pager-delete-selected' )] = array(
				'class' => 'ep-pager-delete-selected',
				'data-type' => ApiDeleteEducation::getTypeForClassName( $this->className )
			);
		}
		
		return $actions;
	}

	/**
	 * (non-PHPdoc)
	 * @see EPPager::getConditions()
	 */
	protected function getConditions() {
		$conds = parent::getConditions();

		if ( array_key_exists( 'status', $conds ) ) {
			$now = wfGetDB( DB_SLAVE )->addQuotes( wfTimestampNow() );

			switch ( $conds['status'] ) {
				case 'passed':
					$conds[] = 'end < ' . $now;
					break;
				case 'planned':
					$conds[] = 'start > ' . $now;
					break;
				case 'current':
					$conds[] = 'end >= ' . $now;
					$conds[] = 'start <= ' . $now;
					break;
			}

			unset( $conds['status'] );
		}

		return $conds;
	}

}
