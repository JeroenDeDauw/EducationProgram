<?php

namespace EducationProgram;

/**
 * An error page that can contain a live link to the page that threw it.
 * (This is useful for error messages in this extension.)
 *
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Andrew Green andrew.green.df@gmail.com
 *
 */
class ErrorPageErrorWithSelflink extends \ErrorPageError {

	private $articleTitle;
	private $linkText;

	/**
	 * An error page error with the given $title and $msg. Optionally,$msg may have a
	 * parameter for an internal link to the article $articleTitle, which can be the
	 * same page from which the error is thrown.
	 *
	 * @param Message|string $title Message key (string) for error page title, or a Message object
	 *
	 * @param Message|string $msg Message key (string) for error text, or a Message object
	 *
	 * @param string $articleTitle (optional) Wiki page title for creating an internal link and setting it
	 *   as a parameter for $msg.
	 *
	 * @param string $linkText (optional) Text for internal link to be set as a parameter for $msg.
	 */
	function __construct( $title, $msg, $articleTitle = null, $linkText = null) {
		$this->articleTitle = $articleTitle;
		$this->linkText = $linkText;

		if ( is_null( $articleTitle ) ) {
			parent::__construct( $title, $msg );
		} else {
			parent::__construct( $title, $msg, array ( $articleTitle ) );
		}
	}

	/**
	 * This method overrides the superclass's method in order to allow HTML
	 * in the message. This is necessary to produce a live internal link
	 * to the same page we're on.
	 *
	 * @see ErrorPageError::report()
	 */
	function report() {
		global $wgOut, $wgParser;

		// get actual message objects
		$titleAsMessageObj = $this->getAsMessageObj( $this->title );
		$msgAsMessageObj = $this->getAsMessageObj($this->msg);

		// create an internal link and set it as a message param
		if (!is_null( $this->articleTitle ) ) {
			$internalLink = $wgParser->makeKnownLinkHolder( $this->articleTitle, $this->linkText );
			$msgAsMessageObj->params( $internalLink );
		}

		// create the output; see ErrorPageError and OutputPage::showErrorPage()
		$wgOut->prepareErrorPage( $titleAsMessageObj );
		$wgOut->addHTML( $msgAsMessageObj->plain() );
		$wgOut->returnToMain();
		$wgOut->output();
	}

	private function getAsMessageObj( $msg ) {
		if ( $msg instanceof \Message ) {
			return $msg;
		}
		return new \Message( $msg );
	}
}