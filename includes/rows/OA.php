<?php

namespace EducationProgram;

use IContextSource;

/**
 * Class representing a single online ambassador.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 *
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class OA extends RoleObject implements IRole {

	/**
	 * Display a pager with online ambassadors.
	 *
	 * @param IContextSource $context
	 * @param array $conditions
	 *
	 * @return string
	 */
	public static function getPager( IContextSource $context, array $conditions = [] ) {
		$pager = new OAPager( $context, $conditions );

		if ( $pager->getNumRows() ) {
			return $pager->getFilterControl() .
				$pager->getNavigationBar() .
				$pager->getBody() .
				$pager->getNavigationBar() .
				$pager->getMultipleItemControl();
		} else {
			return $pager->getFilterControl( true ) .
				$context->msg( 'ep-oa-noresults' )->escaped();
		}
	}

	/**
	 * @see IRole::getRoleName
	 */
	public function getRoleName() {
		return 'online';
	}

}
