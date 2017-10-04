<?php

namespace EducationProgram;

use IContextSource;
use Linker;
use Html;

/**
 * History pager.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class RevisionPager extends \ReverseChronologicalPager {
	/**
	 * Context in which this pager is being shown.
	 * @since 0.1
	 * @var IContextSource
	 */
	protected $context;

	/**
	 * @since 0.1
	 * @var PageTable
	 */
	protected $table;

	/**
	 * @var array
	 */
	private $conds;

	protected $rowNr = 0;

	/**
	 * @param IContextSource $context
	 * @param PageTable $table
	 * @param array $conds
	 */
	public function __construct( IContextSource $context, PageTable $table, array $conds = [] ) {
		if ( method_exists( 'ReverseChronologicalPager', 'getUser' ) ) {
			parent::__construct( $context );
		} else {
			parent::__construct();
		}

		$this->conds = $conds;
		$this->context = $context;
		$this->table = $table;

		$this->mDefaultDirection = true;
		$this->getDateCond(
			$context->getRequest()->getText( 'year', '' ),
			$context->getRequest()->getText( 'month', '' )
		);
	}

	/**
	 * @see parent::getStartBody
	 * @since 0.1
	 */
	function getStartBody() {
		return '<ul>';
	}

	/**
	 * Abstract formatting function. This should return an HTML string
	 * representing the result row $row. Rows will be concatenated and
	 * returned by getBody()
	 *
	 * @param stdClass $row database row
	 *
	 * @return String
	 */
	function formatRow( $row ) {
		/**
		 * @var EPRevision $revision
		 */
		$revision = Revisions::singleton()->newRowFromDBResult( $row );
		$object = $revision->getObject();

		$html = '';

		$html .= $object->getLink(
			'view',
			htmlspecialchars( $this->getLanguage()->timeanddate( $revision->getField( 'time' ) ) ),
			[],
			[ 'revid' => $revision->getId() ]
		);

		$html .= '&#160;&#160;';

		$html .= Linker::userLink( $revision->getUser()->getId(), $revision->getUser()->getName() )
			. Linker::userToolLinks( $revision->getUser()->getId(), $revision->getUser()->getName() );

		if ( $revision->getField( 'minor_edit' ) ) {
			$html .= '&#160;&#160;';
			$html .= '<strong>' . $this->msg( 'minoreditletter' )->escaped() . '</strong>';
		}

		if ( $revision->getField( 'comment' ) !== '' ) {
			$html .= '&#160;.&#160;.&#160;';

			$html .= Html::rawElement(
				'span',
				[
					'dir' => 'auto',
					'class' => 'comment',
				],
				'(' . $this->getOutput()->parseInline( $revision->getField( 'comment' ) ) . ')'
			);
		}

		// If users have full edit rights to course pages, they can undo revisions
		// and restore revisions.
		if ( $this->getUser()->isAllowed( $this->table->getEditRight() ) ) {
			$actionLinks = [];

			if ( $this->mOffset !== '' || $this->rowNr < $this->mResult->numRows() - 1 ) {
				$actionLinks[] = $object->getLink(
					'epundo',
					$this->msg( 'ep-revision-undo' )->escaped(),
					[],
					[ 'revid' => $revision->getId() ]
				);
			}

			if ( $this->mOffset !== '' || $this->rowNr != 0 ) {
				$actionLinks[] = $object->getLink(
					'eprestore',
					$this->msg( 'ep-revision-restore' )->escaped(),
					[],
					[ 'revid' => $revision->getId() ]
				);
			}
		}

		// Any user can do the compare action.
		if ( $this->mOffset !== '' || $this->rowNr != 0 ) {
			$actionLinks[] = $object->getLink(
				'epcompare',
				$this->msg( 'ep-revision-compare' )->escaped(),
				[],
				[ 'revid' => $revision->getId() ]
			);
		}

		// Display the links for available actions for the revision.
		if ( !empty( $actionLinks ) ) {
			$html .= '&#160;.&#160;.&#160;';
			$html .= '(' .  $this->getLanguage()->pipeList( $actionLinks ) . ')';
		}

		$this->rowNr++;

		return '<li>' . $html . '</li>';
	}

	/**
	 * @see parent::getEndBody
	 * @since 0.1
	 */
	function getEndBody() {
		return '</ul>';
	}

	/**
	 * This function should be overridden to provide all parameters
	 * needed for the main paged query. It returns an associative
	 * array with the following elements:
	 *	tables => Table(s) for passing to Database::select()
	 *	fields => Field(s) for passing to Database::select(), may be *
	 *	conds => WHERE conditions
	 *	options => option array
	 *	join_conds => JOIN conditions
	 *
	 * @return array
	 */
	function getQueryInfo() {
		$table = Revisions::singleton();
		return [
			'tables' => $table->getName(),
			'fields' => $table->getPrefixedFields( $table->getFieldNames() ),
			'conds' => $table->getPrefixedValues( $this->conds ),
			'options' => [ 'USE INDEX' => [ 'ep_revisions' => 'ep_revision_time' ] ],
		];
	}

	/**
	 * This function should be overridden to return the name of the index fi-
	 * eld.  If the pager supports multiple orders, it may return an array of
	 * 'querykey' => 'indexfield' pairs, so that a request with &count=querykey
	 * will use indexfield to sort.  In this case, the first returned key is
	 * the default.
	 *
	 * Needless to say, it's really not a good idea to use a non-unique index
	 * for this!  That won't page right.
	 *
	 * @return string|array
	 */
	function getIndexField() {
		return Revisions::singleton()->getPrefixedField( 'time' );
	}

}
