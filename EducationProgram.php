<?php

/**
 * Initialization file for the Education Program extension.
 *
 * Documentation:	 		https://www.mediawiki.org/wiki/Extension:Education_Program
 * Support					https://www.mediawiki.org/wiki/Extension_talk:Education_Program
 * Source code:				http://svn.wikimedia.org/viewvc/mediawiki/trunk/extensions/EducationProgram
 *
 * The source code makes use of a number of terms different from but corresponding to those in the UI:
 * * Org instead of Institution
 * * MC for master course
 * * CA for campus ambassador
 * * OA for online ambassador
 *
 * @file EducationProgram.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v3+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */

/**
 * This documentation group collects source code files belonging to Education Program.
 *
 * @defgroup EducationProgram Education Program
 */

if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'Not an entry point.' );
}

if ( version_compare( $wgVersion, '1.18c', '<' ) ) { // Needs to be 1.18c because version_compare() works in confusing ways.
	die( '<b>Error:</b> Education Program requires MediaWiki 1.18 or above.' );
}

if ( !array_key_exists( 'CountryNames', $wgAutoloadClasses ) ) { // No version constant to check against :/
	die( '<b>Error:</b> Education Program depends on the <a href="https://www.mediawiki.org/wiki/Extension:CLDR">CLDR</a> extension.' );
}

define( 'EP_VERSION', '0.1 alpha' );

$wgExtensionCredits['other'][] = array(
	'path' => __FILE__,
	'name' => 'Education Program',
	'version' => EP_VERSION,
	'author' => array(
		'[http://www.mediawiki.org/wiki/User:Jeroen_De_Dauw Jeroen De Dauw]',
	),
	'url' => 'https://www.mediawiki.org/wiki/Extension:Education_Program',
	'descriptionmsg' => 'educationprogram-desc'
);

// i18n
$wgExtensionMessagesFiles['EducationProgram'] 		= dirname( __FILE__ ) . '/EducationProgram.i18n.php';
$wgExtensionMessagesFiles['EducationProgramAlias']	= dirname( __FILE__ ) . '/EducationProgram.i18n.alias.php';

// Autoloading
$wgAutoloadClasses['EPHooks'] 						= dirname( __FILE__ ) . '/EducationProgram.hooks.php';
$wgAutoloadClasses['EPSettings'] 					= dirname( __FILE__ ) . '/EducationProgram.settings.php';

$wgAutoloadClasses['ApiDeleteEducation'] 			= dirname( __FILE__ ) . '/api/ApiDeleteEducation.php';
$wgAutoloadClasses['ApiInstructor'] 				= dirname( __FILE__ ) . '/api/ApiInstructor.php';
$wgAutoloadClasses['ApiRefreshEducation'] 			= dirname( __FILE__ ) . '/api/ApiRefreshEducation.php';

$wgAutoloadClasses['EPCourse'] 						= dirname( __FILE__ ) . '/includes/EPCourse.php';
$wgAutoloadClasses['EPCoursePager'] 				= dirname( __FILE__ ) . '/includes/EPCoursePager.php';
$wgAutoloadClasses['EPMC'] 							= dirname( __FILE__ ) . '/includes/EPMC.php';
$wgAutoloadClasses['EPMCPager'] 					= dirname( __FILE__ ) . '/includes/EPMCPager.php';
$wgAutoloadClasses['EPDBObject'] 					= dirname( __FILE__ ) . '/includes/EPDBObject.php';
$wgAutoloadClasses['EPInstructor'] 					= dirname( __FILE__ ) . '/includes/EPInstructor.php';
$wgAutoloadClasses['EPLogFormatter'] 				= dirname( __FILE__ ) . '/includes/EPLogFormatter.php';
$wgAutoloadClasses['EPOrg'] 						= dirname( __FILE__ ) . '/includes/EPOrg.php';
$wgAutoloadClasses['EPOrgPager'] 					= dirname( __FILE__ ) . '/includes/EPOrgPager.php';
$wgAutoloadClasses['EPPager'] 						= dirname( __FILE__ ) . '/includes/EPPager.php';
$wgAutoloadClasses['EPStudent'] 					= dirname( __FILE__ ) . '/includes/EPStudent.php';
$wgAutoloadClasses['EPStudentPager'] 				= dirname( __FILE__ ) . '/includes/EPStudentPager.php';
$wgAutoloadClasses['EPUtils'] 						= dirname( __FILE__ ) . '/includes/EPUtils.php';
$wgAutoloadClasses['EPOA'] 							= dirname( __FILE__ ) . '/includes/EPOA.php';
$wgAutoloadClasses['EPOAPager'] 					= dirname( __FILE__ ) . '/includes/EPOAPager.php';
$wgAutoloadClasses['EPCA'] 							= dirname( __FILE__ ) . '/includes/EPCA.php';
$wgAutoloadClasses['EPCAPager'] 					= dirname( __FILE__ ) . '/includes/EPCAPager.php';
$wgAutoloadClasses['EPHTMLDateField'] 				= dirname( __FILE__ ) . '/includes/EPHTMLDateField.php';
$wgAutoloadClasses['EPRevision'] 				    = dirname( __FILE__ ) . '/includes/EPRevision.php';

