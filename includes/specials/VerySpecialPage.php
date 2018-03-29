<?php

namespace EducationProgram;

use Message;
use Html;

/**
 * Base special page for special pages in the Education Program extension,
 * taking care of some common stuff and providing compatibility helpers.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 *
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
abstract class VerySpecialPage extends \SpecialCachedPage {

	/**
	 * The subpage, ie the part after Special:PageName/
	 * Empty string if none is provided.
	 *
	 * @var string
	 */
	public $subPage;

	/**
	 * @see SpecialPage::getDescription
	 *
	 * @return String
	 */
	public function getDescription() {
		return $this->msg( 'special-' . strtolower( $this->getName() ) )->text();
	}

	/**
	 * Sets headers - this should be called from the execute() method of all derived classes!
	 */
	public function setHeaders() {
		$out = $this->getOutput();
		$out->setArticleRelated( false );
		$out->setPageTitle( $this->getDescription() );
	}

	/**
	 * Main method.
	 *
	 * @param string|null $subPage
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
			return;
		}

		Utils::displayResult( $this->getContext() );
	}

	/**
	 * Show a message in an error box.
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
	 */
	protected function displayNavigation() {
		$menu = new Menu( $this->getContext() );
		$menu->display();
	}

	/**
	 * Display the summary data.
	 *
	 * @param IORMRow $item
	 * @param bool $collapsed
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

		$html .= Html::openElement( 'table', [ 'class' => $class ] );

		$html .= '<tr>' .
			Html::element( 'th', [ 'colspan' => 2 ], $this->msg( 'ep-item-summary' )->text() ) .
			'</tr>';

		$summaryData = is_null( $summaryData ) ? $this->getSummaryData( $item ) : $summaryData;

		foreach ( $summaryData as $stat => $value ) {
			$html .= '<tr>';

			$html .= Html::element(
				'th',
				[ 'class' => 'ep-summary-name' ],
				$this->msg( str_replace( 'educationprogram\\', '', strtolower( get_called_class() ) )
					. '-summary-' . $stat )->text()
			);

			$html .= Html::rawElement(
				'td',
				[ 'class' => 'ep-summary-value' ],
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
	 * @param IORMRow $item
	 *
	 * @return array
	 */
	protected function getSummaryData( IORMRow $item ) {
		return [];
	}

}
