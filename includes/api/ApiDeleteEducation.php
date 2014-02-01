<?php

namespace EducationProgram;
use ApiBase;

/**
 * API module to delete objects stored by the Education Program extension.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 * @ingroup API
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ApiDeleteEducation extends ApiBase {

	/**
	 * Maps class names to values for the type parameter.
	 *
	 * @since 0.1
	 *
	 * @var array
	 */
	protected static $typeMap = array(
		'org' => 'EducationProgram\Orgs',
		'course' => 'EducationProgram\Courses',
	);

	/**
	 * Returns the type param value for a class name.
	 *
	 * @since 0.1
	 *
	 * @param string $className
	 *
	 * @return string
	 */
	public static function getTypeForClassName( $className ) {
		static $map = null;

		if ( is_null( $map ) ) {
			$map = array_flip( self::$typeMap );
		}

		return $map[$className];
	}

	public function execute() {
		$params = $this->extractRequestParams();

		if ( !$this->userIsAllowed( $params['type'], $params ) || $this->getUser()->isBlocked() ) {
			$this->dieUsageMsg( array( 'badaccess-groups' ) );
		}

		// If we're deleting institutions, we'll do some extra checks
		if ( $params['type'] === 'org' ) {

			foreach ( $params['ids'] as $id ) {

				$org = Orgs::singleton()
					->selectRow( null, array( 'id' => $id ) );

				$deletionHelper = new OrgDeletionHelper( $org, $this );

				if ( !$deletionHelper->checkRestrictions() ) {

					$this->dieUsage( $deletionHelper->getCantDeleteMsgPlain(),
						'org_deletion_restriction');
				}
			}
		}

		$everythingOk = true;

		$class = self::$typeMap[$params['type']];

		if ( !empty( $params['ids'] ) ) {
			$revAction = new RevisionAction();

			$revAction->setUser( $this->getUser() );
			$revAction->setComment( $params['comment'] );

			$class::singleton()->deleteAndLog( $revAction, array( 'id' =>  $params['ids'] ) );
		}

		$this->getResult()->addValue(
			null,
			'success',
			$everythingOk
		);
	}

	/**
	 * Returns if the user is allowed to delete the specified object(s).
	 *
	 * @since 0.1
	 *
	 * @param string $type
	 * @param array $params
	 *
	 * @return boolean
	 */
	protected function userIsAllowed( $type, array $params ) {
		return $this->getUser()->isAllowed( 'ep-' . $type );
	}

	public function needsToken() {
		return true;
	}

	public function mustBePosted() {
		return true;
	}

	public function getAllowedParams() {
		return array(
			'ids' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_REQUIRED => true,
				ApiBase::PARAM_ISMULTI => true,
			),
			'type' => array(
				ApiBase::PARAM_TYPE => array_keys( self::$typeMap ),
				ApiBase::PARAM_REQUIRED => true,
				ApiBase::PARAM_ISMULTI => false,
			),
			'comment' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => false,
				ApiBase::PARAM_DFLT => '',
			),
			'token' => null,
		);
	}

	public function getParamDescription() {
		return array(
			'ids' => 'The IDs of the reviews to delete',
			'token' => 'Edit token. You can get one of these through prop=info.',
			'type' => 'Type of object to delete.',
			'comment' => 'Message with the reason for this change for the log',
		);
	}

	public function getDescription() {
		return array(
			'API module for deleting objects parts of the Education Program extension.'
		);
	}

	public function getPossibleErrors() {
		return array_merge( parent::getPossibleErrors(), array(
			array( 'badaccess-groups' ),
		) );
	}

	protected function getExamples() {
		return array(
			'api.php?action=deleteeducation&ids=42&type=course',
			'api.php?action=deleteeducation&ids=4|2&type=org',
		);
	}

	public function getVersion() {
		return __CLASS__ . ': $Id$';
	}

}
