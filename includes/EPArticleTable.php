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
	 * Cached name of the course for which students are shown (if any).
	 *
	 * @since 0.1
	 * @var string|false
	 */
	protected $courseName = false;

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
	
	public function getBody() {
		$this->getOutput()->addModules( 'ep.articletable' );
		return parent::getBody();
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
		$user = $this->getUser();

		$rowCount = array_reduce( $articles, function( /* integer */ $sum, EPArticle $article ) use ( $user ) {
			$sum += max( count( $article->getField( 'reviewers' ) ), 1 );

			if ( $article->canBecomeReviewer( $user ) ) {
				$sum++;
			}

			return $sum;
		}, 0 );

		$html = Html::openElement( 'tr', $this->getRowAttrs( $row ) );

		$showArticleAdittion =
			$user->getId() === $student->getField( 'user_id' )
			&& array_key_exists( 'course_id', $this->articleConds )
			&& is_integer( $this->articleConds['course_id'] );

		if ( $showArticleAdittion ) {
			$rowCount++;
		}

		$html .= $this->getUserCell( $student->getField( 'user_id' ), $rowCount );

		$this->addNonStudentHTML( $html, $articles, $showArticleAdittion );

		$html .= '</tr>';

		return $html;
	}
	
	/**
	 * Adds the HTML for the article and reviewers to the table row.
	 * 
	 * @since 0.1
	 * 
	 * @param string $html
	 * @param array $articles
	 * @param boolean $showArticleAdittion
	 */
	protected function addNonStudentHTML( &$html, array $articles, $showArticleAdittion ) {
		$isFirst = true;

		foreach ( $articles as /* EPArticle */ $article ) {
			if ( !$isFirst ) {
				$html .= '</tr><tr>';
			}

			$isFirst = false;

			$reviewers = $article->getField( 'reviewers' );

			$articleRowCount = count( $reviewers );

			if ( $article->canBecomeReviewer( $this->getUser() ) ) {
				$articleRowCount++;
			}

			$articleRowCount = max( 1, $articleRowCount );

			$html .= $this->getArticleCell( $article, $articleRowCount );

			foreach ( $reviewers as $nr => $userId ) {
				if ( $nr !== 0 ) {
					$html .= '</tr><tr>';
				}

				$html .= $this->getReviewerCell( $article, $userId );
			}

			if ( $article->canBecomeReviewer( $this->getUser() ) ) {
				if ( count( $reviewers ) !== 0 ) {
					$html .= '</tr><tr>';
				}

				$html .= $this->getReviewerAdittionControl( $article );
			}
			else if ( count( $reviewers ) === 0 ) {
				$html .= '<td></td>';
			}
		}

		if ( $showArticleAdittion ) {
			if ( !$isFirst ) {
				$html .= '</tr><tr>';
			}

			$html .= $this->getArticleAdittionControl( $this->articleConds['course_id'] );
		}	
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
						'data-user-name' => $name,
						'data-course-name' => $this->getCourseName(),
						'data-token' => $this->getUser()->getEditToken( $this->articleConds['course_id'] . 'remstudent' . $userId ),
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
	 * Returns title of the course for which students are shown.
	 * Only call if there is a single course_id filter condition.
	 *
	 * @since 0.1
	 *
	 * @return string
	 */
	protected function getCourseName() {
		if ( $this->courseName === false ) {
			$this->courseName = EPCourses::singleton()->selectFieldsRow( 'name', array( 'id' => $this->articleConds['course_id'] ) );
		}

		return $this->courseName;
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

		$attr = array(
			'href' => '#',
			'data-article-id' => $article->getId(),
			'data-article-name' => $article->getTitle()->getFullText(),
			'data-token' => $this->getUser()->getEditToken( 'remarticle' . $article->getId() ),
			'class' => 'ep-rem-article',
		);

		if ( array_key_exists( 'course_id', $this->articleConds ) && is_integer( $this->articleConds['course_id'] ) ) {
			$attr['data-course-name'] = $this->getCourseName();
		}

		if ( $this->getUser()->getId() === $article->getField( 'user_id' ) ) {
			$html .= ' (' . Html::element(
				'a',
				$attr,
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
	 * @param integer $userId User id of the reviewer
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
						'data-article-name' => $article->getTitle()->getFullText(),
						'data-student-name' => $article->getUser()->getName(),
						'data-reviewer-name' => $user->getName(),
						'data-reviewer-id' => $user->getId(),
						'data-token' => $this->getUser()->getEditToken( $userId . 'remreviewer' . $article->getId() ),
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
					'data-article-name' => $article->getField( 'name' ),
					'data-student-name' => $article->getUser()->getName(),
					'data-token' => $this->getUser()->getEditToken( $userId . 'remreviewer' . $article->getId() ),
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

	/**
	 * Returns the HTML for the article adittion control.
	 * 
	 * @since 0.1
	 * 
	 * @param integer $courseId
	 * 
	 * @return string
	 */
	protected function getArticleAdittionControl( $courseId ) {
		$html = '';

		$html .= Html::openElement(
			'form',
			array(
				'method' => 'post',
				'action' => $this->getTitle()->getLocalURL( array( 'action' => 'epaddarticle' ) ),
			)
		);

		$html .=  Xml::inputLabel(
			wfMsg( 'ep-artciles-addarticle-text' ),
			'addarticlename',
			'addarticlename'
		);

		$html .= '&#160;' . Html::input(
			'addarticle',
			wfMsg( 'ep-artciles-addarticle-button' ),
			'submit',
			array(
				'class' => 'ep-addarticle',
			)
		);

		$html .= Html::hidden( 'course-id', $courseId );
		$html .= Html::hidden( 'token', $this->getUser()->getEditToken( 'addarticle' . $courseId ) );

		$html .= '</form>';

		return '<td colspan="2">' . $html . '</td>';
	}

	/**
	 * Returns the HTML for the reviewer adittion control.
	 * 
	 * @since 0.1
	 * 
	 * @param EPArticle $article
	 * 
	 * @return string
	 */
	protected function getReviewerAdittionControl( EPArticle $article ) {
		$html = Html::element(
			'button',
			array(
				'class' => 'ep-become-reviewer',
				'disabled' => 'disabled',
				'data-article-id' => $article->getId(),
				'data-article-name' => $article->getTitle()->getFullText(),
				'data-user-name' => $article->getUser()->getName(),
				'data-token' => $this->getUser()->getEditToken( 'addreviewer' . $article->getId() ),
			),
			wfMsg( 'ep-artciles-becomereviewer' )
		);

		return '<td>' . $html . '</td>';
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
			$this->articles[$student->$field] = array();
		}

		$conditions = array_merge( array( 'user_id' => $userIds ), $this->articleConds );

		$articles = EPArticles::singleton()->select( null, $conditions );

		foreach ( $articles as /* EPArticle */ $article ) {
			$this->articles[$article->getField( 'user_id' )][] = $article;
		}
	}

}
