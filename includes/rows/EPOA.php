<?php

/**
 * Class representing a single online ambassador.
 *
 * @since 0.1
 *
 * @file EPOA.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EPOA extends EPRoleObject implements EPIRole {

	/**
	 * Display a pager with online ambassadors.
	 *
	 * @since 0.1
	 *
	 * @param IContextSource $context
	 * @param array $conditions
	 *
	 * @return string
	 */
	public static function getPager( IContextSource $context, array $conditions = array() ) {
		$pager = new EPOAPager( $context, $conditions );

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
				$context->msg( 'ep-oa-noresults' )->escaped();
		}
	}

	/**
	 * @since 0.1
	 * @see EPIRole::getRoleName
	 */
	public function getRoleName() {
		return 'online';
	}

}
