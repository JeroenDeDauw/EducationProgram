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
	 * @since 0.4 alpha
	 *
	 * An OrgDeletionHelper for row currently being processed
	 *
	 * @var OrgDeletionHelper
	 */
	protected $currentDelHelper;

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
				// Give grep a chance to find the usages: eporgpager-yes, eporgpager-no

				// NOTE: Here we're *not* formatting the value, we're calling a
				// method on the current object to compute it. This is *not* how
				// this method was designed to be used. However, it's a simple
				// way to provide accurate information about an institution's
				// activity, with little possibility of additional breakage and
				// no inconvenience to translators or the language team.
				// Given the status of this code, this seems reasonable.
				// (The actual $value received by this method in this case is
				// from a deprecated field that is not updated correctly.)
				// See: https://www.mediawiki.org/wiki/Wikipedia_Education_Program/Database_Analysis_Notes
				// and https://gerrit.wikimedia.org/r/#/c/109631/6
				$value = $this->msg( $this->currentObject->isActive() ?
						'eporgpager-yes' : 'eporgpager-no' )->escaped();
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

			// Check restrictions before adding deletion link
			if ( $this->currentDelHelper->checkRestrictions() ) {

				$links[] = $this->getDeletionLink(
					ApiDeleteEducation::getTypeForClassName( get_class( $this->table ) ),
					$item->getId(),
					$item->getIdentifier()
				);
			}
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

	/**
	 * Calls parent, then sets up and performs checks with OrgDeletionHelper
	 * for the current row. (This lets us access the OrgDeletionHelper at
	 * various points during row formatting.)
	 *
	 * @see EPPager::prepareCurrentRowObjs()
	 *
	 * @since 0.4 alpha
	 */
	protected function prepareCurrentRowObjs() {
		parent::prepareCurrentRowObjs();

		$this->currentDelHelper =
			new OrgDeletionHelper( $this->currentObject, $this->context );

		$this->currentDelHelper->checkRestrictions();
	}

	/**
	 * Calls parent and adds additional row attributes as necessary.
	 *
	 * @see \TablePager::geRowAttrs()
	 *
	 * @since 0.4 alpha
	 *
	 * @param Object $row
	 */
	public function getRowAttrs( $row ) {
		$attrs = parent::getRowAttrs( $row );

		// If this institution can't be deleted, add a data attribute
		// with a plain message explaining why. This will be detected in
		// JS and will prevent mass deletion of any set of rows that includes
		// this one.
		if ( !$this->currentDelHelper->checkRestrictions() ) {
			$attrs = array_merge( $attrs,
				array(
					'data-no-del-text' =>
					$this->currentDelHelper->getCantDeleteMsgPlain()
				)
			);
		}

		return $attrs;
	}
}
