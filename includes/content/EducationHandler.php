<?php

namespace EducationProgram;
use MWException, WikiPage, Title, Content, Language, MWContentSerializationException;

/**
 * Base handler class for ContentHandler classes in Education Program.
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
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
abstract class EducationHandler extends \ContentHandler {

	/**
	 * Constructor.
	 *
	 * @since 0.3
	 *
	 * @param string $modelId
	 */
	public function __construct( $modelId ) {
		$formats = array(
			CONTENT_FORMAT_JSON,
			CONTENT_FORMAT_SERIALIZED
		);

		parent::__construct( $modelId, $formats );
	}

	/**
	 * @see ContentHandler::getDefaultFormat
	 *
	 * @since 0.3
	 *
	 * @return string
	 */
	public function getDefaultFormat() {
		return CONTENT_FORMAT_JSON;
	}

	/**
	 * @see ContentHandler::serializeContent
	 *
	 * @since 0.3
	 *
	 * @param Content $content
	 * @param null|string $format
	 *
	 * @throws MWException
	 * @return string
	 */
	public function serializeContent( Content $content, $format = null ) {

		if ( !( $content instanceof EducationContent ) ) {
			throw new MWException( 'Invalid content type provided to EducationHandler' );
		}

		if ( is_null( $format ) ) {
			$format = $this->getDefaultFormat();
		}

		$data = $content->getNativeData();

		switch ( $format ) {
			case CONTENT_FORMAT_SERIALIZED:
				$blob = serialize( $data );
				break;
			case CONTENT_FORMAT_JSON:
				$blob = json_encode( $data );
				break;
			default:
				throw new MWException( "serialization format $format is not supported for Education content model" );
		}

		return $blob;
	}

	/**
	 * @since 0.3
	 *
	 * @param $blob
	 * @param null $format
	 * @return mixed
	 *
	 * @throws MWException
	 * @throws MWContentSerializationException
	 */
	protected function unserializedData( $blob, $format = null ) {
		if ( is_null( $format ) ) {
			$format = $this->getDefaultFormat();
		}

		switch ( $format ) {
			case CONTENT_FORMAT_SERIALIZED:
				$data = unserialize( $blob ); //FIXME: suppress notice on failed serialization!
				break;
			case CONTENT_FORMAT_JSON:
				$data = json_decode( $blob, true ); //FIXME: suppress notice on failed serialization!
				break;
			default:
				throw new MWException( "serialization format $format is not supported for Education content model" );
				break;
		}

		if ( $data === false || $data === null ) {
			throw new MWContentSerializationException( 'failed to deserialize' );
		}

		return $data;
	}

	/**
	 * @see ContentHandler::canBeUsedOn();
	 *
	 * This implementation returns true if and only if the given title's namespace
	 * is the same as the one returned by $this->getEntityNamespace().
	 *
	 * @param \Title $title
	 * @return bool true if $title represents a page in the appropriate entity namespace.
	 */
	public function canBeUsedOn( Title $title ) {
		if ( $title->getNamespace() !== EP_NS ) {
			return false;
		}

		return Utils::isCourse( $title ) XOR $this->getModelID() === CONTENT_MODEL_EP_ORG;
	}

	/**
	 * Returns false to indicate that the parser cache should not be used.
	 * The html representation of Items depends on the user language, splitting the parser
	 * cache by user language is currently problematic and would need some core changes.
	 *
	 * @note: see also note on getPageLanguage()
	 *
	 * @see ContentHandler::isParserCacheSupported
	 *
	 * @since 0.3
	 *
	 * @return bool false
	 */
	public function isParserCacheSupported() {
		return false;
	}

	/**
	 * @see Content::getPageViewLanguage
	 *
	 * This implementation returns the user language, because education pages get rendered in
	 * the user's language. The PageContentLanguage hook is bypassed.
	 *
	 * @param Title        $title the page to determine the language for.
	 * @param Content|null $content the page's content, if you have it handy, to avoid reloading it.
	 *
	 * @return Language the page's language
	 */
	public function getPageViewLanguage( Title $title, Content $content = null ) {
		global $wgLang;
		return $wgLang;
	}

	/**
	 * @see Content::getPageLanguage
	 *
	 * @since 0.3
	 *
	 * @param Title        $title the page to determine the language for.
	 * @param Content|null $content the page's content, if you have it handy, to avoid reloading it.
	 *
	 * @return Language the page's language
	 */
	public function getPageLanguage( Title $title, Content $content = null ) {
		global $wgContLang;
		return $wgContLang;
	}

	/**
	 * Returns the name of the special page responsible for creating a page for this type of entity content.
	 * Returns null if there is no such special page.
	 * @since 0.2
	 *
	 * @return string|null
	 */
	public function getSpecialPageForCreation() {
		return null;
	}

}
