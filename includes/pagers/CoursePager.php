<?php

namespace EducationProgram;
use IContextSource;

/**
 * Course pager.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class CoursePager extends EPPager {
	/**
	 * When in read only mode, the pager should not show any course editing controls.
	 *
	 * @since 0.1
	 * @var boolean
	 */
	protected $readOnlyMode;

	/**
	 * List of org names, looked up in batch before the rows are displayed.
	 * org id => org name
	 *
	 * @since 0.1
	 * @var array
	 */
	protected $orgNames;

	/**
	 * Constructor.
	 *
	 * @param IContextSource $context
	 * @param array $conds
	 * @param boolean $readOnlyMode
	 */
	public function __construct( IContextSource $context, array $conds = array(), $readOnlyMode = false ) {
		$this->readOnlyMode = $readOnlyMode;
		parent::__construct( $context, $conds, Courses::singleton() );
	}

	/**
	 * Returns the resource loader modules used by the pager.
	 *
	 * @since 0.1
	 *
	 * @return array
	 */
	public static function getModules() {
		return array_merge( parent::getModules(), array( 'ep.pager.course' ) );
	}

	/**
	 * Display a pager with terms.
	 *
	 * @since 0.1
	 *
	 * @param IContextSource $context
	 * @param array $conditions
	 * @param boolean $readOnlyMode
	 * @param bool|string $filterPrefix false
	 *
	 * @return string
	 */
	public static function getPager( IContextSource $context, array $conditions = array(), $readOnlyMode = false, $filterPrefix = false ) {
		$pager = new static( $context, $conditions, $readOnlyMode );

		if ( $filterPrefix !== false ) {
			$pager->setFilterPrefix( $filterPrefix );
		}

		$html = '';

		if ( $pager->getNumRows() ) {
			$html .=
				$pager->getFilterControl() .
					$pager->getNavigationBar() .
					$pager->getBody() .
					$pager->getNavigationBar() .
					$pager->getMultipleItemControl();
		}
		else {
			$html .= $pager->getFilterControl( true );
			$html .= $context->msg( 'ep-courses-noresults' )->escaped();
		}

		return $html;
	}

	/**
	 * @see Pager::getFields()
	 */
	public function getFields() {
		return array(
			'name',
			'org_id',
			'term',
			'lang',
			'student_count',
		);
	}

	/**
	 * @see TablePager::getRowClass()
	 */
	function getRowClass( $row ) {
		return 'ep-course-row';
	}

	/**
	 * @see TablePager::getTableClass()
	 */
	public function getTableClass() {
		return 'TablePager ep-courses';
	}

	/**
	 * @see Pager::getFormattedValue()
	 */
	public function getFormattedValue( $name, $value ) {
		switch ( $name ) {
			case 'name':
				$orgId = $this->currentObject->getField( 'org_id' );

				if ( array_key_exists( $orgId, $this->orgNames ) ) {
					$value = $this->table->getLinkFor(
						$this->currentObject->getField( 'title' ),
						'view',
						htmlspecialchars( $value )
					);
				}
				break;
			case 'org_id':
				if ( array_key_exists( $value, $this->orgNames ) ) {
					$value = Orgs::singleton()->getLinkFor( $this->orgNames[$value] );
				}
				else {
					wfWarn( 'Org id not in $this->orgNames in ' . __METHOD__ );
				}
				break;
			case 'term':
				$value = htmlspecialchars( $value );
				break;
			case 'lang':
				$langs = \LanguageNames::getNames( $this->getLanguage()->getCode() );
				if ( array_key_exists( $value, $langs ) ) {
					$value = htmlspecialchars( $langs[$value] );
				}
				else {
					$value = '<i>' . htmlspecialchars( $this->getMsg( 'invalid-lang' ) ) . '</i>';
				}

				break;
			case 'start': case 'end':
				$value = htmlspecialchars( $this->getLanguage()->date( $value ) );
				break;
			case '_status':
				$value = htmlspecialchars( Course::getStatusMessage( $this->currentObject->getStatus() ) );
				break;
			case 'student_count':
				$value = htmlspecialchars( $this->getLanguage()->formatNum( $value ) );
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
			'term',
			'lang',
			'student_count',
		);
	}

	/**
	 * @see Pager::getFieldNames()
	 */
	public function getFieldNames() {
		$fields = parent::getFieldNames();

		$fields = wfArrayInsertAfter( $fields, array( '_status' => 'status' ), 'student_count' );

		return $fields;
	}

	/**
	 * @see EPPager::getFilterOptions()
	 */
	protected function getFilterOptions() {
		$options = array();

		$orgs = Orgs::singleton();

		$options['org_id'] = array(
			'type' => 'select',
			'options' => array_merge(
				array( '' => '' ),
				$orgs->selectFields( array( 'name', 'id' ) )
			),
			'value' => '',
		);

		$terms = Courses::singleton()->selectFields( 'term', array(), array( 'DISTINCT' ), true );

		natcasesort( $terms );
		$terms = array_merge( array( '' ), $terms );
		$terms = array_combine( $terms, $terms );

		$options['term'] = array(
			'type' => 'select',
			'options' => $terms,
			'value' => '',
		);

		$options['status'] = array(
			'type' => 'select',
			'options' => array_merge(
				array( '' => '' ),
				Course::getStatuses(),
				array( $this->msg( 'ep-course-status-current-planned' )->text() => 'current-planned' )
			),
			'value' => 'current-planned',
		);

		return $options;
	}

	/**
	 * @see EPPager::getControlLinks()
	 */
	protected function getControlLinks( \IORMRow $item ) {
		$links = parent::getControlLinks( $item );

		$links[] = $item->getLink( 'view', $this->msg( 'view' )->escaped() );

		if ( !$this->readOnlyMode && $this->getUser()->isAllowed( 'ep-course' ) ) {
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

		if ( !$this->readOnlyMode
			&& $this->getUser()->isAllowed( 'ep-course' )
			&& $this->getUser()->isAllowed( 'ep-bulkdelcourses' )
			&& $this->getUser()->getOption( 'ep_bulkdelcourses' ) ) {

			$actions[$this->msg( 'ep-pager-delete-selected' )->text()] = array(
				'class' => 'ep-pager-delete-selected',
				'data-type' => ApiDeleteEducation::getTypeForClassName( get_class( $this->table ) )
			);
		}

		return $actions;
	}

	/**
	 * @see EPPager::getConditions()
	 */
	protected function getConditions() {
		$conds = parent::getConditions();

		if ( array_key_exists( 'status', $conds ) ) {
			$now = wfGetDB( DB_SLAVE )->addQuotes( wfTimestampNow() );

			switch ( $conds['status'] ) {
				case 'current-planned':
					$conds[] = 'end >= ' . $now;
					break;
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

	/**
	 * @see EPPager::hasActionsColumn()
	 */
	protected function hasActionsColumn() {
		return !$this->readOnlyMode;
	}

	/**
	 * @see IndexPager::doBatchLookups()
	 */
	protected function doBatchLookups() {
		$orgIds = array();
		$field = $this->table->getPrefixedField( 'org_id' );

		foreach ( $this->mResult as $course ) {
			$orgIds[] = $course->$field;
		}

		$this->orgNames = Orgs::singleton()->selectFields(
			array( 'id', 'name' ),
			array( 'id' => $orgIds )
		);
	}
}
