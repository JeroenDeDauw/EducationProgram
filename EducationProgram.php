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
$wgAutoloadClasses['EducationProgram\Hooks'] 						= $dir . '/EducationProgram.hooks.php';
$wgAutoloadClasses['EducationProgram\Settings'] 					= $dir . '/EducationProgram.settings.php';

// includes/actions (deriving from Action)
$wgAutoloadClasses['EducationProgram\EditCourseAction'] 			= $dir . '/includes/actions/EditCourseAction.php';
$wgAutoloadClasses['EducationProgram\EditOrgAction'] 				= $dir . '/includes/actions/EditOrgAction.php';
$wgAutoloadClasses['EducationProgram\Action'] 						= $dir . '/includes/actions/Action.php';
$wgAutoloadClasses['EducationProgram\AddArticleAction'] 			= $dir . '/includes/actions/AddArticleAction.php';
$wgAutoloadClasses['EducationProgram\AddReviewerAction'] 			= $dir . '/includes/actions/AddReviewerAction.php';
$wgAutoloadClasses['EducationProgram\DeleteAction'] 				= $dir . '/includes/actions/DeleteAction.php';
$wgAutoloadClasses['EducationProgram\EditAction'] 					= $dir . '/includes/actions/EditAction.php';
$wgAutoloadClasses['EducationProgram\HistoryAction'] 				= $dir . '/includes/actions/HistoryAction.php';
$wgAutoloadClasses['EducationProgram\RemoveArticleAction'] 			= $dir . '/includes/actions/RemoveArticleAction.php';
$wgAutoloadClasses['EducationProgram\RemoveReviewerAction'] 		= $dir . '/includes/actions/RemoveReviewerAction.php';
$wgAutoloadClasses['EducationProgram\RemoveStudentAction'] 			= $dir . '/includes/actions/RemoveStudentAction.php';
$wgAutoloadClasses['EducationProgram\RestoreAction'] 				= $dir . '/includes/actions/RestoreAction.php';
$wgAutoloadClasses['EducationProgram\UndeleteAction'] 				= $dir . '/includes/actions/UndeleteAction.php';
$wgAutoloadClasses['EducationProgram\UndoAction'] 					= $dir . '/includes/actions/UndoAction.php';
$wgAutoloadClasses['EducationProgram\ViewAction'] 					= $dir . '/includes/actions/ViewAction.php';
$wgAutoloadClasses['EducationProgram\ViewCourseAction'] 			= $dir . '/includes/actions/ViewCourseAction.php';
$wgAutoloadClasses['EducationProgram\ViewOrgAction'] 				= $dir . '/includes/actions/ViewOrgAction.php';

// includes/api (deriving from ApiBase)
$wgAutoloadClasses['EducationProgram\ApiDeleteEducation'] 			= $dir . '/includes/api/ApiDeleteEducation.php';
$wgAutoloadClasses['EducationProgram\ApiEnlist'] 					= $dir . '/includes/api/ApiEnlist.php';
$wgAutoloadClasses['EducationProgram\ApiRefreshEducation'] 			= $dir . '/includes/api/ApiRefreshEducation.php';

// includes/pagers (implementing Pager)
$wgAutoloadClasses['EducationProgram\ArticlePager'] 				= $dir . '/includes/pagers/ArticlePager.php';
$wgAutoloadClasses['EducationProgram\ArticleTable'] 				= $dir . '/includes/pagers/ArticleTable.php';
$wgAutoloadClasses['EducationProgram\CAPager'] 						= $dir . '/includes/pagers/CAPager.php';
$wgAutoloadClasses['EducationProgram\CoursePager'] 					= $dir . '/includes/pagers/CoursePager.php';
$wgAutoloadClasses['EducationProgram\EPPager'] 						= $dir . '/includes/pagers/EPPager.php';
$wgAutoloadClasses['EducationProgram\OAPager'] 						= $dir . '/includes/pagers/OAPager.php';
$wgAutoloadClasses['EducationProgram\OrgPager'] 					= $dir . '/includes/pagers/OrgPager.php';
$wgAutoloadClasses['EducationProgram\RevisionPager'] 				= $dir . '/includes/pagers/RevisionPager.php';
$wgAutoloadClasses['EducationProgram\StudentPager'] 				= $dir . '/includes/pagers/StudentPager.php';
$wgAutoloadClasses['EducationProgram\StudentActivityPager'] 		= $dir . '/includes/pagers/StudentActivityPager.php';

// includes/pages (here core is a mess :)
$wgAutoloadClasses['EducationProgram\CoursePage'] 					= $dir . '/includes/pages/CoursePage.php';
$wgAutoloadClasses['EducationProgram\EducationPage'] 				= $dir . '/includes/pages/EducationPage.php';
$wgAutoloadClasses['EducationProgram\OrgPage'] 						= $dir . '/includes/pages/OrgPage.php';

