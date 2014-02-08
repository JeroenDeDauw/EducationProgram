<?php

namespace EducationProgram;

use EducationProgram\Events\Timeline;
use IContextSource;
use Linker;
use EducationProgram\Events\EventQuery;
use EducationProgram\Events\EventStore;

/**
 * Page listing the recent actibvity of the users classmates.
 * It works both as a timeline and a dashboard.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class SpecialMyCourses extends VerySpecialPage {

	/**
	 * @var Course[]
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

			$this->fetchCourses();

			$this->startCache( 60 );

			$this->displayDidYouKnow();

			if ( $this->courses === array() ) {
				$this->getOutput()->addWikiMsg( 'ep-dashboard-enroll-first' );
			}
			else {
				$this->displayTimelines();
			}
		}
		else {
			$this->getOutput()->addHTML( Linker::linkKnown(
				\SpecialPage::getTitleFor( 'Userlogin' ),
				$this->msg( 'ep-dashboard-login-first' )->escaped(),
				array(),
				array( 'returnto' => $this->getPageTitle( $this->subPage )->getFullText() )
			) );
		}
	}

	/**
	 * Gets the active courses the current user is associated with (in any role)
	 * and stores them in the courses field.
	 *
	 * @since 0.1
	 */
	protected function fetchCourses() {
		$now = wfGetDB( DB_SLAVE )->addQuotes( wfTimestampNow() );

		$courses = Courses::singleton()->getCoursesForUsers(
			$this->getUser()->getId(),
			array(),
			array(
				'end >= ' . $now
			)
		);

		$this->courses = iterator_to_array( $courses );
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

				/**
				 * @var Course $course
				 */
				$course = array_shift( $courses );

				if ( !is_null( $course ) ) {
					$specificCategory = Orgs::singleton()->selectFieldsRow(
						array( 'name' ),
						array( 'id' => $course->getField( 'org_id' ) )
					);

					if ( is_string( $specificCategory ) ) {
						$specificCategory = str_replace(
							array( '$1', '$2' ),
							array( $specificCategory, Settings::get( 'dykCategory' ) ),
							Settings::get( 'dykOrgCategory' )
						);
					}
				}

				$box = new DYKBox(
					Settings::get( 'dykCategory' ),
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
			$this->getOutput()->addModules( Timeline::getModules() );
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
				function( Course $course ) {
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
	 * @param Course $course
	 */
	protected function displayTimeline( Course $course ) {
		$this->addCachedHTML(
			function( Course $course, IContextSource $context ) {
				// TODO: inject dependency
				$eventStore = new \EducationProgram\Events\EventStore( 'ep_events' );

				$query = new EventQuery();

				$query->setCourses( $course->getId() );

				// TODO: inject settings
				$timeLimit = wfTimestamp( TS_MW, time() - Settings::get( 'timelineDurationLimit' ) );
				$query->setTimeLimit( $timeLimit, EventQuery::COMP_BIGGER );

				$query->setRowLimit( Settings::get( 'timelineCountLimit' ) );

				$query->setSortOrder( EventQuery::ORDER_TIME_DESC );

				$events = $eventStore->query( $query );

				$html = Linker::link(
					$course->getTitle(),
					\Html::element(
						'h2',
						array('class' => 'ep-course-title'),
						$course->getField( 'name' )
					)
				);

				if ( $events === array() ) {
					$html .= $context->msg( 'ep-dashboard-timeline-empty' )->escaped();
				}
				else {
					$timeline = new Timeline(
						$context->getOutput(),
						$context->getLanguage(),
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
			Students::singleton()->setReadDb( DB_MASTER );

			/**
			 * @var Course $course
			 */
			$course = Courses::singleton()->selectRow( null, array( 'id' => $this->getRequest()->getInt( 'enrolled' ) ) );

			if ( $course !== false && in_array( $this->getUser()->getId(), $course->getField( 'students' ) ) ) {
				$this->showSuccess( $this->msg( 'ep-mycourses-enrolled' )->rawParams(
					$course->getLink(),
					$course->getOrg()->getLink()
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
		$menu = new Menu( $this->getContext() );
		$menu->setItemFunction( function( array $items ) {
			unset( $items['ep-nav-mycourses'] );
			return $items;
		} );
		$menu->display();
	}
}
