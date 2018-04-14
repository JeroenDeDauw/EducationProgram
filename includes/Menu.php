<?php

namespace EducationProgram;

use IContextSource;
use Linker;
use Html;
use SpecialPage;
use Title;

/**
 * Education Program menu.
 * TODO: make configurable via wiki somehow
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 *
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class Menu extends \ContextSource {

	/**
	 * Function called before the HTML is build that allows altering the menu items.
	 *
	 * @var callable|null
	 */
	protected $itemFunction = null;

	/**
	 * @param IContextSource $context
	 */
	public function __construct( IContextSource $context ) {
		$this->setContext( $context );
	}

	/**
	 * Sets a function called before the HTML is build that allows altering the menu items.
	 *
	 * @param callable $itemFunction
	 */
	public function setItemFunction( $itemFunction ) {
		$this->itemFunction = $itemFunction;
	}

	/**
	 * Builds and returns the HTML for the menu.
	 *
	 * @return string
	 */
	public function getHTML() {
		$links = [];

		foreach ( $this->getMenuItems() as $label => $data ) {
			if ( is_array( $data ) ) {
				$target = array_shift( $data );
				$attribs = $data;
			} else {
				$target = $data;
				$attribs = [];
			}

			$links[] = Linker::link(
				$target,
				htmlspecialchars( $label ),
				$attribs
			);
		}

		return Html::rawElement( 'p', [], $this->getLanguage()->pipeList( $links ) );
	}

	/**
	 * Displays the menu.
	 */
	public function display() {
		$this->getOutput()->addHTML( $this->getHTML() );
	}

	/**
	 * Returns the default menu items.
	 *
	 * @return Title[]
	 */
	protected function getMenuItems() {
		$items = [
			'ep-nav-orgs' => SpecialPage::getTitleFor( 'Institutions' ),
			'ep-nav-courses' => SpecialPage::getTitleFor( 'Courses' ),
		];

		$items['ep-nav-students'] = SpecialPage::getTitleFor( 'Students' );

		$items['ep-nav-oas'] = SpecialPage::getTitleFor( 'OnlineAmbassadors' );

		$items['ep-nav-cas'] = SpecialPage::getTitleFor( 'CampusAmbassadors' );

		$user = $this->getUser();

		if ( Students::singleton()->has( [ 'user_id' => $user->getId() ] ) ) {
			$items['ep-nav-mycourses'] = SpecialPage::getTitleFor( 'MyCourses' );
		}

		if ( $user->isAllowed( 'eponline' ) ) {
			$items['ep-nav-oaprofile'] = SpecialPage::getTitleFor( 'OnlineAmbassadorProfile' );
		}

		if ( $user->isAllowed( 'epcampus' ) ) {
			$items['ep-nav-caprofile'] = SpecialPage::getTitleFor( 'CampusAmbassadorProfile' );
		}

		if ( !is_null( $this->itemFunction ) ) {
			$items = call_user_func( $this->itemFunction, $items );
		}

		$menuItems = [];

		foreach ( $items as $messageKey => $title ) {
			$menuItems[$this->msg( $messageKey )->text()] = $title;
		}

		return $menuItems;
	}
}
