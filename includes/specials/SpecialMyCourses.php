<?php

/**
 * Page listing the recent actibvity of the users classmates.
 * It works both as a timeline and a dashboard.
 *
 * @since 0.1
 *
 * @file SpecialMyCourses.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class SpecialMyCourses extends SpecialEPPage {

	/**
	 * Constructor.
	 *
	 * @since 0.1
	 */
	public function __construct() {
		parent::__construct( 'MyCourses' );
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

		$this->displayNavigation();

		$out = $this->getOutput();

		if ( $this->getUser()->isLoggedIn() ) {
			if ( $this->getRequest()->getCheck( 'enrolled' ) ) {
				EPStudents::singleton()->setReadDb( DB_MASTER );

				$course = EPCourses::singleton()->selectRow( null, array( 'id' => $this->getRequest()->getInt( 'enrolled' ) ) );

				if ( $course !== false && in_array( $this->getUser()->getId(), $course->getField( 'students' ) ) ) {
					$this->showSuccess( wfMessage(
						'ep-mycourses-enrolled',
						array(
							Message::rawParam( $course->getLink() ),
							Message::rawParam( $course->getOrg()->getLink() )
						)
					) );
				}
			}

			$student = EPStudent::newFromUser( $this->getUser() );
			$courses = $student->getCourses( null, EPCourses::getStatusConds( 'current' ) );

			$dbr = wfGetDB( DB_SLAVE );
			$eventTable = EPEvents::singleton();

			foreach ( $courses as /* EPCourse */ $course ) {
				$conds = array(
					'course_id' => $course->getId(),
					'time > ' . $dbr->addQuotes( wfTimestamp( TS_MW, time() - EPSettings::get( 'timelineDurationLimit' ) ) ),
				);

				$options = array(
					'LIMIT' => EPSettings::get( 'timelineCountLimit' ),
					'ORDER BY' => $eventTable->getPrefixedField( 'time' ) . ' DESC'
				);

				$out->addElement( 'h2', array(), $course->getField( 'name' ) );

				$events = iterator_to_array( $eventTable->select( null, $conds, $options ) );

				if ( empty( $events ) ) {
					$out->addWikiMsg( 'ep-dashboard-timeline-empty' );
				}
				else {
					$timeline = new EPTimeline(
						$this->getContext(),
						$events
					);

					$timeline->display();
				}
			}
		}
		else {
			$out->addHTML( Linker::linkKnown(
				SpecialPage::getTitleFor( 'Userlogin' ),
				wfMsgHtml( 'ep-dashboard-login-first' ), // TODO
				array(),
				array(
					'returnto' => $this->getTitle( $this->subPage )->getFullText()
				)
			) );
		}
	}

	/**
	 * Displays the navigation menu.
	 *
	 * @since 0.1
	 */
	protected function displayNavigation() {
		$menu = new EPMenu( $this->getContext() );
		$menu->setItemFunction( function( array $items ) {
			unset( $items['ep-nav-dashboard'] ); // TODO
			return $items;
		} );
		$menu->display();
	}

}
