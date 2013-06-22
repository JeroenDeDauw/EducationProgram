<?php

namespace EducationProgram;

/**
 * Container for the settings contained by this extension.
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
class Settings {

	/**
	 * @since 0.3
	 *
	 * @var array
	 */
	protected $settings;

	/**
	 * Constructor.
	 *
	 * @since 0.3
	 *
	 * @param array $settings
	 */
	public function __construct( array $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Returns the setting with the provided name.
	 * The specified setting needs to exist.
	 *
	 * @since 0.3
	 *
	 * @param string $settingName
	 *
	 * @return mixed
	 */
	public function getSetting( $settingName ) {
		return $this->settings[$settingName];
	}

	/**
	 * Gets the value of the specified setting.
	 *
	 * @since 0.1
	 * @deprecated since 0.3, use non-global state
	 *
	 * @param string $settingName
	 *
	 * @return mixed
	 */
	public static function get( $settingName ) {
		static $settings = null;

		if ( $settings === null ) {
			$settings = new self( $GLOBALS['egEPSettings'] );
		}

		return $settings->getSetting( $settingName );
	}

}
