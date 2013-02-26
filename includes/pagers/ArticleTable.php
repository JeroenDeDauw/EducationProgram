<?php

namespace EducationProgram;
use IContextSource, Html, User, Linker, Xml;

/**
 * Pager that lists articles per student and for each article the associated reviewers, if any.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ArticleTable extends EPPager {

	/**
	 * The doBatchLookups method gets all articles relevant to the users that will be displayed
	 * and stores them in this field.
	 * int userId => array( Article $article0, ... )
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
	 * @var string|bool false
	 */
	protected $courseName = false;

	/**
	 * Show the students column or not.
	 *
	 * @since 0.1
	 * @var boolean
	 */
	protected $showStudents = true;

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

		parent::__construct( $context, $conds, Students::singleton() );
	}

	/**
	 * Set if the student column should be shown or not.
	 *
	 * @since 0.1
	 *
	 * @param boolean $showStudents
	 */
	public function setShowStudents( $showStudents ) {
		$this->showStudents = $showStudents;
	}

	/**
	 * Returns the resource loader modules used by the pager.
	 *
	 * @since 0.4
	 *
	 * @return array
	 */
	public static function getModules() {
		$modules = parent::getModules();
		$modules[] = 'ep.articletable';
		return $modules;
	}

	public function getBody() {
		return parent::getBody();
	}

	/**
	 * @see Pager::getFields()
	 */
	public function getFields() {
		$fields = array( 'id' );

		if ( $this->showStudents ) {
			$fields[] = 'user_id';
		}

		return $fields;
	}

	/**
	 * @see TablePager::getRowClass()
	 */
	function getRowClass( $row ) {
		return 'ep-articletable-row';
	}

	/**
	 * @see TablePager::getTableClass()
	 */
	public function getTableClass() {
		return 'TablePager ep-articletable';
	}

	/**
	 * @see TablePager::formatRow()
	 */
	function formatRow( $row ) {
		$this->mCurrentRow = $row;
		$this->currentObject = $this->table->newRowFromDBResult( $row );

		$student = $this->currentObject;
		$articles = $this->articles[$student->getField( 'user_id' )];
		$user = $this->getUser();

		$rowCount = array_reduce( $articles, function( /* integer */ $sum, EPArticle $article ) use ( $user ) {
			if ( $article->canBecomeReviewer( $user ) ) {
				$sum++;
			}
			return $sum + count( $article->getField( 'reviewers' ) );
		}, 0 );

		$html = Html::openElement( 'tr', $this->getRowAttrs( $row ) );

		$showArticleAdittion =
			$user->getId() === $student->getField( 'user_id' )
			&& array_key_exists( 'course_id', $this->articleConds )
			&& is_integer( $this->articleConds['course_id'] );

		if ( $this->showStudents ) {
			$html .= $this->getUserCell( $student->getField( 'user_id' ), max( 1, $rowCount ) );
		}

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
	 * @param boolean $showArticleAddition
	 */
	protected function addNonStudentHTML( &$html, array $articles, $showArticleAddition ) {
		$isFirst = true;

		/**
		 * @var EPArticle $article
		 */
		foreach ( $articles as $article ) {
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
			elseif ( count( $reviewers ) === 0 ) {
				$html .= '<td></td>';
			}
		}

		if ( $showArticleAddition ) {
			if ( !$isFirst ) {
				$html .= '</tr><tr>';
			}

			$html .= $this->getArticleAdditionControl( $this->articleConds['course_id'] );
		}
		elseif ( $isFirst ) {
			$html .= '<td></td><td></td>';
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
		$realName = !Settings::get( 'useStudentRealNames' ) || $user->getRealName() === '' ? false : $user->getRealName();

		$html = Linker::userLink( $userId, $user->getName(), $realName );

		if ( $this->getUser()->isAllowed( 'ep-remstudent' )
			&& array_key_exists( 'course_id', $this->articleConds )
			&& is_integer( $this->articleConds['course_id'] ) ) {

			$html .= Utils::getToolLinks(
				$userId,
				$user->getName(),
				$this->getContext(),
				array( Html::element(
					'a',
					array(
						'href' => '#',
						'data-user-id' => $userId,
						'data-course-id' => $this->articleConds['course_id'],
						'data-user-name' => $user->getName(),
						'data-course-name' => $this->getCourseName(),
						'data-token' => $this->getUser()->getEditToken( $this->articleConds['course_id'] . 'remstudent' . $userId ),
						'class' => 'ep-rem-student',
					),
					$this->msg( 'ep-artciles-remstudent' )->text()
				) )
			);
		}
		else {
			$html .= Linker::userToolLinks( $userId, $user->getName() );
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
			$this->courseName = Courses::singleton()->selectFieldsRow( 'name', array( 'id' => $this->articleConds['course_id'] ) );
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

		$user = $this->getUser();

		if ( $user->getId() !== $article->getField( 'user_id' ) ) {
			$attr['data-student-name'] = $user->getName();
		}

		if ( array_key_exists( 'course_id', $this->articleConds ) && is_integer( $this->articleConds['course_id'] ) ) {
			$attr['data-course-name'] = $this->getCourseName();

			$title = Courses::singleton()->getTitleFor( $this->getCourseName() );
			$attr['data-remove-target'] = $title->getLocalURL( array(
				'returnto' => $this->getTitle()->getFullText(),
			) );

			if ( $article->userCanRemove( $user ) ) {
				$html .= ' (' . Html::element(
					'a',
					$attr,
					$this->msg( 'ep-artciles-remarticle' )->text()
				) . ')';
			}
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
		$name = !Settings::get( 'useStudentRealNames' ) || $user->getRealName() === '' ? $user->getName() : $user->getRealName();

		$html = Linker::userLink( $userId, $name );

		if ( $this->getUser()->isAllowed( 'ep-remreviewer' ) ) {
			$html .= Utils::getToolLinks(
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
					$this->msg( 'ep-artciles-remreviewer' )->text()
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
					'data-article-name' => $article->getTitle()->getFullText(),
					'data-student-name' => $article->getUser()->getName(),
					'data-token' => $this->getUser()->getEditToken( $userId . 'remreviewer' . $article->getId() ),
				),
				$this->msg( 'ep-artciles-remreviewer-self' )->text()
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
	 * Returns the HTML for the article addition control.
	 *
	 * @since 0.1
	 *
	 * @param integer $courseId
	 *
	 * @return string
	 */
	protected function getArticleAdditionControl( $courseId ) {
		$courseTitle = Courses::singleton()->selectFieldsRow( 'title', array( 'id' => $courseId ) );
		$query = array( 'action' => 'epaddarticle' );

		if ( $this->getTitle()->getNamespace() !== EP_NS && !Utils::isCourse( $this->getTitle() ) ) {
			$query['returnto'] = $this->getTitle()->getFullText();
		}

		$html = Html::openElement(
			'form',
			array(
				'method' => 'post',
				'action' => Courses::singleton()->getTitleFor( $courseTitle )->getLocalURL( $query ),
			)
		);

		$html .=  Xml::inputLabel(
			$this->msg( 'ep-artciles-addarticle-text' )->text(),
			'addarticlename',
			'addarticlename'
		);

		$html .= '&#160;' . Html::input(
			'addarticle',
			$this->msg( 'ep-artciles-addarticle-button' )->text(),
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
	 * Returns the HTML for the reviewer addition control.
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
			$this->msg( 'ep-artciles-becomereviewer' )->text()
		);

		return '<td>' . $html . '</td>';
	}

	/**
	 * @see Pager::getFormattedValue()
	 */
	protected function getFormattedValue( $name, $value ) { /* ... */ }

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

		unset( $fields['id'] );

		if ( $this->showStudents ) {
			$fields['user_id'] = 'student';
		}

		$fields['_articles'] = 'articles';
		$fields['_reviewers'] = 'reviewers';

		return $fields;
	}

	/**
	 * @see IndexPager::doBatchLookups()
	 */
	protected function doBatchLookups() {
		$userIds = array();
		$field = $this->table->getPrefixedField( 'user_id' );

		foreach( $this->mResult as $student ) {
			$userIds[] = $student->$field;
			$this->articles[$student->$field] = array();
		}

		$conditions = array_merge( array( 'user_id' => $userIds ), $this->articleConds );

		$articles = Articles::singleton()->select( null, $conditions );

		/**
		 * @var EPArticle $article
		 */
		foreach ( $articles as $article ) {
			$this->articles[$article->getField( 'user_id' )][] = $article;
		}
	}
}
