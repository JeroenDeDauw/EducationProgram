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
	 * @var array of EPCourse
	 */
	protected $courses;

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

		if ( $this->getUser()->isLoggedIn() ) {
			$this->displayEnrollmentMessage();

			$student = EPStudent::newFromUser( $this->getUser() );
			$courses = $student->getCourses( null, EPCourses::getStatusConds( 'current' ) );

			$this->courses = $courses;

			$this->startCache( 60 );

			if ( defined( 'DYK_VERSION' ) ) {
				$this->displayDidYouKnow();
			}

			if ( $courses === array() ) {
				$this->getOutput()->addWikiMsg( 'ep-dashboard-enroll-first' );
			}
			else {
				$this->displayTimelines();
			}

			$this->saveCache();
		}
		else {
			$this->getOutput()->addHTML( Linker::linkKnown(
				SpecialPage::getTitleFor( 'Userlogin' ),
				wfMsgHtml( 'ep-dashboard-login-first' ),
				array(),
				array(
					'returnto' => $this->getTitle( $this->subPage )->getFullText()
				)
			) );
		}
	}

	/**
	 * Display the did you know box.
	 *
	 * @since 0.1
	 */
	protected function displayDidYouKnow() {
		$this->addCachedHTML(
			function( IContextSource $context, array $courses ) {
				$specificCategory = false;

				$course = array_shift( $courses );

				if ( !is_null( $course ) ) {
					$specificCategory = EPOrgs::singleton()->selectFieldsRow(
						array( 'name' ),
						array( 'id' => $course->getField( 'org_id' ) )
					);

					if ( is_string( $specificCategory ) ) {
						$specificCategory = str_replace(
							array( '$1', '$2' ),
							array( $specificCategory, EPSettings::get( 'dykCategory' ) ),
							EPSettings::get( 'dykOrgCategory' )
						);
					}
				}

				$box = new DYKBox(
					EPSettings::get( 'dykCategory' ),
					$specificCategory,
					$context
				);

				return $box->getHTML();
			},
			array( $this->getContext(), $this->courses )
		);

		$this->getOutput()->addModules( DYKBox::getModules() );
	}

	/**
	 * Display the course activity timelines.
	 *
	 * @since 0.1
	 */
	protected function displayTimelines() {
		foreach ( $this->courses as $course ) {
			$this->displayTimeline( $course );
		}

		if ( $this->courses !== array() ) {
			$this->getOutput()->addModules( EPTimeline::getModules() );
		}
	}

	/**
	 * Returns the variables used to constructed the cache key in an array.
	 *
	 * @since 0.1
	 *
	 * @return array
	 */
	protected function getCacheKey() {
		return array_merge(
			parent::getCacheKey(),
			array_map(
				function( EPCourse $course ) {
					return $course->getId();
				},
				$this->courses
			),
			array( $this->getUser()->getOption( 'ep_showdyk' ) )
		);
	}

	/**
	 * Displays the activity timeline for a single course.
	 *
	 * @since 0.1
	 *
	 * @param EPCourse $course
	 */
	protected function displayTimeline( EPCourse $course ) {
		$this->addCachedHTML(
			function( EPCourse $course, IContextSource $context ) {
				$eventTable = EPEvents::singleton();

				$conds = array(
					'course_id' => $course->getId(),
					'time > ' . wfGetDB( DB_SLAVE )->addQuotes( wfTimestamp( TS_MW, time() - EPSettings::get( 'timelineDurationLimit' ) ) ),
				);

				$options = array(
					'LIMIT' => EPSettings::get( 'timelineCountLimit' ),
					'ORDER BY' => $eventTable->getPrefixedField( 'time' ) . ' DESC'
				);

				$html = Linker::link(
					$course->getTitle(),
					Html::element(
						'h2',
						array(),
						$course->getField( 'name' )
					)
				);

				$events = iterator_to_array( $eventTable->select( null, $conds, $options ) );

				if ( $events === array() ) {
					$html .= $context->msg( 'ep-dashboard-timeline-empty' )->escaped();
				}
				else {
					$timeline = new EPTimeline(
						$context,
						$events
					);

					$html .= $timeline->getHTML();
				}

				return $html;
			},
			array( $course, $this->getContext() )
		);
	}

	/**
	 * Display the enrollment sucecss message if needed.
	 *
	 * @since 0.1
	 */
	protected function displayEnrollmentMessage() {
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
