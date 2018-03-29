<?php

namespace EducationProgram;

use IContextSource;

/**
 * Campus ambassador pager.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 *
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class CAPager extends OAPager {

	/**
	 * @param IContextSource $context
	 * @param array $conds
	 */
	public function __construct( IContextSource $context, array $conds = [] ) {
		parent::__construct( $context, $conds, CAs::singleton() );
	}

	/**
	 * @see TablePager::getRowClass()
	 */
	function getRowClass( $row ) {
		return 'ep-ca-row';
	}

	/**
	 * @see TablePager::getTableClass()
	 */
	public function getTableClass() {
		return 'TablePager ep-cas';
	}

	/**
	 * Make sure that a user has the ep-becampus permission before
	 * listing them among the CA profiles.
	 */
	protected function hideRowCheck() {
		$result = !$this->currentObject->getUser()->isAllowed( 'ep-becampus' );
		return $result;
	}
}
