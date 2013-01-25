<?php

namespace EducationProgram;
use IORMRow, Message, Html;

/**
 * Base special page for special pages in the Education Program extension,
 * taking care of some common stuff and providing compatibility helpers.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
abstract class VerySpecialPage extends \SpecialCachedPage {

	/**
	 * The subpage, ie the part after Special:PageName/
	 * Empty string if none is provided.
	 *
	 * @since 0.1
	 * @var string
	 */
	public $subPage;

	/**
	 * @see SpecialPage::getDescription
	 *
	 * @since 0.1
	 * @return String
	 */
	public function getDescription() {
		return $this->msg( 'special-' . strtolower( $this->getName() ) )->text();
	}

	/**
	 * Sets headers - this should be called from the execute() method of all derived classes!
	 *
	 * @since 0.1
	 */
	public function setHeaders() {
		$out = $this->getOutput();
		$out->setArticleRelated( false );
		$out->setPageTitle( $this->getDescription() );
	}

	/**
	 * Main method.
	 *
	 * @since 0.1
	 *
	 * @param string|null $subPage
	 *
	 * @return boolean
	 */
	public function execute( $subPage ) {
		$this->cacheEnabled = Settings::get( 'enablePageCache' );

		$subPage = is_null( $subPage ) ? '' : $subPage;
		$this->subPage = trim( str_replace( '_', ' ', $subPage ) );

		$this->setHeaders();
		$this->outputHeader();

		// If the user is authorized, display the page, if not, show an error.
		if ( !$this->userCanExecute( $this->getUser() ) ) {
			$this->displayRestrictionError();
			return false;
		}

		Utils::displayResult( $this->getContext() );

		return true;
	}

	/**
	 * Show a message in an error box.
	 *
	 * @since 0.1
	 *
	 * @param Message $message
	 */
	protected function showError( Message $message ) {
		$this->getOutput()->addHTML(
			'<p class="visualClear errorbox">' . $message->parse() . '</p>'
			. '<hr style="display: block; clear: both; visibility: hidden;" />'
		);
	}

	/**
	 * Show a message in a warning box.
	 *
	 * @since 0.1
	 *
	 * @param Message $message
	 */
	protected function showWarning( Message $message ) {
		$this->getOutput()->addHTML(
			'<p class="visualClear warningbox">' . $message->parse() . '</p>'
			. '<hr style="display: block; clear: both; visibility: hidden;" />'
		);
	}

	/**
	 * Show a message in a success box.
	 *
	 * @since 0.1
	 *
	 * @param Message $message
	 */
	protected function showSuccess( Message $message ) {
		$this->getOutput()->addHTML(
			'<div class="successbox"><strong><p>' . $message->parse() . '</p></strong></div>'
			. '<hr style="display: block; clear: both; visibility: hidden;" />'
		);
	}

	/**
	 * Displays the navigation menu.
	 *
	 * @since 0.1
	 */
	protected function displayNavigation() {
		$menu = new Menu( $this->getContext() );
		$menu->display();
	}

	/**
	 * Display the summary data.
	 *
	 * @since 0.1
	 *
	 * @param IORMRow $item
	 * @param boolean $collapsed
	 * @param array $summaryData
	 */
	protected function displaySummary( IORMRow $item, $collapsed = false, array $summaryData = null ) {
		$this->getOutput()->addHTML( $item, $collapsed, $summaryData );
	}

	/**
	 * Display the summary data.
	 *
	 * @since 0.1
	 *
	 * @param IORMRow $item
	 * @param boolean $collapsed
	 * @param array $summaryData
	 *
	 * @return string
	 */
	protected function getSummary( IORMRow $item, $collapsed = false, array $summaryData = null ) {
		$html = '';

		$class = 'wikitable ep-summary mw-collapsible';

		if ( $collapsed ) {
			$class .= ' mw-collapsed';
		}

		$html .= Html::openElement( 'table', array( 'class' => $class ) );

		$html .= '<tr>' . Html::element( 'th', array( 'colspan' => 2 ), $this->msg( 'ep-item-summary' )->text() ) . '</tr>';

		$summaryData = is_null( $summaryData ) ? $this->getSummaryData( $item ) : $summaryData;

		foreach ( $summaryData as $stat => $value ) {
			$html .= '<tr>';

			$html .= Html::element(
				'th',
				array( 'class' => 'ep-summary-name' ),
				$this->msg( str_replace( 'educationprogram\\', '', strtolower( get_called_class() ) ) . '-summary-' . $stat )->text()
			);

			$html .= Html::rawElement(
				'td',
				array( 'class' => 'ep-summary-value' ),
				$value
			);

			$html .= '</tr>';
		}

		$html .= Html::closeElement( 'table' );

		return $html;
	}

	/**
	 * Gets the summary data.
	 * Returned values must be escaped.
	 *
	 * @since 0.1
	 *
	 * @param IORMRow $item
	 *
	 * @return array
	 */
	protected function getSummaryData( IORMRow $item ) {
		return array();
	}

}
