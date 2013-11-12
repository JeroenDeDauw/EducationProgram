<?php

namespace EducationProgram;
use IContextSource, Html, Xml;

/**
 * Class representing a single organization/institution.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class Org extends PageObject {
	/**
	 * Cached array of the linked Course objects.
	 *
	 * @since 0.1
	 * @var Course[]|bool false
	 */
	protected $courses = false;

	/**
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
			$fields['courses'] = Courses::singleton()->selectFields( 'id', array( 'org_id' => $this->getId() ) );
			$fields['course_count'] = count( $fields['courses'] );
		}

		if ( in_array( 'active', $summaryFields ) ) {
			$now = wfGetDB( DB_SLAVE )->addQuotes( wfTimestampNow() );

			$fields['active'] = Courses::singleton()->has( array(
				'org_id' => $this->getId(),
				'end >= ' . $now,
				'start <= ' . $now,
			) );
		}

		foreach ( array( 'student_count', 'instructor_count', 'oa_count', 'ca_count' ) as $field ) {
			$fields[$field] = Courses::singleton()->rawSelectRow(
				array( 'SUM(' . Courses::singleton()->getPrefixedField( $field ) . ') AS sum' ),
				Courses::singleton()->getPrefixedValues( array(
					'org_id' => $this->getId()
				) )
			)->sum;
		}

		$this->setFields( $fields );
	}

	/**
	 * @see RevisionedObject::onRemoved()
	 */
	protected function onRemoved() {
		/**
		 * @var Course $course
		 */
		foreach ( Courses::singleton()->select( null, array( 'org_id' => $this->getId() ) ) as $course ) {
			$revAction = clone $this->revAction;

			if ( trim( $revAction->getComment() ) === '' ) {
				$revAction->setComment( wfMessage(
					'ep-org-course-delete',
					$this->getField( 'name' )
				)->text() );
			}
			else {
				$revAction->setComment( wfMessage(
					'ep-org-course-delete-comment',
					$this->getField( 'name' ),
					$revAction->getComment()
				)->text() );
			}

			$course->revisionedRemove( $revAction );
		}

		parent::onRemoved();
	}

	/**
	 * @see ORMRow::setField()
	 */
	public function setField( $name, $value ) {
		if ( $name === 'name' ) {
			$value = $GLOBALS['wgLang']->ucfirst( $value );
		}

		parent::setField( $name, $value );
	}

	/**
	 * @see RevisionedObject::undelete()
	 */
	public function undelete( RevisionAction $revAction ) {
		$success = parent::undelete( $revAction );

		if ( $success ) {
			$courseRevAction = new RevisionAction();

			$courseRevAction->setUser( $revAction->getUser() );
			$courseRevAction->setComment( $revAction->getComment() );

			foreach ( $this->getField( 'courses' ) as $courseId ) {
				/**
				 * @var EPRevision $courseRevision
				 */
				$courseRevision = Revisions::singleton()->getLatestRevision( array(
					'object_id' => $courseId,
					'type' => 'Courses',
				) );

				if ( $courseRevision !== false ) {
					$courseRevision->getObject()->undelete( $courseRevAction );
				}
			}
		}

		return $success;
	}

	/**
	 * @see PageObject::save
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
			$coursesTable = Courses::singleton();

			$coursesTable->setReadDb( DB_MASTER );
			$courses = $coursesTable->select( array( 'id', 'title' ), array( 'org_id' => $this->getId() ) );
			$coursesTable->setReadDb( DB_SLAVE );

			/**
			 * @var Course $course
			 */
			foreach ( $courses as $course ) {
				$originalTitle = $course->getField( 'title' );
				$titleParts = explode( '/', $originalTitle, 2 );
				$newTitle = $this->getField( 'name' ) . '/' . array_pop( $titleParts );

				if ( $originalTitle !== $newTitle ) {
					$course->setField( 'title', $newTitle );
					$course->save( __METHOD__ );
				}
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
				'action' => Orgs::singleton()->getTitleFor( 'NAME_PLACEHOLDER' )->getLocalURL( array( 'action' => 'edit' ) ),
			)
		);

		$html .= '<fieldset>';

		$html .= '<legend>' . $context->msg( 'ep-institutions-addnew' )->escaped() . '</legend>';

		$html .= Html::element( 'p', array(), $context->msg( 'ep-institutions-namedoc' )->text() );

		$html .= Xml::inputLabel(
			$context->msg( 'ep-institutions-newname' )->text(),
			'newname',
			'newname',
			false,
			array_key_exists( 'name', $args ) ? $args['name'] : false
		);

		$html .= '&#160;' . Html::input(
			'addneworg',
			$context->msg( 'ep-institutions-add' )->text(),
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
	 * Returns the courses linked to this org.
	 *
	 * @since 0.1
	 *
	 * @param array|string|null $fields
	 *
	 * @return Course[]
	 */
	public function getCourses( array $fields = null ) {
		if ( $this->courses === false ) {
			$courses = Courses::singleton()->selectObjects( $fields, array( 'org_id' => $this->getId() ) );

			if ( is_null( $fields ) ) {
				$this->courses = $courses;
			}
		}

		return $this->courses === false ? $courses : $this->courses;
	}

	/**
	 * @see RevisionedObject::getTypeId
	 *
	 * @since 0.3
	 *
	 * @return string
	 */
	protected function getTypeId() {
		return 'EPOrgs';
	}

}
