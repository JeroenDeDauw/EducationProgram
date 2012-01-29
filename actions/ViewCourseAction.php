<?php

class ViewCourseAction extends EPViewAction {
	
	public function getName() {
		return 'viewcourse';
	}

	protected function getDescription() {
		return wfMsg( 'viewcourse' );
	}

	/**
	 * 
	 *
	 * @return String HTML
	 */
	public function onView() {
		$out = $this->getOutput();
		
		$name = $this->getTitle()->getText();
		
		$course = EPCourse::get( $name );

		if ( $course === false ) {
			$this->displayNavigation();

			if ( $this->getUser()->isAllowed( 'ep-course' ) ) {
				$out->addWikiMsg( 'ep-course-create', $name );
				
				$bracketPos = strpos( $name, '(' );
				
				if ( $bracketPos !== false ) {
					if ( $bracketPos > 0 && in_array( $name{$bracketPos - 1}, array( ' ', '_' ) ) ) {
						$bracketPos -= 1;
					}
					
					$newName = substr( $name, 0, $bracketPos );
				}
				
				EPCourse::displayAddNewRegion(
					$this->getContext(),
					array(
						'name' => $this->getRequest()->getText(
							'newname',
							$newName
						),
						'term' => $this->getRequest()->getText( 'newterm', '' ),
					)
				);
			}
			else {
				$out->addWikiMsg( 'ep-course-none', $name );
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

		return '';
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

		$stats['term'] = htmlspecialchars( $course->getField( 'term' ) );
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