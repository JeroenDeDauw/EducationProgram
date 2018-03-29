<?php

namespace EducationProgram;

use Xml;
use Html;
use Linker;

/**
 * I so love HTMLForm.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 *
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class FailForm extends \HTMLForm {

	/**
	 * The query for the action URL.
	 *
	 * @var array
	 */
	protected $query = [];

	/**
	 * Should the summary field be shown or not?
	 *
	 * @var bool
	 */
	protected $showSummary = true;

	/**
	 * Should the minor edit checkbox be shown or not?
	 *
	 * @var bool
	 */
	protected $showMinorEdit = true;

	/**
	 * Wrap the form innards in an actual <form> element
	 * @param string $html HTML contents to wrap.
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
		$attribs = [
			'action'  => $this->getTitle()->getFullURL( $this->query ),
			'method'  => $this->mMethod,
			'class'   => 'visualClear',
			'enctype' => $encType,
		];
		if ( !empty( $this->mId ) ) {
			$attribs['id'] = $this->mId;
		}

		return Html::rawElement( 'form', $attribs, $html );
	}

	/**
	 * Sets the query for the action URL.
	 *
	 * @param array $query
	 */
	public function setQuery( array $query ) {
		$this->query = $query;
	}

	/**
	 * Sets if the summary field be shown or not.
	 *
	 * @param bool $showSummary
	 */
	public function setShowSummary( $showSummary ) {
		$this->showSummary = $showSummary;
	}

	/**
	 * Sets if the minor edit checkbox be shown or not.
	 *
	 * @param bool $showMinorEdit
	 */
	public function setShowMinorEdit( $showMinorEdit ) {
		$this->showMinorEdit = $showMinorEdit;
	}

	/**
	 * @see HTMLForm::getBody()
	 */
	function getBody() {
		$html = $this->displaySection( $this->mFieldTree );

		$html .= '<br />';

		if ( $this->showSummary ) {
			$html .= Html::element(
				'label',
				[ 'for' => 'wpSummary' ],
				$this->msg( 'ep-form-summary' )->text()
			) . '&#160;';

			$attrs = [
				'id' => 'wpSummary',
				'name' => 'wpSummary',
				'size' => 60,
				'maxlength' => 250,
				'spellcheck' => true
			];

			$attrs = array_merge( $attrs, Linker::tooltipAndAccesskeyAttribs( 'ep-summary' ) );

			$html .= Html::element(
				'input',
				$attrs
			) . '<br />';
		}

		if ( $this->showMinorEdit ) {
			$attrs = Linker::tooltipAndAccesskeyAttribs( 'ep-minor' );

			$html .= Xml::checkLabel(
				$this->msg( 'ep-form-minor' )->text(),
				'wpMinoredit',
				'wpMinoredit',
				false,
				$attrs
			) . '<br />';
		}

		return $html;
	}

}
