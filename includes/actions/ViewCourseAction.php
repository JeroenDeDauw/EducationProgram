<?php

namespace EducationProgram;
use Page, IContextSource, Html, IORMRow, Linker, SpecialPage;

/**
 * Action for viewing a course.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 * @ingroup Action
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ViewCourseAction extends ViewAction {

	/**
	 * Constructor.
	 *
	 * @since 0.1
	 *
	 * @param Page $page
	 * @param IContextSource $context
	 */
	public function __construct( Page $page, IContextSource $context = null ) {
		parent::__construct( $page, $context, Courses::singleton() );
	}

	/**
	 * @see Action::getName()
	 */
	public function getName() {
		return 'viewcourse';
	}

	/**
	 * @see FormlessAction::onView()
	 */
	public function onView() {
		// Only cache for anon users. Else we need to cache per user,
		// since the page has an ArticleTable, which has per user stuff.
		$this->cacheEnabled = $this->cacheEnabled && $this->getUser()->isAnon();

		$this->getOutput()->addModules( ArticleTable::getModules() );

		return parent::onView();
	}

	/**
	 * @see ViewAction::getPageHTML()
	 */
	public function getPageHTML( IORMRow $course ) {
		$html = $this->getOutput()->parse( $course->getField( 'description' ) );

		$html .= parent::getPageHTML( $course );

		$studentIds = $course->getField( 'students' );

		if ( !empty( $studentIds ) ) {
			$html .= Html::element( 'h2', array(), $this->msg( 'ep-course-students' )->text() );

			$pager = new ArticleTable(
				$this->getContext(),
				array( 'user_id' => $studentIds ),
				array( 'course_id' => $course->getId() )
			);

			if ( $pager->getNumRows() ) {
				$html .=
					$pager->getFilterControl() .
					$pager->getNavigationBar() .
					$pager->getBody() .
					$pager->getNavigationBar() .
					$pager->getMultipleItemControl();
			}
		}
		else {
			// TODO
		}

		return $html;
	}

	/**
	 * Gets the summary data.
	 *
	 * @since 0.1
	 *
	 * @param Course|IORMRow $course
	 *
	 * @return array
	 */
	protected function getSummaryData( IORMRow $course ) {
		$stats = array();

		$orgName = Orgs::singleton()->selectFieldsRow( 'name', array( 'id' => $course->getField( 'org_id' ) ) );
		$stats['org'] = Orgs::singleton()->getLinkFor( $orgName );

		$lang = $this->getLanguage();

		$stats['term'] = htmlspecialchars( $course->getField( 'term' ) );
		$stats['start'] = htmlspecialchars( $lang->date( $course->getField( 'start' ), true ) );
		$stats['end'] = htmlspecialchars( $lang->date( $course->getField( 'end' ), true ) );

		$stats['students'] = htmlspecialchars( $lang->formatNum( $course->getField( 'student_count' ) ) );

		$stats['status'] = htmlspecialchars( Course::getStatusMessage( $course->getStatus() ) );

		if ( $this->getUser()->isAllowed( 'ep-token' ) ) {
			$stats['token'] = Linker::linkKnown(
				SpecialPage::getTitleFor( 'Enroll', $course->getField( 'title' ) . '/' . $course->getField( 'token' ) ),
				htmlspecialchars( $course->getField( 'token' ) )
			);
		}

		$stats['instructors'] = $this->getRoleList( $course, 'instructor' ) . $this->getRoleControls( $course, 'instructor' );
		$stats['online'] = $this->getRoleList( $course, 'online' ) . $this->getRoleControls( $course, 'online' );
		$stats['campus'] = $this->getRoleList( $course, 'campus' ) . $this->getRoleControls( $course, 'campus' );

		return $stats;
	}

	/**
	 * Returns a list with the users that the specified role for the provided course
	 * or a message indicating there are none.
	 *
	 * @since 0.1
	 *
	 * @param Course $course
	 * @param string $roleName
	 *
	 * @return string
	 */
	protected function getRoleList( Course $course, $roleName ) {
		$users = $course->getUserWithRole( $roleName );

		if ( empty( $users ) ) {
			$html = $this->msg( 'ep-course-no-' . $roleName )->escaped();
		}
		else {
			$instList = array();

			foreach ( $users as /* IRole */ $user ) {
				$instList[] = $user->getUserLink() . $user->getToolLinks( $this->getContext(), $course );
			}

			if ( false ) { // count( $instructors ) == 1
				$html = $instList[0];
			}
			else {
				$html = '<ul><li>' . implode( '</li><li>', $instList ) . '</li></ul>';
			}
		}

		return Html::rawElement(
			'div',
			array( 'id' => 'ep-course-' . $roleName ),
			$html
		);
	}

	/**
	 * Returns role a controls for the course if the
	 * current user has the right permissions.
	 *
	 * @since 0.1
	 *
	 * @param Course $course
	 * @param string $roleName
	 *
	 * @return string
	 */
	protected function getRoleControls( Course $course, $roleName ) {
		$user = $this->getUser();
		$links = array();

		$field = $roleName === 'instructor' ? 'instructors' : $roleName . '_ambs';

		if ( ( $user->isAllowed( 'ep-' . $roleName ) || $user->isAllowed( 'ep-be' . $roleName ) )
			&& !in_array( $user->getId(), $course->getField( $field ) )
		) {
			$links[] = Html::element(
				'a',
				array(
					'href' => '#',
					'class' => 'ep-add-role',
					'data-role' => $roleName,
					'data-courseid' => $course->getId(),
					'data-coursename' => $course->getField( 'name' ),
					'data-mode' => 'self',
				),
				$this->msg( 'ep-course-become-' . $roleName )->text()
			);
		}

		if ( $user->isAllowed( 'ep-' . $roleName ) ) {
			$links[] = Html::element(
				'a',
				array(
					'href' => '#',
					'class' => 'ep-add-role',
					'data-role' => $roleName,
					'data-courseid' => $course->getId(),
					'data-coursename' => $course->getField( 'name' ),
				),
				$this->msg( 'ep-course-add-' . $roleName )->text()
			);
		}

		if ( empty( $links ) ) {
			return '';
		}
		else {
			$this->getOutput()->addModules( 'ep.enlist' );
			return '<br />' . $this->getLanguage()->pipeList( $links );
		}
	}

	/**
	 * @see CachedAction::getCacheKey
	 * @return array
	 */
	protected function getCacheKey() {
		$user = $this->getUser();

		return array_merge( array(
			$user->isAllowed( 'ep-course' ),
			$user->isAllowed( 'ep-bulkdelcourses' ) && $user->getOption( 'ep_bulkdelcourses' ),
		), parent::getCacheKey() );
	}

}
