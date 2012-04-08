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
	 * Builds and returns the HTML for the menu.
	 *
	 * @since 0.1
	 *
	 * @return string
	 */
	public function getHTML() { // TODO
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
	protected function getMenuItems() { // TODO
		$items = array(
			wfMsg( 'ep-nav-orgs' ) => SpecialPage::getTitleFor( 'Institutions' ),
			wfMsg( 'ep-nav-courses' ) => SpecialPage::getTitleFor( 'Courses' ),
		);

		$items[wfMsg( 'ep-nav-students' )] = SpecialPage::getTitleFor( 'Students' );

		$items[wfMsg( 'ep-nav-oas' )] = SpecialPage::getTitleFor( 'OnlineAmbassadors' );

		$items[wfMsg( 'ep-nav-cas' )] = SpecialPage::getTitleFor( 'CampusAmbassadors' );

		$user = $this->getUser();

		if ( EPStudents::singleton()->has( array( 'user_id' => $user->getId() ) ) ) {
			$items[wfMsg( 'ep-nav-mycourses' )] = SpecialPage::getTitleFor( 'MyCourses' );
		}

		if ( EPOA::newFromUser( $user )->hasCourse() ) {
			$items[wfMsg( 'ep-nav-oaprofile' )] = SpecialPage::getTitleFor( 'OnlineAmbassadorProfile' );
		}

		if ( EPCA::newFromUser( $user )->hasCourse() ) {
			$items[wfMsg( 'ep-nav-caprofile' )] = SpecialPage::getTitleFor( 'CampusAmbassadorProfile' );
		}

		return $items;
	}

}