// includes/rows (deriving from ORMRow)
$wgAutoloadClasses['EducationProgram\CA'] 							= $dir . '/includes/rows/CA.php';
$wgAutoloadClasses['EducationProgram\Course'] 						= $dir . '/includes/rows/Course.php';
$wgAutoloadClasses['EducationProgram\EPArticle'] 					= $dir . '/includes/rows/EPArticle.php';
$wgAutoloadClasses['EducationProgram\EPRevision'] 					= $dir . '/includes/rows/EPRevision.php';
$wgAutoloadClasses['EducationProgram\Event'] 						= $dir . '/includes/rows/Event.php';
$wgAutoloadClasses['EducationProgram\Instructor'] 					= $dir . '/includes/rows/Instructor.php';
$wgAutoloadClasses['EducationProgram\OA'] 							= $dir . '/includes/rows/OA.php';
$wgAutoloadClasses['EducationProgram\Org'] 							= $dir . '/includes/rows/Org.php';
$wgAutoloadClasses['EducationProgram\PageObject'] 					= $dir . '/includes/rows/PageObject.php';
$wgAutoloadClasses['EducationProgram\RevisionedObject'] 			= $dir . '/includes/rows/RevisionedObject.php';
$wgAutoloadClasses['EducationProgram\Student'] 						= $dir . '/includes/rows/Student.php';

// includes/specials (deriving from SpecialPage)
$wgAutoloadClasses['EducationProgram\SpecialCourses'] 				= $dir . '/includes/specials/SpecialCourses.php';
$wgAutoloadClasses['EducationProgram\SpecialEducationProgram'] 		= $dir . '/includes/specials/SpecialEducationProgram.php';
$wgAutoloadClasses['EducationProgram\SpecialInstitutions'] 			= $dir . '/includes/specials/SpecialInstitutions.php';
$wgAutoloadClasses['EducationProgram\SpecialMyCourses'] 			= $dir . '/includes/specials/SpecialMyCourses.php';
$wgAutoloadClasses['EducationProgram\SpecialStudent'] 				= $dir . '/includes/specials/SpecialStudent.php';
$wgAutoloadClasses['EducationProgram\SpecialStudents'] 				= $dir . '/includes/specials/SpecialStudents.php';
$wgAutoloadClasses['EducationProgram\SpecialEnroll'] 				= $dir . '/includes/specials/SpecialEnroll.php';
$wgAutoloadClasses['EducationProgram\SpecialDisenroll'] 			= $dir . '/includes/specials/SpecialDisenroll.php';
$wgAutoloadClasses['EducationProgram\SpecialCAs'] 					= $dir . '/includes/specials/SpecialCAs.php';
$wgAutoloadClasses['EducationProgram\SpecialOAs'] 					= $dir . '/includes/specials/SpecialOAs.php';
$wgAutoloadClasses['EducationProgram\SpecialOAProfile'] 			= $dir . '/includes/specials/SpecialOAProfile.php';
$wgAutoloadClasses['EducationProgram\SpecialCAProfile'] 			= $dir . '/includes/specials/SpecialCAProfile.php';
$wgAutoloadClasses['EducationProgram\SpecialAmbassadorProfile'] 	= $dir . '/includes/specials/SpecialAmbassadorProfile.php';
$wgAutoloadClasses['EducationProgram\SpecialStudentActivity'] 		= $dir . '/includes/specials/SpecialStudentActivity.php';
$wgAutoloadClasses['EducationProgram\SpecialArticles'] 				= $dir . '/includes/specials/SpecialArticles.php';
$wgAutoloadClasses['EducationProgram\SpecialManageCourses'] 		= $dir . '/includes/specials/SpecialManageCourses.php';
$wgAutoloadClasses['EducationProgram\VerySpecialPage'] 				= $dir . '/includes/specials/VerySpecialPage.php';

// includes/tables (deriving from ORMTable)
$wgAutoloadClasses['EducationProgram\Articles'] 					= $dir . '/includes/tables/Articles.php';
$wgAutoloadClasses['EducationProgram\CAs'] 							= $dir . '/includes/tables/CAs.php';
$wgAutoloadClasses['EducationProgram\Courses'] 						= $dir . '/includes/tables/Courses.php';
$wgAutoloadClasses['EducationProgram\Events'] 						= $dir . '/includes/tables/Events.php';
$wgAutoloadClasses['EducationProgram\Instructors'] 					= $dir . '/includes/tables/Instructors.php';
$wgAutoloadClasses['EducationProgram\OAs'] 							= $dir . '/includes/tables/OAs.php';
$wgAutoloadClasses['EducationProgram\Orgs'] 						= $dir . '/includes/tables/Orgs.php';
$wgAutoloadClasses['EducationProgram\PageTable'] 					= $dir . '/includes/tables/PageTable.php';
$wgAutoloadClasses['EducationProgram\Revisions'] 					= $dir . '/includes/tables/Revisions.php';
$wgAutoloadClasses['EducationProgram\Students'] 					= $dir . '/includes/tables/Students.php';