$wgAutoloadClasses['SpecialCourse'] 				= dirname( __FILE__ ) . '/specials/SpecialCourse.php';
$wgAutoloadClasses['SpecialCourses'] 				= dirname( __FILE__ ) . '/specials/SpecialCourses.php';
$wgAutoloadClasses['SpecialEditCourse'] 			= dirname( __FILE__ ) . '/specials/SpecialEditCourse.php';
$wgAutoloadClasses['SpecialEditInstitution'] 		= dirname( __FILE__ ) . '/specials/SpecialEditInstitution.php';
$wgAutoloadClasses['SpecialEditMasterCourse'] 		= dirname( __FILE__ ) . '/specials/SpecialEditMasterCourse.php';
$wgAutoloadClasses['SpecialEducationProgram'] 		= dirname( __FILE__ ) . '/specials/SpecialEducationProgram.php';
$wgAutoloadClasses['SpecialEPFormPage'] 			= dirname( __FILE__ ) . '/specials/SpecialEPFormPage.php';
$wgAutoloadClasses['SpecialEPPage'] 				= dirname( __FILE__ ) . '/specials/SpecialEPPage.php';
$wgAutoloadClasses['SpecialInstitution'] 			= dirname( __FILE__ ) . '/specials/SpecialInstitution.php';
$wgAutoloadClasses['SpecialInstitutions'] 			= dirname( __FILE__ ) . '/specials/SpecialInstitutions.php';
$wgAutoloadClasses['SpecialMyCourses'] 				= dirname( __FILE__ ) . '/specials/SpecialMyCourses.php';
$wgAutoloadClasses['SpecialStudent'] 				= dirname( __FILE__ ) . '/specials/SpecialStudent.php';
$wgAutoloadClasses['SpecialStudents'] 				= dirname( __FILE__ ) . '/specials/SpecialStudents.php';
$wgAutoloadClasses['SpecialMasterCourse'] 			= dirname( __FILE__ ) . '/specials/SpecialMasterCourse.php';
$wgAutoloadClasses['SpecialMasterCourses'] 			= dirname( __FILE__ ) . '/specials/SpecialMasterCourses.php';
$wgAutoloadClasses['SpecialEnroll'] 				= dirname( __FILE__ ) . '/specials/SpecialEnroll.php';
$wgAutoloadClasses['SpecialCAs'] 					= dirname( __FILE__ ) . '/specials/SpecialCAs.php';
$wgAutoloadClasses['SpecialOAs'] 					= dirname( __FILE__ ) . '/specials/SpecialOAs.php';
$wgAutoloadClasses['SpecialCA'] 					= dirname( __FILE__ ) . '/specials/SpecialCA.php';
$wgAutoloadClasses['SpecialOA'] 					= dirname( __FILE__ ) . '/specials/SpecialOA.php';
$wgAutoloadClasses['SpecialEPHistory'] 				= dirname( __FILE__ ) . '/specials/SpecialEPHistory.php';
$wgAutoloadClasses['SpecialCourseHistory'] 			= dirname( __FILE__ ) . '/specials/SpecialCourseHistory.php';

