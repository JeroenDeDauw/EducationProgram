<?php

namespace EducationProgram;
use IContextSource, Linker, SpecialPage, IORMRow;

/**
 * Org pager, primarily for Special:Institutions.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class OrgPager extends EPPager {

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
		$pager = new OrgPager( $context, $conditions );

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
	 * @see EPPager::getModules
	 *
	 * @since 0.3
	 *
	 * @return array
	 */
	public static function getModules() {
		return array_merge( parent::getModules(), array( 'ep.pager.org' ) );
	}

	/**
	 * Constructor.
	 *
	 * @param IContextSource $context
	 * @param array $conds
	 */
	public function __construct( IContextSource $context, array $conds = array() ) {
		parent::__construct( $context, $conds, Orgs::singleton() );
	}

	/**
	 * @see Pager::getFields()
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
	 * @see TablePager::getRowClass()
	 */
	function getRowClass( $row ) {
		return 'ep-org-row';
	}

	/**
	 * @see TablePager::getTableClass()
	 */
	public function getTableClass() {
		return 'TablePager ep-orgs';
	}

	/**
	 * @see Pager::getFormattedValue()
	 */
	public function getFormattedValue( $name, $value ) {
		switch ( $name ) {
			case 'name':
				$value = Orgs::singleton()->getLinkFor( $value );
				break;
			case 'country':
				$countries = array_flip( Utils::getCountryOptions( $this->getLanguage()->getCode() ) );
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
	 * @see Pager::getSortableFields()
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
	 * @see EPPager::getFilterOptions()
	 */
	protected function getFilterOptions() {
		return array(
			'country' => array(
				'type' => 'select',
				'options' => Utils::getCountryOptions( $this->getLanguage()->getCode() ),
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