// includes
$wgAutoloadClasses['EducationProgram\DYKBox'] 						= $dir . '/includes/DYKBox.php';
$wgAutoloadClasses['EducationProgram\LogFormatter'] 				= $dir . '/includes/LogFormatter.php';
$wgAutoloadClasses['EducationProgram\RoleChangeFormatter'] 			= $dir . '/includes/LogFormatter.php';
$wgAutoloadClasses['EducationProgram\ArticleFormatter'] 			= $dir . '/includes/LogFormatter.php';
$wgAutoloadClasses['EducationProgram\Utils'] 						= $dir . '/includes/Utils.php';
$wgAutoloadClasses['EducationProgram\HTMLDateField'] 				= $dir . '/includes/HTMLDateField.php';
$wgAutoloadClasses['EducationProgram\HTMLCombobox'] 				= $dir . '/includes/HTMLCombobox.php';
$wgAutoloadClasses['EducationProgram\FailForm'] 					= $dir . '/includes/FailForm.php';
$wgAutoloadClasses['EducationProgram\IRole'] 						= $dir . '/includes/IRole.php';
$wgAutoloadClasses['EducationProgram\RoleObject'] 					= $dir . '/includes/RoleObject.php';
$wgAutoloadClasses['EducationProgram\RevisionAction'] 				= $dir . '/includes/RevisionAction.php';
$wgAutoloadClasses['EducationProgram\RevisionDiff'] 				= $dir . '/includes/RevisionDiff.php';
$wgAutoloadClasses['EducationProgram\DiffTable'] 					= $dir . '/includes/DiffTable.php';
$wgAutoloadClasses['EducationProgram\Menu'] 						= $dir . '/includes/Menu.php';
$wgAutoloadClasses['EducationProgram\Timeline'] 					= $dir . '/includes/Timeline.php';
$wgAutoloadClasses['EducationProgram\TimelineGroup'] 				= $dir . '/includes/TimelineGroup.php';

$wgAutoloadClasses['EducationProgram\Test\MockSuperUser'] 			= $dir . '/tests/phpunit/MockSuperUser.php';

// Special pages
$wgSpecialPages['MyCourses'] 						= 'EducationProgram\SpecialMyCourses';
$wgSpecialPages['Institutions'] 					= 'EducationProgram\SpecialInstitutions';
$wgSpecialPages['Student'] 							= 'EducationProgram\SpecialStudent';
$wgSpecialPages['Students'] 						= 'EducationProgram\SpecialStudents';
$wgSpecialPages['Courses'] 							= 'EducationProgram\SpecialCourses';
//$wgSpecialPages['EducationProgram'] 				= 'EducationProgram\SpecialEducationProgram';
$wgSpecialPages['Enroll'] 							= 'EducationProgram\SpecialEnroll';
$wgSpecialPages['Disenroll'] 						= 'EducationProgram\SpecialDisenroll';
$wgSpecialPages['CampusAmbassadors'] 				= 'EducationProgram\SpecialCAs';
$wgSpecialPages['OnlineAmbassadors'] 				= 'EducationProgram\SpecialOAs';
$wgSpecialPages['CampusAmbassadorProfile'] 			= 'EducationProgram\SpecialCAProfile';
$wgSpecialPages['OnlineAmbassadorProfile'] 			= 'EducationProgram\SpecialOAProfile';
$wgSpecialPages['StudentActivity'] 					= 'EducationProgram\SpecialStudentActivity';
$wgSpecialPages['Articles'] 						= 'EducationProgram\SpecialArticles';
$wgSpecialPages['ManageCourses'] 					= 'EducationProgram\SpecialManageCourses';

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
$wgAPIModules['deleteeducation'] 					= 'EducationProgram\ApiDeleteEducation';
$wgAPIModules['enlist'] 							= 'EducationProgram\ApiEnlist';
$wgAPIModules['refresheducation'] 					= 'EducationProgram\ApiRefreshEducation';