// Special pages
$wgSpecialPages['MyCourses'] 						= 'SpecialMyCourses';
$wgSpecialPages['Institution'] 						= 'SpecialInstitution';
$wgSpecialPages['Institutions'] 					= 'SpecialInstitutions';
$wgSpecialPages['Student'] 							= 'SpecialStudent';
$wgSpecialPages['Students'] 						= 'SpecialStudents';
$wgSpecialPages['Course'] 							= 'SpecialCourse';
$wgSpecialPages['Courses'] 							= 'SpecialCourses';
$wgSpecialPages['MasterCourse'] 					= 'SpecialMasterCourse';
$wgSpecialPages['MasterCourses'] 					= 'SpecialMasterCourses';
$wgSpecialPages['EducationProgram'] 				= 'SpecialEducationProgram';
$wgSpecialPages['EditCourse'] 						= 'SpecialEditCourse';
$wgSpecialPages['EditInstitution'] 					= 'SpecialEditInstitution';
$wgSpecialPages['EditMasterCourse'] 				= 'SpecialEditMasterCourse';
$wgSpecialPages['Enroll'] 							= 'SpecialEnroll';
$wgSpecialPages['CampusAmbassadors'] 				= 'SpecialCAs';
$wgSpecialPages['OnlineAmbassadors'] 				= 'SpecialOAs';
$wgSpecialPages['CampusAmbassador'] 				= 'SpecialCA';
$wgSpecialPages['OnlineAmbassador'] 				= 'SpecialOA';
$wgSpecialPages['CourseHistory'] 				    = 'SpecialCourseHistory';

$wgSpecialPageGroups['MyCourses'] 					= 'education';
$wgSpecialPageGroups['Institution'] 				= 'education';
$wgSpecialPageGroups['Institutions'] 				= 'education';
$wgSpecialPageGroups['Student'] 					= 'education';
$wgSpecialPageGroups['Students'] 					= 'education';
$wgSpecialPageGroups['Course'] 						= 'education';
$wgSpecialPageGroups['Courses'] 					= 'education';
$wgSpecialPageGroups['MasterCourse'] 				= 'education';
$wgSpecialPageGroups['MasterCourses'] 				= 'education';
$wgSpecialPageGroups['EducationProgram'] 			= 'education';
$wgSpecialPageGroups['EditCourse'] 					= 'education';
$wgSpecialPageGroups['EditInstitution'] 			= 'education';
$wgSpecialPageGroups['EditMasterCourse'] 			= 'education';
$wgSpecialPageGroups['CampusAmbassadors'] 			= 'education';
$wgSpecialPageGroups['OnlineAmbassadors'] 			= 'education';
$wgSpecialPageGroups['CampusAmbassador'] 			= 'education';
$wgSpecialPageGroups['OnlineAmbassador'] 			= 'education';
$wgSpecialPageGroups['CourseHistory'] 		    	= 'education';

// DB object classes
$egEPDBObjects = array();
$egEPDBObjects['EPRevision'] = array( 'table' => 'ep_revisions', 'prefix' => 'rev_' );
$egEPDBObjects['EPOrg'] = array( 'table' => 'ep_orgs', 'prefix' => 'org_' );
$egEPDBObjects['EPMC'] = array( 'table' => 'ep_mcs', 'prefix' => 'mc_' );
$egEPDBObjects['EPCourse'] = array( 'table' => 'ep_courses', 'prefix' => 'course_' );
$egEPDBObjects['EPStudent'] = array( 'table' => 'ep_students', 'prefix' => 'student_' );
$egEPDBObjects['EPOA'] = array( 'table' => 'ep_oas', 'prefix' => 'oa_' );
$egEPDBObjects['EPCA'] = array( 'table' => 'ep_cas', 'prefix' => 'ca_' );
$egEPDBObjects[] = array( 'table' => 'ep_students_per_course', 'prefix' => 'spc_' );

