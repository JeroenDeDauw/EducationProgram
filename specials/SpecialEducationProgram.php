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

		$this->startCache( 3600 );

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

		$data['org-count'] = EPOrgs::singleton()->count();
		$data['course-count'] = EPCourses::singleton()->count();
		$data['active-course-count'] = EPCourses::singleton()->count( EPCourses::getStatusConds( 'current' ) );

		$data['student-count'] = $this->getRoleCount( EP_STUDENT );

		$studentLists = array_map( 'unserialize', EPCourses::singleton()->selectFields( 'students', EPCourses::getStatusConds( 'current' ) ) );
		$data['current-student-count'] = empty( $studentLists ) ? 0 : count( array_unique( call_user_func_array(
			'array_merge',
			$studentLists
		) ) );

		$data['instructor-count'] = $this->getRoleCount( EP_INSTRUCTOR );
		$data['oa-count'] = $this->getRoleCount( EP_OA );
		$data['ca-count'] = $this->getRoleCount( EP_CA );

		$lang = $this->getLanguage();

		foreach ( $data as &$number ) {
			$number = $lang->formatNum( $number );
		}

		return $data;
	}

	/**
	 * Returns the amount of people that currently have a role for at least one course.
	 * So users that have not enlisted for a single course are not counted.
	 *
	 * @since 0.1
	 *
	 * @param integer $roleId
	 *
	 * @return integer
	 */
	protected function getRoleCount( $roleId ) {
		$dbr = wfGetDB( DB_SLAVE );

		return $dbr->selectRow(
			'ep_users_per_course',
			array( 'COUNT(upc_user_id) AS rowcount' ),
			array( 'upc_role' => $roleId ),
			__METHOD__,
			array( 'DISTINCT' )
		)->rowcount;
	}

	public function displayByTerm() {
		$termsData = $this->getTermData();

		$html = Html::element( 'h2', array(), $this->msgTxt( 'by-term' ) );

		$html .= $this->getByTermTable( $termsData['terms'] );

		$html .= Html::element( 'h2', array(), $this->msgTxt( 'genders' ) );

		$html .= $this->getByGenderTable( $termsData['bygender'] );

		return $html;
	}

	protected function getByGenderTable( $terms ) {
		$html = Html::openElement( 'table', array( 'class' => 'wikitable ep-termbreakdown' ) );

		$term = array_shift( $terms );
		$rows = array_keys( $term );
		array_unshift( $terms, $term );

		$html .= '<tr>';

		$html .= Html::element( 'th', array( 'colspan' => 2 ), '' );

		foreach ( $terms as $termName => $term ) {
			$html .= Html::element(
				'th',
				array(),
				$termName
			);
		}

		$html .= '</tr>';

		foreach ( $rows as $row ) {
			$html .= '<tr>';

			$html .= Html::element( 'th', array( 'rowspan' => 3 ), $this->msgTxt( 'gender-' . $row ) );

			foreach ( array( 'male', 'female', 'unknown' ) as $gender ) {
				if ( $gender !== 'male' ) {
					$html .= '</tr><tr>';
				}

				$html .= Html::element( 'th', array(), $this->msgTxt( $gender ) );

				foreach ( $terms as $term ) {
					$html .= Html::element(
						'td',
						array(),
						$this->getLanguage()->formatNum( round( $term[$row][$gender], 2 ) * 100 ) . '%'
					);
				}
			}

			$html .= '</tr>';
		}

		$html .= Html::closeElement( 'table' );

		return $html;
	}

	protected function getByTermTable( $terms ) {
		$html = Html::openElement( 'table', array( 'class' => 'wikitable ep-termbreakdown' ) );

		reset( $terms );
		$rows = array_keys( $terms[key( $terms )] );
		array_unshift( $rows, 'header' );

		foreach ( $rows as $row ) {
			$isHeader = $row === 'header';

			$html .= '<tr>';

			$html .= Html::element( 'th', array(), $isHeader ? '' : $this->msgTxt( $row ) );

			foreach ( $terms as $termName => $term ) {
				$html .= Html::element(
					$isHeader ? 'th' : 'td',
					array(),
					$isHeader ? $termName : $this->getLanguage()->formatNum( $term[$row] )
				);
			}

			$html .= '</tr>';
		}

		$html .= Html::closeElement( 'table' );

		return $html;
	}

	protected function getTermData() {
		$termNames = EPCourses::singleton()->selectFields( 'term', array(), array( 'DISTINCT' ) );
		$terms = array();
		$byGender = array();

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
				'courses' => $courses->count(),
				'students' => count( $students ),
				'instructors' => count( $instructors ),
				'oas' => count( $oas ),
				'cas' => count( $cas ),
				'orgs' => count( $orgs ),
				'articles' => count( $pageIds ),
			);

			$terms[$termName] = $term;
			$byGender[$termName] = $this->getByGender( $students, $oas, $cas, $instructors );
		}

		return array( 'terms' => $terms, 'bygender' => $byGender );
	}

	protected function getByGender( array $students, array $oas, array $cas, array $instructors ) {
		$genders = $this->getGenders( array_unique( array_merge( $students, $oas, $cas, $instructors ) ) );

		return array(
			'students' => $this->getGenderDistribution( $students, $genders ),
			'oas' => $this->getGenderDistribution( $oas, $genders ),
			'cas' => $this->getGenderDistribution( $cas, $genders ),
			'instructors' => $this->getGenderDistribution( $instructors, $genders ),
		);
	}

	protected function getGenderDistribution( array $users, array $genders ) {
		$distribution = array( 'unknown' => 0, 'male' => 0, 'female' => 0 );

		foreach ( $users as $userId ) {
			$distribution[$genders[$userId]]++;
		}

		$userCount = count( $users );

		foreach ( $distribution as &$amount ) {
			$amount = $userCount === 0 ? 1 : $amount / $userCount;
		}

		return $distribution;
	}

	protected function getGenders( $userIds ) {
		$dbr = wfGetDB( DB_SLAVE );

		$users = $dbr->select(
			'user_properties',
			array( 'up_user', 'up_value' ),
			array( 'up_property' => 'gender' ),
			__METHOD__
		);

		$genders = array_fill_keys( $userIds, 'unknown' );

		while ( $user = $users->fetchObject() ) {
			$genders[$user->up_user] = $user->up_value;
		}

		return $genders;
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
