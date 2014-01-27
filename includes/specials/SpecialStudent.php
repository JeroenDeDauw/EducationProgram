<?php

namespace EducationProgram;
use User, IORMRow, Html, Linker;

/**
 * Shows the info for a single student.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class SpecialStudent extends VerySpecialPage {
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
			$this->getOutput()->redirect( \SpecialPage::getTitleFor( 'Students' )->getLocalURL() );
		}
		else {
			$this->startCache( 3600 );

			$this->displayNavigation();

			$student = false;
			$user = User::newFromName( $subPage );

			if ( $user !== false && $user->getId() !== 0 ) {
				$student = $this->getCachedValue(
					function( $userId ) {

						$cachableStudent = Students::singleton()->
							selectRow( null, array( 'user_id' => $userId ) );

						// This page displays info about a student's enrollment
						// status. The following will query the database to get
						// that info, but we won't bother saving it to the
						// corresponding DB field in the Students table.
						$cachableStudent->loadSummaryFields( 'active_enroll' );

						return $cachableStudent;
					},
					$user->getId()
				);
			}

			if ( $student === false ) {
				$out->addWikiMsg( 'ep-student-none', $this->subPage );
			}
			else {
				$out->setPageTitle( $this->msg( 'ep-student-title', $student->getName() ) );
				$this->addCachedHTML( array( $this, 'getPageHTML' ), $student );
			}
		}
	}

	/**
	 * Builds the HTML for the page and returns it.
	 *
	 * @since 0.1
	 *
	 * @param Student $student
	 *
	 * @return string
	 */
	public function getPageHTML( Student $student ) {
		$courseIds = array_map(
			function( Course $course ) {
				return $course->getId();
			},
			$student->getCourses( 'id' )
		);

		$html = $this->getSummary( $student );

		if ( $courseIds === array() ) {
			// TODO: high
		}
		else {
			$html .= Html::element( 'h2', array(), $this->msg( 'ep-student-courses' )->text() );

			$html .= CoursePager::getPager( $this->getContext(), array( 'id' => $courseIds ) );
			$this->getOutput()->addModules( CoursePager::getModules() );

			$pager = new ArticleTable(
				$this->getContext(),
				array( 'user_id' => $this->getUser()->getId() ),
				$courseIds,
				array( $this->getUser()->getId() )
			);

			$this->getOutput()->addModules( ArticleTable::getModules() );

			$pager->setShowStudents( false );

			if ( $pager->getNumRows() ) {
				$html .= Html::element( 'h2', array(), $this->msg( 'ep-student-articles' )->text() );

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
	 * @param Student $student
	 *
	 * @return array
	 */
	protected function getSummaryData( IORMRow $student ) {
		$stats = array();

		$id = $student->getUser()->getId();
		$stats['user'] = Linker::userLink( $id, $student->getName() ) . Linker::userToolLinks( $id, $student->getName() );

		$stats['first-enroll'] = htmlspecialchars( $this->getLanguage()->timeanddate( $student->getField( 'first_enroll' ), true ) );
		$stats['last-active'] = htmlspecialchars( $this->getLanguage()->timeanddate( $student->getField( 'last_active' ), true ) );
		$stats['active-enroll'] = $this->msg( $student->getField( 'active_enroll' ) ? 'ep-student-actively-enrolled' : 'ep-student-no-active-enroll' )->escaped();

		return $stats;
	}
}