// API
$wgAPIModules['deleteeducation'] 					= 'ApiDeleteEducation';
$wgAPIModules['instructor'] 						= 'ApiInstructor';
$wgAPIModules['refresheducation'] 					= 'ApiRefreshEducation';

// Hooks
$wgHooks['LoadExtensionSchemaUpdates'][] 			= 'EPHooks::onSchemaUpdate';
$wgHooks['UnitTestsList'][] 						= 'EPHooks::registerUnitTests';
$wgHooks['PersonalUrls'][] 							= 'EPHooks::onPersonalUrls';
$wgHooks['GetPreferences'][] 						= 'EPHooks::onGetPreferences';
$wgHooks['SkinTemplateNavigation::SpecialPage'][] 	= 'EPHooks::onSpecialPageTabs';

// Logging
$wgLogTypes[] = 'institution';
$wgLogTypes[] = 'course';
$wgLogTypes[] = 'mc';
$wgLogTypes[] = 'student';
$wgLogTypes[] = 'ambassador';
$wgLogTypes[] = 'instructor';

if ( array_key_exists( 'LogFormatter', $wgAutoloadLocalClasses ) ) {
	$wgLogActionsHandlers['institution/*'] = 'EPLogFormatter';
	$wgLogActionsHandlers['course/*'] = 'EPLogFormatter';
	$wgLogActionsHandlers['mc/*'] = 'EPLogFormatter';
	$wgLogActionsHandlers['student/*'] = 'EPLogFormatter';
	$wgLogActionsHandlers['ambassador/*'] = 'EPLogFormatter';
	$wgLogActionsHandlers['instructor/*'] = 'EPLogFormatter';
}
else {
	// Compatibility with MediaWiki 1.18.
	foreach ( array( 'institution', 'course', 'mc' ) as $type ) {
		foreach ( array( 'add', 'remove', 'update' ) as $action ) {
			$wgLogActionsHandlers[$type . '/' . $action] = 'EPHooks::formatLogEntry';
		}
	}
	
	foreach ( array( 'instructor', 'ambassador' ) as $type ) {
		foreach ( array( 'add', 'remove', 'selfadd', 'selfremove' ) as $action ) {
			$wgLogActionsHandlers[$type . '/' . $action] = 'EPHooks::formatLogEntry';
		}
	}
	
	$wgLogActionsHandlers['student/enroll'] = 'EPHooks::formatLogEntry';
	$wgLogActionsHandlers['student/remove'] = 'EPHooks::formatLogEntry';
	
	// Compatibility with MediaWiki 1.18.
	$wgLogNames['institution'] = 'log-name-institution';
	$wgLogNames['course'] = 'log-name-course';
	$wgLogNames['mc'] = 'log-name-mc';
	$wgLogNames['student'] = 'log-name-student';
	$wgLogNames['ambassador'] = 'log-name-ambassador';
	$wgLogNames['instructor'] = 'log-name-instructor';
	
	// Compatibility with MediaWiki 1.18.
	$wgLogHeaders['institution'] = 'log-header-institution';
	$wgLogHeaders['course'] = 'log-header-course';
	$wgLogHeaders['mc'] = 'log-header-mc';
	$wgLogHeaders['student'] = 'log-header-student';
	$wgLogHeaders['ambassador'] = 'log-header-ambassador';
	$wgLogHeaders['instructor'] = 'log-header-instructor';
}

// Rights
$wgAvailableRights[] = 'ep-org'; 			// Manage orgs
$wgAvailableRights[] = 'ep-course';			// Manage courses
$wgAvailableRights[] = 'ep-mc';				// Manage master courses
$wgAvailableRights[] = 'ep-token';			// See enrollment tokens
$wgAvailableRights[] = 'ep-enroll';			// Enroll as a student
$wgAvailableRights[] = 'ep-remstudent';		// Disassociate students from terms
$wgAvailableRights[] = 'ep-online';			// Add or remove online ambassadors from terms
$wgAvailableRights[] = 'ep-campus';			// Add or remove campus ambassadors from terms
$wgAvailableRights[] = 'ep-instructor';		// Add or remove instructors from courses
$wgAvailableRights[] = 'ep-beonline';		// Add or remove yourself as online ambassador from terms
$wgAvailableRights[] = 'ep-becampus';		// Add or remove yourself as campus ambassador from terms
$wgAvailableRights[] = 'ep-beinstructor';	// Add or remove yourself as instructor from courses

