<?php

namespace EducationProgram;

use IContextSource;
use Exception;

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
	 * First revision type, used to construct the message key for the column
	 * header. Default message is 'ep-diff-old'.
	 *
	 * @since 0.5
	 */
	protected $firstRevisionType = 'old';

	/**
	 * Second revision type, used to construct the message key for the column
	 * header. Default message is 'ep-diff-new'.
	 *
	 * @since 0.5
	 */
	protected $secondRevisionType = 'new';

	/**
	 * @since 0.1
	 *
	 * @param IContextSource $context
	 * @param RevisionDiff $diff
	 * @throws Exception
	 */
	public function __construct( IContextSource $context, RevisionDiff $diff ) {
		if ( !$diff->isValid() ) {
			throw new Exception( 'Ivalid RevisionDiff passed to DiffTable.' );
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

		$out->addElement( 'th', [], '' );
		$out->addElement( 'th', [], $this->msg( 'ep-diff-' . $this->firstRevisionType )->plain() );
		$out->addElement( 'th', [], $this->msg( 'ep-diff-' . $this->secondRevisionType )->plain() );

		$out->addHTML( '</tr>' );

		foreach ( $this->diff->getChangedFields() as $field => $values ) {
			$out->addHTML( '<tr>' );

			$source = array_key_exists( 'source', $values ) ? $this->formatValue( $values['source'], $field ) : '';
			$target = array_key_exists( 'target', $values ) ? $this->formatValue( $values['target'], $field ) : '';

			$out->addElement( 'th', [], $field );
			$out->addElement( 'td', [], $source );
			$out->addElement( 'td', [], $target );

			$out->addHTML( '</tr>' );
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
		if ( in_array( $name, [ 'start', 'end' ] ) ) {
			$value = $this->getLanguage()->timeanddate( $value );
		}

		return $value;
	}

	/**
	 * Set the revision types, which are used in message keys for the columns of
	 * the DiffTable. The defaults are 'old' and 'new'. The string 'ep-diff-'
	 * will be prepended to the strings set here to construct the keys.
	 *
	 * @since 0.5
	 *
	 * @param string $firstRevisionType
	 * @param string $secondRevisionType
	 */
	public function setRevisionTypes( $firstRevisionType, $secondRevisionType ) {
		$this->firstRevisionType = $firstRevisionType;
		$this->secondRevisionType = $secondRevisionType;
	}

}
