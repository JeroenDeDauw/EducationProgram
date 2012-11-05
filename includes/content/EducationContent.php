<?php

namespace EducationProgram;
use AbstractContent, Title, ParserOutput, IORMROw;

/**
 * Content class for Education Program pages.
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
abstract class EducationContent extends AbstractContent {

	/**
	 * @since 0.3
	 *
	 * @return IORMROw
	 */
	protected abstract function getValue();

	/**
	 * @see Content::getWikitextForTransclusion
	 */
	public function getWikitextForTransclusion() {
		return false;
	}

	/**
	 * @see Content::isCountable
	 *
	 * @since 0.3
	 *
	 * @param boolean|null $hasLinks
	 *
	 * @return boolean
	 */
	public function isCountable( $hasLinks = null ) {
		return true;
	}

	/**
	 * @see Content::copy
	 *
	 * @since 0.3
	 *
	 * @return EducationContent
	 */
	public function copy() {
		$array = array();

		foreach ( $this->getValue()->toArray() as $key => $value ) {
			$array[$key] = is_object( $value ) ? clone $value : $value;
		}

		return new static( $this->getValue()->getTable()->newRow( $array ) );
	}

	/**
	 * @see Content::getNativeData
	 *
	 * @since 0.3
	 *
	 * @return mixed
	 */
	public function getNativeData() {
		return $this->getValue()->toArray();
	}

	/**
	 * @see Content::getTextForSummary
	 *
	 * @since 0.3
	 *
	 * @param integer $maxLength
	 *
	 * @return string
	 */
	public function getTextForSummary( $maxLength = 250 ) {
		return ''; // TODO
	}

	/**
	 * @see Content::getTextForSearchIndex
	 *
	 * @since 0.3
	 *
	 * @return string
	 */
	public function getTextForSearchIndex() {
		return ''; // TODO
	}

	/**
	 * @see Content::getSize
	 *
	 * @since 0.3
	 *
	 * @return integer
	 */
	public function getSize() {
		return strlen( \FormatJson::encode( $this->getValue()->toArray() ) );
	}

}
