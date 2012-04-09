<?php

/**
 * Page listing the recent actibvity of the users classmates.
 * It works both as a timeline and a dashboard.
 *
 * @since 0.1
 *
 * @file SpecialMyCourses.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class SpecialMyCourses extends SpecialEPPage {

	/**
	 * Constructor.
	 *
	 * @since 0.1
	 */
	public function __construct() {
		parent::__construct( 'MyCourses' );
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

		$events = EPEvents::singleton()->select();

		$timeline = new EPTimeline( $this->getContext(), $events );

		$timeline->display();
	}

	/**
	 * Displays the navigation menu.
	 *
	 * @since 0.1
	 */
	protected function displayNavigation() {
		$menu = new EPMenu( $this->getContext() );
		$menu->setItemFunction( function( array $items ) {
			unset( $items['ep-nav-timeline'] );
			return $items;
		} );
		$menu->display();
	}

}
