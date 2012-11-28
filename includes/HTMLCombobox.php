<?php

namespace EducationProgram;

/**
 * HTMLForm combobox field input.
 * Requires easyui.combobox
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class HTMLCombobox extends \HTMLSelectField {

	public function __construct( $params ) {
		parent::__construct( $params );
		$this->mClass .= " ep-combobox-tr";
	}

	function getInputHTML( $value ) {
		if ( !in_array( $value, $this->mParams['options'] ) ) {
			 $this->mParams['options'][$value] = $value;
		}

		return parent::getInputHTML( $value );
	}

	function validate( $value, $alldata ) {
		// TODO: further validation

		return true;
	}

}
