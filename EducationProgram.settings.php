<?php

global $wgExtensionAssetsPath, $wgScriptPath;

$epResourceDir = $egSWLScriptPath = $wgExtensionAssetsPath === false ? $wgScriptPath . '/extensions' : $wgExtensionAssetsPath;
$epResourceDir .= '/EducationProgram/resources/';

$egEPSettings = array(
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
	'resourceDir' => $epResourceDir,
	'imageDir' => $epResourceDir . 'images/',
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
	'timelineDurationLimit' => 5 * 24 * 60 * 60,
	'timelineCountLimit' => 200,
	'timelineUserLimit' => 3,
	'dykCategory' => 'MyCourses Did You Know',
	'dykOrgCategory' => '$2/$1', // $1 = org name, $2 = dykCategory setting
	'enableDykSetting' => true,
	'timelineMessageLengthLimit' => 250,
	'requireRealName' => false,
	'collectRealName' => false,
	'enablePageCache' => true,
	'courseHeaderPage' => 'MediaWiki:Course header',
	'courseHeaderPageCountry' => '$2/$1', // $1 = course country name, $2 = courseHeaderPage setting
	'activityTabMaxAgeInSeconds' => 7 * 24 * 60 * 60,

	// In the user role message showing a user's roles and courses (inserted
	// into User contributions page), this is the maximum number of courses
	// to mention.
	'maxCoursesInUserRolesMessage' => 3,
);

unset( $epResourceDir );
