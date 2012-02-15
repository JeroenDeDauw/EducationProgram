<?php

/**
 * Pager that lists articles per student and for each article the associated reviewers, if any.
 *
 * @since 0.1
 *
 * @file EPArticleTable.php
 * @ingroup EductaionProgram
 *
 * @licence GNU GPL v3 or later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EPArticleTable extends EPPager {

	/**
	 * Constructor.
	 *
	 * @param IContextSource $context
	 * @param array $conds
	 * @param array $articleConds
	 */
	public function __construct( IContextSource $context, array $conds = array(), $articleConds = array() ) {
		$this->mDefaultDirection = true;
		$this->articleConds = $articleConds;

		// when MW 1.19 becomes min, we want to pass an IContextSource $context here.
		parent::__construct( $context, $conds, EPStudents::singleton() );
	}

	/**
	 * (non-PHPdoc)
	 * @see EPPager::getFields()
	 */
	public function getFields() {
		return array(
			'id',
			'user_id',
		);
	}

	/**
	 * (non-PHPdoc)
	 * @see TablePager::getRowClass()
	 */
	function getRowClass( $row ) {
		return 'ep-student-row';
	}

	/**
	 * (non-PHPdoc)
	 * @see TablePager::getTableClass()
	 */
	public function getTableClass() {
		return 'TablePager ep-students';
	}

	/**
	 * (non-PHPdoc)
	 * @see EPPager::getFormattedValue()
	 */
	protected function getFormattedValue( $name, $value ) {
		switch ( $name ) {
			case 'user_id':
				$user = User::newFromId( $value );
				$name = $user->getRealName() === '' ? $user->getName() : $user->getRealName();

				$value = Linker::userLink( $value, $name ) . Linker::userToolLinks( $value, $name );
				break;
			case '_articles':
				// TODO
				$value = serialize( $this->articles[$this->currentObject->getField( 'user_id' )] );
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

		$fields['_articles'] = 'articles';

		return $fields;
	}

	protected $articles = array();

	protected $articleConds;

	/**
	 * (non-PHPdoc)
	 * @see IndexPager::doBatchLookups()
	 */
	protected function doBatchLookups() {
		$userIds = array();

		while( $student = $this->mResult->fetchObject() ) {
			$field = EPStudents::singleton()->getPrefixedField( 'user_id' );
			$userIds[] = $student->$field;
			$this->articles[$student->$field] = array();
		}

		$conditions = array_merge( array( 'user_id' => $userIds ), $this->articleConds );

		$articles = EPArticles::singleton()->select( null, $conditions );

		foreach ( $articles as /* EPArticle */ $article ) {
			$this->articles[$article->getField( 'user_id' )][] = $article;
		}
	}

}
