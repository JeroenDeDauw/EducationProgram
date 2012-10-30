<?php

namespace EducationProgram;
use IContextSource, MWException;

/**
 * Utility for visualizing diffs between two revisions.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DiffTable extends \ContextSource {

	/**
	 * @since 0.1
	 * @var RevisionDiff
	 */
	protected $diff;

	/**
	 * Constructor.
	 *
	 * @since 0.1
	 *
	 * @param IContextSource $context
	 * @param RevisionDiff $diff
	 * @throws MWException
	 */
	public function __construct( IContextSource $context, RevisionDiff $diff ) {
		if ( !$diff->isValid() ) {
			throw new MWException( 'Ivalid RevisionDiff passed to DiffTable.' );
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

			$source = array_key_exists( 'source', $values ) ? $this->formatValue( $values['source'], $field ) : '';
			$target = array_key_exists( 'target', $values ) ? $this->formatValue( $values['target'], $field ) : '';

			$out->addElement( 'th', array(), $field );
			$out->addElement( 'td', array(), $source );
			$out->addElement( 'td', array(), $target );

			$out->addHtml( '</tr>' );
		}

		$out->addHTML( '</table>' );
	}

	/**
	 * Do additional formatting.
	 * This is a bit of a hack and really ought to be aware of the field type rather then using field names.
	 *
	 * @since 0.2
	 *
	 * @param mixed $value
	 * @param string $name
	 *
	 * @return string
	 */
	protected function formatValue( $value, $name ) {
		if ( in_array( $name, array( 'start', 'end' ) ) ) {
			$value = $this->getLanguage()->timeanddate( $value );
		}

		return $value;
	}

}
