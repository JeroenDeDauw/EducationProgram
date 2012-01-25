<?php

/**
 * Shows the info for a single course, with management and
 * enrollment controls depending on the user and his rights.
 *
 * @since 0.1
 *
 * @file SpecialCourse.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v3 or later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class SpecialCourse extends SpecialEPPage {

	/**
	 * Constructor.
	 *
	 * @since 0.1
	 */
	public function __construct() {
		parent::__construct( 'Course', '', false );
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
			$this->getOutput()->redirect( SpecialPage::getTitleFor( 'Courses' )->getLocalURL() );
		}
		else {
			$out->setPageTitle( wfMsgExt( 'ep-course-title', 'parsemag', $this->subPage ) );

			$course = EPCourse::selectRow( null, array( 'id' => $this->subPage ) );

			if ( $course === false ) {
				$this->displayNavigation();

				if ( $this->getUser()->isAllowed( 'ep-term' ) ) {
					$out->addWikiMsg( 'ep-course-create', $this->subPage );
					EPCourse::displayAddNewRegion( $this->getContext(), array( 'id' => $this->subPage ) );
				}
				else {
					$out->addWikiMsg( 'ep-course-none', $this->subPage );
				}
			}
			else {
				$this->displayNavigation();

				$this->displaySummary( $course );

				$out->addHTML( Html::element( 'h2', array(), wfMsg( 'ep-course-description' ) ) );

				$out->addHTML( $this->getOutput()->parse( $course->getField( 'description' ) ) );

				$studentIds = array_map(
					function( EPStudent $student ) {
						return $student->getId();
					},
					$course->getStudents( 'id' )
				);

				if ( count( $studentIds ) > 0 ) {
					$out->addHTML( Html::element( 'h2', array(), wfMsg( 'ep-course-students' ) ) );
					EPStudent::displayPager( $this->getContext(), array( 'id' => $studentIds ) );
				}
				else {
					// TODO
				}
			}
		}
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
	protected function getSummaryData( EPDBObject $course ) {
		$stats = array();

		$org = EPOrg::selectFieldsRow( 'name', array( 'id' => $course->getField( 'org_id' ) ) );

		$stats['org'] = Linker::linkKnown(
			SpecialPage::getTitleFor( 'Institution', $org ),
			htmlspecialchars( $org )
		);

		$lang = $this->getLanguage();

		$stats['year'] = htmlspecialchars( $lang->formatNum( $course->getField( 'year' ), true ) );
		$stats['start'] = htmlspecialchars( $lang->timeanddate( $course->getField( 'start' ), true ) );
		$stats['end'] = htmlspecialchars( $lang->timeanddate( $course->getField( 'end' ), true ) );

		$stats['students'] = htmlspecialchars( $lang->formatNum( $course->getField( 'students' ) ) );

		$stats['status'] = htmlspecialchars( EPCourse::getStatusMessage( $course->getStatus() ) );

		if ( $this->getUser()->isAllowed( 'ep-token' ) ) {
			$stats['token'] = Linker::linkKnown(
				SpecialPage::getTitleFor( 'Enroll', $course->getId() . '/' . $course->getField( 'token' ) ),
				htmlspecialchars( $course->getField( 'token' ) )
			);
		}

		return $stats;
	}

}
