<?php

/**
 * Utility for visualizing diffs between two revisions.
 *
 * @since 0.1
 *
 * @file EPDiffTable.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EPDiffTable extends ContextSource {

	/**
	 * @since 0.1
	 * @var EPRevisionDiff
	 */
	protected $diff;

	/**
	 * Constructor.
	 *
	 * @since 0.1
	 *
	 * @param IContextSource $context
	 * @param EPRevisionDiff $diff
	 */
	public function __construct( IContextSource $context, EPRevisionDiff $diff ) {
		if ( !$diff->isValid() ) {
			throw new MWException( 'Ivalid EPRevisionDiff passed to EPDiffTable.' );
		}

		$this->diff = $diff;

		$this->setContext( $context );
	}

	/**
	 * Display the diff as a table.
	 *
	 * @since 0.1
	 */
	public function display() {
		$out = $this->getOutput();

		$out->addHTML( '<table class="wikitable sortable"><tr>' );

		$out->addElement( 'th', array(), '' );
		$out->addElement( 'th', array(), $this->msg( 'ep-diff-old' )->plain() );
		$out->addElement( 'th', array(), $this->msg( 'ep-diff-new' )->plain() );

		$out->addHTML( '</tr>' );

		foreach ( $this->diff->getChangedFields() as $field => $values ) {
			$out->addHtml( '<tr>' );

			$source = array_key_exists( 'source', $values ) ? $values['source'] : '';
			$target = array_key_exists( 'target', $values ) ? $values['target'] : '';

			$out->addElement( 'th', array(), $field );
			$out->addElement( 'td', array(), $source );
			$out->addElement( 'td', array(), $target );

			$out->addHtml( '</tr>' );
		}

		$out->addHTML( '</table>' );
	}

}