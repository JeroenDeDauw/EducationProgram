<?php

namespace EducationProgram;

use ORMTable;

/**
 * Main extension class, acts as dependency injection container look-alike.
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
 * @file
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class Extension {

	/**
	 * @since 0.3
	 *
	 * @var Settings
	 */
	protected $settings;

	/**
	 * Constructor
	 *
	 * @since 0.3
	 *
	 * @param Settings $settings
	 */
	public function __construct( Settings $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Returns a new article ORMTable.
	 *
	 * @since 0.3
	 *
	 * @return ORMTable
	 */
	public function newArticleTable() {
		return new ORMTable(
			'ep_articles',
			array(
				'id' => 'id',

				'course_id' => 'int',
				'user_id' => 'int',
				'page_id' => 'int',
				'page_title' => 'str',
				'reviewers' => 'array',
			),
			array(
				'reviewers' => array(),
			),
			'EducationProgram\Article',
			'article_'
		);
	}

	/**
	 * Global instance access.
	 *
	 * This is evil and should not be used except in intermediate steps during
	 * refactoring aimed at killing dependency pulling code.
	 *
	 * @since 0.3
	 * @deprecated since 0.3
	 *
	 * @return Extension
	 */
	public static function globalInstance() {
		static $instance = null;

		if ( $instance === null ) {
			$instance = new static( Settings::newFromGlobals( $GLOBALS ) );
		}

		return $instance;
	}

}
