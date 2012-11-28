<?php

/**
 * File defining the settings for the Education Program extension.
 * More info can be found at https://www.mediawiki.org/wiki/Extension:Education_Program#Settings
 *
 * NOTICE:
 * Changing one of these settings can be done by assigning to $egEPSettings,
 * AFTER the inclusion of the extension itself.
 *
 * @since 0.1
 *
 * @file EducationProgram.settings.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EPSettings {

	/**
	 * Returns the default values for the settings.
	 * setting name (string) => setting value (mixed)
	 *
	 * @since 0.1
	 *
	 * @return array
	 */
	protected static function getDefaultSettings() {
		global $wgExtensionAssetsPath, $wgScriptPath;

		$resourceDir = $egSWLScriptPath = $wgExtensionAssetsPath === false ? $wgScriptPath . '/extensions' : $wgExtensionAssetsPath;
		$resourceDir .= '/EducationProgram/resources/';

		return array(
			'enableTopLink' => true,
			'ambassadorPictureDomains' => array(
				'wikimedia.org'
			),
			'ambassadorCommonsUrl' => 'https://commons.wikimedia.org/wiki/Special:UploadWizard',
			'citylessCountries' => array(
				'BT', 'BV', 'IO', 'VG', 'TD', 'CX', 'CC', 'KM', 'DJ', 'GQ', 'FK', 'FX', 'TF', 'GW', 'HM', 'KI', 'YT',
				'MS', 'NR', 'NU', 'NF', 'PN', 'SH', 'PM', 'WS', 'SC', 'GS', 'SJ', 'TK', 'TP', 'TV', 'UM', 'VU', 'EH'
			),
			'ambassadorImgWidth' => 140,
			'ambassadorImgHeight' => 140,
			'recentActivityLimit' => 24 * 60 * 60,
			'resourceDir' => $resourceDir,
			'imageDir' => $resourceDir . 'images/',
			'flagWidth' => 25,
			'flagHeight' => 25,
			'countryFlags' => array(
				'US' => 'Flag of the United States.svg',
				'BR' => 'Flag of Brazil.svg',
				'CA' => 'Flag of Canada.svg',
				'IN' => 'Flag of India.svg',
				'EG' => 'Flag of Egypt.svg',
				'IT' => 'Flag of Italy.svg',
				'MK' => 'Flag of Macedonia.svg',
				'MX' => 'Flag of Mexico.svg',
				'RU' => 'Flag of Russia.svg',
				'UK' => 'Flag of the United Kingdom.svg',
				'DE' => 'Flag of Germany.svg',
				'NZ' => 'Flag of New Zealand.svg',
				'CZ' => 'Flag of the Czech Republic.svg',
			),
			'fallbackFlag' => 'Nuvola unknown flag.svg',
			'courseDescPage' => 'MediaWiki:Course description',
			'courseOrgDescPage' => '$2/$1', // $1 = org name, $2 = courseDescPage setting
			'useStudentRealNames' => false,
			'timelineDurationLimit' => 2 *24 * 60 *60,
			'timelineCountLimit' => 42,
			'timelineUserLimit' => 3,
			'dykCategory' => 'Wikipedia:Education Program Did You Know',
			'dykOrgCategory' => '$2/$1', // $1 = org name, $2 = dykCategory setting
			'enableDykSetting' => true,
			'timelineMessageLengthLimit' => 250,
			'requireRealName' => false,
			'enablePageCache' => true,
		);
	}

	/**
	 * Retruns an array with all settings after making sure they are
	 * initialized (ie set settings have been merged with the defaults).
	 * setting name (string) => setting value (mixed)
	 *
	 * @since 0.1
	 *
	 * @return array
	 */
	public static function getSettings() {
		static $settings = false;

		if ( $settings === false ) {
			$settings = array_merge(
				self::getDefaultSettings(),
				$GLOBALS['egEPSettings']
			);
		}

		return $settings;
	}

	/**
	 * Gets the value of the specified setting.
	 *
	 * @since 0.1
	 *
	 * @param string $settingName
	 *
	 * @throws MWException
	 * @return mixed
	 */
	public static function get( $settingName ) {
		$settings = self::getSettings();

		if ( !array_key_exists( $settingName, $settings ) ) {
			throw new MWException( 'Attempt to get non-existing setting "' . $settingName . '"' );
		}

		return $settings[$settingName];
	}

}
