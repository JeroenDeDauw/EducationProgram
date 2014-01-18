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
	 * Displays the navigation menu.
	 *
	 * @since 0.1
	 */
	protected function displayNavigation() {
		$headerPage = Settings::get( 'courseHeaderPage' );

		$wikiText = null;

		if ( $this->object !== null ) {
			$countryName = Orgs::singleton()->selectFieldsRow( 'country', array( 'id' => $this->object->getField( 'org_id' ) ) );

			$specificHeaderPage = Settings::get( 'courseHeaderPageCountry' );

			$languages = \CountryNames::getNames( $this->getTitle()->getPageLanguage()->getCode() );

			if ( array_key_exists( $countryName, $languages ) ) {
				$countryName = $languages[$countryName];
			}

			$specificHeaderPage = str_replace( array( '$2', '$1' ), array( $headerPage, $countryName ), $specificHeaderPage );

			$wikiText = $this->getArticleContent( $specificHeaderPage );
		}

		if ( $wikiText === null ) {
			$wikiText = $this->getArticleContent( $headerPage );
		}

		if ( $wikiText !== null ) {
			$this->getOutput()->addWikiText( $wikiText );
		}
	}

	protected function getArticleContent( $titleText ) {
		$title = \Title::newFromText( $titleText );

		if ( is_null( $title ) ) {
			return null;
		}

		$wikiPage = \WikiPage::newFromID( $title->getArticleID() );

		if ( is_null( $wikiPage ) ) {
			return null;
		}

		$content = $wikiPage->getContent();

		if ( is_null( $content ) || !( $content instanceof \TextContent ) ) {
			return null;
		}

		return $content->getWikitextForTransclusion();
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
		$html = '';

		$wikiText = $course->getField( 'description' );

		$studentIds = $course->getField( 'students' );

		if ( !empty( $studentIds ) ) {
			$wikiText .= "\n" . '==' . $this->msg( 'ep-course-summary-students' )->text() . '==';
		}

		$html .= $this->getOutput()->parse( $wikiText );

		$html .= parent::getPageHTML( $course );

		$html .= Html::element('a', array( 'name' => 'studentstable' ) );

		if ( !empty( $studentIds ) ) {
			$pager = new ArticleTable(
				$this->getContext(),
				array( 'user_id' => $studentIds ),
				$course->getId(),
				null,
				$course
			);

			$pager->mLimit = 200;

			if ( $pager->getNumRows() ) {
				$html .=
					$pager->getFilterControl() .
						$pager->getNavigationBar() .
						$pager->getBody() .
						$pager->getNavigationBar() .
						$pager->getMultipleItemControl();
			}
		}

		$user = $this->getUser();

		if ( $user->isAllowed( 'ep-addstudent' ) || RoleObject::isInRoleObjArray(
			$user->getId(),
			$course->getAllNonStudentRoleObjs() ) ) {

			$html .= $this->getAddStudentsControls($course);
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
			// Give grep a chance to find the usages:
			// ep-course-no-instructor, ep-course-no-online, ep-course-no-campus
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
			// Give grep a chance to find the usages:
			// ep-course-become-instructor, ep-course-become-online, ep-course-become-campus
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
			// Give grep a chance to find the usages:
			// ep-course-add-instructor, ep-course-add-online, ep-course-add-campus
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

	protected function getAddStudentsControls( $course ) {

		// add the required client-side module
		$this->getOutput()->addModules( 'ep.addstudents' );

		// Are we here following a page reload due to adding students?
		// Check by looking at URl params. If so, controls start out
		// expanded. Otherwise, they start out collapsed.
		$queryVals = $this->getContext()->getRequest()->getQueryValues();

		if ( isset ( $queryVals['studentsadded'] )  ||
			isset ( $queryVals['alreadyenrolled'] ) ) {

			$collapsedClassStr = '';
			$expandMsg = $this->msg( 'collapsible-collapse' )->text();

		} else {

			$collapsedClassStr = ' mw-collapsed';
			$expandMsg = $this->msg( 'collapsible-expand' )->text();
		}

		// open outer div
		$html = Html::openElement(
			'div',
			array( 'class' => 'ep-addstudents-area' )
		);

		// open div for headline and expand/collapse link
		$html .= Html::openElement(
			'div',
			array( 'class' => 'ep-addstudents-headline-area' )
		);

		// headline (looks like a wiki page "section")
		$html .= Html::openElement( 'h2' );

		$html .= Html::element(
			'span',
			array( 'class' => 'mw-headline' ),
			$this->msg( 'ep-addstudents-section' )->text()
		);

		$html .= Html::closeElement( 'h2' );

		// expand/collapse link
		$html .= ' ['
			. Html::element(
				'a',
				array(
					'class' => 'mw-customtoggle-addstudents',
					'href' => '#'
				),
				$expandMsg
			)
			.']';

		// close div for headline and expand/collapse link
		$html .= Html::closeElement( 'div' );

		// open div with collapsible contents
		$html .= Html::openElement(
			'div',
			array(
				'id' => 'mw-customcollapsible-addstudents',
				'class' => 'mw-collapsible ep-addstudents-controls' . $collapsedClassStr
			)
		);

		// general instructions
		$html .= Html::openElement(
			'div',
			array( 'id' => 'ep-addstudents-instructions' )
		);

		$html .= $this->msg( 'ep-addstudents-instructions' )->parseAsBlock();

		$html .= Html::closeElement( 'div' );

		// input for hooking up tagsinput
		$html .= Html::element(
			'input',
			array(
				'id' => 'ep-addstudents-input',
				'data-courseid' => $course->getId()
			)
		);

		// hidden empty div for error messages
		$html .= Html::element(
			'div',
			array(
				'id' => 'ep-addstudents-error',
				'style' => 'display: none;'
			)
		);

		// "Add" button
		$html .= Html::element(
			'button',
			array(
				'id' => 'ep-addstudents-btn',
				'disabled' => 'true'
			),
			$this->msg( 'ep-addstudents-btn' )->text()
		);

		// open div for instructions on enroll link
		$html .= Html::openElement(
			'div',
			array( 'id' => 'ep-addstudents-link-instructions' )
		);

		// instructions for enroll link
		$html .= Html::element(
			'p',
			array(),
			$this->msg( 'ep-addstudents-url-instructions' )->text()
		);

		// URL for enroll link (not a live link, but rather text for pasting)

		$token = $course->getField( 'token' );
		$tokenSuffix = $token === '' ? '' : '/' . $token;

		$enrollLink = SpecialPage::getTitleFor(
			'Enroll', $course->getField( 'title' ) . $tokenSuffix )
			->getFullUrl( '', false, PROTO_CANONICAL );

		$html .= Html::element(
			'p',
			array( 'id' => 'ep-addstudents-link' ),
			$enrollLink
		);

		// close div for instructions on enroll link
		$html .= Html::closeElement( 'div' );

		// close div with collapsible contents
		$html .= Html::closeElement( 'div' );

		// close outer div
		$html .= Html::closeElement( 'div' );

		return $html;
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
