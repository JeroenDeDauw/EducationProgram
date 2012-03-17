<?php

/**
 * Page listing recent student activity.
 *
 * @since 0.1
 *
 * @file SpecialStudentActivity.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v3 or later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class SpecialStudentActivity extends SpecialEPPage {

	/**
	 * Constructor.
	 *
	 * @since 0.1
	 */
	public function __construct() {
		$this->cacheExpiry = 180;
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

		$this->getOutput()->addModules( 'ep.studentactivity' );

		$this->displayNavigation();

		$this->addCachedHTML( array( $this, 'displayCachedContent' ) );

		$this->saveCache();
	}

	/**
	 * Displays the content of the page that should be cached.
	 *
	 * @since 0.1
	 *
	 * @return string
	 */
	protected function displayCachedContent() {
		$conds = array( 'last_active > ' . wfGetDB( DB_SLAVE )->addQuotes(
			wfTimestamp( TS_MW, time() - ( EPSettings::get( 'recentActivityLimit' ) ) )
		) );

		return $this->displayStudentMeter( $conds ) .
			'<br />' .
			$this->displayPager( $conds );
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
	public function displayPager( array $conds ) {
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
				. wfMsgExt( 'ep-studentactivity-noresults', 'parseinline' );
		}

		return '<div class="studentactivity">' . $html . '</div>';
	}

	/**
	 * Returns the HTML for the student activity meter.
	 *
	 * @since 0.1
	 *
	 * @param array $conds
	 *
	 * @return string
	 */
	public function displayStudentMeter( array $conds ) {
		$studentCount = EPStudents::singleton()->count( $conds );

		if ( $studentCount < 10 ) {
			$image = $studentCount < 5 ? 0 : 5;
		}
		else {
			$image = min( round( $studentCount / 10 ) * 10, 60 );
		}

		$message = $this->msg( 'ep-studentactivity-count', $studentCount )->escaped();

		return Html::element( 'img', array(
			'src' => EPSettings::get( 'imageDir' ) . 'student-o-meter_morethan-' . $image . '.png',
			'alt' => $message,
			'title' => $message,
			'class' => 'studentometer'
		) );
	}

}
