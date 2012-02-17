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
	 * The doBatchLookups method gets all articles relevant to the users that will be displayed
	 * and stores them in this field.
	 * int userId => array( EPArticle $article0, ... )
	 *
	 * @since 0.1
	 * @var array
	 */
	protected $articles = array();

	/**
	 * Adittion conditions the articles need to match.
	 * By default all articles for the users are obtained,
	 *
	 * @var array
	 */
	protected $articleConds;

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

	protected $currentArticleKey = 0;


	/**
	 * (non-PHPdoc)
	 * @see TablePager::formatRow()
	 */
	function formatRow( $row ) {
		$this->mCurrentRow = $row;
		$this->currentObject = $this->table->newFromDBResult( $row );

		$student = $this->currentObject;
		$articles = $this->articles[$student->getField( 'user_id' )];

		$articleCount = count( $articles );
		$reviewerCount = array_reduce( $articles, function( /* integer */ $sum, EPArticle $article ) {
			return $sum + count( $article->getField( 'reviewers' ) );
		}, 0 );

		$html = Html::openElement( 'tr', $this->getRowAttrs( $row ) );

		$html .= $this->getUserCell( $student->getField( 'user_id' ), $reviewerCount );

		$isFirst = true;

		foreach ( $articles as /* EPArticle */ $article ) {
			if ( !$isFirst ) {
				$html .= '</tr><tr>';
			}

			$isFirst = false;

			$reviewers = $article->getField( 'reviewers' );

			$html .= Html::rawElement(
				'td',
				array_merge(
					$this->getCellAttrs( 'articles', $article ),
					array( 'rowspan' => max( 1, count( $reviewers ) ) )
				),
				serialize( $article ) // TODO
			);

			foreach ( $reviewers as $nr => $userId ) {
				if ( $nr !== 0 ) {
					$html .= '</tr><tr>';
				}

				$html .= Html::rawElement(
					'td',
					$this->getCellAttrs( 'reviewers', $userId ),
					$userId // TODO
				);
			}
		}

		$html .= '</tr>';

		return $html;
	}

	protected function getUserCell( $userId, $rowSpan ) {
		$user = User::newFromId( $userId );
		$name = $user->getRealName() === '' ? $user->getName() : $user->getRealName();

		return html::rawElement(
			'td',
			array_merge(
				$this->getCellAttrs( 'user_id', $userId ),
				array( 'rowspan' => $rowSpan )
			),
			Linker::userLink( $userId, $name ) . Linker::userToolLinks( $userId, $name )
		);
	}


	/**
	 * (non-PHPdoc)
	 * @see EPPager::getFormattedValue()
	 */
	protected function getFormattedValue( $name, $value ) { /* ... */ }

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

		unset( $fields['id'] );

		$fields['user_id'] = 'student';
		$fields['_articles'] = 'articles';
		$fields['_reviewers'] = 'reviewers';

		return $fields;
	}

	/**
	 * (non-PHPdoc)
	 * @see IndexPager::doBatchLookups()
	 */
	protected function doBatchLookups() {
		$userIds = array();

		while( $student = $this->mResult->fetchObject() ) {
			$field = EPStudents::singleton()->getPrefixedField( 'user_id' );
			$userIds[] = $student->$field;
			$this->articles[$student->$field] = array(
				EPArticles::singleton()->newFromArray( array( 'page_id' => 1, 'reviewers' => array( 'rev 0', 'rev 1' ) ) ),
				EPArticles::singleton()->newFromArray( array( 'page_id' => 2, 'reviewers' => array( 'rev 2', 'rev 3' ) ) ),
			);
		}

		$conditions = array_merge( array( 'user_id' => $userIds ), $this->articleConds );

		$articles = EPArticles::singleton()->select( null, $conditions );

		foreach ( $articles as /* EPArticle */ $article ) {
			$this->articles[$article->getField( 'user_id' )][] = $article;
		}
	}

}