// User group rights
$wgGroupPermissions['*']['ep-enroll'] = true;
$wgGroupPermissions['*']['ep-org'] = false;
$wgGroupPermissions['*']['ep-course'] = false;
$wgGroupPermissions['*']['ep-mc'] = false;
$wgGroupPermissions['*']['ep-token'] = false;
$wgGroupPermissions['*']['ep-remstudent'] = false;
$wgGroupPermissions['*']['ep-online'] = false;
$wgGroupPermissions['*']['ep-campus'] = false;
$wgGroupPermissions['*']['ep-instructor'] = false;
$wgGroupPermissions['*']['ep-beonline'] = false;
$wgGroupPermissions['*']['ep-becampus'] = false;
$wgGroupPermissions['*']['ep-beinstructor'] = false;

$wgGroupPermissions['epstaff']['ep-org'] = true;
$wgGroupPermissions['epstaff']['ep-course'] = true;
$wgGroupPermissions['epstaff']['ep-mc'] = true;
$wgGroupPermissions['epstaff']['ep-token'] = true;
$wgGroupPermissions['epstaff']['ep-enroll'] = true;
$wgGroupPermissions['epstaff']['ep-remstudent'] = true;
$wgGroupPermissions['epstaff']['ep-online'] = true;
$wgGroupPermissions['epstaff']['ep-campus'] = true;
$wgGroupPermissions['epstaff']['ep-instructor'] = true;
$wgGroupPermissions['epstaff']['ep-beonline'] = true;
$wgGroupPermissions['epstaff']['ep-becampus'] = true;
$wgGroupPermissions['epstaff']['ep-beinstructor'] = true;

$wgGroupPermissions['epadmin']['ep-org'] = true;
$wgGroupPermissions['epadmin']['ep-course'] = true;
$wgGroupPermissions['epadmin']['ep-mc'] = true;
$wgGroupPermissions['epadmin']['ep-token'] = true;
$wgGroupPermissions['epadmin']['ep-enroll'] = true;
$wgGroupPermissions['epadmin']['ep-remstudent'] = true;
$wgGroupPermissions['epadmin']['ep-online'] = true;
$wgGroupPermissions['epadmin']['ep-campus'] = true;
$wgGroupPermissions['epadmin']['ep-instructor'] = true;
$wgGroupPermissions['epadmin']['ep-beonline'] = true;
$wgGroupPermissions['epadmin']['ep-becampus'] = true;
$wgGroupPermissions['epadmin']['ep-beinstructor'] = true;

$wgGroupPermissions['eponlineamb']['ep-org'] = true;
$wgGroupPermissions['eponlineamb']['ep-course'] = true;
$wgGroupPermissions['eponlineamb']['ep-mc'] = true;
$wgGroupPermissions['eponlineamb']['ep-token'] = true;
$wgGroupPermissions['eponlineamb']['ep-beonline'] = true;

$wgGroupPermissions['epcampamb']['ep-org'] = true;
$wgGroupPermissions['epcampamb']['ep-course'] = true;
$wgGroupPermissions['epcampamb']['ep-mc'] = true;
$wgGroupPermissions['epcampamb']['ep-token'] = true;
$wgGroupPermissions['epcampamb']['ep-becampus'] = true;

$wgGroupPermissions['epinstructor']['ep-org'] = true;
$wgGroupPermissions['epinstructor']['ep-course'] = true;
$wgGroupPermissions['epinstructor']['ep-mc'] = true;
$wgGroupPermissions['epinstructor']['ep-token'] = true;
$wgGroupPermissions['epinstructor']['ep-remstudent'] = true;
$wgGroupPermissions['epinstructor']['ep-online'] = true;
$wgGroupPermissions['epinstructor']['ep-campus'] = true;
$wgGroupPermissions['epinstructor']['ep-beinstructor'] = true;

