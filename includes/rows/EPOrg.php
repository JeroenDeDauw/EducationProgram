<?php

/**
 * Class representing a single organization/institution.
 *
 * @since 0.1
 *
 * @file EPOrg.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EPOrg extends EPPageObject {
	/**
	 * Cached array of the linked EPCourse objects.
	 *
	 * @since 0.1
	 * @var array|bool false
	 */
	protected $courses = false;

	/**
	 * (non-PHPdoc)
	 * @see ORMRow::loadSummaryFields()
	 */
	public function loadSummaryFields( $summaryFields = null ) {
		if ( is_null( $summaryFields ) ) {
			$summaryFields = array( 'course_count', 'active', 'student_count', 'instructor_count', 'oa_count', 'ca_count', 'courses' );
		}
		else {
			$summaryFields = (array)$summaryFields;
		}

		$fields = array();

		if ( in_array( 'course_count', $summaryFields ) || in_array( 'courses', $summaryFields ) ) {
			$fields['courses'] = EPCourses::singleton()->selectFields( 'id', array( 'org_id' => $this->getId() ) );
			$fields['course_count'] = count( $fields['courses'] );
		}

		if ( in_array( 'active', $summaryFields ) ) {
			$now = wfGetDB( DB_SLAVE )->addQuotes( wfTimestampNow() );

			$fields['active'] = EPCourses::singleton()->has( array(
				'org_id' => $this->getId(),
				'end >= ' . $now,
				'start <= ' . $now,
			) );
		}

		foreach ( array( 'student_count', 'instructor_count', 'oa_count', 'ca_count' ) as $field ) {
			$fields[$field] = EPCourses::singleton()->rawSelectRow(
				array( 'SUM(' . EPCourses::singleton()->getPrefixedField( $field ). ') AS sum' ),
				EPCourses::singleton()->getPrefixedValues( array(
					'org_id' => $this->getId()
				) )
			)->sum;
		}

		$this->setFields( $fields );
	}

	/**
	 * (non-PHPdoc)
	 * @see EPRevisionedObject::onRemoved()
	 */
	protected function onRemoved() {
		/**
		 * @var EPCourse $course
		 */
		foreach ( EPCourses::singleton()->select( null, array( 'org_id' => $this->getId() ) ) as $course ) {
			$revAction = clone $this->revAction;

			if ( trim( $revAction->getComment() ) === '' ) {
				$revAction->setComment( wfMessage(
					'ep-org-course-delete',
					$this->getField( 'name' )
				)->parse() );
			}
			else {
				$revAction->setComment( wfMessage(
					'ep-org-course-delete-comment',
					$this->getField( 'name' ),
					$revAction->getComment()
				)->parse() );
			}

			$course->revisionedRemove( $revAction );
		}

		parent::onRemoved();
	}

	/**
	 * (non-PHPdoc)
	 * @see ORMRow::setField()
	 */
	public function setField( $name, $value ) {
		if ( $name === 'name' ) {
			$value = $GLOBALS['wgLang']->ucfirst( $value );
		}

		parent::setField( $name, $value );
	}

	/**
	 * (non-PHPdoc)
	 * @see EPRevisionedObject::undelete()
	 */
	public function undelete( EPRevisionAction $revAction ) {
		$success = parent::undelete( $revAction );

		if ( $success ) {
			$courseRevAction = new EPRevisionAction();

			$courseRevAction->setUser( $revAction->getUser() );
			$courseRevAction->setComment( $revAction->getComment() );

			foreach ( $this->getField( 'courses' ) as $courseId ) {
				/**
				 * @var EPRevision $courseRevision
				 */
				$courseRevision = EPRevisions::singleton()->getLatestRevision( array(
					'object_id' => $courseId,
					'type' => 'EPCourses',
				) );

				if ( $courseRevision !== false ) {
					$courseRevision->getObject()->undelete( $courseRevAction );
				}
			}
		}

		return $success;
	}

	/**
	 * @see EPPageObject::save
	 *
	 * @since 0.2
	 *
	 * @param string|null $functionName
	 *
	 * @return boolean Success indicator
	 */
	public function save( $functionName = null ) {
		wfGetDB( DB_MASTER )->begin( __METHOD__ );

		$success = parent::save( $functionName );

		if ( $success ) {
			$coursesTable = EPCourses::singleton();

			$coursesTable->setReadDb( DB_MASTER );
			$courses = $coursesTable->select( array( 'id', 'title' ), array( 'org_id' => $this->getId() ) );
			$coursesTable->setReadDb( DB_SLAVE );

			/**
			 * @var EPCourse $course
			 */
			foreach ( $courses as $course ) {
				$titleParts = explode( '/', $course->getField( 'title' ), 2 );
				$course->setField( 'title', $this->getField( 'name' ) . '/' . array_pop( $titleParts ) );
				$course->save( __METHOD__ );
			}
		}

		wfGetDB( DB_MASTER )->commit( __METHOD__ );

		return $success;
	}

	/**
	 * Returns thr HTML for a control to add a new org to the provided context.
	 * Adittional arguments can be provided to set the default values for the control fields.
	 *
	 * @since 0.1
	 *
	 * @param IContextSource $context
	 * @param array $args
	 *
	 * @return string
	 */
	public static function getAddNewControl( IContextSource $context, array $args = array() ) {
		$html = '';

		$html .= Html::openElement(
			'form',
			array(
				'method' => 'post',
				'action' => EPOrgs::singleton()->getTitleFor( 'NAME_PLACEHOLDER' )->getLocalURL( array( 'action' => 'edit' ) ),
			)
		);

		$html .= '<fieldset>';

		$html .= '<legend>' . $context->msg( 'ep-institutions-addnew' )->escaped() . '</legend>';

		$html .= Html::element( 'p', array(), $context->msg( 'ep-institutions-namedoc' )->plain() );

		$html .= Xml::inputLabel(
			$context->msg( 'ep-institutions-newname' )->plain(),
			'newname',
			'newname',
			false,
			array_key_exists( 'name', $args ) ? $args['name'] : false
		);

		$html .= '&#160;' . Html::input(
			'addneworg',
			$context->msg( 'ep-institutions-add' )->plain(),
			'submit',
			array(
				'disabled' => 'disabled',
				'class' => 'ep-org-add',
			)
		);

		$html .= Html::hidden( 'isnew', 1 );

		$html .= '</fieldset></form>';

		return $html;
	}

	/**
	 * Retruns the courses linked to this org.
	 *
	 * @since 0.1
	 *
	 * @param array|string|null $fields
	 *
	 * @return array of EPCourse
	 */
	public function getCourses( array $fields = null ) {
		if ( $this->courses === false ) {
			$courses = EPCourses::singleton()->selectObjects( $fields, array( 'org_id' => $this->getId() ) );

			if ( is_null( $fields ) ) {
				$this->courses = $courses;
			}
		}

		return $this->courses === false ? $courses : $this->courses;
	}
}
