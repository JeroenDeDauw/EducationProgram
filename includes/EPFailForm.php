<?php

/**
 * I so love HTMLForm.
 *
 * @since 0.1
 *
 * @file EPFailForm.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v3 or later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EPFailForm extends HTMLForm {

	/**
	 * Wrap the form innards in an actual <form> element
	 * @param $html String HTML contents to wrap.
	 * @return String wrapped HTML.
	 */
	function wrapForm( $html ) {

		// Include a <fieldset> wrapper for style, if requested.
		if ( $this->mWrapperLegend !== false ) {
			$html = Xml::fieldset( $this->mWrapperLegend, $html );
		}
		// Use multipart/form-data
		$encType = $this->mUseMultipart
			? 'multipart/form-data'
			: 'application/x-www-form-urlencoded';
		// Attributes
		$attribs = array(
			'action'  => $this->getTitle()->getFullURL( $this->query ),
			'method'  => $this->mMethod,
			'class'   => 'visualClear',
			'enctype' => $encType,
		);
		if ( !empty( $this->mId ) ) {
			$attribs['id'] = $this->mId;
		}

		return Html::rawElement( 'form', $attribs, $html );
	}
	
	protected $query = array();
	
	public function setQuery( array $query ) {
		$this->query = $query;
	}

	function getBody() {
		$html = $this->displaySection( $this->mFieldTree );

		$html .= '<br />';

		$html .= Html::element(
			'label',
			array( 'for' => 'wpSummary' ),
			wfMsg( 'ep-form-summary' )
		) . '&#160;';

		$attrs = array(
			'id' => 'wpSummary',
			'name' => 'wpSummary',
			'size' => 60,
			'maxlength' => 250,
			'spellcheck' => true
		);

		$attrs = array_merge( $attrs, Linker::tooltipAndAccesskeyAttribs( 'ep-summary' ) );

		$html .= Html::element(
			'input',
			$attrs
		) . '<br />';

		$attrs = Linker::tooltipAndAccesskeyAttribs( 'ep-minor' );

		$html .= Xml::checkLabel(
			wfMsg( 'ep-form-minor' ),
			'wpMinoredit',
			'wpMinoredit',
			false,
			$attrs
		) . '<br />';

		return $html;
	}

}