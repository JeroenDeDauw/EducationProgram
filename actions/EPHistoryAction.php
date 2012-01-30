<?php

/**
 * Abstract action for viewing the history of EPDBObject items.
 *
 * @since 0.1
 *
 * @file EPHistoryAction.php
 * @ingroup EducationProgram
 * @ingroup Action
 *
 * @licence GNU GPL v3+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
abstract class EPHistoryAction extends FormlessAction {

	/**
	 * Returns the class name of the EPDBObject this action handles.
	 *
	 * @since 0.1
	 *
	 * @return string
	 */
	protected abstract function getItemClass();
	
	public function onView() {
		$c = $this->getItemClass(); // Yeah, this is needed in PHP 5.3 >_>
		
		$object = $c::get( $this->getTitle()->getText() );

		if ( $object === false ) {
			// TODO
		}
		else {
			$this->displayRevisions( $object );
		}
		
		return '';
	}
	
	/**
	 * @see SpecialPage::getDescription
	 *
	 * @since 0.1
	 *
	 * @return string
	 */
//	public function getDescription() {
//		return wfMsgExt( 'special-' . strtolower( $this->getName() ), 'parsemag', $this->subPage );
//	}

	/**
	 * Display a list with the passed revisions.
	 *
	 * @since 0.1
	 *
	 * @param EPDBObject $object
	 */
	protected function displayRevisions( EPDBObject $object ) {
		$conditions = array(
			'type' => get_class( $object ),
		);

		if ( $object->hasIdField() ) {
			$conditions['object_id'] = $object->getId();
		}

		$action = htmlspecialchars( $GLOBALS['wgScript'] );

		$request = $this->getRequest();
		$out = $this->getOutput();

		/**
		 * Add date selector to quickly get to a certain time
		 */
		$year        = $request->getInt( 'year' );
		$month       = $request->getInt( 'month' );
		$tagFilter   = $request->getVal( 'tagfilter' );
		$tagSelector = ChangeTags::buildTagFilterSelector( $tagFilter );

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

		$pager = new EPRevisionPager( $this->getContext(), $conditions );

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

}