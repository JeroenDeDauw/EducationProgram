<?php

/**
 * Shows the info for a single student.
 *
 * @since 0.1
 *
 * @file SpecialStudent.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class SpecialStudent extends SpecialEPPage {

	/**
	 * Constructor.
	 *
	 * @since 0.1
	 */
	public function __construct() {
		parent::__construct( 'Student', '', false );
	}

	/**
	 * Main method.
	 *
	 * @since 0.1
	 *
	 * @param string $subPage
	 */
	public function execute( $subPage ) {
		parent::execute( $subPage );

		$out = $this->getOutput();

		if ( trim( $subPage ) === '' ) {
			$this->getOutput()->redirect( SpecialPage::getTitleFor( 'Students' )->getLocalURL() );
		}
		else {
			$this->startCache( 3600 );

			$this->displayNavigation();

			$student = false;
			$user = User::newFromName( $subPage );

			if ( $user !== false && $user->getId() !== 0 ) {
				$student = $this->getCachedValue(
					function( $userId ) {
						return EPStudents::singleton()->selectRow( null, array( 'user_id' => $userId ) );
					},
					$user->getId()
				);
			}

			if ( $student === false ) {
				$out->addWikiMsg( 'ep-student-none', $this->subPage );
			}
			else {
				$out->setPageTitle( wfMsgExt( 'ep-student-title', 'parsemag', $student->getName() ) );
				$this->addCachedHTML( array( $this, 'getPageHTML' ), $student );
			}

			$this->saveCache();
		}
	}

	/**
	 * Builds the HTML for the page and returns it.
	 *
	 * @since 0.1
	 *
	 * @param EPStudent $student
	 *
	 * @return string
	 */
	public function getPageHTML( EPStudent $student ) {
		$courseIds = array_map(
			function( EPCourse $course ) {
				return $course->getId();
			},
			$student->getCourses( 'id' )
		);

		$html = $this->getSummary( $student );

		if ( $courseIds === array() ) {
			// TODO: high
		}
		else {
			$html .= Html::element( 'h2', array(), wfMsg( 'ep-student-courses' ) );

			$html .= EPCoursePager::getPager( $this->getContext(), array( 'id' => $courseIds ) );
			$this->getOutput()->addModules( EPCoursePager::getModules() );

			$pager = new EPArticleTable(
				$this->getContext(),
				array( 'user_id' => $this->getUser()->getId() ),
				array(
					'course_id' => $courseIds,
					'user_id' => $this->getUser()->getId(),
				)
			);

			$pager->setShowStudents( false );

			if ( $pager->getNumRows() ) {
				$html .= Html::element( 'h2', array(), wfMsg( 'ep-student-articles' ) );

				$html .=
					$pager->getFilterControl() .
						$pager->getNavigationBar() .
						$pager->getBody() .
						$pager->getNavigationBar() .
						$pager->getMultipleItemControl();
			}
		}

		return $html;
	}

	/**
	 * @see SpecialCachedPage::getCacheKey
	 * @return array
	 */
	protected function getCacheKey() {
		$values = $this->getRequest()->getValues();

		$values[] = $this->getUser()->getId();
		$values[] = $this->subPage;

		return array_merge( $values, parent::getCacheKey() );
	}

	/**
	 * Gets the summary data.
	 *
	 * @since 0.1
	 *
	 * @param EPStudent $student
	 *
	 * @return array
	 */
	protected function getSummaryData( IORMRow $student ) {
		$stats = array();

		$id = $student->getUser()->getId();
		$stats['user'] = Linker::userLink( $id, $student->getName() ) . Linker::userToolLinks( $id, $student->getName() );

		$stats['first-enroll'] = htmlspecialchars( $this->getLanguage()->timeanddate( $student->getField( 'first_enroll' ), true ) );
		$stats['last-active'] = htmlspecialchars( $this->getLanguage()->timeanddate( $student->getField( 'last_active' ), true ) );
		$stats['active-enroll'] = wfMsgHtml( $student->getField( 'active_enroll' ) ? 'ep-student-actively-enrolled' : 'ep-student-no-active-enroll' );

		return $stats;
	}

}