$wgGroupPermissions['epstaff']['userrights'] = false;
$wgAddGroups['epstaff'] = array( 'epstaff', 'epadmin', 'eponlineamb', 'epcampamb', 'epinstructor' );
$wgRemoveGroups['epstaff'] = array( 'epstaff', 'epadmin', 'eponlineamb', 'epcampamb', 'epinstructor' );

$wgGroupPermissions['epadmin']['userrights'] = false;
$wgAddGroups['epadmin'] = array( 'eponlineamb', 'epcampamb', 'epinstructor' );
$wgRemoveGroups['epadmin'] = array( 'eponlineamb', 'epcampamb', 'epinstructor' );

// Resource loader modules
$moduleTemplate = array(
	'localBasePath' => dirname( __FILE__ ) . '/resources',
	'remoteExtPath' => 'EducationProgram/resources'
);

$wgResourceModules['ep.core'] = $moduleTemplate + array(
	'scripts' => array(
		'ep.js',
	),
);

$wgResourceModules['ep.api'] = $moduleTemplate + array(
	'scripts' => array(
		'ep.api.js',
	),
	'dependencies' => array(
		'ep.core',
	),
);

$wgResourceModules['ep.pager'] = $moduleTemplate + array(
	'scripts' => array(
		'ep.pager.js',
	),
	'styles' => array(
		'ep.pager.css',
	),
	'dependencies' => array(
		'ep.api',
		'mediawiki.jqueryMsg',
	),
	'messages' => array(
		'ep-pager-confirm-delete',
		'ep-pager-delete-fail',
		'ep-pager-confirm-delete-selected',
		'ep-pager-delete-selected-fail',
	),
);

$wgResourceModules['ep.datepicker'] = $moduleTemplate + array(
	'scripts' => array(
		'ep.datepicker.js',
	),
	'dependencies' => array(
		'jquery.ui.datepicker',
	),
);

$wgResourceModules['ep.formpage'] = $moduleTemplate + array(
	'scripts' => array(
		'ep.formpage.js',
	),
	'dependencies' => array(
		'jquery.ui.button',
	),
);

if ( array_key_exists( 'WikiEditorHooks', $GLOBALS['wgAutoloadClasses'] ) ) {
	$wgResourceModules['ep.formpage']['dependencies'][] = 'ext.wikiEditor.toolbar';
}

$wgResourceModules['ep.instructor'] = $moduleTemplate + array(
	'scripts' => array(
		'ep.instructor.js',
	),
	'dependencies' => array(
		'jquery.ui.dialog',
		'ep.core',
		'ep.api',
		'mediawiki.jqueryMsg',
		'mediawiki.language'
	),
	'messages' => array(
		'ep-instructor-remove-title',
		'ep-instructor-remove-button',
		'ep-instructor-removing',
		'ep-instructor-removal-success',
		'ep-instructor-close-button',
		'ep-instructor-remove-retry',
		'ep-instructor-remove-failed',
		'ep-instructor-cancel-button',
		'ep-instructor-remove-text',
		'ep-instructor-adding',
		'ep-instructor-addittion-success',
		'ep-instructor-addittion-self-success',
		'ep-instructor-add-close-button',
		'ep-instructor-add-retry',
		'ep-instructor-addittion-failed',
		'ep-instructor-add-title',
		'ep-instructor-add-button',
		'ep-instructor-add-self-button',
		'ep-instructor-add-text',
		'ep-instructor-add-self-text',
		'ep-instructor-add-self-title',
		'ep-instructor-add-cancel-button',
		'ep-instructor-summary-input',
		'ep-instructor-name-input',
		'ep-mc-no-instructors',
	),
);

unset( $moduleTemplate );

$egEPSettings = array();

# The default value for the user preferences.
$wgDefaultUserOptions['ep_showtoplink'] = false;
