<?php

/**
 * Master course pager.
 *
 * @since 0.1
 *
 * @file EPMCPager.php
 * @ingroup EductaionProgram
 *
 * @licence GNU GPL v3 or later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EPMCPager extends EPPager {

	/**
	 * Constructor.
	 *
	 * @param IContextSource $context
	 * @param array $conds
	 */
	public function __construct( IContextSource $context, array $conds = array() ) {
		parent::__construct( $context, $conds, 'EPMC' );
	}

	/**
	 * (non-PHPdoc)
	 * @see EPPager::getFields()
	 */
	public function getFields() {
		return array(
			'name',
			'org_id',
			'students',
			'active',
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
	protected function getFormattedValue( $name, $value ) {
		switch ( $name ) {
			case 'name':
				$value = Linker::linkKnown(
					SpecialPage::getTitleFor( 'MasterCourse', $value ),
					htmlspecialchars( $value )
				);
				break;
			case 'org_id':
				$value = EPOrg::selectRow( 'name', array( 'id' => $value ) )->getField( 'name' );

				$value = Linker::linkKnown(
					SpecialPage::getTitleFor( 'Institution', $value ),
					htmlspecialchars( $value )
				);
				break;
			case 'students':
				$value = htmlspecialchars( $this->getLanguage()->formatNum( $value ) );
				break;
			case 'active':
				$value = wfMsgHtml( 'epmcpager-' . ( $value == '1' ? 'yes' : 'no' ) );
				break;
		}

		return $value;
	}

	function getDefaultSort() {
		$c = $this->className; // Yeah, this is needed in PHP 5.3 >_>
		return $c::getPrefixedField( 'name' );
	}

	/**
	 * (non-PHPdoc)
	 * @see EPPager::getSortableFields()
	 */
	protected function getSortableFields() {
		return array(
			'name',
			'students',
			'active',
		);
	}

	/**
	 * (non-PHPdoc)
	 * @see EPPager::getFilterOptions()
	 */
	protected function getFilterOptions() {
		return array(
			'org_id' => array(
				'type' => 'select',
				'options' => array_merge(
					array( '' => '' ),
					EPOrg::getOrgOptions( EPOrg::select( array( 'name', 'id' ) ) )
				),
				'value' => '',
				'datatype' => 'int',
			),
			'active' => array(
				'type' => 'select',
				'options' => array(
					'' => '',
					wfMsg( 'epcoursepager-yes' ) => '1',
					wfMsg( 'epcoursepager-no' ) => '0',
				),
				'value' => '',
			),
		);
	}

	/**
	 * (non-PHPdoc)
	 * @see EPPager::getControlLinks()
	 */
	protected function getControlLinks( EPDBObject $item ) {
		$links = parent::getControlLinks( $item );

		$links[] = $value = Linker::linkKnown(
			SpecialPage::getTitleFor( 'MasterCourse', $item->getField( 'name' ) ),
			wfMsgHtml( 'view' )
		);

		if ( $this->getUser()->isAllowed( 'ep-mc' ) ) {
			$links[] = $value = Linker::linkKnown(
				SpecialPage::getTitleFor( 'EditMasterCourse', $item->getField( 'name' ) ),
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

}
