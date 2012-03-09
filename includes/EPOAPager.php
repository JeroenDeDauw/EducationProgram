<?php

/**
 * Online ambassador pager.
 *
 * @since 0.1
 *
 * @file EPOAPager.php
 * @ingroup EductaionProgram
 *
 * @licence GNU GPL v3 or later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EPOAPager extends EPPager {

	/**
	 * Constructor.
	 *
	 * @param IContextSource $context
	 * @param array $conds
	 * @param DBTable|null $table
	 */
	public function __construct( IContextSource $context, array $conds = array(), DBTable $table = null ) {
		$this->mDefaultDirection = true;

		$conds = array_merge(
			array( 'visible' => true ),
			$conds
		);

		parent::__construct(
			$context,
			$conds,
			is_null( $table ) ? EPOAs::singleton() : $table
		);
	}

	/**
	 * (non-PHPdoc)
	 * @see EPPager::getFields()
	 */
	public function getFields() {
		return array(
			'photo',
			'user_id',
			'bio',
		);
	}

	/**
	 * (non-PHPdoc)
	 * @see TablePager::getRowClass()
	 */
	function getRowClass( $row ) {
		return 'ep-oa-row';
	}

	/**
	 * (non-PHPdoc)
	 * @see TablePager::getTableClass()
	 */
	public function getTableClass() {
		return 'TablePager ep-oas';
	}

	function getCellAttrs( $field, $value ) {
		$attr = parent::getCellAttrs( $field, $value );

		if ( in_array( $field, array( 'user_id', '_courses' ) ) ) {
			$attr['style'] = 'min-width: 200px';
		}

		return $attr;
	}

	/**
	 * (non-PHPdoc)
	 * @see EPPager::getFormattedValue()
	 */
	protected function getFormattedValue( $name, $value ) {
		switch ( $name ) {
			case 'photo':
				$value = explode( ':', $value, 2 );
				$value = array_pop( $value );

				$file = wfFindFile( $value );
				$value = '';

				if ( $file !== false ) {
					$thumb = $file->transform( array( 'width' => 200 ) );

					if ( $thumb && !$thumb->isError() ) {
						$value = $thumb->toHtml();
					}
				}
				break;
			case 'user_id':
				$oa = $this->currentObject;
				$value = Linker::userLink( $value, $oa->getName() ) . Linker::userToolLinks( $value, $oa->getName() );
				break;
			case 'bio':
				$value = $this->getOutput()->parseInline( $value );
				break;
			case '_courses':
				$oa = $this->currentObject;
				$value = $this->getLanguage()->listToText( array_map(
					function( EPCourse $course ) {
						return $course->getLink();
					},
					$oa->getCourses( 'name', EPCourses::getStatusConds( 'current' ) )
				) );
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
	 * @see EPPager::getFieldNames()
	 */
	public function getFieldNames() {
		$fields = parent::getFieldNames();
		$fields['_courses'] = 'courses';
		return $fields;
	}

}
