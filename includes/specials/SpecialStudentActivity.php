<?php

namespace EducationProgram;

/**
 * Page listing recent student activity.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 *
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class SpecialStudentActivity extends VerySpecialPage {

	public function __construct() {
		parent::__construct( 'StudentActivity' );
	}

	/**
	 * Main method.
	 *
	 * @param string $subPage
	 */
	public function execute( $subPage ) {
		parent::execute( $subPage );

		$this->startCache( 180 );

		$this->getOutput()->addModules( 'ep.studentactivity' );

		$this->displayNavigation();

		$this->addCachedHTML( [ $this, 'displayCachedContent' ] );
	}

	/**
	 * Displays the content of the page that should be cached.
	 *
	 * @return string
	 */
	public function displayCachedContent() {
		$duration = Settings::get( 'recentActivityLimit' );

		$conds = [ 'last_active > ' . wfGetDB( DB_REPLICA )->addQuotes(
			wfTimestamp( TS_MW, time() - $duration )
		) ];

		return $this->displayStudentMeter( $conds, $duration ) .
			'<br />' .
			$this->displayPager( $conds, $duration );
	}

	/**
	 * Returns the HTML for the pager.
	 *
	 * @param array $conds
	 *
	 * @param int $duration
	 *
	 * @return string
	 */
	public function displayPager( array $conds, $duration ) {
		$pager = new StudentActivityPager( $this->getContext(), $conds );

		if ( $pager->getNumRows() ) {
			$html =
				$pager->getFilterControl() .
				$pager->getNavigationBar() .
				$pager->getBody() .
				$pager->getNavigationBar() .
				$pager->getMultipleItemControl();
		} else {
			$html = $pager->getFilterControl( true )
				. '<br />'
				. $this->msg(
					'ep-studentactivity-noresults',
					$this->getLanguage()->formatDuration( $duration, [ 'hours' ] )
				)->parse();
		}

		return '<div class="studentactivity">' . $html . '</div>';
	}

	/**
	 * Returns the HTML for the student activity meter.
	 *
	 * @param array $conds
	 * @param int $duration
	 *
	 * @return string
	 */
	public function displayStudentMeter( array $conds, $duration ) {
		global $wgExtensionAssetsPath;
		$studentCount = Students::singleton()->count( $conds );

		if ( $studentCount < 10 ) {
			$image = $studentCount < 5 ? 0 : 5;
		} else {
			$image = min( round( $studentCount / 10 ) * 10, 60 );
		}

		$message = $this->msg(
			'ep-studentactivity-count',
			$studentCount,
			$this->getLanguage()->formatDuration( $duration, [ 'hours' ] )
		)->text();

		return \Html::element( 'img', [
			'src' => $wgExtensionAssetsPath .
				'/EducationProgram/resources/images/student-o-meter_morethan-' . $image . '.png',
			'alt' => $message,
			'title' => $message,
			'class' => 'studentometer'
		] );
	}

	protected function getGroupName() {
		return 'education';
	}
}
