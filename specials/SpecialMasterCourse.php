<?php

/**
 * Shows the info for a single master course, with management and
 * enrollment controls depending on the user and his rights.
 *
 * @since 0.1
 *
 * @file SpecialMasterCourse.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v3 or later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class SpecialMasterCourse extends SpecialEPPage {

	/**
	 * Constructor.
	 *
	 * @since 0.1
	 */
	public function __construct() {
		parent::__construct( 'MasterCourse' );
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
			$this->getOutput()->redirect( SpecialPage::getTitleFor( 'MasterCourses' )->getLocalURL() );
		}
		else {
			$out->setPageTitle( wfMsgExt( 'ep-mc-title', 'parsemag', $this->subPage ) );

			$masterCourse = EPMC::selectRow( null, array( 'name' => $this->subPage ) );

			if ( $masterCourse === false ) {
				$this->displayNavigation();

				if ( $this->getUser()->isAllowed( 'ep-mc' ) ) {
					$out->addWikiMsg( 'ep-mc-create', $this->subPage );
					EPMC::displayAddNewRegion( $this->getContext(), array( 'name' => $this->subPage ) );
				}
				else {
					$out->addWikiMsg( 'ep-mc-none', $this->subPage );
				}
			}
			else {
				$this->displayNavigation();

				$this->displaySummary( $masterCourse );

				$out->addHTML( Html::element( 'h2', array(), wfMsg( 'ep-mc-description' ) ) );

				$out->addHTML( $this->getOutput()->parse( $masterCourse->getField( 'description' ) ) );

				$out->addHTML( Html::element( 'h2', array(), wfMsg( 'ep-mc-courses' ) ) );

				EPMC::displayPager( $this->getContext(), array( 'id' => $masterCourse->getId() ) );

				if ( $this->getUser()->isAllowed( 'ep-mc' ) ) {
					$out->addHTML( Html::element( 'h2', array(), wfMsg( 'ep-mc-add-course' ) ) );

					EPMC::displayAddNewControl( $this->getContext(), array( 'mc' => $masterCourse->getId() ) );
				}
			}
		}
	}

	/**
	 * Gets the summary data.
	 *
	 * @since 0.1
	 *
	 * @param EPMC $masterCourse
	 *
	 * @return array
	 */
	protected function getSummaryData( EPDBObject $masterCourse ) {
		$stats = array();

		$stats['name'] = htmlspecialchars( $masterCourse->getField( 'name' ) );

		$org = EPOrg::selectFieldsRow( 'name', array( 'id' => $masterCourse->getField( 'org_id' ) ) );

		$stats['org'] = Linker::linkKnown(
			SpecialPage::getTitleFor( 'Institution', $org ),
			htmlspecialchars( $org )
		);

		$stats['status'] = wfMsgHtml( $masterCourse->getField( 'active' ) ? 'ep-mc-active' : 'ep-mc-inactive' );

		$lang = $this->getLanguage();

		$stats['students'] = htmlspecialchars( $lang->formatNum( $masterCourse->getField( 'students' ) ) );

		$courseCount = EPCourse::count( array( 'mc_id' => $masterCourse->getId() ) );
		$stats['courses'] = htmlspecialchars( $lang->formatNum( $courseCount ) );

		if ( $courseCount > 0 ) {
			$stats['courses'] = Linker::linkKnown(
				SpecialPage::getTitleFor( 'Courses' ),
				 $stats['courses'],
				array(),
				array( 'mc_id' => $masterCourse->getId() )
			);
		}
		
		$stats['instructors'] = $this->getInstructorsList( $masterCourse ) . $this->getInstructorControls( $masterCourse );

		return $stats;
	}
	
	/**
	 * Returns a list with the instructors for the provided course
	 * or a message indicating there are none.
	 * 
	 * @since 0.1
	 *  
	 * @param EPMC $masterCourse
	 * 
	 * @return string
	 */
	protected function getInstructorsList( EPMC $masterCourse ) {
		$instructors = $masterCourse->getInstructors();
		
		if ( count( $instructors ) > 0 ) {
			$instList = array();
			
			foreach ( $instructors as /* EPInstructor */ $instructor ) {
				$instList[] = $instructor->getUserLink() . $instructor->getToolLinks( $this->getContext(), $masterCourse );
			}
			
			if ( false ) { // count( $instructors ) == 1
				$html = $instList[0];
			}
			else {
				$html = '<ul><li>' . implode( '</li><li>', $instList ) . '</li></ul>';
			}
		}
		else {
			$html = wfMsgHtml( 'ep-mc-no-instructors' );
		}

		return Html::rawElement(
			'div',
			array( 'id' => 'ep-mc-instructors' ),
			$html
		);
	}

	/**
	 * Returns instructor addition controls for the course if the
	 * current user has the right permissions.
	 *
	 * @since 0.1
	 *
	 * @param EPMC $masterCourse
	 *
	 * @return string
	 */
	protected function getInstructorControls( EPMC $masterCourse ) {
		$user = $this->getUser();
		$links = array();
		
		if ( ( $user->isAllowed( 'ep-instructor' ) || $user->isAllowed( 'ep-beinstructor' ) )
			&& !in_array( $user->getId(), $masterCourse->getField( 'instructors' ) )
			) {
			$links[] = Html::element(
				'a',
				array(
					'href' => '#',
					'class' => 'ep-add-instructor',
					'data-courseid' => $masterCourse->getId(),
					'data-coursename' => $masterCourse->getField( 'name' ),
					'data-mode' => 'self',
				),
				wfMsg( 'ep-mc-become-instructor' )
			);
		}
		
		if ( $user->isAllowed( 'ep-instructor' ) ) {
			$links[] = Html::element(
				'a',
				array(
					'href' => '#',
					'class' => 'ep-add-instructor',
					'data-courseid' => $masterCourse->getId(),
					'data-coursename' => $masterCourse->getField( 'name' ),
				),
				wfMsg( 'ep-mc-add-instructor' )
			);
		}
		
		if ( count( $links ) > 0 ) {
			$this->getOutput()->addModules( 'ep.instructor' );
			return '<br />' . $this->getLanguage()->pipeList( $links );
		}
		else {
			return '';
		}
	}

}
