<?php

namespace EducationProgram;

/**
 * Page listing all students in a pager with filter control.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class SpecialStudents extends VerySpecialPage {

	/**
	 * Constructor.
	 *
	 * @since 0.1
	 */
	public function __construct() {
		parent::__construct( 'Students' );
	}

	/**
	 * Main method.
	 *
	 * @since 0.1
	 *
	 * @param string $subPage
	 */
	public function execute( $subPage ) {
		parent::execute( $subPage );

		if ( $this->subPage === '' ) {
			$this->displayNavigation();

			$this->startCache( 3600 );
			$this->addCachedHTML( 'EducationProgram\Student::getPager', $this->getContext() );
		}
		else {
			$this->getOutput()->redirect( \SpecialPage::getTitleFor( 'Student', $this->subPage )->getLocalURL() );
		}
	}

	/**
	 * @see SpecialCachedPage::getCacheKey
	 * @return array
	 */
	protected function getCacheKey() {
		return array_merge( $this->getRequest()->getValues(), parent::getCacheKey() );
	}

}
