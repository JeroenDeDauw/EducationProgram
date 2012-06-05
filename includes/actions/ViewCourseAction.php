<?php

/**
 * Action for viewing a course.
 *
 * @since 0.1
 *
 * @file ViewCourseAction.php
 * @ingroup EducationProgram
 * @ingroup Action
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ViewCourseAction extends EPViewAction {

	/**
	 * Constructor.
	 *
	 * @since 0.1
	 *
	 * @param Page $page
	 * @param IContextSource $context
	 */
	protected function __construct( Page $page, IContextSource $context = null ) {
		parent::__construct( $page, $context, EPCourses::singleton() );
	}

	/**
	 * (non-PHPdoc)
	 * @see Action::getName()
	 */
	public function getName() {
		return 'viewcourse';
	}

	/**
	 * (non-PHPdoc)
	 * @see FormlessAction::onView()
	 */
	public function onView() {
		// Only cache for anon users. Else we need to cache per user,
		// since the page has an EPArticleTable, which has per user stuff.
		$this->cacheEnabled = $this->getUser()->isAnon();

		return parent::onView();
	}

	/**
	 * (non-PHPdoc)
	 * @see EPViewAction::getPageHTML()
	 */
	public function getPageHTML( IORMRow $course ) {
		$html = parent::getPageHTML( $course );

		$html .= Html::element( 'h2', array(), wfMsg( 'ep-course-description' ) );

		$html .= $this->getOutput()->parse( $course->getField( 'description' ) );

		$studentIds = $course->getField( 'students' );

		if ( !empty( $studentIds ) ) {
			$html .= Html::element( 'h2', array(), wfMsg( 'ep-course-students' ) );

			$pager = new EPArticleTable(
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
	 * @param EPCourse $course
	 *
	 * @return array
	 */
	protected function getSummaryData( IORMRow $course ) {
		$stats = array();

		$orgName = EPOrgs::singleton()->selectFieldsRow( 'name', array( 'id' => $course->getField( 'org_id' ) ) );
		$stats['org'] = EPOrgs::singleton()->getLinkFor( $orgName );

		$lang = $this->getLanguage();

		$stats['term'] = htmlspecialchars( $course->getField( 'term' ) );
		$stats['start'] = htmlspecialchars( $lang->date( $course->getField( 'start' ), true ) );
		$stats['end'] = htmlspecialchars( $lang->date( $course->getField( 'end' ), true ) );

		$stats['students'] = htmlspecialchars( $lang->formatNum( $course->getField( 'student_count' ) ) );

		$stats['status'] = htmlspecialchars( EPCourse::getStatusMessage( $course->getStatus() ) );

		if ( $this->getUser()->isAllowed( 'ep-token' ) ) {
			$stats['token'] = Linker::linkKnown(
				SpecialPage::getTitleFor( 'Enroll', $course->getField( 'name' ) . '/' . $course->getField( 'token' ) ),
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
	 * @param EPCourse $course
	 * @param string $roleName
	 *
	 * @return string
	 */
	protected function getRoleList( EPCourse $course, $roleName ) {
		$users = $course->getUserWithRole( $roleName );

		if ( empty( $users ) ) {
			$html = wfMsgHtml( 'ep-course-no-' . $roleName );
		}
		else {
			$instList = array();

			foreach ( $users as /* EPIRole */ $user ) {
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
	 * @param EPCourse $course
	 * @param string $roleName
	 *
	 * @return string
	 */
	protected function getRoleControls( EPCourse $course, $roleName ) {
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
				wfMsg( 'ep-course-become-' . $roleName )
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
				wfMsg( 'ep-course-add-' . $roleName )
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
