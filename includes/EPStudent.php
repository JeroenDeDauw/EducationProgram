<?php

/**
 * Class representing a single student.
 *
 * @since 0.1
 *
 * @file EPStudent.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v3 or later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EPStudent extends EPRoleObject {

	/**
	 * Display a pager with students.
	 *
	 * @since 0.1
	 *
	 * @param IContextSource $context
	 * @param array $conditions
	 */
	public static function displayPager( IContextSource $context, array $conditions = array() ) {
		$pager = new EPStudentPager( $context, $conditions );

		if ( $pager->getNumRows() ) {
			$context->getOutput()->addHTML(
				$pager->getFilterControl() .
					$pager->getNavigationBar() .
					$pager->getBody() .
					$pager->getNavigationBar() .
					$pager->getMultipleItemControl()
			);
		}
		else {
			$context->getOutput()->addHTML( $pager->getFilterControl( true ) );
			$context->getOutput()->addWikiMsg( 'ep-students-noresults' );
		}
	}

	/**
	 * @since 0.1
	 * @see EPIRole::getRoleName
	 */
	public function getRoleName() {
		return 'student';
	}
	
	/**
	 * (non-PHPdoc)
	 * @see DBDataObject::loadSummaryFields()
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
			$fields['active_enroll'] = $this->hasCourse( EPCourses::getStatusConds( 'current' ) );
		}

		if ( in_array( 'last_active', $summaryFields ) ) {
			// TODO
		}
		
		$this->setFields( $fields );
	}

	/**
	 * Should be called whenever a user is enrolled as student.
	 *
	 * @since 0.1
	 *
	 * @param integer $courseId
	 */
	public function onEnrolled( $courseId ) {
		if ( !$this->hasField( 'first_course' ) ) {
			$this->setField( 'first_course', $courseId );
			$this->setField( 'first_enroll', wfTimestampNow() );
		}

		$this->setField( 'last_course', $courseId );
		$this->setField( 'last_enroll', wfTimestampNow() );

		$this->getUser()->setOption( 'ep_showtoplink', true );
		$this->getUser()->saveSettings();
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
		return EPUtils::getToolLinks(
			$userId,
			$userName,
			$context,
			array( Linker::link(
				SpecialPage::getTitleFor( 'Student', $userName ),
				$context->msg( 'ep-student-view-profile' )->escaped()
			) )
		);
	}

}
