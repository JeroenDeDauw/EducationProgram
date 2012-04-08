<?php

/**
 * Education Program menu.
 * @since 0.1
 *
 * @file EPMenu.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EPMenu extends ContextSource {

	/**
	 * Function called before the HTML is build that allows altering the menu items.
	 *
	 * @since 0.1
	 * @var function|null
	 */
	protected $itemFunction = null;

	/**
	 * Constructor.
	 *
	 * @since 0.1
	 *
	 * @param IContextSource $context
	 */
	public function __construct( IContextSource $context ) {
		$this->setContext( $context );
	}

	/**
	 * Sets a function called before the HTML is build that allows altering the menu items.
	 *
	 * @since 0.1
	 *
	 * @param function $itemFunction
	 */
	public function setItemFunction( $itemFunction ) {
		$this->itemFunction = $itemFunction;
	}

	/**
	 * Builds and returns the HTML for the menu.
	 *
	 * @since 0.1
	 *
	 * @return string
	 */
	public function getHTML() {
		$links = array();

		foreach ( $this->getMenuItems() as $label => $data ) {
			if ( is_array( $data ) ) {
				$target = array_shift( $data );
				$attribs = $data;
			}
			else {
				$target = $data;
				$attribs = array();
			}

			$links[] = Linker::link(
				$target,
				htmlspecialchars( $label ),
				$attribs
			);
		}

		return Html::rawElement( 'p', array(), $this->getLanguage()->pipeList( $links ) );
	}

	/**
	 * Displays the menu.
	 *
	 * @since 0.1
	 */
	public function display() {
		$this->getOutput()->addHTML( $this->getHTML() );
	}

	/**
	 * Returns the default menu items.
	 *
	 * @since 0.1
	 *
	 * @return array of Title
	 */
	protected function getMenuItems() {
		$items = array(
			'ep-nav-orgs' => SpecialPage::getTitleFor( 'Institutions' ),
			'ep-nav-courses' => SpecialPage::getTitleFor( 'Courses' ),
		);

		$items['ep-nav-students'] = SpecialPage::getTitleFor( 'Students' );

		$items['ep-nav-oas'] = SpecialPage::getTitleFor( 'OnlineAmbassadors' );

		$items['ep-nav-cas'] = SpecialPage::getTitleFor( 'CampusAmbassadors' );

		$user = $this->getUser();

		if ( EPStudents::singleton()->has( array( 'user_id' => $user->getId() ) ) ) {
			$items['ep-nav-mycourses'] = SpecialPage::getTitleFor( 'MyCourses' );
		}

		if ( EPOA::newFromUser( $user )->hasCourse() ) {
			$items['ep-nav-oaprofile'] = SpecialPage::getTitleFor( 'OnlineAmbassadorProfile' );
		}

		if ( EPCA::newFromUser( $user )->hasCourse() ) {
			$items['ep-nav-caprofile'] = SpecialPage::getTitleFor( 'CampusAmbassadorProfile' );
		}

		if ( !is_null( $this->itemFunction ) ) {
			$items = call_user_func( $this->itemFunction, $items );
		}

		$menuItems = array();

		foreach ( $items as $messageKey => $title ) {
			$menuItems[wfMsg( $messageKey )] = $title;
		}

		return $menuItems;
	}

}
