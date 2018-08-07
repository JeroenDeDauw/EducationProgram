<?php

namespace EducationProgram;

use Title;
use Exception;
use Linker;
use Message;

/**
 * Class for logging changes to objects managed by the Education Program extension.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 *
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class LogFormatter extends \LogFormatter {

	/**
	 * @see LogFormatter::makePageLink()
	 *
	 * This is overridden to change the link text to only include the name of the object,
	 * rather then the full name of it's page.
	 *
	 * @param Title|null $title
	 * @param array $parameters
	 * @param string|null $html Linktext of the link as raw html
	 *
	 * @throws Exception
	 * @return String
	 */
	protected function makePageLink( Title $title = null, $parameters = [], $html = null ) {
		if ( !$title instanceof Title ) {
			throw new Exception( 'Expected title, got null' );
		}

		$text = explode( '/', $title->getText(), 2 );
		$text = $text[count( $text ) - 1];

		if ( !$this->plaintext ) {
			$link = Linker::link( $title, htmlspecialchars( $text ), [], $parameters );
		} else {
			$link = '[[' . $title->getPrefixedText() . '|' . $text . ']]';
		}

		return $link;
	}

}

/**
 * Class for logging role changes. ie people gaining or losing a role.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 *
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class RoleChangeFormatter extends LogFormatter {

	/**
	 * @see LogFormatter::extractParameters()
	 */
	protected function extractParameters() {
		$params = parent::extractParameters();

		if ( !empty( $params ) ) {
			$lang = $this->context->getLanguage();

			$params[3] = $lang->formatNum( $params[3] );
			$params[4] = $lang->listToText( (array)$params[4] );
		}

		return $params;
	}

}

/**
 * Class for logging role changes to student article associations.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 *
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ArticleFormatter extends LogFormatter {

	/**
	 * @see LogFormatter::extractParameters()
	 */
	protected function extractParameters() {
		$params = parent::extractParameters();

		if ( !empty( $params ) ) {
			$params[3] = Message::rawParam( Linker::link( Title::newFromText( $params[3] ) ) );

			if ( isset( $params[4] ) ) {
				list( $id, $name ) = $params[4];
				$params[4] = Message::rawParam( Linker::userLink( $id, $name ) );
				$params[5] = $name;
			}
		}

		return $params;
	}

}
