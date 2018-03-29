<?php

namespace EducationProgram;

use Html;

/**
 *
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 *
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class SpecialEducationProgram extends VerySpecialPage {

	public function __construct() {
		parent::__construct( 'EducationProgram' );
	}

	/**
	 * Main method.
	 *
	 * @param string $subPage
	 */
	public function execute( $subPage ) {
		parent::execute( $subPage );

		$this->startCache( 3600 );

		$this->displayNavigation();

		$this->addCachedHTML( [ $this, 'displaySummaryTable' ] );

		$this->addCachedHTML( [ $this, 'displayByTerm' ] );
	}

	/**
	 * Display a table with a basic summary of what the extension is handling.
	 *
	 * @return string
	 */
	public function displaySummaryTable() {
		$html = Html::openElement( 'table', [ 'class' => 'wikitable ep-summary' ] );

		$html .= '<tr>' . Html::element(
			'th',
			[ 'colspan' => 2 ],
			$this->msg( 'ep-summary-table-header' )->text()
		) . '</tr>';

		$summaryData = $this->getSummaryInfo();

		foreach ( $summaryData as $stat => $value ) {
			$html .= '<tr>';

			$html .= Html::rawElement(
				'th',
				[ 'class' => 'ep-summary-name' ],
				$this->msg( str_replace( 'educationprogram\\', 'ep-', strtolower( get_called_class() ) )
					. '-summary-' . $stat )->parse()
			);

			$html .= Html::rawElement(
				'td',
				[ 'class' => 'ep-summary-value' ],
				$value
			);

			$html .= '</tr>';
		}

		$html .= Html::closeElement( 'table' );

		return $html;
	}

	protected function getSummaryInfo() {
		$data = [];

		$data['org-count'] = Orgs::singleton()->count();
		$data['course-count'] = Courses::singleton()->count();
		$data['active-course-count'] =
			Courses::singleton()->count( Courses::getStatusConds( 'current' ) );

		$data['student-count'] = $this->getRoleCount( EP_STUDENT );

		$studentLists = array_map( 'unserialize',
			Courses::singleton()->selectFields( 'students', Courses::getStatusConds( 'current' ) )
		);
		$data['current-student-count'] = empty( $studentLists )
			? 0
			: count( array_unique( call_user_func_array(
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
	 * @param int $roleId
	 *
	 * @return int
	 */
	protected function getRoleCount( $roleId ) {
		$dbr = wfGetDB( DB_REPLICA );

		return $dbr->selectRow(
			'ep_users_per_course',
			[ 'COUNT(upc_user_id) AS rowcount' ],
			[ 'upc_role' => $roleId ],
			__METHOD__,
			[ 'DISTINCT' ]
		)->rowcount;
	}

	public function displayByTerm() {
		$termsData = $this->getTermData();

		if ( empty( $termsData['terms'] ) ) {
			$html = $this->msgTxt( 'nodata' );
		} else {
			$html = Html::element( 'h2', [], $this->msgTxt( 'by-term' ) );

			$html .= $this->getByTermTable( $termsData['terms'] );

			$html .= Html::element( 'h2', [], $this->msgTxt( 'genders' ) );

			$html .= $this->getByGenderTable( $termsData['bygender'] );
		}

		return $html;
	}

	protected function getByGenderTable( $terms ) {
		$html = Html::openElement( 'table', [ 'class' => 'wikitable ep-termbreakdown' ] );

		reset( $terms );
		$rows = array_keys( $terms[key( $terms )] );

		$html .= '<tr>';

		$html .= Html::element( 'th', [ 'colspan' => 2 ], '' );

		foreach ( $terms as $termName => $term ) {
			$html .= Html::element(
				'th',
				[],
				$termName
			);
		}

		$html .= '</tr>';

		foreach ( $rows as $row ) {
			$html .= '<tr>';

			$html .= Html::element( 'th', [ 'rowspan' => 3 ], $this->msgTxt( 'gender-' . $row ) );

			foreach ( [ 'male', 'female', 'unknown' ] as $gender ) {
				if ( $gender !== 'male' ) {
					$html .= '</tr><tr>';
				}

				$html .= Html::element( 'th', [], $this->msgTxt( $gender ) );

				foreach ( $terms as $term ) {
					$html .= Html::element(
						'td',
						[],
						$this->getLanguage()->formatNum( round( $term[$row][$gender], 2 ) * 100 ) . '%'
					);
				}
			}

			$html .= '</tr>';
		}

		$html .= Html::closeElement( 'table' );

		return $html;
	}

	/**
	 * Returns the HTML for the term table.
	 *
	 * @param array[] $terms
	 *
	 * @return string
	 */
	protected function getByTermTable( array $terms ) {
		$html = Html::openElement( 'table', [ 'class' => 'wikitable ep-termbreakdown' ] );

		reset( $terms );
		$rows = array_keys( $terms[key( $terms )] );
		array_unshift( $rows, 'header' );

		foreach ( $rows as $row ) {
			$isHeader = $row === 'header';

			$html .= '<tr>';

			$html .= Html::element( 'th', [], $isHeader ? '' : $this->msgTxt( $row ) );

			foreach ( $terms as $termName => $term ) {
				$html .= Html::element(
					$isHeader ? 'th' : 'td',
					[],
					$isHeader ? $termName : $this->getLanguage()->formatNum( $term[$row] )
				);
			}

			$html .= '</tr>';
		}

		$html .= Html::closeElement( 'table' );

		return $html;
	}

	/**
	 * Returns the term data.
	 * It's an array. Element with key 'terms' contains an array with the terms.
	 * Element with key 'bygender' contains gender breakdown data.
	 *
	 * @return array[]
	 */
	protected function getTermData() {
		$termNames = Courses::singleton()->selectFields( 'term', [], [ 'DISTINCT' ] );
		$terms = [];
		$byGender = [];

		foreach ( $termNames as $termName ) {
			$courses = Courses::singleton()->select( null, [ 'term' => $termName ] );

			$students = [];
			$oas = [];
			$cas = [];
			$instructors = [];
			$orgs = [];
			$courseIds = [];

			// FIXME: use new EPCourse object getters
			foreach ( $courses as $course ) {
				$students = array_merge( $students, $course->getField( 'students' ) );
				$oas = array_merge( $oas, $course->getField( 'online_ambs' ) );
				$cas = array_merge( $cas, $course->getField( 'campus_ambs' ) );
				$instructors = array_merge( $instructors, $course->getField( 'instructors' ) );
				$orgs[] = $course->getField( 'org_id' );
				$courseIds[] = $course->getId();
			}

			// FIXME: use new ArticleStore
			$pageIds = Extension::globalInstance()->newArticleTable()->selectFields(
				'page_id', [ 'course_id' => $courseIds ], [ 'DISTINCT' ]
			);
			$pageIds = array_unique( $pageIds );

			$students = array_unique( $students );
			$oas = array_unique( $oas );
			$cas = array_unique( $cas );
			$instructors = array_unique( $instructors );
			$orgs = array_unique( $orgs );

			$term = [
				'courses' => $courses->count(),
				'students' => count( $students ),
				'instructors' => count( $instructors ),
				'oas' => count( $oas ),
				'cas' => count( $cas ),
				'orgs' => count( $orgs ),
				'articles' => count( $pageIds ),
			];

			$terms[$termName] = $term;
			$byGender[$termName] = $this->getByGender( $students, $oas, $cas, $instructors );
		}

		return [ 'terms' => $terms, 'bygender' => $byGender ];
	}

	/**
	 * Returns gender breakdowns for the provided lists of users.
	 *
	 * @param int[] $students
	 * @param int[] $oas
	 * @param int[] $cas
	 * @param int[] $instructors
	 *
	 * @return float[]
	 */
	protected function getByGender( array $students, array $oas, array $cas, array $instructors ) {
		$genders = $this->getGenders(
			array_unique( array_merge( $students, $oas, $cas, $instructors ) )
		);

		$result = [
			'students' => $this->getGenderDistribution( $students, $genders ),
			'oas' => $this->getGenderDistribution( $oas, $genders ),
			'cas' => $this->getGenderDistribution( $cas, $genders ),
			'instructors' => $this->getGenderDistribution( $instructors, $genders ),
		];

		return $result;
	}

	/**
	 * Returns a gender breakdown for the provided users and their associated genders.
	 *
	 * @param int[] $users User IDs
	 * @param string[] $genders An array mapping user id to gender
	 *
	 * @return float[]
	 */
	protected function getGenderDistribution( array $users, array $genders ) {
		$distribution = [ 'unknown' => 0, 'male' => 0, 'female' => 0 ];

		foreach ( $users as $userId ) {
			$distribution[$genders[$userId]]++;
		}

		$userCount = count( $users );

		foreach ( $distribution as &$amount ) {
			$amount = $userCount === 0 ? 1 : $amount / $userCount;
		}

		return $distribution;
	}

	/**
	 * Returns the genders for the provided user ids.
	 *
	 * @param int[] $userIds
	 *
	 * @return string[]
	 */
	protected function getGenders( array $userIds ) {
		$dbr = wfGetDB( DB_REPLICA );

		$users = $dbr->select(
			'user_properties',
			[ 'up_user', 'up_value' ],
			[ 'up_property' => 'gender' ],
			__METHOD__
		);

		$genders = array_fill_keys( $userIds, 'unknown' );

		foreach ( $users as $user ) {
			$genders[$user->up_user] = $user->up_value;
		}

		return $genders;
	}

	/**
	 * Returns the message text for the provided message key after the key gets prefixed.
	 *
	 * @return string
	 */
	protected function msgTxt() {
		$args = func_get_args();
		array_unshift( $args, $this->prefixKey( array_shift( $args ) ) );
		return call_user_func_array( [ $this, 'msg' ], $args )->text();
	}

	/**
	 * Returns the prefixed version of the provided message key.
	 *
	 * @param string $key
	 *
	 * @return string
	 */
	protected function prefixKey( $key ) {
		return 'ep-' . strtolower( $this->mName ) . '-' . $key;
	}

	protected function getGroupName() {
		return 'education';
	}
}
