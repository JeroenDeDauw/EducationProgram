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
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class CAPager extends OAPager {

	/**
	 * Constructor.
	 *
	 * @param IContextSource $context
	 * @param array $conds
	 */
	public function __construct( IContextSource $context, array $conds = array() ) {
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

}
