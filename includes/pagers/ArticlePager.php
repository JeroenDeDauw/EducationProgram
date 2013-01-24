<?php

namespace EducationProgram;
use IContextSource, Linker, User, Title;

/**
 * Article pager which lists students and their associated articles reviewers for those if any.
 *
 * TODO: batch lookup user info to improve performance
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ArticlePager extends EPPager {

	/**
	 * Course ids pointing to their corresponding course titles.
	 *
	 * @since 0.2
	 *
	 * @var array
	 */
	protected $courseTitles;

	/**
	 * Constructor.
	 *
	 * @param IContextSource $context
	 * @param array $conds
	 */
	public function __construct( IContextSource $context, array $conds = array() ) {
		$this->mDefaultDirection = true;

		// when MW 1.19 becomes min, we want to pass an IContextSource $context here.
		parent::__construct( $context, $conds, Articles::singleton() );
	}

	/**
	 * @see EPPager::getFields()
	 */
	public function getFields() {
		return array(
			'page_id',
			'user_id',
			'course_id',
			'reviewers',
		);
	}

	/**
	 * @see TablePager::getRowClass()
	 */
	function getRowClass( $row ) {
		return 'ep-article-row';
	}

	/**
	 * @see TablePager::getTableClass()
	 */
	public function getTableClass() {
		return 'TablePager ep-articles';
	}

	/**
	 * @see Pager::getFormattedValue()
	 */
	protected function getFormattedValue( $name, $value ) {
		switch ( $name ) {
			case 'page_id':
				$value = Linker::link( Title::newFromID( $value ) );
				break;
			case 'user_id':
				$value = $this->getUserLink( $value );
				break;
			case 'course_id':
				$value = Courses::singleton()->getLinkFor( $this->courseTitles[$value] );
				break;
			case 'reviewers':
				$reviewers = array();

				foreach ( $this->currentObject->getField( $name ) as $userId ) {
					$reviewers[] = $this->getUserLink( $userId );
				}

				$value = implode( '<br />', $reviewers );
				break;
		}

		return $value;
	}

	/**
	 * @since 0.2
	 *
	 * @param integer $userId
	 *
	 * @return string
	 */
	protected function getUserLink( $userId ) {
		$user = User::newFromId( $userId );
		$name = !Settings::get( 'useStudentRealNames' ) || $user->getRealName() === '' ? $user->getName() : $user->getRealName();

		return Linker::userLink( $userId, $name ) . Linker::userToolLinks( $userId, $name );
	}

	/**
	 * @see Pager::getSortableFields()
	 */
	protected function getSortableFields() {
		return array(
		);
	}

	/**
	 * @see EP::hasActionsColumn()
	 */
	protected function hasActionsColumn() {
		return false;
	}

	function getDefaultSort() {
		return 'page_id';
	}

	/**
	 * @see IndexPager::doBatchLookups()
	 *
	 * @since 0.2
	 */
	protected function doBatchLookups() {
		$courseIds = array();
		$field = $this->table->getPrefixedField( 'course_id' );

		foreach( $this->mResult as $article ) {
			$courseIds[] = $article->$field;
		}

		$this->courseTitles = Courses::singleton()->selectFields(
			array( 'id', 'title' ),
			array( 'id' => $courseIds )
		);
	}

}
