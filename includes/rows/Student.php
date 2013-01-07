<?php

namespace EducationProgram;
use IContextSource;

/**
 * Class representing a single student.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class Student extends RoleObject {

	/**
	 * Display a pager with students.
	 *
	 * @since 0.1
	 *
	 * @param IContextSource $context
	 * @param array $conditions
	 *
	 * @return string
	 */
	public static function getPager( IContextSource $context, array $conditions = array() ) {
		$pager = new StudentPager( $context, $conditions );

		if ( $pager->getNumRows() ) {
			return
				$pager->getFilterControl() .
				$pager->getNavigationBar() .
				$pager->getBody() .
				$pager->getNavigationBar() .
				$pager->getMultipleItemControl();
		}
		else {
			return $pager->getFilterControl( true ) .
				$context->msg( 'ep-students-noresults' )->escaped();
		}
	}

	/**
	 * @since 0.1
	 * @see IRole::getRoleName
	 */
	public function getRoleName() {
		return 'student';
	}

	/**
	 * @see ORMRow::loadSummaryFields()
	 */
	public function loadSummaryFields( $summaryFields = null ) {
		if ( is_null( $summaryFields ) ) {
			$summaryFields = array( 'last_active', 'active_enroll' );
		}
		else {
			$summaryFields = (array)$summaryFields;
		}

		$fields = array();

		if ( in_array( 'active_enroll', $summaryFields ) ) {
			$fields['active_enroll'] = $this->hasCourse( Courses::getStatusConds( 'current' ) );
		}

		$this->setFields( $fields );
	}

	/**
	 * Returns the view link for the student.
	 * These are the user page, contribs and student profile.
	 *
	 * @since 0.1
	 *
	 * @param IContextSource $context
	 *
	 * @return string
	 */
	public function getViewLinks( IContextSource $context ) {
		return self::getViewLinksFor(
			$context,
			$this->getUser()->getId(),
			$this->getUser()->getName()
		);
	}

	/**
	 * Returns the view links for the student with provided user id and name.
	 * These are the user page, contribs and student profile.
	 *
	 * @since 0.1
	 *
	 * @param IContextSource $context
	 * @param integer $userId
	 * @param string $userName
	 *
	 * @return string
	 */
	public static function getViewLinksFor( IContextSource $context, $userId, $userName ) {
		return Utils::getToolLinks(
			$userId,
			$userName,
			$context,
			array( \Linker::link(
				\SpecialPage::getTitleFor( 'Student', $userName ),
				$context->msg( 'ep-student-view-profile' )->escaped()
			) )
		);
	}

}
