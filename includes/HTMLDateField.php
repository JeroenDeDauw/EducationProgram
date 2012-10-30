<?php

namespace EducationProgram;

/**
 * HTMLForm date field input.
 * Requires jquery.datepicker
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class HTMLDateField extends \HTMLTextField {

	public function __construct( $params ) {
		parent::__construct( $params );

		$this->mClass .= " ep-datepicker-tr mwe-date";
	}

	function getSize() {
		return isset( $this->mParams['size'] )
			? $this->mParams['size']
			: 20;
	}

	function getInputHTML( $value ) {
		$value = explode( 'T',  wfTimestamp( TS_ISO_8601, strtotime( $value . ' UTC' ) ) );
		return parent::getInputHTML( $value[0] );
	}

	function validate( $value, $alldata ) {
		$p = parent::validate( $value, $alldata );

		if ( $p !== true ) {
			return $p;
		}

		//$value = trim( $value );

		// TODO: further validation

		return true;
	}

}
