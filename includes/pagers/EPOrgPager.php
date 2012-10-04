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
	 * Returns the HTML for a pager with institutions.
	 *
	 * @since 0.1
	 *
	 * @param IContextSource $context
	 * @param array $conditions
	 *
	 * @return string
	 */
	public static function getPager( IContextSource $context, array $conditions = array() ) {
		$pager = new EPOrgPager( $context, $conditions );

		if ( $pager->getNumRows() ) {
			return
				$pager->getFilterControl() .
				$pager->getNavigationBar() .
				$pager->getBody() .
				$pager->getNavigationBar() .
				$pager->getMultipleItemControl();
		}
		else {
			return $pager->getFilterControl( true ) .
				$context->msg( 'ep-institutions-noresults' )->escaped();
		}
	}

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
				// @todo FIXME: Add full text of all used message keys here for grepping
				//              and transparancy purposes.
				$value = $this->msg( 'eporgpager-' . ( $value == '1' ? 'yes' : 'no' ) )->escaped();
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
					$this->msg( 'eporgpager-yes' )->text() => '1',
					$this->msg( 'eporgpager-no' )->text() => '0',
				),
				'value' => '',
			),
		);
	}

	/**
	 * (non-PHPdoc)
	 * @see EPPager::getControlLinks()
	 */
	protected function getControlLinks( IORMRow $item ) {
		$links = parent::getControlLinks( $item );

		$links[] = $item->getLink( 'view', $this->msg( 'view' )->escaped() );

		if ( $this->getUser()->isAllowed( 'ep-org' ) ) {
			$links[] = $item->getLink(
				'edit',
				$this->msg( 'edit' )->escaped(),
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

			$actions[$this->msg( 'ep-pager-delete-selected' )->text()] = array(
				'class' => 'ep-pager-delete-selected',
				'data-type' => ApiDeleteEducation::getTypeForClassName( get_class( $this->table ) )
			);
		}

		return $actions;
	}
}
