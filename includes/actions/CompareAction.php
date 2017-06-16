<?php

namespace EducationProgram;

use Linker;
use Html;
use Xml;

/**
 * Action for comparing two revisions of a PageObject.
 *
 * @since 0.5
 *
 * @ingroup EducationProgram
 * @ingroup Action
 *
 * @licence GNU GPL v2+
 * @author Sage Ross < ragesoss@gmail.com >
 */
class CompareAction extends Action {

	/**
	 * @see Action::getName()
	 */
	public function getName() {
		return 'epcompare';
	}

	/**
	 * @see Action::getDescription()
	 */
	protected function getDescription() {
		return $this->msg( 'backlinksubtitle' )->rawParams( Linker::link( $this->getTitle() ) );
	}

	/**
	 * @see FormlessAction::onView()
	 */
	public function onView() {
		$this->getOutput()->setPageTitle( $this->getPageTitle() );

		$object = $this->page->getTable()->getFromTitle( $this->getTitle() );
		$req = $this->getRequest();

		if ( $object !== false && $req->getCheck( 'revid' ) ) {
			$revision = Revisions::singleton()->selectRow( null, [ 'id' => $req->getInt( 'revid' ) ] );

			if ( $revision !== false ) {
				// Check whether user has full course edit rights.
				// If not, hide the enrollment token from the diff.
				if ( $this->getUser()->isAllowed( $this->page->getEditRight() ) ) {
					$hideToken = false;
				} else {
					$hideToken = true;
				}
				$diff = $object->getCompareDiff( $revision, null, $hideToken );

				if ( $diff->isValid() ) {
					if ( $diff->hasChanges() ) {
						$diffTable = new DiffTable( $this->getContext(), $diff );
						$diffTable->setRevisionTypes( 'previous', 'current' );
						$diffTable->display();
					}

					return '';
				}
			}
		}

		// If we got here, something went wrong
		$this->getOutput()->addWikiMsg( $this->prefixMsg( 'none' ), $this->getTitle()->getText() );
		$this->getOutput()->setSubtitle( '' );

		return '';
	}

	/**
	 * Returns the page title.
	 *
	 * @return string
	 */
	protected function getPageTitle() {
		return $this->msg(
			$this->prefixMsg( 'title' ),
			$this->getTitle()->getText()
		)->text();
	}

}
