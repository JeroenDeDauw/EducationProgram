<?php

/**
 * Page listing recent student activity.
 *
 * @since 0.1
 *
 * @file SpecialStudentActivity.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class SpecialStudentActivity extends SpecialEPPage {

	/**
	 * Constructor.
	 *
	 * @since 0.1
	 */
	public function __construct() {
		parent::__construct( 'StudentActivity' );
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

		$this->startCache( 180 );

		$this->getOutput()->addModules( 'ep.studentactivity' );

		$this->displayNavigation();

		$this->addCachedHTML( array( $this, 'displayCachedContent' ) );
	}

	/**
	 * Displays the content of the page that should be cached.
	 *
	 * @since 0.1
	 *
	 * @return string
	 */
	public function displayCachedContent() {
		$duration = EPSettings::get( 'recentActivityLimit' );

		$conds = array( 'last_active > ' . wfGetDB( DB_SLAVE )->addQuotes(
			wfTimestamp( TS_MW, time() - $duration )
		) );

		return $this->displayStudentMeter( $conds, $duration ) .
			'<br />' .
			$this->displayPager( $conds, $duration );
	}

	/**
	 * Returns the HTML for the pager.
	 *
	 * @since 0.1
	 *
	 * @param array $conds
	 *
	 * @return string
	 */
	public function displayPager( array $conds, $duration ) {
		$pager = new EPStudentActivityPager( $this->getContext(), $conds );

		if ( $pager->getNumRows() ) {
			$html =
				$pager->getFilterControl() .
				$pager->getNavigationBar() .
				$pager->getBody() .
				$pager->getNavigationBar() .
				$pager->getMultipleItemControl();
		}
		else {
			$html = $pager->getFilterControl( true )
				. '<br />'
				. wfMsgExt( 'ep-studentactivity-noresults', 'parseinline', EPUtils::formatDuration( $duration, array( 'hours' ) ) );
		}

		return '<div class="studentactivity">' . $html . '</div>';
	}

	/**
	 * Returns the HTML for the student activity meter.
	 *
	 * @since 0.1
	 *
	 * @param array $conds
	 * @param integer $duration
	 *
	 * @return string
	 */
	public function displayStudentMeter( array $conds, $duration ) {
		$studentCount = EPStudents::singleton()->count( $conds );

		if ( $studentCount < 10 ) {
			$image = $studentCount < 5 ? 0 : 5;
		}
		else {
			$image = min( round( $studentCount / 10 ) * 10, 60 );
		}

		$message = $this->msg( 'ep-studentactivity-count', $studentCount, EPUtils::formatDuration( $duration, array( 'hours' ) ) )->escaped();

		return Html::element( 'img', array(
			'src' => EPSettings::get( 'imageDir' ) . 'student-o-meter_morethan-' . $image . '.png',
			'alt' => $message,
			'title' => $message,
			'class' => 'studentometer'
		) );
	}

}
