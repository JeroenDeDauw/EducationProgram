<?php

/**
 * Initialization file for the Education Program extension.
 *
 * Documentation:	 		https://www.mediawiki.org/wiki/Extension:Education_Program
 * Support					https://www.mediawiki.org/wiki/Extension_talk:Education_Program
 * Source code:				https://gerrit.wikimedia.org/r/gitweb?p=mediawiki/extensions/EducationProgram.git
 *
 * The source code makes use of a number of terms different from but corresponding to those in the UI:
 * * Org instead of Institution
 * * CA for campus ambassador
 * * OA for online ambassador
 * * Article is often used to refer to "article student associations" rather then the Article class.
 *
 * @file EducationProgram.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
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

if ( version_compare( $wgVersion, '1.21c', '<' ) ) { // Needs to be 1.21c because version_compare() works in confusing ways.
	die( '<strong>Error:</strong> Education Program requires MediaWiki 1.21 or above.' );
}

if ( !array_key_exists( 'CountryNames', $wgAutoloadClasses ) ) { // No version constant to check against :/
	die( '<strong>Error:</strong> Education Program depends on the <a href="https://www.mediawiki.org/wiki/Extension:CLDR">CLDR</a> extension.' );
}

define( 'EP_VERSION', '0.3 alpha' );

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
$dir = __DIR__;
$wgExtensionMessagesFiles['EducationProgram'] 		= $dir . '/EducationProgram.i18n.php';
$wgExtensionMessagesFiles['EducationProgramAlias']	= $dir . '/EducationProgram.i18n.alias.php';
$wgExtensionMessagesFiles['EPNamespaces'] 			= $dir . '/EducationProgram.i18n.ns.php';

// Autoloading
$wgAutoloadClasses['EPHooks'] 						= $dir . '/EducationProgram.hooks.php';
$wgAutoloadClasses['EPSettings'] 					= $dir . '/EducationProgram.settings.php';

// includes/actions (deriving from Action)
$wgAutoloadClasses['EditCourseAction'] 				= $dir . '/includes/actions/EditCourseAction.php';
$wgAutoloadClasses['EditOrgAction'] 				= $dir . '/includes/actions/EditOrgAction.php';
$wgAutoloadClasses['EPAction'] 						= $dir . '/includes/actions/EPAction.php';
$wgAutoloadClasses['EPAddArticleAction'] 			= $dir . '/includes/actions/EPAddArticleAction.php';
$wgAutoloadClasses['EPAddReviewerAction'] 			= $dir . '/includes/actions/EPAddReviewerAction.php';
$wgAutoloadClasses['EPDeleteAction'] 				= $dir . '/includes/actions/EPDeleteAction.php';
$wgAutoloadClasses['EPEditAction'] 					= $dir . '/includes/actions/EPEditAction.php';
$wgAutoloadClasses['EPHistoryAction'] 				= $dir . '/includes/actions/EPHistoryAction.php';
$wgAutoloadClasses['EPRemoveArticleAction'] 		= $dir . '/includes/actions/EPRemoveArticleAction.php';
$wgAutoloadClasses['EPRemoveReviewerAction'] 		= $dir . '/includes/actions/EPRemoveReviewerAction.php';
$wgAutoloadClasses['EPRemoveStudentAction'] 		= $dir . '/includes/actions/EPRemoveStudentAction.php';
$wgAutoloadClasses['EPRestoreAction'] 				= $dir . '/includes/actions/EPRestoreAction.php';
$wgAutoloadClasses['EPUndeleteAction'] 				= $dir . '/includes/actions/EPUndeleteAction.php';
$wgAutoloadClasses['EPUndoAction'] 					= $dir . '/includes/actions/EPUndoAction.php';
$wgAutoloadClasses['EPViewAction'] 					= $dir . '/includes/actions/EPViewAction.php';
$wgAutoloadClasses['ViewCourseAction'] 				= $dir . '/includes/actions/ViewCourseAction.php';
$wgAutoloadClasses['ViewOrgAction'] 				= $dir . '/includes/actions/ViewOrgAction.php';

// includes/api (deriving from ApiBase)
$wgAutoloadClasses['ApiDeleteEducation'] 			= $dir . '/includes/api/ApiDeleteEducation.php';
$wgAutoloadClasses['ApiEnlist'] 					= $dir . '/includes/api/ApiEnlist.php';
$wgAutoloadClasses['ApiRefreshEducation'] 			= $dir . '/includes/api/ApiRefreshEducation.php';

// includes/pagers (implementing Pager)
$wgAutoloadClasses['EPArticlePager'] 				= $dir . '/includes/pagers/EPArticlePager.php';
$wgAutoloadClasses['EPArticleTable'] 				= $dir . '/includes/pagers/EPArticleTable.php';
$wgAutoloadClasses['EPCAPager'] 					= $dir . '/includes/pagers/EPCAPager.php';
$wgAutoloadClasses['EPCoursePager'] 				= $dir . '/includes/pagers/EPCoursePager.php';
$wgAutoloadClasses['EPOAPager'] 					= $dir . '/includes/pagers/EPOAPager.php';
$wgAutoloadClasses['EPOrgPager'] 					= $dir . '/includes/pagers/EPOrgPager.php';
$wgAutoloadClasses['EPPager'] 						= $dir . '/includes/pagers/EPPager.php';
$wgAutoloadClasses['EPRevisionPager'] 				= $dir . '/includes/pagers/EPRevisionPager.php';
$wgAutoloadClasses['EPStudentPager'] 				= $dir . '/includes/pagers/EPStudentPager.php';
$wgAutoloadClasses['EPStudentActivityPager'] 		= $dir . '/includes/pagers/EPStudentActivityPager.php';

// includes/pages (here core is a mess :)
$wgAutoloadClasses['CoursePage'] 					= $dir . '/includes/pages/CoursePage.php';
$wgAutoloadClasses['EPPage'] 						= $dir . '/includes/pages/EPPage.php';
$wgAutoloadClasses['OrgPage'] 						= $dir . '/includes/pages/OrgPage.php';

// includes/rows (deriving from ORMRow)
$wgAutoloadClasses['EPArticle'] 					= $dir . '/includes/rows/EPArticle.php';
$wgAutoloadClasses['EPCA'] 							= $dir . '/includes/rows/EPCA.php';
$wgAutoloadClasses['EPCourse'] 						= $dir . '/includes/rows/EPCourse.php';
$wgAutoloadClasses['EPEvent'] 						= $dir . '/includes/rows/EPEvent.php';
$wgAutoloadClasses['EPInstructor'] 					= $dir . '/includes/rows/EPInstructor.php';
$wgAutoloadClasses['EPOA'] 							= $dir . '/includes/rows/EPOA.php';
$wgAutoloadClasses['EPOrg'] 						= $dir . '/includes/rows/EPOrg.php';
$wgAutoloadClasses['EPPageObject'] 					= $dir . '/includes/rows/EPPageObject.php';
$wgAutoloadClasses['EPRevision'] 					= $dir . '/includes/rows/EPRevision.php';
$wgAutoloadClasses['EPRevisionedObject'] 			= $dir . '/includes/rows/EPRevisionedObject.php';
$wgAutoloadClasses['EPStudent'] 					= $dir . '/includes/rows/EPStudent.php';

// includes/specials (deriving from SpecialPage)
$wgAutoloadClasses['SpecialCourses'] 				= $dir . '/includes/specials/SpecialCourses.php';
$wgAutoloadClasses['SpecialEducationProgram'] 		= $dir . '/includes/specials/SpecialEducationProgram.php';
$wgAutoloadClasses['SpecialEPPage'] 				= $dir . '/includes/specials/SpecialEPPage.php';
$wgAutoloadClasses['SpecialInstitutions'] 			= $dir . '/includes/specials/SpecialInstitutions.php';
$wgAutoloadClasses['SpecialMyCourses'] 				= $dir . '/includes/specials/SpecialMyCourses.php';
$wgAutoloadClasses['SpecialStudent'] 				= $dir . '/includes/specials/SpecialStudent.php';
$wgAutoloadClasses['SpecialStudents'] 				= $dir . '/includes/specials/SpecialStudents.php';
$wgAutoloadClasses['SpecialEnroll'] 				= $dir . '/includes/specials/SpecialEnroll.php';
$wgAutoloadClasses['SpecialDisenroll'] 				= $dir . '/includes/specials/SpecialDisenroll.php';
$wgAutoloadClasses['SpecialCAs'] 					= $dir . '/includes/specials/SpecialCAs.php';
$wgAutoloadClasses['SpecialOAs'] 					= $dir . '/includes/specials/SpecialOAs.php';
$wgAutoloadClasses['SpecialOAProfile'] 				= $dir . '/includes/specials/SpecialOAProfile.php';
$wgAutoloadClasses['SpecialCAProfile'] 				= $dir . '/includes/specials/SpecialCAProfile.php';
$wgAutoloadClasses['SpecialAmbassadorProfile'] 		= $dir . '/includes/specials/SpecialAmbassadorProfile.php';
$wgAutoloadClasses['SpecialStudentActivity'] 		= $dir . '/includes/specials/SpecialStudentActivity.php';
$wgAutoloadClasses['SpecialArticles'] 				= $dir . '/includes/specials/SpecialArticles.php';
$wgAutoloadClasses['SpecialManageCourses'] 			= $dir . '/includes/specials/SpecialManageCourses.php';

// includes/tables (deriving from ORMTable)
$wgAutoloadClasses['EPArticles'] 					= $dir . '/includes/tables/EPArticles.php';
$wgAutoloadClasses['EPCAs'] 						= $dir . '/includes/tables/EPCAs.php';
$wgAutoloadClasses['EPCourses'] 					= $dir . '/includes/tables/EPCourses.php';
$wgAutoloadClasses['EPEvents'] 						= $dir . '/includes/tables/EPEvents.php';
$wgAutoloadClasses['EPInstructors'] 				= $dir . '/includes/tables/EPInstructors.php';
$wgAutoloadClasses['EPOAs'] 						= $dir . '/includes/tables/EPOAs.php';
$wgAutoloadClasses['EPOrgs'] 						= $dir . '/includes/tables/EPOrgs.php';
$wgAutoloadClasses['EPPageTable'] 					= $dir . '/includes/tables/EPPageTable.php';
$wgAutoloadClasses['EPRevisions'] 					= $dir . '/includes/tables/EPRevisions.php';
$wgAutoloadClasses['EPStudents'] 					= $dir . '/includes/tables/EPStudents.php';

// includes
$wgAutoloadClasses['EPLogFormatter'] 				= $dir . '/includes/EPLogFormatter.php';
$wgAutoloadClasses['EPRoleChangeFormatter'] 		= $dir . '/includes/EPLogFormatter.php';
$wgAutoloadClasses['EPArticleFormatter'] 			= $dir . '/includes/EPLogFormatter.php';
$wgAutoloadClasses['EPUtils'] 						= $dir . '/includes/EPUtils.php';
$wgAutoloadClasses['EPHTMLDateField'] 				= $dir . '/includes/EPHTMLDateField.php';
$wgAutoloadClasses['EPHTMLCombobox'] 				= $dir . '/includes/EPHTMLCombobox.php';
$wgAutoloadClasses['EPFailForm'] 					= $dir . '/includes/EPFailForm.php';
$wgAutoloadClasses['EPIRole'] 						= $dir . '/includes/EPIRole.php';
$wgAutoloadClasses['EPRoleObject'] 					= $dir . '/includes/EPRoleObject.php';
$wgAutoloadClasses['EPRevisionAction'] 				= $dir . '/includes/EPRevisionAction.php';
$wgAutoloadClasses['EPRevisionDiff'] 				= $dir . '/includes/EPRevisionDiff.php';
$wgAutoloadClasses['EPDiffTable'] 					= $dir . '/includes/EPDiffTable.php';
$wgAutoloadClasses['EPMenu'] 						= $dir . '/includes/EPMenu.php';
$wgAutoloadClasses['EPTimeline'] 					= $dir . '/includes/EPTimeline.php';
$wgAutoloadClasses['EPTimelineGroup'] 				= $dir . '/includes/EPTimelineGroup.php';

// Special pages
$wgSpecialPages['MyCourses'] 						= 'SpecialMyCourses';
$wgSpecialPages['Institutions'] 					= 'SpecialInstitutions';
$wgSpecialPages['Student'] 							= 'SpecialStudent';
$wgSpecialPages['Students'] 						= 'SpecialStudents';
$wgSpecialPages['Courses'] 							= 'SpecialCourses';
//$wgSpecialPages['EducationProgram'] 				= 'SpecialEducationProgram';
$wgSpecialPages['Enroll'] 							= 'SpecialEnroll';
$wgSpecialPages['Disenroll'] 						= 'SpecialDisenroll';
$wgSpecialPages['CampusAmbassadors'] 				= 'SpecialCAs';
$wgSpecialPages['OnlineAmbassadors'] 				= 'SpecialOAs';
$wgSpecialPages['CampusAmbassadorProfile'] 			= 'SpecialCAProfile';
$wgSpecialPages['OnlineAmbassadorProfile'] 			= 'SpecialOAProfile';
$wgSpecialPages['StudentActivity'] 					= 'SpecialStudentActivity';
$wgSpecialPages['Articles'] 						= 'SpecialArticles';
$wgSpecialPages['ManageCourses'] 					= 'SpecialManageCourses';

$wgSpecialPageGroups['MyCourses'] 					= 'education';
$wgSpecialPageGroups['Institutions'] 				= 'education';
$wgSpecialPageGroups['Student'] 					= 'education';
$wgSpecialPageGroups['Students'] 					= 'education';
$wgSpecialPageGroups['Courses'] 					= 'education';
//$wgSpecialPageGroups['EducationProgram'] 			= 'education';
$wgSpecialPageGroups['CampusAmbassadors'] 			= 'education';
$wgSpecialPageGroups['OnlineAmbassadors'] 			= 'education';
$wgSpecialPageGroups['CampusAmbassadorProfile'] 	= 'education';
$wgSpecialPageGroups['OnlineAmbassadorProfile'] 	= 'education';
$wgSpecialPageGroups['Enroll'] 						= 'education';
$wgSpecialPageGroups['Disenroll'] 					= 'education';
$wgSpecialPageGroups['StudentActivity'] 			= 'education';
$wgSpecialPageGroups['Articles'] 					= 'education';
$wgSpecialPageGroups['ManageCourses'] 				= 'education';

define( 'EP_STUDENT', 0 );
define( 'EP_INSTRUCTOR', 1 );
define( 'EP_OA', 2 );
define( 'EP_CA', 3 );

// API
$wgAPIModules['deleteeducation'] 					= 'ApiDeleteEducation';
$wgAPIModules['enlist'] 							= 'ApiEnlist';
$wgAPIModules['refresheducation'] 					= 'ApiRefreshEducation';

// Hooks
$wgHooks['LoadExtensionSchemaUpdates'][] 			= 'EPHooks::onSchemaUpdate';
$wgHooks['UnitTestsList'][] 						= 'EPHooks::registerUnitTests';
$wgHooks['PersonalUrls'][] 							= 'EPHooks::onPersonalUrls';
$wgHooks['GetPreferences'][] 						= 'EPHooks::onGetPreferences';
$wgHooks['SkinTemplateNavigation'][] 				= 'EPHooks::onPageTabs';
$wgHooks['SkinTemplateNavigation::SpecialPage'][] 	= 'EPHooks::onSpecialPageTabs';
$wgHooks['ArticleFromTitle'][] 						= 'EPHooks::onArticleFromTitle';
$wgHooks['CanonicalNamespaces'][] 					= 'EPHooks::onCanonicalNamespaces';
$wgHooks['TitleIsAlwaysKnown'][] 					= 'EPHooks::onTitleIsAlwaysKnown';
$wgHooks['AbortMove'][] 							= 'EPHooks::onAbortMove';
$wgHooks['NewRevisionFromEditComplete'][] 			= 'EPHooks::onNewRevisionFromEditComplete';
$wgHooks['NamespaceIsMovable'][] 					= 'EPHooks::onNamespaceIsMovable';


// Actions
$wgActions['epremarticle'] = 'EPRemoveArticleAction';
$wgActions['epremstudent'] = 'EPRemoveStudentAction';
$wgActions['epremreviewer'] = 'EPRemoveReviewerAction';
$wgActions['epaddarticle'] = 'EPAddArticleAction';
$wgActions['epaddreviewer'] = 'EPAddReviewerAction';
$wgActions['epundo'] = 'EPUndoAction';
$wgActions['eprestore'] = 'EPRestoreAction';
$wgActions['epundelete'] = 'EPUndeleteAction';

// Logging
$wgLogTypes[] = 'institution';
$wgLogTypes[] = 'course';
$wgLogTypes[] = 'student';
$wgLogTypes[] = 'online';
$wgLogTypes[] = 'campus';
$wgLogTypes[] = 'instructor';

$wgLogActionsHandlers['institution/*'] = 'EPLogFormatter';
$wgLogActionsHandlers['course/*'] = 'EPLogFormatter';
$wgLogActionsHandlers['student/*'] = 'EPLogFormatter';
$wgLogActionsHandlers['student/add'] = 'EPRoleChangeFormatter';
$wgLogActionsHandlers['student/remove'] = 'EPRoleChangeFormatter';
$wgLogActionsHandlers['online/*'] = 'EPLogFormatter';
$wgLogActionsHandlers['online/add'] = 'EPRoleChangeFormatter';
$wgLogActionsHandlers['online/remove'] = 'EPRoleChangeFormatter';
$wgLogActionsHandlers['campus/*'] = 'EPLogFormatter';
$wgLogActionsHandlers['campus/add'] = 'EPRoleChangeFormatter';
$wgLogActionsHandlers['campus/remove'] = 'EPRoleChangeFormatter';
$wgLogActionsHandlers['instructor/*'] = 'EPLogFormatter';
$wgLogActionsHandlers['instructor/add'] = 'EPRoleChangeFormatter';
$wgLogActionsHandlers['instructor/remove'] = 'EPRoleChangeFormatter';
$wgLogActionsHandlers['eparticle/*'] = 'EPArticleFormatter';

// Rights
$wgAvailableRights[] = 'ep-org'; 			// Manage orgs
$wgAvailableRights[] = 'ep-course';			// Manage courses
$wgAvailableRights[] = 'ep-token';			// See enrollment tokens
$wgAvailableRights[] = 'ep-enroll';			// Enroll as a student
$wgAvailableRights[] = 'ep-remstudent';		// Disassociate students from terms
$wgAvailableRights[] = 'ep-online';			// Add or remove online ambassadors from terms
$wgAvailableRights[] = 'ep-campus';			// Add or remove campus ambassadors from terms
$wgAvailableRights[] = 'ep-instructor';		// Add or remove instructors from courses
$wgAvailableRights[] = 'ep-beonline';		// Add or remove yourself as online ambassador from terms
$wgAvailableRights[] = 'ep-becampus';		// Add or remove yourself as campus ambassador from terms
$wgAvailableRights[] = 'ep-beinstructor';	// Add or remove yourself as instructor from courses
$wgAvailableRights[] = 'ep-bereviewer';		// Add or remove yourself as reviewer from articles
$wgAvailableRights[] = 'ep-remreviewer';	// Remove reviewers from articles
$wgAvailableRights[] = 'ep-bulkdelorgs';	// Bulk delete institutions
$wgAvailableRights[] = 'ep-bulkdelcourses';	// Bulk delete courses
$wgAvailableRights[] = 'ep-remarticle';		// Remove artiles (from being student associated)
$wgAvailableRights[] = 'ep-addstudent';		// Enroll users as student


// User group rights
$wgGroupPermissions['*']['ep-enroll'] = true;
$wgGroupPermissions['*']['ep-org'] = false;
$wgGroupPermissions['*']['ep-course'] = false;
$wgGroupPermissions['*']['ep-token'] = false;
$wgGroupPermissions['*']['ep-remstudent'] = false;
$wgGroupPermissions['*']['ep-online'] = false;
$wgGroupPermissions['*']['ep-campus'] = false;
$wgGroupPermissions['*']['ep-instructor'] = false;
$wgGroupPermissions['*']['ep-beonline'] = false;
$wgGroupPermissions['*']['ep-becampus'] = false;
$wgGroupPermissions['*']['ep-beinstructor'] = false;
$wgGroupPermissions['*']['ep-bereviewer'] = true;
$wgGroupPermissions['*']['ep-remreviewer'] = false;
$wgGroupPermissions['*']['ep-bulkdelorgs'] = false;
$wgGroupPermissions['*']['ep-bulkdelcourses'] = false;
$wgGroupPermissions['*']['ep-remarticle'] = false;
$wgGroupPermissions['*']['ep-addstudent'] = false;

$wgGroupPermissions['sysop']['ep-org'] = true;
$wgGroupPermissions['sysop']['ep-course'] = true;
$wgGroupPermissions['sysop']['ep-token'] = true;
$wgGroupPermissions['sysop']['ep-enroll'] = true;
$wgGroupPermissions['sysop']['ep-remstudent'] = true;
$wgGroupPermissions['sysop']['ep-online'] = true;
$wgGroupPermissions['sysop']['ep-campus'] = true;
$wgGroupPermissions['sysop']['ep-instructor'] = true;
$wgGroupPermissions['sysop']['ep-beonline'] = true;
$wgGroupPermissions['sysop']['ep-becampus'] = true;
$wgGroupPermissions['sysop']['ep-beinstructor'] = true;
$wgGroupPermissions['sysop']['ep-bereviewer'] = true;
$wgGroupPermissions['sysop']['ep-remreviewer'] = true;
$wgGroupPermissions['sysop']['ep-bulkdelorgs'] = true;
$wgGroupPermissions['sysop']['ep-bulkdelcourses'] = true;
$wgGroupPermissions['sysop']['ep-remarticle'] = true;
$wgGroupPermissions['sysop']['ep-addstudent'] = true;

$wgGroupPermissions['epcoordinator']['ep-org'] = true;
$wgGroupPermissions['epcoordinator']['ep-course'] = true;
$wgGroupPermissions['epcoordinator']['ep-token'] = true;
$wgGroupPermissions['epcoordinator']['ep-enroll'] = true;
$wgGroupPermissions['epcoordinator']['ep-remstudent'] = true;
$wgGroupPermissions['epcoordinator']['ep-campus'] = true;
$wgGroupPermissions['epcoordinator']['ep-instructor'] = true;
$wgGroupPermissions['epcoordinator']['ep-becampus'] = true;
$wgGroupPermissions['epcoordinator']['ep-beinstructor'] = true;
$wgGroupPermissions['epcoordinator']['ep-bereviewer'] = true;
$wgGroupPermissions['epcoordinator']['ep-remreviewer'] = true;
$wgGroupPermissions['epcoordinator']['ep-bulkdelcourses'] = true;
$wgGroupPermissions['epcoordinator']['ep-remarticle'] = true;
$wgGroupPermissions['epcoordinator']['ep-addstudent'] = true;

$wgGroupPermissions['eponline']['ep-org'] = true;
$wgGroupPermissions['eponline']['ep-course'] = true;
$wgGroupPermissions['eponline']['ep-token'] = true;
$wgGroupPermissions['eponline']['ep-beonline'] = true;
$wgGroupPermissions['eponline']['ep-remarticle'] = true;

$wgGroupPermissions['epcampus']['ep-org'] = true;
$wgGroupPermissions['epcampus']['ep-course'] = true;
$wgGroupPermissions['epcampus']['ep-token'] = true;
$wgGroupPermissions['epcampus']['ep-becampus'] = true;
$wgGroupPermissions['epcampus']['ep-remarticle'] = true;

$wgGroupPermissions['epinstructor']['ep-org'] = true;
$wgGroupPermissions['epinstructor']['ep-course'] = true;
$wgGroupPermissions['epinstructor']['ep-token'] = true;
$wgGroupPermissions['epinstructor']['ep-beinstructor'] = true;
$wgGroupPermissions['epinstructor']['ep-remstudent'] = true;
$wgGroupPermissions['epinstructor']['ep-remarticle'] = true;

$wgGroupPermissions['epcoordinator']['userrights'] = false;

$wgAddGroups['epcoordinator'] = array( 'eponline', 'epcampus', 'epinstructor' );
$wgRemoveGroups['epcoordinator'] = array( 'eponline', 'epcampus', 'epinstructor' );
$wgAddGroups['sysop'] = array_merge( $wgAddGroups['sysop'], array( 'eponline', 'epcampus', 'epinstructor', 'epcoordinator' ) );
$wgRemoveGroups['sysop'] = array_merge( $wgRemoveGroups['sysop'], array( 'eponline', 'epcampus', 'epinstructor', 'epcoordinator' ) );

// Namespaces
define( 'EP_NS',					442 + 4 );
define( 'EP_NS_TALK', 				442 + 5 );

// Resource loader modules
$moduleTemplate = array(
	'localBasePath' => $dir . '/resources',
	'remoteExtPath' => 'EducationProgram/resources'
);

$wgResourceModules['ep.core'] = $moduleTemplate + array(
	'scripts' => array(
		'ep.js',
	),
	'dependencies' => array(
		'mediawiki.jqueryMsg',
		'mediawiki.language',
	),
);

$wgResourceModules['ep.api'] = $moduleTemplate + array(
	'scripts' => array(
		'ep.api.js',
	),
	'dependencies' => array(
		'mediawiki.user',
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
		'jquery.ui.dialog',
	),
	'messages' => array(
		'ep-pager-confirm-delete',
		'ep-pager-delete-fail',
		'ep-pager-confirm-delete-selected',
		'ep-pager-delete-selected-fail',
	),
);

$wgResourceModules['ep.pager.course'] = $moduleTemplate + array(
	'messages' => array(
		'ep-pager-cancel-button-course',
		'ep-pager-delete-button-course',
		'ep-pager-confirm-delete-course',
		'ep-pager-confirm-message-course',
		'ep-pager-confirm-message-course-many',
		'ep-pager-retry-button-course',
		'ep-pager-summary-message-course',
	),
	'dependencies' => array(
		'ep.pager',
	),
);

$wgResourceModules['ep.pager.org'] = $moduleTemplate + array(
	'messages' => array(
		'ep-pager-cancel-button-org',
		'ep-pager-delete-button-org',
		'ep-pager-confirm-delete-org',
		'ep-pager-confirm-message-org',
		'ep-pager-confirm-message-org-many',
		'ep-pager-retry-button-org',
		'ep-pager-summary-message-org',
	),
	'dependencies' => array(
		'ep.pager',
	),
);

$wgResourceModules['ep.datepicker'] = $moduleTemplate + array(
	'scripts' => array(
		'ep.datepicker.js',
	),
	'styles' => array(
		'ep.datepicker.css',
	),
	'dependencies' => array(
		'jquery.ui.datepicker',
	),
);

$wgResourceModules['ep.combobox'] = $moduleTemplate + array(
	'scripts' => array(
		'ep.combobox.js',
	),
	'styles' => array(
		'ep.combobox.css',
	),
	'dependencies' => array(
		'jquery.ui.core',
		'jquery.ui.widget',
		'jquery.ui.autocomplete',
	),
);

$wgResourceModules['ep.formpage'] = $moduleTemplate + array(
	'scripts' => array(
		'ep.formpage.js',
	),
	'styles' => array(
		'ep.formpage.css',
	),
	'dependencies' => array(
		'jquery.ui.button',
	),
);

$wgResourceModules['ep.disenroll'] = $moduleTemplate + array(
	'scripts' => array(
		'ep.disenroll.js',
	),
	'dependencies' => array(
		'jquery.ui.button',
	),
);

$wgResourceModules['ep.ambprofile'] = $moduleTemplate + array(
	'scripts' => array(
		'ep.ambprofile.js',
	),
	'styles' => array(
		'ep.ambprofile.css',
	),
	'dependencies' => array(
		'jquery.ui.button',
		'ep.imageinput',
	),
);

$wgResourceModules['ep.imageinput'] = $moduleTemplate + array(
	'scripts' => array(
		'jquery.imageinput.js',
		'ep.imageinput.js',
	),
	'dependencies' => array(
		'jquery.ui.autocomplete',
	),
);

$wgResourceModules['ep.articletable'] = $moduleTemplate + array(
	'scripts' => array(
		'ep.articletable.js',
	),
	'dependencies' => array(
		'jquery.ui.button',
		'jquery.ui.dialog',
		'jquery.ui.autocomplete',
		'ep.core',
	),
	'messages' => array(
		'ep-articletable-addreviwer-title',
		'ep-articletable-addreviwer-button',
		'ep-articletable-addreviwer-cancel',
		'ep-articletable-addreviwer-text',

		'ep-articletable-remreviwer-title',
		'ep-articletable-remreviwer-button',
		'ep-articletable-remreviwer-cancel',
		'ep-articletable-remreviwer-text',
		'ep-articletable-remreviwer-title-self',
		'ep-articletable-remreviwer-button-self',
		'ep-articletable-remreviwer-text-self',

		'ep-articletable-remstudent-title',
		'ep-articletable-remstudent-button',
		'ep-articletable-remstudent-cancel',
		'ep-articletable-remstudent-text',

		'ep-articletable-remarticle-title',
		'ep-articletable-remarticle-button',
		'ep-articletable-remarticle-cancel',
		'ep-articletable-remarticle-text',
		'ep-articletable-remarticle-text-self',
	),
);

$wgResourceModules['ep.addorg'] = $moduleTemplate + array(
	'scripts' => array(
		'ep.addorg.js',
	),
);

$wgResourceModules['ep.addcourse'] = $moduleTemplate + array(
	'scripts' => array(
		'ep.addcourse.js',
	),
);

$wgResourceModules['ep.timeline'] = $moduleTemplate + array(
	'styles' => array(
		'ep.timeline.css',
	),
);

$wgResourceModules['ep.studentactivity'] = $moduleTemplate + array(
	'styles' => array(
		'ep.studentactivity.css',
	),
);

if ( array_key_exists( 'WikiEditorHooks', $GLOBALS['wgAutoloadClasses'] ) ) {
	$wgResourceModules['ep.formpage']['dependencies'][] = 'ext.wikiEditor.toolbar';
	$wgResourceModules['ep.ambprofile']['dependencies'][] = 'ext.wikiEditor.toolbar';
}

$wgResourceModules['ep.enlist'] = $moduleTemplate + array(
	'scripts' => array(
		'ep.enlist.js',
	),
	'dependencies' => array(
		'mediawiki.user',
		'jquery.ui.dialog',
		'ep.core',
		'ep.api',
		'jquery.ui.autocomplete',
	),
	'messages' => array(
		'ep-instructor-remove-title',
		'ep-online-remove-title',
		'ep-campus-remove-title',
		'ep-instructor-remove-button',
		'ep-online-remove-button',
		'ep-campus-remove-button',
		'ep-instructor-removing',
		'ep-online-removing',
		'ep-campus-removing',
		'ep-instructor-removal-success',
		'ep-online-removal-success',
		'ep-campus-removal-success',
		'ep-instructor-close-button',
		'ep-online-close-button',
		'ep-campus-close-button',
		'ep-instructor-remove-retry',
		'ep-online-remove-retry',
		'ep-campus-remove-retry',
		'ep-instructor-remove-failed',
		'ep-online-remove-failed',
		'ep-campus-remove-failed',
		'ep-instructor-cancel-button',
		'ep-online-cancel-button',
		'ep-campus-cancel-button',
		'ep-instructor-remove-text',
		'ep-online-remove-text',
		'ep-campus-remove-text',
		'ep-instructor-adding',
		'ep-online-adding',
		'ep-campus-adding',
		'ep-instructor-addittion-success',
		'ep-online-addittion-success',
		'ep-campus-addittion-success',
		'ep-instructor-addittion-self-success',
		'ep-online-addittion-self-success',
		'ep-campus-addittion-self-success',
		'ep-instructor-addittion-null',
		'ep-online-addittion-null',
		'ep-campus-addittion-null',
		'ep-instructor-addittion-invalid-user',
		'ep-online-addittion-invalid-user',
		'ep-campus-addittion-invalid-user',
		'ep-instructor-add-close-button',
		'ep-online-add-close-button',
		'ep-campus-add-close-button',
		'ep-instructor-add-retry',
		'ep-online-add-retry',
		'ep-campus-add-retry',
		'ep-instructor-addittion-failed',
		'ep-online-addittion-failed',
		'ep-campus-addittion-failed',
		'ep-instructor-add-title',
		'ep-online-add-title',
		'ep-campus-add-title',
		'ep-instructor-add-button',
		'ep-online-add-button',
		'ep-campus-add-button',
		'ep-instructor-add-self-button',
		'ep-online-add-self-button',
		'ep-campus-add-self-button',
		'ep-instructor-add-text',
		'ep-online-add-text',
		'ep-campus-add-text',
		'ep-instructor-add-self-text',
		'ep-online-add-self-text',
		'ep-campus-add-self-text',
		'ep-instructor-add-self-title',
		'ep-online-add-self-title',
		'ep-campus-add-self-title',
		'ep-instructor-add-cancel-button',
		'ep-online-add-cancel-button',
		'ep-campus-add-cancel-button',
		'ep-instructor-summary-input',
		'ep-online-summary-input',
		'ep-campus-summary-input',
		'ep-instructor-name-input',
		'ep-online-name-input',
		'ep-campus-name-input',
		'ep-course-no-instructor',
		'ep-course-no-online',
		'ep-course-no-campus',
		'ep-instructor-summary',
		'ep-online-summary',
		'ep-campus-summary',
	),
);

unset( $moduleTemplate );

$egEPSettings = array();

// The default value for the user preferences.
$wgDefaultUserOptions['ep_showtoplink'] = false;
$wgDefaultUserOptions['ep_bulkdelorgs'] = false;
$wgDefaultUserOptions['ep_bulkdelcourses'] = true;
$wgDefaultUserOptions['ep_showdyk'] = true;
