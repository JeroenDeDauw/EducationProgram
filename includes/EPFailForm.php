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
	 * The query for the action URL.
	 * @since 0.1
	 * @var array
	 */
	protected $query = array();
	
	/**
	 * Should the summary field be shown or not?
	 * @since 0.1
	 * @var boolean
	 */
	protected $showSummary = true;

	/**
	 * Should the minor edit checkbox be shown or not?
	 * @since 0.1
	 * @var boolean
	 */
	protected $showMinorEdit = true;
	
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
	
	
	/**
	 * Sets the query for the action URL.
	 * 
	 * @since 0.1
	 * 
	 * @param array $query
	 */
	public function setQuery( array $query ) {
		$this->query = $query;
	}
	
	/**
	 * Sets if the summary field be shown or not.
	 * 
	 * @since 0.1
	 * 
	 * @param boolean $showSummary
	 */
	public function setShowSummary( $showSummary ) {
		$this->showSummary = $showSummary;
	}

	/**
	 * Sets if the minor edit checkbox be shown or not.
	 *
	 * @since 0.1
	 *
	 * @param boolean $showMinorEdit
	 */
	public function setShowMinorEdit( $showMinorEdit ) {
		$this->showMinorEdit = $showMinorEdit;
	}

	/**
	 * (non-PHPdoc)
	 * @see HTMLForm::getBody()
	 */
	function getBody() {
		$html = $this->displaySection( $this->mFieldTree );

		$html .= '<br />';

		if ( $this->showSummary ) {
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
		}

		if ( $this->showMinorEdit ) {
			$attrs = Linker::tooltipAndAccesskeyAttribs( 'ep-minor' );

			$html .= Xml::checkLabel(
				wfMsg( 'ep-form-minor' ),
				'wpMinoredit',
				'wpMinoredit',
				false,
				$attrs
			) . '<br />';
		}

		return $html;
	}

}