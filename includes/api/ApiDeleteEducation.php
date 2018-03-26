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
	 * @var array
	 */
	protected static $typeMap = [
		'org' => 'EducationProgram\Orgs',
		'course' => 'EducationProgram\Courses',
	];

	/**
	 * Returns the type param value for a class name.
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
			$this->dieWithError( 'apierror-permissiondenied-generic', 'permissiondenied' );
		}

		// If we're deleting institutions, we'll do some extra checks
		if ( $params['type'] === 'org' ) {
			foreach ( $params['ids'] as $id ) {
				$org = Orgs::singleton()
					->selectRow( null, [ 'id' => $id ] );

				$deletionHelper = new OrgDeletionHelper( $org, $this );

				if ( !$deletionHelper->checkRestrictions() ) {
					if ( is_callable( [ $this, 'dieWithError' ] ) ) {
						$this->dieWithError( $deletionHelper->getCantDeleteMsg(),
							'org_deletion_restriction' );
					} else {
						$this->dieUsage( $deletionHelper->getCantDeleteMsgPlain(),
							'org_deletion_restriction' );
					}
				}
			}
		}

		$everythingOk = true;

		$class = self::$typeMap[$params['type']];

		if ( !empty( $params['ids'] ) ) {
			$revAction = new RevisionAction();

			$revAction->setUser( $this->getUser() );
			$revAction->setComment( $params['comment'] );

			$class::singleton()->deleteAndLog( $revAction, [ 'id' => $params['ids'] ] );
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
	 * @param string $type
	 * @param array $params
	 *
	 * @return bool
	 */
	protected function userIsAllowed( $type, array $params ) {
		return $this->getUser()->isAllowed( 'ep-' . $type );
	}

	public function needsToken() {
		return 'csrf';
	}

	public function mustBePosted() {
		return true;
	}

	public function getAllowedParams() {
		return [
			'ids' => [
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_REQUIRED => true,
				ApiBase::PARAM_ISMULTI => true,
			],
			'type' => [
				ApiBase::PARAM_TYPE => array_keys( self::$typeMap ),
				ApiBase::PARAM_REQUIRED => true,
				ApiBase::PARAM_ISMULTI => false,
			],
			'comment' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => false,
				ApiBase::PARAM_DFLT => '',
			],
			'token' => null,
		];
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getParamDescription() {
		return [
			'ids' => 'The IDs of the objects to delete',
			'token' => 'Edit token. You can get one of these through prop=info.',
			'type' => 'Type of object to delete.',
			'comment' => 'Message with the reason for this change for the log',
		];
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getDescription() {
		return [
			'API module for deleting objects created by the Education Program extension.'
		];
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	protected function getExamples() {
		return [
			'api.php?action=deleteeducation&ids=42&type=course',
			'api.php?action=deleteeducation&ids=4|2&type=org',
		];
	}

	/**
	 * @see ApiBase::getExamplesMessages()
	 */
	protected function getExamplesMessages() {
		return [
			'action=deleteeducation&ids=42&type=course'
				=> 'apihelp-deleteeducation-example-1',
			'action=deleteeducation&ids=4|2&type=org'
				=> 'apihelp-deleteeducation-example-2',
		];
	}

}
