<?php

/**
 *
 *
 * @since 0.1
 *
 * @file SpecialEducationProgram.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v3 or later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class SpecialEducationProgram extends SpecialEPPage {

	/**
	 * Constructor.
	 *
	 * @since 0.1
	 */
	public function __construct() {
		$this->cacheExpiry = 3600;
		parent::__construct( 'EducationProgram' );
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

		$this->addCachedHTML( array( $this, 'displaySummaryTable' ) );

		$this->saveCache();
	}

	/**
	 * Display a table with a basic summary of what the extension is handling.
	 *
	 * @since 0.1
	 *
	 * @return string
	 */
	protected function displaySummaryTable() {
		$html = Html::openElement( 'table', array( 'class' => 'wikitable ep-summary' ) );

		$html .= '<tr>' . Html::element( 'th', array( 'colspan' => 2 ), wfMsg( 'ep-summary-table-header' ) ) . '</tr>';

		$summaryData = $this->getSummaryInfo();

		foreach ( $summaryData as $stat => $value ) {
			$html .= '<tr>';

			$html .=  Html::rawElement(
				'th',
				array( 'class' => 'ep-summary-name' ),
				wfMsgExt( strtolower( get_called_class() ) . '-summary-' . $stat, 'parseinline' )
			);

			$html .=  Html::rawElement(
				'td',
				array( 'class' => 'ep-summary-value' ),
				$value
			);

			$html .= '</tr>';
		}

		$html .= Html::closeElement( 'table' );

		return $html;
	}

	protected function getSummaryInfo() {
		$data = array();

		$lang = $this->getLanguage();

		$data['org-count'] = $lang->formatNum( EPOrgs::singleton()->count() );
		$data['course-count'] = $lang->formatNum( EPCourses::singleton()->count() );
		$data['student-count'] = $lang->formatNum( EPStudents::singleton()->count() );
		$data['instructor-count'] = $lang->formatNum( EPInstructors::singleton()->count() );
		$data['oa-count'] = $lang->formatNum( EPOAs::singleton()->count() );
		$data['ca-count'] = $lang->formatNum( EPCAs::singleton()->count() );

		return $data;
	}

}
