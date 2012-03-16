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

		$this->displayNavigation();

		$conds = array( 'last_active > ' . wfGetDB( DB_SLAVE )->addQuotes(
			wfTimestamp( TS_MW, time() - ( EPSettings::get( 'recentActivityLimit' ) ) )
		) );

		$this->showStudentMeter( $conds );

		$out = $this->getOutput();

		$pager = new EPStudentActivityPager( $this->getContext(), $conds );

		if ( $pager->getNumRows() ) {
			$out->addHTML(
				$pager->getFilterControl() .
				$pager->getNavigationBar() .
				$pager->getBody() .
				$pager->getNavigationBar() .
				$pager->getMultipleItemControl()
			);
		}
		else {
			$out->addHTML( $pager->getFilterControl( true ) );
			$out->addWikiMsg( 'ep-studentactivity-noresults' );
		}
	}

	public function showStudentMeter( array $conds ) {
		$studentCount = EPStudents::singleton()->count( $conds );

		if ( $studentCount < 10 ) {
			$image = $studentCount < 5 ? 0 : 5;
		}
		else {
			$image = floor( $studentCount / 10 );
		}

		$message = $this->msg( 'ep-studentactivity-count', $studentCount )->escaped();

		$this->getOutput()->addElement( 'img', array(
			'src' => EPSettings::get( 'imageDir' ) . 'student-o-meter_morethan-' . $image . '.png',
			'alt' => $message,
			'title' => $message,
		) );
	}

}
