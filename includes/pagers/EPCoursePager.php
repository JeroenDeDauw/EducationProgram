<?php

/**
 * Course pager.
 *
 * @since 0.1
 *
 * @file EPCoursePager.php
 * @ingroup EductaionProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EPCoursePager extends EPPager {

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
		parent::__construct( $context, $conds, EPCourses::singleton() );
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
	 * @param string|false $filterPrefix
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
	 * (non-PHPdoc)
	 * @see EPPager::getFields()
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
			case 'name':
				$value = $this->table->getLinkFor( $value );
				break;
			case 'org_id':
				if ( array_key_exists( $value, $this->orgNames ) ) {
					$value = EPOrgs::singleton()->getLinkFor( $this->orgNames[$value] );
				}
				else {
					wfWarn( 'Org id not in $this->orgNames in ' . __METHOD__ );
				}
				break;
			case 'term':
				$value = htmlspecialchars( $value );
				break;
			case 'lang':
				$langs = LanguageNames::getNames( $this->getLanguage()->getCode() );
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
				$value = htmlspecialchars( EPCourse::getStatusMessage( $this->currentObject->getStatus() ) );
			case 'student_count':
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
			'name',
			'term',
			'lang',
			'student_count',
		);
	}

	/**
	 * (non-PHPdoc)
	 * @see EPPager::getFieldNames()
	 */
	public function getFieldNames() {
		$fields = parent::getFieldNames();

		$fields = wfArrayInsertAfter( $fields, array( '_status' => 'status' ), 'student_count' );

		return $fields;
	}

	/**
	 * (non-PHPdoc)
	 * @see EPPager::getFilterOptions()
	 */
	protected function getFilterOptions() {
		$options = array();

		$orgs = EPOrgs::singleton();

		$options['org_id'] = array(
			'type' => 'select',
			'options' => array_merge(
				array( '' => '' ),
				$orgs->selectFields( array( 'name', 'id' ) )
			),
			'value' => '',
		);

		$terms = EPCourses::singleton()->selectFields( 'term', array(), array( 'DISTINCT' ), true );

		natcasesort( $terms );
		$terms = array_merge( array( '' ), $terms );
		$terms = array_combine( $terms, $terms );

		$options['term'] = array(
			'type' => 'select',
			'options' => $terms,
			'value' => '',
		);

//		$options['lang'] = array(
//			'type' => 'select',
//			'options' => EPUtils::getLanguageOptions( $this->getLanguage()->getCode() ),
//			'value' => '',
//		);

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
	protected function getControlLinks( ORMRow $item ) {
		$links = parent::getControlLinks( $item );

		$links[] = $item->getLink( 'view', wfMsgHtml( 'view' ) );

		if ( !$this->readOnlyMode && $this->getUser()->isAllowed( 'ep-course' ) ) {
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

		if ( !$this->readOnlyMode
			&& $this->getUser()->isAllowed( 'ep-course' )
			&& $this->getUser()->isAllowed( 'ep-bulkdelcourses' )
			&& $this->getUser()->getOption( 'ep_bulkdelcourses' ) ) {

			$actions[wfMsg( 'ep-pager-delete-selected' )] = array(
				'class' => 'ep-pager-delete-selected',
				'data-type' => ApiDeleteEducation::getTypeForClassName( get_class( $this->table ) )
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

	/**
	 * (non-PHPdoc)
	 * @see EPPager::hasActionsColumn()
	 */
	protected function hasActionsColumn() {
		return !$this->readOnlyMode;
	}

	/**
	 * (non-PHPdoc)
	 * @see IndexPager::doBatchLookups()
	 */
	protected function doBatchLookups() {
		$orgIds = array();
		$field = $this->table->getPrefixedField( 'org_id' );

		while( $course = $this->mResult->fetchObject() ) {
			$orgIds[] = $course->$field;
		}

		$this->orgNames = EPOrgs::singleton()->selectFields(
			array( 'id', 'name' ),
			array( 'id' => $orgIds )
		);
	}

}
