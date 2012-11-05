<?php

namespace EducationProgram;
use Title, ParserOptions, ParserOutput;

/**
 * Content class for Education Program institution pages.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @since 0.3
 *
 * @ingroup EducationProgram
 * @ingroup Content
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class OrgContent extends EducationContent {

	/**
	 * @since 0.3
	 * @var Org
	 */
	protected $org;

	/**
	 * Constructor.
	 * Do not use to construct new stuff from outside of this class, use the static newFoobar methods.
	 * In other words: treat as protected (which it was, but now cannot be since we derive from Content).
	 *
	 * @since 0.3
	 *
	 * @param Org $org
	 */
	public function __construct( Org $org ) {
		parent::__construct( CONTENT_MODEL_EP_ORG );

		$this->org = $org;
	}

	/**
	 * Returns a ParserOutput object containing the HTML.
	 *
	 * @since 0.3
	 *
	 * @param Title              $title
	 * @param null               $revId
	 * @param null|ParserOptions $options
	 * @param bool               $generateHtml
	 *
	 * @return Title
	 */
	public function getParserOutput( Title $title, $revId = null, ParserOptions $options = null, $generateHtml = true )  {
		if ( $options === null ) {
			$options = new ParserOptions();
			$options->setEditSection( false );
		}

		$parserOutput = new ParserOutput();

		// TODO: dafuiq

		if ( $generateHtml ) {
			$context = new \RequestContext();
			$context->setLanguage( $options->getTargetLanguage() );
			$context->setUser( $options->getUser() );
			$context->setTitle( $title );

			$viewAction = new CourseView( $this, $context );

			$parserOutput->setText( $viewAction->getHtml() );

			$parserOutput->addModules( array( 'wikibase.common' ) );
		}

		return $parserOutput;
	}

	/**
	 * @see EducationContent::getValue
	 *
	 * @since 0.3
	 *
	 * @return Course
	 */
	protected function getValue() {
		return $this->org;
	}

	/**
	 * @since 0.3
	 *
	 * @param array $array
	 *
	 * @return OrgContent
	 */
	public static function newFromArray( array $array ) {
		return new self( Orgs::singleton()->newRow( $array ) );
	}

}
