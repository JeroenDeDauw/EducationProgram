<?php

/**
 * Shows the info for a single student.
 *
 * @since 0.1
 *
 * @file SpecialStudent.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v3 or later
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

				$this->addCachedHTML( array( $this, 'getSummary' ), $student );

				$this->addCachedHTML( function( EPStudent $student ) {
					$courseIds = array_map(
						function( EPCourse $course ) {
							return $course->getId();
						},
						$student->getCourses( 'id' )
					);

					$html = '';

					if ( empty( $courseIds ) ) {
						// TODO: high
					}
					else {
						$html .= Html::element( 'h2', array(), wfMsg( 'ep-student-courses' ) );
						$html .= ''; // TODO
							// EPCourse::displayPager( $this->getContext(), array( 'id' => $courseIds ) );
					}

					return $html;
				}, $student );
			}

			$this->saveCache();
		}
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
	protected function getSummaryData( DBDataObject $student ) {
		$stats = array();

		$id = $student->getUser()->getId();
		$stats['user'] = Linker::userLink( $id, $student->getName() ) . Linker::userToolLinks( $id, $student->getName() );

		$stats['first-enroll'] = htmlspecialchars( $this->getLanguage()->timeanddate( $student->getField( 'first_enroll' ), true ) );
		$stats['last-active'] = htmlspecialchars( $this->getLanguage()->timeanddate( $student->getField( 'last_active' ), true ) );
		$stats['active-enroll'] = wfMsgHtml( $student->getField( 'active_enroll' ) ? 'ep-student-actively-enrolled' : 'ep-student-no-active-enroll' );

		return $stats;
	}

}