// Hooks
$wgHooks['LoadExtensionSchemaUpdates'][] 			= 'EducationProgram\Hooks::onSchemaUpdate';
$wgHooks['UnitTestsList'][] 						= 'EducationProgram\Hooks::registerUnitTests';
$wgHooks['PersonalUrls'][] 							= 'EducationProgram\Hooks::onPersonalUrls';
$wgHooks['GetPreferences'][] 						= 'EducationProgram\Hooks::onGetPreferences';
$wgHooks['SkinTemplateNavigation'][] 				= 'EducationProgram\Hooks::onPageTabs';
$wgHooks['SkinTemplateNavigation::SpecialPage'][] 	= 'EducationProgram\Hooks::onSpecialPageTabs';
$wgHooks['ArticleFromTitle'][] 						= 'EducationProgram\Hooks::onArticleFromTitle';
$wgHooks['CanonicalNamespaces'][] 					= 'EducationProgram\Hooks::onCanonicalNamespaces';
$wgHooks['TitleIsAlwaysKnown'][] 					= 'EducationProgram\Hooks::onTitleIsAlwaysKnown';
$wgHooks['AbortMove'][] 							= 'EducationProgram\Hooks::onAbortMove';
$wgHooks['NewRevisionFromEditComplete'][] 			= 'EducationProgram\Hooks::onNewRevisionFromEditComplete';
$wgHooks['NamespaceIsMovable'][] 					= 'EducationProgram\Hooks::onNamespaceIsMovable';


// Actions
$wgActions['epremarticle'] = 'EducationProgram\RemoveArticleAction';
$wgActions['epremstudent'] = 'EducationProgram\RemoveStudentAction';
$wgActions['epremreviewer'] = 'EducationProgram\RemoveReviewerAction';
$wgActions['epaddarticle'] = 'EducationProgram\AddArticleAction';
$wgActions['epaddreviewer'] = 'EducationProgram\AddReviewerAction';
$wgActions['epundo'] = 'EducationProgram\UndoAction';
$wgActions['eprestore'] = 'EducationProgram\RestoreAction';
$wgActions['epundelete'] = 'EducationProgram\UndeleteAction';

// Logging
$wgLogTypes[] = 'institution';
$wgLogTypes[] = 'course';
$wgLogTypes[] = 'student';
$wgLogTypes[] = 'online';
$wgLogTypes[] = 'campus';
$wgLogTypes[] = 'instructor';

$wgLogActionsHandlers['institution/*'] = 'EducationProgram\LogFormatter';
$wgLogActionsHandlers['course/*'] = 'EducationProgram\LogFormatter';
$wgLogActionsHandlers['student/*'] = 'EducationProgram\LogFormatter';
$wgLogActionsHandlers['student/add'] = 'EducationProgram\RoleChangeFormatter';
$wgLogActionsHandlers['student/remove'] = 'EducationProgram\RoleChangeFormatter';
$wgLogActionsHandlers['online/*'] = 'EducationProgram\LogFormatter';
$wgLogActionsHandlers['online/add'] = 'EducationProgram\RoleChangeFormatter';
$wgLogActionsHandlers['online/remove'] = 'EducationProgram\RoleChangeFormatter';
$wgLogActionsHandlers['campus/*'] = 'EducationProgram\LogFormatter';
$wgLogActionsHandlers['campus/add'] = 'EducationProgram\RoleChangeFormatter';
$wgLogActionsHandlers['campus/remove'] = 'EducationProgram\RoleChangeFormatter';
$wgLogActionsHandlers['instructor/*'] = 'EducationProgram\LogFormatter';
$wgLogActionsHandlers['instructor/add'] = 'EducationProgram\RoleChangeFormatter';
$wgLogActionsHandlers['instructor/remove'] = 'EducationProgram\RoleChangeFormatter';
$wgLogActionsHandlers['eparticle/*'] = 'EducationProgram\ArticleFormatter';

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

if ( !array_key_exists( 'sysop', $wgAddGroups ) ) {
	$wgAddGroups['sysop'] = array();
}

if ( !array_key_exists( 'sysop', $wgRemoveGroups ) ) {
	$wgRemoveGroups['sysop'] = array();
}

$wgAddGroups['sysop'] = array_merge( $wgAddGroups['sysop'], array( 'eponline', 'epcampus', 'epinstructor', 'epcoordinator' ) );
$wgRemoveGroups['sysop'] = array_merge( $wgRemoveGroups['sysop'], array( 'eponline', 'epcampus', 'epinstructor', 'epcoordinator' ) );

// Namespaces
define( 'EP_NS',					442 + 4 );
define( 'EP_NS_TALK', 				442 + 5 );

$wgNamespacesWithSubpages[EP_NS_TALK] = true;

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

$wgResourceModules['ep.dyk'] = $moduleTemplate + array(
	'styles' => array(
		'ep.dyk.css',
	),
);

unset( $moduleTemplate );

$egEPSettings = array();

// The default value for the user preferences.
$wgDefaultUserOptions['ep_showtoplink'] = false;
$wgDefaultUserOptions['ep_bulkdelorgs'] = false;
$wgDefaultUserOptions['ep_bulkdelcourses'] = true;
$wgDefaultUserOptions['ep_showdyk'] = true;
