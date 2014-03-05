<?php

namespace EducationProgram;

use IContextSource;
use Html;
use User;
use Linker;
use Xml;
use Title;

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
	 * Id of the course all articles should belong to.
	 *
	 * @since 0.3
	 * @var int[]
	 */
	protected $courseIds;

	/**
	 * The course object for these articles, if the table is for only one course.
	 *
	 * @since 0.4 alpha
	 * @var Course
	 */
	protected $course;

	/**
	 * Id of the user all articles should belong to
	 * or null for no such restriction.
	 *
	 * @since 0.3
	 * @var int|null
	 */
	protected $userIds;

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
	 * @param int[]|int $courseIds
	 * @param int[]|null $userIds
	 * @param Course $course The course object or these articles. Should be sent
	 *   when the table is for only one course.
	 */
	public function __construct( IContextSource $context, array $conds = array(),
			$courseIds, array $userIds = null, $course = null ) {
		$this->mDefaultDirection = true;
		$this->courseIds = (array)$courseIds;
		$this->userIds = $userIds;
		$this->course = $course;

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
	 * TODO fix this version number?
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
		$studentUserId = $student->getField( 'user_id' );
		$articles = $this->articles[$studentUserId];
		$user = $this->getUser();
		$userId = $user->getId();
		$showArticleAddition = false;

		// Show the article addition control for this student?
		// Only if the table contains articles for only one course, and...
		if ( ( $this->isForOneCourse() ) &&

			// ...the user is the student referred to by this row, or...
			( ( $userId === $student->getField( 'user_id' ) ) ||

			// we received a valid $this->course object, and the user is
			// an instructor, an online ambassador or a campus ambassador
			// for this course.
			( !is_null($this->course) && ( RoleObject::isInRoleObjArray(
				$userId, $this->course->getAllNonStudentRoleObjs() ) ) ) ) ) {

				$showArticleAddition = true;
		}

		// initial calculation for row count
		$rowCount = array_reduce(
			$articles,
			function( /* integer */ $sum, EPArticle $article ) use ( $user ) {

				// At least one row per article the student has to review;
				// for articles that have more than one reviewer, provide extra rows for them.
				$reviewersCount = count( $article->getReviewers() );
				$inc = max( 1, $reviewersCount );

				// The "Become a reviewer" control also adds an extra row, unless there are no
				// reviewers.
				if ( $article->canBecomeReviewer( $user ) && ( $reviewersCount > 0 ) ) {
					$inc++;
				}

				return $sum + $inc;
			},
			0
		);

		// one extra row for showing the article addition control (which spans the
		// last two columns)
		if ($showArticleAddition) {
			$rowCount++;
		}

		// must be at least one, even if there's nothing in the following columns
		$rowCount = max( 1, $rowCount );

		$studentUserId = $student->getField( 'user_id' );

		$html = Html::openElement( 'tr', array_merge (
				$this->getRowAttrs( $row ),
				array( 'data-user-id' => $studentUserId )
		) );

		if ( $this->showStudents ) {
			$html .= $this->getUserCell( $studentUserId, $rowCount );
		}

		$this->addNonStudentHTML( $html, $articles, $showArticleAddition, $studentUserId );

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
	protected function addNonStudentHTML( &$html, array $articles, $showArticleAddition, $studentUserId ) {
		$isFirst = true;

		/**
		 * @var EPArticle $article
		 */
		foreach ( $articles as $article ) {
			if ( !$isFirst ) {
				$html .= '</tr><tr>';
			}

			$isFirst = false;

			$reviewers = $article->getReviewers();

			$canBecomeReviewer = $article->canBecomeReviewer( $this->getUser() );

			$articleRowCount = count( $reviewers );

			if ($canBecomeReviewer) {
				$articleRowCount++;
			}

			$articleRowCount = max( 1, $articleRowCount );

			$html .= $this->getArticleCell( $article, $articleRowCount );

			$isFirstReviewer = true;

			foreach ( $reviewers as $nr => $userId ) {
				if ( !$isFirstReviewer ) {
					$html .= '</tr><tr>';
				}

				$isFirstReviewer = false;

				$html .= $this->getReviewerCell( $article, $userId );
			}

			if ( $canBecomeReviewer ) {
				if ( count( $reviewers ) !== 0 ) {
					$html .= '</tr><tr>';
				}

				$html .= $this->getReviewerAdditionControl( $article );
			}
			elseif ( count( $reviewers ) === 0 ) {
				$html .= '<td></td>';
			}
		}

		if ( $showArticleAddition ) {
			if ( !$isFirst ) {
				$html .= '</tr><tr>';
			}

			$html .= $this->getArticleAdditionControl( reset( $this->courseIds ), $studentUserId );
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
			&& $this->isForOneCourse() ) {

			$html .= Utils::getToolLinks(
				$userId,
				$user->getName(),
				$this->getContext(),
				array( Html::element(
					'a',
					array(
						'href' => '#',
						'data-user-id' => $userId,
						'data-course-id' => reset( $this->courseIds ),
						'data-user-name' => $user->getName(),
						'data-course-name' => $this->getCourseName(),
						'data-token' => $this->getUser()->getEditToken( reset( $this->courseIds ) . 'remstudent' . $userId ),
						'class' => 'ep-rem-student',
					),
					$this->msg( 'ep-articles-remstudent' )->text()
				) )
			);
		}
		else {
			$html .= Utils::getToolLinks( $userId, $user->getName(), $this->getContext() );
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
			$this->courseName = Courses::singleton()->selectFieldsRow(
				'name',
				array(
					'id' => $this->courseIds
				)
			);
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
		$title = Title::newFromID( $article->getPageId() );
		$hasValidTitle = $title !== null;
		$titleText = $hasValidTitle ? $title->getFullText() : $article->getPageTitle();

		$html = htmlspecialchars( $titleText );

		if ( $hasValidTitle ) {
			$html = Linker::link(
				$title,
				$html
			);
		}
		else {
			wfDebugLog(
				'bug46577',
				json_encode( array(
					'title' => $article->getPageTitle(),
					'pageid' => $article->getPageId(),
					'id' => $article->getId(),
				) )
			);
		}

		$attr = array(
			'href' => '#',
			'data-article-id' => $article->getId(),
			'data-article-name' => $titleText,
			'data-token' => $this->getUser()->getEditToken( 'remarticle' . $article->getId() ),
			'class' => 'ep-rem-article',
		);

		$user = $this->getUser();

		if ( $user->getId() !== $article->getUserId() ) {
			$attr['data-student-name'] = $article->getUser()->getName();
		}

		if ( $this->isForOneCourse() ) {
			$attr['data-course-name'] = $this->getCourseName();

			$title = Courses::singleton()->getTitleFor( $this->getCourseName() );
			$attr['data-remove-target'] = $title->getLocalURL( array(
				'returnto' => $this->getTitle()->getFullText(),
			) );

			if ( $article->userCanRemove( $user ) ) {
				$html .= ' (' . Html::element(
					'a',
					$attr,
					$this->msg( 'ep-articles-remarticle' )->text()
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
	 * Returns if the pager is showing articles for only a single course.
	 *
	 * @since 0.3
	 *
	 * @return bool
	 */
	protected function isForOneCourse() {
		return count( $this->courseIds ) == 1;
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

		$title = Title::newFromID( $article->getPageId() );
		$titleText = $title === null ? '' : $title->getFullText();

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
						'data-article-name' => $titleText,
						'data-student-name' => $article->getUser()->getName(),
						'data-reviewer-name' => $user->getName(),
						'data-reviewer-id' => $user->getId(),
						'data-token' => $this->getUser()->getEditToken( $userId . 'remreviewer' . $article->getId() ),
						'class' => 'ep-rem-reviewer',
					),
					$this->msg( 'ep-articles-remreviewer' )->text()
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
					'data-article-name' => $titleText,
					'data-student-name' => $article->getUser()->getName(),
					'data-token' => $this->getUser()->getEditToken( $userId . 'remreviewer' . $article->getId() ),
				),
				$this->msg( 'ep-articles-remreviewer-self' )->text()
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
	protected function getArticleAdditionControl( $courseId, $studentUserId ) {
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
			$this->msg( 'ep-articles-addarticle-text' )->text(),
			'addarticlename',
			'addarticlename',
			false,
			false,
			array ( 'class' => 'ep-addarticlename' )
		);

		$html .= '&#160;' . Html::input(
			'addarticle',
			$this->msg( 'ep-articles-addarticle-button' )->text(),
			'submit',
			array(
				'class' => 'ep-addarticle',
			)
		);

		$html .= Html::hidden( 'course-id', $courseId );
		$html .= Html::hidden( 'token', $this->getUser()->getEditToken( 'addarticle' . $courseId . $studentUserId) );
		$html .= Html::hidden( 'student-user-id', $studentUserId );

		$html .= '</form>';

		return '<td colspan="2" class="ep-addarticle-cell">' . $html . '</td>';
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
	protected function getReviewerAdditionControl( EPArticle $article ) {
		$title = Title::newFromID( $article->getPageId() );
		$titleText = $title === null ? '' : $title->getFullText();

		$html = Html::element(
			'button',
			array(
				'class' => 'ep-become-reviewer',
				'disabled' => 'disabled',
				'data-article-id' => $article->getId(),
				'data-article-name' => $titleText,
				'data-user-name' => $article->getUser()->getName(),
				'data-token' => $this->getUser()->getEditToken( 'addreviewer' . $article->getId() ),
			),
			$this->msg( 'ep-articles-becomereviewer' )->text()
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

		foreach ( $this->mResult as $student ) {
			$userIds[] = $student->$field;
			$this->articles[$student->$field] = array();
		}

		$articles = Extension::globalInstance()->newArticleStore()->getArticlesByCourseAndUsers(
			$this->courseIds,
			$userIds
		);

		foreach ( $articles as $article ) {
			$this->articles[$article->getUserId()][] = $article;
		}
	}

}
