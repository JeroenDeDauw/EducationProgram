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

		$out = $this->getOutput();

		$this->displaySummaryTable();
	}

	/**
	 * @since 0.1
	 */
	protected function displaySummaryTable() {
		$out = $this->getOutput();

		$out->addHTML( Html::openElement( 'table', array( 'class' => 'wikitable ep-summary' ) ) );

		$out->addHTML( '<tr>' . Html::element( 'th', array( 'colspan' => 2 ), wfMsg( 'ep-summary-table-header' ) ) . '</tr>' );

		$summaryData = $this->getSummaryInfo();

		foreach ( $summaryData as $stat => $value ) {
			$out->addHTML( '<tr>' );

			$out->addHtml( Html::rawElement(
				'th',
				array( 'class' => 'ep-summary-name' ),
				wfMsgExt( strtolower( get_called_class() ) . '-summary-' . $stat, 'parseinline' )
			) );

			$out->addHTML( Html::rawElement(
				'td',
				array( 'class' => 'ep-summary-value' ),
				$value
			) );

			$out->addHTML( '</tr>' );
		}

		$out->addHTML( Html::closeElement( 'table' ) );
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
