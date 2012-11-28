<?php

namespace EducationProgram;
use Xml, Html;

/**
 * Action for viewing the history of PageObject items.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 * @ingroup Action
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class HistoryAction extends Action {

	/**
	 * @see Action::getName()
	 */
	public function getName() {
		return 'history';
	}

	/**
	 * @see FormlessAction::onView()
	 */
	public function onView() {
		$this->getOutput()->setPageTitle( $this->getPageTitle() );

		$object = $this->page->getTable()->getFromTitle( $this->getTitle() );

		if ( $object === false ) {
			$this->displayNoRevisions();
		}
		else {
			$this->displayRevisions( $object );
		}

		return '';
	}

	protected function displayNoRevisions() {
		$this->getOutput()->addWikiMsg( $this->prefixMsg( 'norevs' ) );

		$this->displayDeletionLog();
	}

	/**
	 * Returns the page title.
	 *
	 * @since 0.1
	 *
	 * @return string
	 */
	protected function getPageTitle() {
		return $this->msg(
			$this->prefixMsg( 'title' ),
			$this->getTitle()->getText()
		)->text();
	}

	/**
	 * Display a list with the passed revisions.
	 *
	 * @since 0.1
	 *
	 * @param PageObject $object
	 */
	protected function displayRevisions( PageObject $object ) {
		$conditions = $object->getRevisionIdentifiers();

		$action = htmlspecialchars( $GLOBALS['wgScript'] );

		$request = $this->getRequest();
		$out = $this->getOutput();

		/**
		 * Add date selector to quickly get to a certain time
		 */
		$year        = $request->getInt( 'year' );
		$month       = $request->getInt( 'month' );
		$tagFilter   = $request->getVal( 'tagfilter' );
		$tagSelector = \ChangeTags::buildTagFilterSelector( $tagFilter );

		/**
		 * Option to show only revisions that have been (partially) hidden via RevisionDelete
		 */
		if ( $request->getBool( 'deleted' ) ) {
			$conditions['deleted'] = true;
		}

		$checkDeleted = Xml::checkLabel( $this->msg( 'history-show-deleted' )->text(),
			'deleted', 'mw-show-deleted-only', $request->getBool( 'deleted' ) ) . "\n";

		$out->addHTML(
			"<form action=\"$action\" method=\"get\" id=\"mw-history-searchform\">" .
				Xml::fieldset(
					$this->msg( 'history-fieldset-title' )->text(),
					false,
					array( 'id' => 'mw-history-search' )
				) .
				Html::hidden( 'title', $this->getTitle()->getPrefixedDBKey() ) . "\n" .
				Html::hidden( 'action', 'history' ) . "\n" .
				Xml::dateMenu( $year, $month ) . '&#160;' .
				( $tagSelector ? ( implode( '&#160;', $tagSelector ) . '&#160;' ) : '' ) .
				$checkDeleted .
				Xml::submitButton( $this->msg( 'allpagessubmit' )->text() ) . "\n" .
				'</fieldset></form>'
		);

		$pager = new RevisionPager( $this->getContext(), $this->page->getTable(), $conditions );

		if ( $pager->getNumRows() ) {
			$out->addHTML(
				$pager->getNavigationBar() .
				$pager->getBody() .
				$pager->getNavigationBar()
			);
		}
		else {
			// TODO
		}
	}

	/**
	 * @see Action::getDescription()
	 */
	protected function getDescription() {
		return \Linker::linkKnown(
			\SpecialPage::getTitleFor( 'Log' ),
			$this->msg( $this->prefixMsg( 'description' ) )->escaped(),
			array(),
			array( 'page' => $this->getTitle()->getPrefixedText() )
		);
	}

}
