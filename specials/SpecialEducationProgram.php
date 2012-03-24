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

		$this->startCache( 1 );

		$this->displayNavigation();

		$this->addCachedHTML( array( $this, 'displaySummaryTable' ) );

		$this->addCachedHTML( array( $this, 'displayByTerm' ) );

		$this->saveCache();
	}

	/**
	 * Display a table with a basic summary of what the extension is handling.
	 *
	 * @since 0.1
	 *
	 * @return string
	 */
	public function displaySummaryTable() {
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

	public function displayByTerm() {
		$terms = $this->getTermData();

		$html = Html::element( 'h2', array(), $this->msgTxt( 'by-term' ) );

		$html .= Html::openElement( 'table', array( 'class' => 'wikitable ep-termbreakdown' ) );

		$term = array_shift( $terms );
		$rows = array_keys( $term );
		array_unshift( $terms, $term );

		array_unshift( $rows, 'header' );

		foreach ( $rows as $row ) {
			$isHeader = $row === 'header';

			$html .= '<tr>';

			$html .= Html::element( 'th', array(), $isHeader ? '' : $this->msgTxt( $row ) );

			foreach ( $terms as $termName => $term ) {
				$html .= Html::element( $isHeader ? 'th' : 'td', array(), $isHeader ? $termName : $term[$row] );
			}

			$html .= '</tr>';
		}

		$html .= Html::closeElement( 'table' );

		return $html;
	}

	protected function getTermData() {
		$termNames = EPCourses::singleton()->selectFields( 'term', array(), array( 'DISTINCT' ) );
		$terms = array();

		foreach ( $termNames as $termName ) {
			$courses = EPCourses::singleton()->select( null, array( 'term' => $termName ) );

			$students = array();
			$oas = array();
			$cas = array();
			$instructors = array();
			$orgs = array();
			$courseIds = array();

			foreach ( $courses as /* EPCourse */ $course ) {
				$students = array_merge( $students, $course->getField( 'students' ) );
				$oas = array_merge( $oas, $course->getField( 'online_ambs' ) );
				$cas = array_merge( $cas, $course->getField( 'campus_ambs' ) );
				$instructors = array_merge( $instructors, $course->getField( 'instructors' ) );
				$orgs[] = $course->getField( 'org_id' );
				$courseIds[] = $course->getId();
			}

			$pageIds = EPArticles::singleton()->selectFields( 'page_id', array( 'course_id' => $courseIds ), array( 'DISTINCT' ) );
			$pageIds = array_unique( $pageIds );

			$students = array_unique( $students );
			$oas = array_unique( $oas );
			$cas = array_unique( $cas );
			$instructors = array_unique( $instructors );
			$orgs = array_unique( $orgs );

			$term = array(
				'courses' => count( $courses ),
				'students' => count( $students ),
				'instructors' => count( $instructors ),
				'oas' => count( $oas ),
				'cas' => count( $cas ),
				'orgs' => count( $orgs ),
				'articles' => count( $pageIds ),
			);

			$terms[$termName] = $term;
		}

		return $terms;
	}

	protected function msgTxt() {
		$args = func_get_args();
		array_unshift( $args, $this->prefixKey( array_shift( $args ) ) );
		return call_user_func_array( array( $this, 'msg' ), $args );
	}

	protected function prefixKey( $key ) {
		return  'ep-' . strtolower( $this->mName ) . '-' . $key;
	}

}
