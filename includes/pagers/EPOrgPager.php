<?php

/**
 * Org pager, primarily for Special:Institutions.
 *
 * @since 0.1
 *
 * @file EPOrgPager.php
 * @ingroup EductaionProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EPOrgPager extends EPPager {

	/**
	 * Constructor.
	 *
	 * @param IContextSource $context
	 * @param array $conds
	 */
	public function __construct( IContextSource $context, array $conds = array() ) {
		parent::__construct( $context, $conds, EPOrgs::singleton() );
		$this->context->getOutput()->addModules( 'ep.pager.org' );
	}

	/**
	 * (non-PHPdoc)
	 * @see EPPager::getFields()
	 */
	public function getFields() {
		return array(
			'name',
			'city',
			'country',
			'course_count',
			'student_count',
			'active',
		);
	}

	/**
	 * (non-PHPdoc)
	 * @see TablePager::getRowClass()
	 */
	function getRowClass( $row ) {
		return 'ep-org-row';
	}

	/**
	 * (non-PHPdoc)
	 * @see TablePager::getTableClass()
	 */
	public function getTableClass() {
		return 'TablePager ep-orgs';
	}

	/**
	 * (non-PHPdoc)
	 * @see EPPager::getFormattedValue()
	 */
	public function getFormattedValue( $name, $value ) {
		switch ( $name ) {
			case 'name':
				$value = EPOrgs::singleton()->getLinkFor( $value );
				break;
			case 'country':
				$countries = array_flip( EPUtils::getCountryOptions( $this->getLanguage()->getCode() ) );
				$value = htmlspecialchars( $countries[$value] );
				break;
			case 'course_count': case 'student_count':
				$rawValue = $value;
				$value = htmlspecialchars( $this->getLanguage()->formatNum( $value ) );

				if ( $rawValue > 0 && $name === 'course_count' ) {
					$value = Linker::linkKnown(
						SpecialPage::getTitleFor( 'Courses' ),
						$value,
						array(),
						array( 'org_id' => $this->currentObject->getId() )
					);
				}

				break;
			case 'active':
				$value = wfMsgHtml( 'eporgpager-' . ( $value == '1' ? 'yes' : 'no' ) );
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
			'name',
			'city',
			'country',
			'course_count',
			'student_count',
			'active',
		);
	}

	function getDefaultSort() {
		return 'name';
	}

	/**
	 * (non-PHPdoc)
	 * @see EPPager::getFilterOptions()
	 */
	protected function getFilterOptions() {
		return array(
			'country' => array(
				'type' => 'select',
				'options' => EPUtils::getCountryOptions( $this->getLanguage()->getCode() ),
				'value' => ''
			),
			'active' => array(
				'type' => 'select',
				'options' => array(
					'' => '',
					wfMsg( 'eporgpager-yes' ) => '1',
					wfMsg( 'eporgpager-no' ) => '0',
				),
				'value' => '',
			),
		);
	}

	/**
	 * (non-PHPdoc)
	 * @see EPPager::getControlLinks()
	 */
	protected function getControlLinks( ORMRow $item ) {
		$links = parent::getControlLinks( $item );

		$links[] = $item->getLink( 'view', wfMsgHtml( 'view' ) );

		if ( $this->getUser()->isAllowed( 'ep-org' ) ) {
			$links[] = $item->getLink(
				'edit',
				wfMsgHtml( 'edit' ),
				array(),
				array( 'wpreturnto' => $this->getTitle()->getFullText() )
			);

			$links[] = $this->getDeletionLink(
				ApiDeleteEducation::getTypeForClassName( get_class( $this->table ) ),
				$item->getId(),
				$item->getIdentifier()
			);
		}

		return $links;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see EPPager::getMultipleItemActions()
	 */
	protected function getMultipleItemActions() {
		$actions = parent::getMultipleItemActions();

		if ( $this->getUser()->isAllowed( 'ep-org' )
			&& $this->getUser()->isAllowed( 'ep-bulkdelorgs' )
			&& $this->getUser()->getOption( 'ep_bulkdelorgs' ) ) {
				
			$actions[wfMsg( 'ep-pager-delete-selected' )] = array(
				'class' => 'ep-pager-delete-selected',
				'data-type' => ApiDeleteEducation::getTypeForClassName( get_class( $this->table ) )
			);
		}
		
		return $actions;
	}

}
