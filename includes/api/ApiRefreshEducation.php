<?php

namespace EducationProgram;
use ApiBase;

/**
 * API module to refresh objects stored by the Education Program extension.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 * @ingroup API
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ApiRefreshEducation extends ApiBase {

	/**
	 * Maps class names to values for the type parameter.
	 *
	 * @since 0.1
	 *
	 * @var array
	 */
	protected static $typeMap = array(
		'org' => 'Orgs',
		'course' => 'Courses',
		'student' => 'Students',
	);

	public function execute() {
		$params = $this->extractRequestParams();

		if ( $this->getUser()->isBlocked() ) {
			$this->dieUsageMsg( array( 'badaccess-groups' ) );
		}

		$c = self::$typeMap[$params['type']];
		$c::singleton()->updateSummaryFields( null, array( 'id' => $params['ids'] ) );

		$this->getResult()->addValue(
			null,
			'success',
			true
		);
	}

	public function needsToken() {
		return 'csrf';
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
			'token' => null,
		);
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getParamDescription() {
		return array(
			'ids' => 'The IDs of the reviews to refresh',
			'token' => 'Edit token. You can get one of these through prop=info.',
			'type' => 'Type of object to delete.',
		);
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getDescription() {
		return array(
			'API module for refreshing (rebuilding) summary data of objects parts of the Education Program extension.'
		);
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	protected function getExamples() {
		return array(
			'api.php?action=refresheducation&ids=42&type=course',
			'api.php?action=refresheducation&ids=4|2&type=student',
		);
	}

	/**
	 * @see ApiBase::getExamplesMessages()
	 */
	protected function getExamplesMessages() {
		return array(
			'action=refresheducation&ids=42&type=course'
				=> 'apihelp-refresheducation-example-1',
			'action=refresheducation&ids=4|2&type=student'
				=> 'apihelp-refresheducation-example-2',
		);
	}
}
