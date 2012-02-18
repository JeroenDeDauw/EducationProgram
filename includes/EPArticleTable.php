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

			$html .= $this->getArticleCell( $article, max( 1, count( $reviewers ) )  );

			foreach ( $reviewers as $nr => $userId ) {
				if ( $nr !== 0 ) {
					$html .= '</tr><tr>';
				}

				$html .= $this->getReviewerCell( $article, $userId );
			}

			// TODO: add reviewer adittion control for reviewers
		}

		// TODO: add article adittion control for student

		$html .= '</tr>';

		return $html;
	}

	/**
	 * Returns the HTML for a user cell.
	 *
	 * @since 0.1
	 *
	 * @param integer $userId
	 * @param integer $rowSpan
	 *
	 * @return string
	 */
	protected function getUserCell( $userId, $rowSpan ) {
		$user = User::newFromId( $userId );
		$name = $user->getRealName() === '' ? $user->getName() : $user->getRealName();

		$html = Linker::userLink( $userId, $name );

		if ( $this->getUser()->isAllowed( 'ep-remstudent' )
			&& array_key_exists( 'course_id', $this->articleConds )
			&& is_integer( $this->articleConds['course_id'] ) ) {

			$html .= EPUtils::getToolLinks(
				$userId,
				$name,
				$this->getContext(),
				array( Html::element(
					'a',
					array(
						'href' => '#',
						'data-user-id' => $userId,
						'data-course-id' => $this->articleConds['course_id'],
						'class' => 'ep-rem-student',
					),
					wfMsg( 'ep-artciles-remstudent' )
				) )
			);
		}
		else {
			$html .= Linker::userToolLinks( $userId, $name );
		}

		return html::rawElement(
			'td',
			array_merge(
				$this->getCellAttrs( 'user_id', $userId ),
				array( 'rowspan' => $rowSpan )
			),
			$html
		);
	}

	/**
	 * Returns the HTML for an article cell.
	 *
	 * @since 0.1
	 *
	 * @param EPArticle $article
	 * @param integer $rowSpan
	 *
	 * @return string
	 */
	protected function getArticleCell( EPArticle $article, $rowSpan ) {
		$html = Linker::link(
			$article->getTitle(),
			htmlspecialchars( $article->getTitle()->getFullText() )
		);

		if ( $this->getUser()->getId() === $article->getField( 'user_id' ) ) {
			$html .= ' (' . Html::element(
				'a',
				array(
					'href' => '#',
					'data-article-id' => $article->getId(),
					'class' => 'ep-rem-article',
				),
				wfMsg( 'ep-artciles-remarticle' )
			) . ')';
		}

		return Html::rawElement(
			'td',
			array_merge(
				$this->getCellAttrs( 'articles', $article ),
				array( 'rowspan' => $rowSpan )
			),
			$html
		);
	}

	/**
	 * Returns the HTML for a reviewer cell.
	 *
	 * @since 0.1
	 *
	 * @param EPArticle $article
	 * @param integer $userId
	 *
	 * @return string
	 */
	protected function getReviewerCell( EPArticle $article, $userId ) {
		$user = User::newFromId( $userId );
		$name = $user->getRealName() === '' ? $user->getName() : $user->getRealName();

		$html = Linker::userLink( $userId, $name );

		if ( $this->getUser()->isAllowed( 'ep-remreviewer' ) ) {
			$html .= EPUtils::getToolLinks(
				$userId,
				$name,
				$this->getContext(),
				array( Html::element(
					'a',
					array(
						'href' => '#',
						'data-user-id' => $userId,
						'data-article-id' => $article->getId(),
						'class' => 'ep-rem-reviewer',
					),
					wfMsg( 'ep-artciles-remreviewer' )
				) )
			);
		}
		elseif ( $this->getUser()->getId() === $userId ) {
			$html .= Linker::userToolLinks( $userId, $name );
			$html .= '<br />';
			$html .= Html::element(
				'button',
				array(
					'class' => 'ep-rem-reviewer-self',
					'disabled' => 'disabled',
					'data-article-id' => $article->getId(),
				),
				wfMsg( 'ep-artciles-remreviewer-self' )
			);
		}
		else {
			$html .= Linker::userToolLinks( $userId, $name );
		}

		return Html::rawElement(
			'td',
			$this->getCellAttrs( 'reviewers', $userId ),
			$html
		);
	}

	protected function addArticleRemovalControl() {

	}

	protected function addReviwerRemovalControl() {

	}

	protected function addArticleAdittionControl() {

	}

	protected function addReviewerAdittionControl() {

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
			$this->articles[$student->$field] = array( // TODO
				EPArticles::singleton()->newFromArray( array( 'page_id' => 1, 'user_id' => 1, 'reviewers' => array( 1, 1 ) ) ),
				EPArticles::singleton()->newFromArray( array( 'page_id' => 2, 'user_id' => 1, 'reviewers' => array( 1, 1 ) ) ),
			);
		}

		$conditions = array_merge( array( 'user_id' => $userIds ), $this->articleConds );

		$articles = EPArticles::singleton()->select( null, $conditions );

		foreach ( $articles as /* EPArticle */ $article ) {
			$this->articles[$article->getField( 'user_id' )][] = $article;
		}
	}

}
