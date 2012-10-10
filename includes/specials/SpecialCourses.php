<?php

/**
 * Page listing all courses in a pager with filter control.
 * Also has a form for adding new items for those with matching privileges.
 *
 * @since 0.1
 *
 * @file SpecialCourses.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class SpecialCourses extends SpecialEPPage {

	/**
	 * Constructor.
	 *
	 * @since 0.1
	 */
	public function __construct() {
		parent::__construct( 'Courses' );
	}

	/**
	 * Main method.
	 *
	 * @since 0.1
	 *
	 * @param string|null $subPage
	 * @return bool|void
	 */
	public function execute( $subPage ) {
		parent::execute( $subPage );

		$this->displayNavigation();

		$this->startCache( 900 );

		if ( $this->getUser()->isAllowed( 'ep-course' ) ) {
			$this->getOutput()->addModules( 'ep.addcourse' );
			$this->addCachedHTML( 'EPCourse::getAddNewRegion', $this->getContext() );
		}

		$this->addCachedHTML( 'EPCoursePager::getPager', $this->getContext() );
		$this->getOutput()->addModules( EPCoursePager::getModules() );
	}

	/**
	 * @see SpecialCachedPage::getCacheKey
	 * @return array
	 */
	protected function getCacheKey() {
		// TODO: fix session issue
		$values = $this->getRequest()->getValues();

		$user = $this->getUser();

		$values[] = $user->isAllowed( 'ep-course' );
		$values[] = $user->isAllowed( 'ep-bulkdelcourses' );
		$values[] = $user->getOption( 'ep_bulkdelcourses' );

		return array_merge( $values, parent::getCacheKey() );
	}

}
