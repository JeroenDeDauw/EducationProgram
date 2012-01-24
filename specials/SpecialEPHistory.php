<?php

/**
 * Base class for history special pages.
 *
 * @since 0.1
 *
 * @file SpecialEPHistory.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v3 or later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
abstract class SpecialEPHistory extends SpecialEPPage {

	/**
	 * @see parent::__construct
	 *
	 * @since 0.1
	 *
	 * @param string $name
	 * @param string $restriction
	 */
	public function __construct( $name, $restriction = '' ) {
		parent::__construct( $name, $restriction, false );
	}

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

//		$revisions = EPRevision::select(
//			null,
//			$conditions
//		);
//
//		if ( count( $revisions ) > 0 ) {
//			array_unshift( $revisions, EPRevision::newFromObject( $object ) );
//			$this->displayRevisionList( $revisions );
//		}
//		else {
//			// TODO
//		}
	}

	protected function displayRevisionList( array /* of EPRevision */ $revisions ) {
		foreach ( $revisions as &$revision ) {
			$revision = '<li>' . $this->getRevisionItem( $revision ) . '</li>';
		}

		$this->getOutput()->addHTML( '<ul>' . implode( '', $revisions ) . '</ul>' );
	}

	protected function getRevisionItem( EPRevision $revision ) {
		return $revision->getField( 'time' ) . json_encode( $revision->getField( 'data' ) ); // TODO
	}

}
