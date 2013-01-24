<?php

namespace EducationProgram;
use IContextSource, IORMTable, Linker;

/**
 * Online ambassador pager.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class OAPager extends EPPager {

	/**
	 * @see EPPager::$currentObject
	 * @since 0.1
	 * @var RoleObject
	 */
	protected $currentObject;

	/**
	 * Constructor.
	 *
	 * @param IContextSource $context
	 * @param array $conds
	 * @param IORMTable|null $table
	 */
	public function __construct( IContextSource $context, array $conds = array(), IORMTable $table = null ) {
		$this->mDefaultDirection = true;

		$conds = array_merge(
			array( 'visible' => true ),
			$conds
		);

		parent::__construct(
			$context,
			$conds,
			is_null( $table ) ? OAs::singleton() : $table
		);
	}

	/**
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
	 * @see TablePager::getRowClass()
	 */
	function getRowClass( $row ) {
		return 'ep-oa-row';
	}

	/**
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
	 * @see Pager::getFormattedValue()
	 */
	protected function getFormattedValue( $name, $value ) {
		switch ( $name ) {
			case 'photo':
				$value = explode( ':', $value, 2 );
				$value = array_pop( $value );

				$file = wfFindFile( $value );
				$value = '';

				if ( $file !== false ) {
					$thumb = $file->transform( array(
						'width' => Settings::get( 'ambassadorImgWidth' ),
						'height' => Settings::get( 'ambassadorImgHeight' ),
					) );

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

				$courses = array();

				/**
				 * @var Course $course
				 */
				foreach ( $oa->getCourses( array( 'title', 'name' ), Courses::getStatusConds( 'current' ) ) as $course ) {
					$courses[] = $course->getLink();
				}

				$value = $this->getLanguage()->listToText( $courses );
				break;
		}

		return $value;
	}

	/**
	 * @see Pager::getSortableFields()
	 */
	protected function getSortableFields() {
		return array(
		);
	}

	/**
	 * @see EPPager::hasActionsColumn()
	 */
	protected function hasActionsColumn() {
		return false;
	}

	/**
	 * @see EPPager::getFieldNames()
	 */
	public function getFieldNames() {
		$fields = parent::getFieldNames();
		$fields['_courses'] = 'courses';
		return $fields;
	}

}
