<?php

/**
 * Internationalization file for the Education Program extension.
 *
 * @since 0.1
 *
 * @file EducationProgram.i18n.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v3+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */

$messages = array();

/** English
 * @author Jeroen De Dauw
 */
$messages['en'] = array(
	'educationprogram-desc' => 'Allows for running education courses in which students can enroll.',

	// Misc
	'ep-item-summary' => 'Summary',
	'ep-toplink' => 'My courses',
	'ep-org-course-delete-comment' => "Deleted institution $1 and all its courses with comment $2",
	'ep-org-course-delete' => "Deleted institution $1 and all its courses",
	'ep-form-summary' => 'Summary:',
	'ep-form-minor' => 'This is a minor edit',
	'ep-move-error' => 'You are not allowed to move articles in or out of the education namespaces.',
	'ep-student-view-profile' => 'student profile',

	// Tabs
	'ep-tab-view' => 'Read',
	'ep-tab-edit' => 'Edit',
	'ep-tab-create' => 'Create',
	'ep-tab-history' => 'View history',
	'ep-tab-enroll' => 'Enroll',
	'ep-tab-disenroll' => 'Disenroll',
	'ep-tab-delete' => 'Delete',

	// Tooltips
	'tooltip-ep-form-save' => 'Save',
	'tooltip-ep-edit-institution' => 'Edit this institution',
	'tooltip-ep-edit-course' => 'Edit this course',
	'tooltip-ep-summary' => 'Enter a short summary',
	'tooltip-ep-minor' => 'Marks this as a minor edit',

	// Access keys
	'accesskey-ep-form-save' => 's', # do not translate or duplicate this message to other languages
	'accesskey-ep-edit-institution' => 'e', # do not translate or duplicate this message to other languages
	'accesskey-ep-edit-course' => 'e', # do not translate or duplicate this message to other languages
	'accesskey-ep-summary' => 'b', # do not translate or duplicate this message to other languages
	'accesskey-ep-minor' => 'i', # do not translate or duplicate this message to other languages

	// Navigation links
	'ep-nav-orgs' => 'Institution list',
	'ep-nav-courses' => 'Courses list',
	'ep-nav-mycourses' => 'My courses',
	'ep-nav-students' => 'Student list',
	'ep-nav-mentors' => 'Ambassador list',
	'ep-nav-cas' => 'Campus Ambassadors',
	'ep-nav-oas' => 'Online Ambassadors',
	'ep-nav-oaprofile' => 'Online Ambassador profile',
	'ep-nav-caprofile' => 'Campus Ambassador profile',

	// Logging
	'log-name-institution' => 'Education Program institution log',
	'log-name-course' => 'Education Program course log',
	'log-name-student' => 'Education Program student log',
	'log-name-online' => 'Education Program Online Ambassador log',
	'log-name-campus' => 'Education Program Campus Ambassador log',
	'log-name-instructor' => 'Education Program instructor log',
	'log-name-eparticle' => 'Education Program article log',

	'log-header-institution' => 'These events track the changes that are made to Education Program institutions.',
	'log-header-course' => 'These events track the changes that are made to Education Program courses.',
	'log-header-instructor' => 'These events track the changes that are made to Education Program instructors.',
	'log-header-campus' => 'These events track the changes that are made to Education Program Campus Ambassadors.',
	'log-header-online' => 'These events track the changes that are made to Education Program Online Ambassadors.',
	'log-header-student' => 'These events track the changes that are made to Education Program students.',

	'log-description-institution' => 'Log of all changes to [[Special:Institutions|institutions]].',
	'log-description-course' => 'Log of all changes to [[Special:Courses|courses]].',
	'log-description-instructor' => 'Log of all changes to instructors.',
	'log-description-online' => 'Log of all changes to Education Program [[Special:OnlineAmbassadors|Online Ambassadors]]',
	'log-description-campus' => 'Log of all changes to Education Program [[Special:CampusAmbassadors|Campus Ambassadors]]',
	'log-description-student' => 'Log of all changes to [[Special:Students|students]].',

	'logentry-institution-add' => '$1 created institution $3',
	'logentry-institution-remove' => '$1 removed institution $3',
	'logentry-institution-update' => '$1 updated institution $3',
	'logentry-institution-undelete' => '$1 undeleted institution $3',

	'logentry-course-add' => '$1 created course $3',
	'logentry-course-remove' => '$1 removed course $3',
	'logentry-course-update' => '$1 updated course $3',
	'logentry-course-undelete' => '$1 undeleted course $3',

	'logentry-instructor-add' => '$1 {{GENDER:$2|added}} {{PLURAL:$4|instructor|instructors}} $5 to course $3',
	'logentry-instructor-remove' => '$1 {{GENDER:$2|removed}} {{PLURAL:$4|instructor|instructors}} $5 from course $3',
	'logentry-instructor-selfadd' => '$1 added {{GENDER:$2|added himself|herself}} as {{GENDER:$2|instructor}} to course $3',
	'logentry-instructor-selfremove' => '$1 removed {{GENDER:$2|himself|herself}} as {{GENDER:$2|instructor}} from course $3',

	'logentry-online-add' => '$1 added {{PLURAL:$4|Online Ambassador|Online Ambassadors}} $5 to course $3',
	'logentry-online-remove' => '$1 removed {{PLURAL:$4|Online Ambassador|Online Ambassadors}} $5 from course $3',
	'logentry-online-selfadd' => '$1 added {{GENDER:$2|himself|herself}} as {{GENDER:$2|Online Ambassador}} to course $3',
	'logentry-online-selfremove' => '$1 removed {{GENDER:$2|himself|herself}} as {{GENDER:$2|Online Ambassador}} from course $3',
	'logentry-online-profilesave' => '$1 updated {{GENDER:$2|his|her}} Online Ambassador profile',

	'logentry-campus-add' => '$1 added {{PLURAL:$4|Campus Ambassador|Campus Ambassadors}} $5 to course $3',
	'logentry-campus-remove' => '$1 removed {{PLURAL:$4|Campus Ambassador|Campus Ambassadors}} $5 from course $3',
	'logentry-campus-selfadd' => '$1 added {{GENDER:$2|himself|herself}} as {{GENDER:$2|Campus Ambassador}} to course $3',
	'logentry-campus-selfremove' => '$1 removed {{GENDER:$2|himself|herself}} as {{GENDER:$2|Campus Ambassador}} from course $3',
	'logentry-campus-profilesave' => '$1 updated {{GENDER:$2|his|her}} Campus Ambassador profile',

	'logentry-student-add' => '$1 enrolled in course $3',
	'logentry-student-remove' => '$1 removed $5 as {{GENDER:$2|student}} from course $3',
	'logentry-student-selfadd' => '$1 enrolled in course $3',
	'logentry-student-selfremove' => '$1 disenrolled from course $3',

	'logentry-eparticle-selfadd' => '$1 added article $3 to {{GENDER:$2|his|her}} list of articles at course $4',
	'logentry-eparticle-selfremove' => '$1 removed article $3 from {{GENDER:$2|his|her}} list of articles at course $4',
	'logentry-eparticle-add' => '$1 added article $3 to $5 {{GENDER:$6|his|her}} list of articles at course $4',
	'logentry-eparticle-remove' => '$1 removed article $3 from $5 {{GENDER:$6|his|her}} list of articles at course $4',
	'logentry-eparticle-review' => '$1 added {{GENDER:$2|himself|herself}} as reviewer to article $3 worked upon by $5 as part of course $4',
	'logentry-eparticle-unreview' => '$1 removed {{GENDER:$2|himself|herself}} as reviewer to article $3 worked upon by $5 as part of course $4',

	// Preferences
	'prefs-education' => 'Education',
	'ep-prefs-showtoplink' => 'Show a link to [[Special:MyCourses|your courses]] at the top of every page.',
	'ep-prefs-bulkdelorgs' => 'Show a bulk deletion control for the [[Special:Institutions|institutions]].',
	'ep-prefs-bulkdelcourses' => 'Show a bulk deletion control for the [[Special:Courses|courses]].',

	// Rights
	'right-ep-org' => 'Manage Education Program institutions',
	'right-ep-course' => 'Manage Education Program courses',
	'right-ep-token' => 'See Education Program enrollment tokens',
	'right-ep-remstudent' => 'Remove students from courses',
	'right-ep-enroll' => 'Enroll in Education Program courses',
	'right-ep-online' => 'Add or remove online ambassadors to courses',
	'right-ep-campus' => 'Add or remove campus ambassadors to courses',
	'right-ep-instructor' => 'Add or remove instructors to courses',
	'right-ep-beonline' => 'Add or remove yourself as online ambassador from terms',
	'right-ep-becampus' => 'Add or remove yourself as campus ambassador from terms',
	'right-ep-beinstructor' => 'Add or remove yourself as instructor from courses',
	'right-ep-bereviewer' => 'Add or remove yourself as reviewer from articles',
	'right-ep-remreviewer' => 'Remove reviewers from articles',
	'right-ep-bulkdelorgs' => 'Bulk delete institutions',
	'right-ep-bulkdelcourses' => 'Bulk delete courses',

	// Actions
	'action-ep-org' => 'manage institutions',
	'action-ep-course' => 'manage courses',
	'action-ep-token' => 'see enrollment tokens',
	'action-ep-remstudent' => 'remove students from courses',
	'action-ep-enroll' => 'enroll in courses',
	'action-ep-online' => 'add or remove online ambassadors to courses',
	'action-ep-campus' => 'add or remove campus ambassadors to courses',
	'action-ep-instructor' => 'add or remove instructors to courses',
	'action-ep-beonline' => 'add or remove yourself as online ambassador from terms',
	'action-ep-becampus' => 'add or remove yourself as campus ambassador from terms',
	'action-ep-beinstructor' => ' add or remove yourself as instructor from courses',
	'action-ep-bereviewer' => 'add or remove yourself as reviewer from articles',
	'action-ep-remreviewer' => 'remove reviewers from articles',
	'action-ep-bulkdelorgs' => 'bulk delete institutions',
	'action-ep-bulkdelcourses' => 'bulk delete courses',

	// Groups
	'group-epadmin' => 'Education program admins',
	'group-epadmin-member' => '{{GENDER:$1|Education Program admin}}',
	'grouppage-epadmin' => '{{ns:project}}:Education_program_administrators',

	'group-epstaff' => 'Education program staff',
	'group-epstaff-member' => '{{GENDER:$1|Education Program staff}}',
	'grouppage-epstaff' => '{{ns:project}}:Education_program_staff',

	'group-eponlineamb' => 'Education program online ambassador',
	'group-eponlineamb-member' => '{{GENDER:$1|Education Program online ambassador}}',
	'grouppage-eponlineamb' => '{{ns:project}}:Education_program_online_ambassadors',

	'group-epcampamb' => 'Education program campus ambassador',
	'group-epcampamb-member' => '{{GENDER:$1|Education Program campus ambassador}}',
	'grouppage-epcampamb' => '{{ns:project}}:Education_program_campus_ambassadors',

	'group-epinstructor' => 'Education program instructor',
	'group-epinstructor-member' => '{{GENDER:$1|Education Program instructor}}',
	'grouppage-epinstructor' => '{{ns:project}}:Education_program_instructors',

	// Special pages
	'specialpages-group-education' => 'Education',
	'special-mycourses' => 'My courses',
	'special-institutions' => 'Institutions',
	'special-student' => 'Student',
	'special-students' => 'Students',
	'special-courses' => 'Courses',
	'special-educationprogram' => 'Education Program',
	'special-enroll' => 'Enroll',
	'special-onlineambassadors' => 'Online ambassadors',
	'special-campusambassadors' => 'Campus ambassadors',
	'special-onlineambassador' => 'Online ambassador',
	'special-campusambassador' => 'Campus ambassador',
	'special-disenroll' => 'Disenroll',
	'special-studentactivity' => 'Student activity',

	// Course statuses
	'ep-course-status-passed' => 'Passed',
	'ep-course-status-current' => 'Current',
	'ep-course-status-planned' => 'Planned',

	// Special:EducationProgram
	'ep-summary-table-header' => 'Education Program summary',
	'specialeducationprogram-summary-org-count' => 'Number of [[Special:Institutions|institutions]]',
	'specialeducationprogram-summary-course-count' => 'Number of [[Special:Courses|courses]]',
	'specialeducationprogram-summary-student-count' => 'Number of [[Special:Students|students]]',
	'specialeducationprogram-summary-instructor-count' => 'Number of instructors',
	'specialeducationprogram-summary-ca-count' => 'Number of [[Special:CampusAmbassadors|Campus Ambassadors]]',
	'specialeducationprogram-summary-oa-count' => 'Number of [[Special:OnlineAmbassadors|Online Ambassadors]]',

	// Special:Institutions
	'ep-institutions-nosuchinstitution' => 'There is no institution with name "$1". Existing institutions are listed below.',
	'ep-institutions-noresults' => 'There are no institutions to list.',
	'ep-institutions-addnew' => 'Add a new institution',
	'ep-institutions-namedoc' => 'Enter the name for the new institution (which should be unique) and hit the button.',
	'ep-institutions-newname' => 'Institution name:',
	'ep-institutions-add' => 'Add institution',

	// Special:Courses
	'ep-courses-nosuchcourse' => 'There is no course with name "$1". Existing courses are listed below.',
	'ep-courses-noresults' => 'There are no courses to list.',
	'ep-courses-addnew' => 'Add a new course',
	'ep-courses-namedoc' => 'Enter the institution the course belongs to and the year in which it is active.',
	'ep-courses-newterm' => 'Term:',
	'ep-courses-newname' => 'Course name:',
	'ep-courses-neworg' => 'Institution:',
	'ep-courses-add' => 'Add course',
	'ep-courses-noorgs' => 'There are no institutions yet. You need to [[Special:Institutions|add an institution]] before you can create any courses.',
	'ep-courses-addorgfirst' => 'There are no institutions yet. You need to [[Special:Institutions|add an institution]] before you can create any courses.',

	// Special:Students
	'ep-students-noresults' => 'There are no students to list.',

    // Special:Ambassadors
    'ep-mentors-noresults' => 'There are no ambassadors to list.',

	// Pager
	'ep-pager-showonly' => 'Show only items with',
	'ep-pager-clear' => 'Clear filters',
	'ep-pager-go' => 'Go',
	'ep-pager-withselected' => 'With selected',
	'ep-pager-delete-selected' => 'Delete',

	// Revision pager
	'ep-revision-undo' => 'undo',
	'ep-revision-restore' => 'restore',

	// Org pager
	'eporgpager-header-name' => 'Name',
	'eporgpager-header-city' => 'City',
	'eporgpager-header-country' => 'Country',
	'eporgpager-filter-country' => 'Country',
	'eporgpager-header-course-count' => 'Courses',
	'eporgpager-header-student-count' => 'Students',
	'eporgpager-header-active' => 'Active',
	'eporgpager-filter-active' => 'Active courses',
	'eporgpager-yes' => 'Yes',
	'eporgpager-no' => 'No',
	'ep-pager-cancel-button-org' => 'Cancel',
	'ep-pager-delete-button-org' => 'Remove {{PLURAL:$1|institution|institutions}}',
	'ep-pager-confirm-delete-org' => '{{PLURAL:$1|Confirm institution removal|Confirm removal of multiple institutions}}',
	'ep-pager-retry-button-org' => 'Retry',
	'ep-pager-summary-message-org' => 'Summary:',
	// Yeah we need two of these - having a jQuery node in PLURAL breaks, at least at r110788.
	'ep-pager-confirm-message-org' => 'You are about to remove institution $1. This will remove all associated courses and their student data!',
	'ep-pager-confirm-message-org-many' => 'You are about to remove these institutions: $1. This will remove all associated courses and their student data!',

	// Course pager
	'epcoursepager-header-name' => 'Name',
	'epcoursepager-header-term' => 'Term',
	'epcoursepager-header-start' => 'Start',
	'epcoursepager-header-org-id' => 'Institution',
	'epcoursepager-header-end' => 'End',
	'epcoursepager-header-status' => 'Status',
	'epcoursepager-header-student-count' => 'Students',
	'epcoursepager-header-lang' => 'Language',
	'epcoursepager-filter-term' => 'Term',
	'epcoursepager-filter-lang' => 'Language',
	'epcoursepager-filter-org-id' => 'Institution',
	'epcoursepager-filter-status' => 'Status',
	'epcoursepager-invalid-lang' => 'Invalid',
	'ep-pager-cancel-button-course' => 'Cancel',
	'ep-pager-delete-button-course' => 'Remove {{PLURAL:$1|course|courses}}',
	'ep-pager-confirm-delete-course' => '{{PLURAL:$1|Confirm course removal|Confirm removal of multiple courses}}',
	'ep-pager-retry-button-course' => 'Retry',
	'ep-pager-summary-message-course' => 'Summary:',
	// Yeah we need two of these - having a jQuery node in PLURAL breaks, at least at r110788.
	'ep-pager-confirm-message-course' => 'You are about to remove course $1. This will remove all associated student data!',
	'ep-pager-confirm-message-course-many' => 'You are about to remove these courses: $1. This will remove all associated student data!',

	// Student pager
	'epstudentpager-header-user-id' => 'User',
	'epstudentpager-header-id' => 'Id',
	'epstudentpager-header-current-courses' => 'Current courses',
	'epstudentpager-header-passed-courses' => 'Passed courses',
	'epstudentpager-header-first-enroll' => 'First enrollment',
	'epstudentpager-header-last-active' => 'Last active',
	'epstudentpager-header-active-enroll' => 'Currently enrolled',
	'epstudentpager-yes' => 'Yes',
	'epstudentpager-no' => 'No',

	// Article table
	'epstudentpager-header-student' => 'Student',
	'epstudentpager-header-articles' => 'Articles',
	'epstudentpager-header-reviewers' => 'Reviewers',
	'ep-artciles-remstudent' => 'remove from course',
	'ep-artciles-remreviewer-self' => 'Remove myself as {{GENDER:$1|reviewer}}',
	'ep-artciles-remreviewer' => 'remove as {{GENDER:$1|reviewer}}',
	'ep-artciles-remarticle' => 'remove article',
	'ep-artciles-addarticle-text' => 'Add an article:',
	'ep-artciles-addarticle-button' => 'Add article',
	'ep-artciles-becomereviewer' => 'Add myself as {{GENDER:$1|reviewer}}',

	// ep.articletable
	'ep-articletable-addreviwer-title' => 'Become {{GENDER:$1|reviewer}}',
	'ep-articletable-addreviwer-button' => 'Become {{GENDER:$1|reviewer}}',
	'ep-articletable-addreviwer-cancel' => 'Cancel',
	'ep-articletable-addreviwer-text' => 'You are about to enlist yourself as {{GENDER:$1|reviewer}} for article $2 worked on by $3.',

	'ep-articletable-remreviwer-title' => 'Remove $1 as {{GENDER:$1|reviewer}}',
	'ep-articletable-remreviwer-title-self' => 'Remove yourself as {{GENDER:$1|reviewer}}',
	'ep-articletable-remreviwer-button' => 'Remove {{GENDER:$1|reviewer}}',
	'ep-articletable-remreviwer-button-self' => 'Remove yourself',
	'ep-articletable-remreviwer-cancel' => 'Cancel',
	'ep-articletable-remreviwer-text-self' => 'You are about to remove yourself as {{GENDER:$1|reviewer}} for article $2 worked on by $3.',
	'ep-articletable-remreviwer-text' => 'You are about to remove $4 as {{GENDER:$1|reviewer}} for article $2 worked on by $3.',

	'ep-articletable-remstudent-title' => 'Remove {{GENDER:$1|student}} from course',
	'ep-articletable-remstudent-button' => 'Remove {{GENDER:$1|student}}',
	'ep-articletable-remstudent-cancel' => 'Cancel',
	'ep-articletable-remstudent-text' => 'You are about to remove $3 as {{GENDER:$1|student}} from course $2.

This will permanently remove their associated articles and reviewers!',

	'ep-articletable-remarticle-title' => 'Remove article $1',
	'ep-articletable-remarticle-button' => 'Remove article',
	'ep-articletable-remarticle-cancel' => 'Cancel',
	'ep-articletable-remarticle-text-self' => 'You are about to remove article $1 from the list of articles you are working on as part of course $2.',
	'ep-articletable-remarticle-text' => 'You are about to remove article $1 from the list of articles $3 is working on as part of course $2.',

	// Article pager
	'ep-articles-noresults' => 'There are no articles to list.',

	// Campus ambassador pager
	'epcapager-header-photo' => 'Photo',
	'epcapager-header-user-id' => 'User',
	'epcapager-header-bio' => 'Profile',
	'epcapager-header-courses' => 'Current courses',
	'ep-ca-noresults' => 'There are no Campus Ambassadors to list.',

	// Online ambassador pager
	'epoapager-header-photo' => 'Photo',
	'epoapager-header-user-id' => 'User',
	'epoapager-header-bio' => 'Profile',
	'epoapager-header-courses' => 'Current courses',
	'ep-oa-noresults' => 'There are no Online Ambassadors to list.',

	// Student activity pager
	'epstudentactivitypager-header-user-id' => 'Student',
	'epstudentactivitypager-header-org-id' => 'Institution',
	'epstudentactivitypager-header-last-course' => 'Course',
	'epstudentactivitypager-header-last-active' => 'Last activity',

	// Institution editing
	'editinstitution-text' => 'Enter the institution details below and click submit to save your changes.',
	'educationprogram-org-edit-name' => 'Institution name',
	'orgpage-edit-legend-add' => 'Add institution',
	'orgpage-edit-legend-edit' => 'Edit institution',
	'educationprogram-org-edit-city' => 'City',
	'educationprogram-org-edit-country' => 'Country',
	'educationprogram-org-submit' => 'Submit',
	'ep-addorg' => 'There is no institution with this name yet, but you can add it.',
	'ep-editorg' => 'You are editing an existing institution.',
	'ep-editorg-exists-already' => 'This institution already exists. You are editing it.',
	'orgpage-edit-title-edit' => 'Editing institution: $1',
	'orgpage-edit-title-add' => 'Adding institution: $1',
	'orgpage-edit-deleted' => "'''Warning: You are recreating an institution that was previously deleted.'''

You should consider whether it is appropriate to continue editing this institution.
The deletion log for this institution is provided below for convenience:",
	'orgpage-edit-undelete-revisions' => 'This institution has been deleted. You can $1.',
	'orgpage-edit-undelete-link' => 'restore $1 {{PLURAL:$1|revision|revisions}}',

	'educationprogram-org-invalid-name' => 'The name needs to be at least contain $1 {{PLURAL:$1|character|characters}}.',
	'educationprogram-org-invalid-city' => 'The city name needs to be at least contain $1 {{PLURAL:$1|character|characters}}.',
	'educationprogram-org-invalid-country' => 'This is not a valid country.',

	// Course editing
	'coursepage-edit-legend-add' => 'Add course',
	'coursepage-edit-legend-edit' => 'Edit course',
	'ep-course-edit-term' => 'Term',
	'ep-course-edit-org' => 'Institution',
	'ep-course-edit-start' => 'Start date',
	'ep-course-edit-end' => 'End date',
	'ep-course-edit-token' => 'Enrollment token',
	'ep-course-help-token' => 'Optional. When filled in, students will need to provide this token in order to enroll. This prevents non-students from signing up.',
	'ep-course-edit-description' => 'Description',
	'ep-course-edit-name-format' => '$1 ($2)',
	'ep-course-edit-name' => 'Page title',
	'ep-course-help-name' => 'The title of the course page. By convention this should be the course name followed by the term in brackets.',
	'ep-course-edit-field' => 'Field of study',
	'ep-course-edit-level' => 'Course level',
	'ep-course-edit-term' => 'Academic term',
	'ep-course-edit-mc' => 'Course name',
	'ep-course-help-mc' => 'The name of the course. If this course has already run in a previous term, it should have the same name.',
	'ep-course-edit-lang' => 'Course language',
	'ep-addcourse' => 'There is no course with this name yet, but you can add it.',
	'ep-editcourse' => 'You are editing an existing course.',
	'ep-editcourse-exists-already' => 'This course already exists. You are editing it.',
	'coursepage-edit-title-edit' => 'Editing course: $1',
	'coursepage-edit-title-add' => 'Adding course: $1',
	'coursepage-edit-deleted' => "'''Warning: You are recreating a course that was previously deleted.'''

You should consider whether it is appropriate to continue editing this course.
The deletion log for this course is provided below for convenience:",

	'ep-course-invalid-org' => 'This institution does not exist.',
	'ep-course-invalid-token' => 'The token needs to be at least contain $1 {{PLURAL:$1|character|characters}}.',
	'ep-course-invalid-description' => 'The description needs to be at least contain $1 {{PLURAL:$1|character|characters}}.',
	'ep-course-invalid-lang' => 'This language is not valid.',
	'coursepage-edit-undelete-revisions' => 'This course has been deleted. You can $1.',
	'coursepage-edit-undelete-link' => 'restore $1 {{PLURAL:$1|revision|revisions}}',

	// ep.pager
	'ep-pager-confirm-delete' => 'Are you sure you want to delete this item?',
	'ep-pager-delete-fail' => 'Could not delete this item.',
	'ep-pager-confirm-delete-selected' => 'Are you sure you want to delete the selected {{PLURAL:$1|item|items}}?',
	'ep-pager-delete-selected-fail' => 'Could not delete the selected {{PLURAL:$1|item|items}}.',

	// Institution viewing
	'vieworgaction-none' => 'There is no institution with name "$1". See [[Special:Institutions|here]] for a list of institutions.',
	'ep-institution-create' => 'There is no institution with name "$1" yet, but you can create it.',
	'ep-institution-title' => 'Institution: $1',
	'vieworgaction-summary-name' => 'Name',
	'vieworgaction-summary-city' => 'City',
	'vieworgaction-summary-country' => 'Country',
	'vieworgaction-summary-status' => 'Status',
	'vieworgaction-summary-courses' => 'Course count',
	'vieworgaction-summary-students' => 'Student count',
	'ep-institution-add-course' => 'Add a course',
	'ep-institution-inactive' => 'Inactive',
	'ep-institution-active' => 'Active',
	'ep-institution-courses' => 'Courses',
	'ep-vieworg-deleted' => 'This institution has been deleted. The deletion log for the institution is provided below for reference.',

	// Course viewing
	'ep-course-title' => 'Course: $1',
	'ep-course-students' => 'Students',
	'ep-course-articles' => 'Articles',
	'viewcourseaction-none' => 'There is no course with name "$1". See [[Special:Courses|here]] for a list of courses.',
	'ep-course-create' => 'There is no course with name "$1", but you can create a new one.',
	'ep-course-description' => 'Description',
	'ep-course-no-online' => '',
	'viewcourseaction-summary-org' => 'Institution',
	'viewcourseaction-summary-term' => 'Term',
	'viewcourseaction-summary-start' => 'Start',
	'viewcourseaction-summary-end' => 'End',
	'viewcourseaction-summary-students' => 'Student count',
	'viewcourseaction-summary-status' => 'Status',
	'viewcourseaction-summary-token' => 'Enrollment token',
	'viewcourseaction-summary-instructors' => 'Instructors',
	'viewcourseaction-summary-online' => 'Online Ambassadors',
	'viewcourseaction-summary-campus' => 'Campus Ambassadors',
	'ep-course-no-instructor' => 'There are no instructors for this course yet.',
	'ep-course-become-instructor' => 'Become an instructor',
	'ep-course-add-instructor' => 'Add an instructor',
	'ep-course-no-online' => 'There are no Online Ambassadors for this course yet.',
	'ep-course-become-online' => 'Become an Online Ambassador',
	'ep-course-add-online' => 'Add an Online Ambassador',
	'ep-course-no-campus' => 'There are no Campus Ambassadors for this course yet.',
	'ep-course-become-campus' => 'Become a Campus Ambassador',
	'ep-course-add-campus' => 'Add a Campus Ambassador',
	'ep-instructor-summary' => 'Summary:',
	'ep-online-summary' => 'Summary:',
	'ep-campus-summary' => 'Summary:',
	'ep-viewcourse-deleted' => 'This course has been deleted. The deletion log for the course is provided below for reference.',

	// Institution history
	'orgpage-history-description' => 'View logs for this institution',
	'orgpage-history-title' => 'Revision history of institution "$1"',
	'orgpage-history-norevs' => 'There is no edit history for this institution.',
	'orgpage-history-deleted' => 'This institution has been deleted. The deletion log for the institution is provided below for reference.',

	// Course history
	'coursepage-history-description' => 'View logs for this course',
	'coursepage-history-title' => 'Revision history of course "$1"',
	'coursepage-history-norevs' => 'There is no edit history for this course.',
	'coursepage-history-deleted' => 'This course has been deleted. The deletion log for the course is provided below for reference.',

	// Course deletion
	'coursepage-delete-text' => 'You are about to delete course $1. This will remove all associated students!',
	'coursepage-delete-summary' => 'Reason for deletion:',
	'coursepage-delete-title' => 'Delete course "$1"',
	'coursepage-delete-cancel-button' => 'Cancel',
	'coursepage-delete-delete-button' => 'Delete course',
	'coursepage-delete-none' => 'There is no course titled "$1". Existing courses can be found in [[Special:Courses|the courses list]].',
	'coursepage-delete-deleted' => 'Successfully deleted course $1.',
	'coursepage-delete-delete-failed' => 'Failed to deleted course [[Course:$1|$1]].',

	// Institution deletion
	'orgpage-delete-text' => "You are about to delete institution $1. This will remove all it's courses and their associated students!",
	'orgpage-delete-summary' => 'Reason for deletion:',
	'orgpage-delete-title' => 'Delete institution "$1"',
	'orgpage-delete-cancel-button' => 'Cancel',
	'orgpage-delete-delete-button' => 'Delete institution',
	'orgpage-delete-none' => 'There is no institution titled "$1". Existing institutions can be found in [[Special:Courses|the institution list]].',
	'orgpage-delete-deleted' => 'Successfully deleted institution $1 and its associated courses.',
	'orgpage-delete-delete-failed' => 'Failed to deleted institution [[Institution:$1|$1]].',

	// Institution restoration
	'orgpage-eprestore-title' => 'Restore institution "$1"',
	'orgpage-eprestore-text' => 'You are about to restore institution $1 to a previous revision.', 
	'orgpage-eprestore-summary' => 'Reason for restoration:',
	'orgpage-eprestore-restore-button' => 'Restore revision',
	'orgpage-eprestore-cancel-button' => 'Cancel',
	'orgpage-eprestore-summary-value' => 'Restore institution to the revision made on $1 by $2',
	'orgpage-eprestore-restored' => 'Successfully restored institution $1.',
	'orgpage-eprestore-restore-failed' => 'Failed to restore institution $1.',

	// Course restoration
	'coursepage-eprestore-title' => 'Restore course "$1"',
	'coursepage-eprestore-text' => 'You are about to restore course $1 to a previous revision.', 
	'coursepage-eprestore-summary' => 'Reason for restoration:',
	'coursepage-eprestore-restore-button' => 'Restore revision',
	'coursepage-eprestore-cancel-button' => 'Cancel',
	'coursepage-eprestore-summary-value' => 'Restore course to the revision made on $1 by $2',
	'coursepage-eprestore-restored' => 'Successfully restored course $1.',
	'coursepage-eprestore-restore-failed' => 'Failed to restore course $1.',

	// Institution undo revision
	'orgpage-epundo-title' => 'Undo revision of institution "$1"',
	'orgpage-epundo-text' => 'You are about undo a single revison of institution $1.', 
	'orgpage-epundo-summary' => 'Reason for revert:',
	'orgpage-epundo-undo-button' => 'Undo revision',
	'orgpage-epundo-cancel-button' => 'Cancel',
	'orgpage-epundo-summary-value' => 'Undo revision made on $1 by $2',
	'orgpage-epundo-undid' => 'Successfully undid revision of institution $1.',
	'orgpage-epundo-undo-failed' => 'Failed to undo revision of institution $1.',

	// Course undo revision
	'coursepage-epundo-title' => 'Undo revision of  course "$1"',
	'coursepage-epundo-text' => 'You are about undo a single revison of course $1.', 
	'coursepage-epundo-summary' => 'Reason for revert:',
	'coursepage-epundo-undo-button' => 'Undo revision',
	'coursepage-epundo-cancel-button' => 'Cancel',
	'coursepage-epundo-summary-value' => 'Undo revision made on $1 by $2',
	'coursepage-epundo-undid' => 'Successfully undid revision of course $1.',
	'coursepage-epundo-undo-failed' => 'Failed to undo revision of course $1.',

	// Course undeletion
	'coursepage-epundelete-title' => 'Undelete course "$1"',
	'coursepage-epundelete-text' => 'You are about to undelete course $1',
	'coursepage-epundelete-summary' => 'Reason for undeletion:',
	'coursepage-epundelete-undelete-button' => 'Undelete course',
	'coursepage-epundelete-cancel-button' => 'Cancel',
	'coursepage-epundelete-undid' => 'Successfully undeleted course $1.',
	'coursepage-epundelete-undo-failed' => 'Failed to undelete course $1.',
	'coursepage-epundelete-failed-norevs' => 'Failed to undelete course $1. It has no revisions to undelete.',
	'coursepage-epundelete-failed-exists' => 'Failed to undelete course $1. It already exists.',

	// Institution undeletion
	'orgpage-epundelete-title' => 'Undelete institution "$1"',
	'orgpage-epundelete-text' => 'You are about to undelete institution $1',
	'orgpage-epundelete-summary' => 'Reason for undeletion:',
	'orgpage-epundelete-undelete-button' => 'Undelete institution',
	'orgpage-epundelete-cancel-button' => 'Cancel',
	'orgpage-epundelete-undid' => 'Successfully undeleted institution $1.',
	'orgpage-epundelete-undo-failed' => 'Failed to undelete institution $1.',
	'orgpage-epundelete-failed-norevs' => 'Failed to undelete institution $1. It has no revisions to undelete.',
	'orgpage-epundelete-failed-exists' => 'Failed to undelete institution $1. It already exists.',

	// Special:Ambassador
	'ep-ambassador-does-not-exist' => 'There is no ambassador with name "$1". See [[Special:Ambassadors|here]] for a list of ambassadors.',
	'ep-ambassador-title' => 'Ambassador: $1',

	// Special:Student
	'ep-student-none' => 'There is no student with user name "$1". See [[Special:Students|here]] for a list of all students.',
	'ep-student-title' => 'Student: $1',
	'ep-student-actively-enrolled' => 'Currently enrolled',
	'ep-student-no-active-enroll' => 'Not currently enrolled',
	'specialstudent-summary-active-enroll' => 'Enrollment status',
	'specialstudent-summary-last-active' => 'Last activity',
	'specialstudent-summary-first-enroll' => 'First enrollment',
	'specialstudent-summary-user' => 'User',
	'ep-student-courses' => 'Courses this student has enrolled in',

	// Special:Enroll
	'ep-enroll-title' => 'Enroll for $1 at $2',
	'ep-enroll-login-first' => 'Before you can enroll in this course, you need to login.',
	'ep-enroll-login-and-enroll' => 'Login with an existing account & enroll',
	'ep-enroll-signup-and-enroll' => 'Create a new account & enroll',
	'ep-enroll-not-allowed' => 'Your account is not allowed to enroll',
	'ep-enroll-invalid-id' => 'The course you tried to enroll for does not exist. A list of existing courses can be found [[Special:Courses|here]].',
	'ep-enroll-no-id' => 'You need to specify a course to enroll for. A list of existing courses can be found [[Special:Courses|here]].',
	'ep-enroll-invalid-token' => 'The token you provided is invalid.',
	'ep-enroll-legend' => 'Enroll',
	'ep-enroll-header' => 'In order to enroll for this course, all you need to do is fill out this form and click the submission button. After that you will be enrolled.',
	'ep-enroll-gender' => 'Gender (optional)',
	'ep-enroll-realname' => 'Real name (required)',
	'ep-enroll-invalid-name' => 'The name needs to be at least contain $1 {{PLURAL:$1|character|characters}}.',
	'ep-enroll-invalid-gender' => 'Please select one of these genders',
	'ep-enroll-add-token' => 'Enter your enrollment token',
	'ep-enroll-add-token-doc' => 'In order to enroll for this course, you need a token provided by your instructor or one of the ambassadors for your course.',
	'ep-enroll-token' => 'Enrollment token',
	'ep-enroll-submit-token' => 'Enroll with this token',
	'ep-enroll-course-passed' => 'This course has ended, so you can no longer enroll for it. A list of existing courses can be found [[Special:Courses|here]].',
	'ep-enroll-course-planned' => 'This course has not yet started, please be patient. A list of existing courses can be found [[Special:Courses|here]].',

	// Special:Disenroll
	'ep-disenroll-no-name' => 'You need to provide the name of the course you want to disenroll from.',
	'ep-disenroll-invalid-name' => 'There is no course with name "$1".',
	'ep-disenroll-course-passed' => 'This course has ended. You can no longer disenroll from it.',
	'ep-disenroll-not-enrolled' => 'You are not enrolled in this course, so cannot disenroll from it.',
	'ep-disenroll-title' => 'Disenroll from course "$1"',
	'ep-disenroll-text' => 'You are about to disenroll from course "$1". This will remove any articles you are working on for this course from your student profile.',
	'ep-disenroll-button' => 'Disenroll',
	'ep-disenroll-summary' => 'Why are you disenrolling?',
	'ep-disenroll-cancel' => 'Cancel',
	'ep-disenroll-fail' => 'Something went wrong - could not disenroll you from this course.',
	'ep-disenroll-success' => 'You have been successfully disenrolled from this course!',
	'ep-disenroll-returnto' => 'Return to [[Course:$1|course $1]].',

	// Special:MyCourses
	'ep-mycourses-enrolled' => 'You have successfully enrolled for $1 at $2.',
	'ep-mycourses-not-enrolled' => 'You are not enrolled in any course. A list of courses can be found [[Special:Courses|here]].',
	'ep-mycourses-current' => 'Active courses',
	'ep-mycourses-passed' => 'Passed courses',
	'ep-mycourses-header-name' => 'Name',
	'ep-mycourses-header-institution' => 'Institution',
	'ep-mycourses-show-all' => 'This page shows one of the courses you are enrolled in. You can also view all [[Special:MyCourses|your courses]].',
	'ep-mycourses-no-such-course' => 'You are not enrolled in any course with name "$1". The courses you are enrolled in are listed below.',
	'ep-mycourses-course-title' => 'My courses: $1 at $2',
	'specialmycourses-summary-name' => 'Course name',
	'specialmycourses-summary-org' => 'Institution name',
    'ep-mycourses-nocourses-epstudent' => 'You are not enrolled in any [[Special:Courses|courses]].',
	'ep-mycourses-enrollment' => 'Courses I am enrolled in',
	'ep-mycourses-login-first' => 'You need to login before you can view your courses.',
	'ep-mycourses-courses-epoa' => '{{PLURAL:$1|Course|Courses}} I am {{GENDER:$1|Online Ambassador}} for',
	'ep-mycourses-courses-epca' => '{{PLURAL:$1|Course|Courses}} I am {{GENDER:$1|Campus Ambassador}} for',
	'ep-mycourses-courses-epinstructor' => '{{PLURAL:$1|Course|Courses}} I am {{GENDER:$1|Instructor}} for',
	'ep-mycourses-courses-epstudent' => '{{PLURAL:$1|Course|Courses}} I am enrolled in',
	'ep-mycourses-nocourses-epca' => 'There are no courses you are Campus Ambassador for yet.',
	'ep-mycourses-nocourses-epoa' => 'There are no courses you are Online Ambassador for yet.',
	'ep-mycourses-nocourses-epinstructor' => 'There are no courses you are Instructor for yet.',
	'ep-mycourses-enrolledin' => 'You are currently enrolled in course $1 at institution $2.',
	'ep-mycourses-course-org-links' => 'Course $1 at institution $2.',
	'ep-mycourses-articletable' => 'These are the articles you are working on and their reviewers:',

	// ep.enlist instructor
	'ep-instructor-remove-title' => 'Remove instructor from course',
	'ep-instructor-remove-button' => 'Remove instructor',
	'ep-instructor-removing' => 'Removing...',
	'ep-instructor-removal-success' => 'This instructor has been successfully removed from this course.',
	'ep-instructor-close-button' => 'Close',
	'ep-instructor-remove-retry' => 'Retry',
	'ep-instructor-remove-failed' => 'Something went wrong - could not remove the instructor from the course.',
	'ep-instructor-cancel-button' => 'Cancel',
	'ep-instructor-remove-text' => 'You are about to remove $2 (Username: $1) as {{GENDER:$1|instructor}} from course $3. Please enter a brief summary with the reason for this removal.',
	'ep-instructor-adding' => 'Adding...',
	'ep-instructor-addittion-success' => '$1 has been successfully added as {{GENDER:$1|instructor}} for course $2!',
	'ep-instructor-addittion-self-success' => 'You have been successfully added as {{GENDER:$1|instructor}} for course $2!',
	'ep-instructor-addittion-null' => '$1 has already been added as {{GENDER:$1|instructor}} to course $2',
	'ep-instructor-addittion-invalid-user' => 'There is no user with name $1, so no one has been added to course $2',
	'ep-instructor-add-close-button' => 'Close',
	'ep-instructor-add-retry' => 'Retry',
	'ep-instructor-addittion-failed' => 'Something went wrong - could not add the instructor to the course.',
	'ep-instructor-add-title' => 'Add an instructor to the course',
	'ep-instructor-add-self-title' => 'Become an {{GENDER:$1|instructor}} for this course',
	'ep-instructor-add-button' => 'Add instructor',
	'ep-instructor-add-self-button' => 'Become {{GENDER:$1|instructor}}',
	'ep-instructor-add-text' => 'You are adding an instructor for course $1. Enter the username of the instructor and a brief description why this person is being added.',
	'ep-instructor-add-self-text' => 'You are adding yourself as {{GENDER:$1|instructor}} for course $1. Please add a brief description why you are doing so.',
	'ep-instructor-add-cancel-button' => 'Cancel',
	'ep-instructor-summary-input' => 'Summary:',
	'ep-instructor-name-input' => 'User name:',

	// ep.enlist online
	'ep-online-remove-title' => 'Remove Online Ambassador from course',
	'ep-online-remove-button' => 'Remove Online Ambassador',
	'ep-online-removing' => 'Removing...',
	'ep-online-removal-success' => 'This Online Ambassador has been successfully removed from this course.',
	'ep-online-close-button' => 'Close',
	'ep-online-remove-retry' => 'Retry',
	'ep-online-remove-failed' => 'Something went wrong - could not remove the Online Ambassador from the course.',
	'ep-online-cancel-button' => 'Cancel',
	'ep-online-remove-text' => 'You are about to remove $2 (Username: $1) as {{GENDER:$1|Online Ambassador}} from course $3. Please enter a brief summary with the reason for this removal.',
	'ep-online-adding' => 'Adding...',
	'ep-online-addittion-success' => '$1 has been successfully added as {{GENDER:$1|Online Ambassador}} for course $2!',
	'ep-online-addittion-self-success' => 'You have been successfully added as {{GENDER:$1|Online Ambassador}} for course $2!',
	'ep-online-addittion-null' => '$1 has already been added as {{GENDER:$1|Online Ambassador}} to course $2',
	'ep-online-addittion-invalid-user' => 'There is no user with name $1, so no one has been added to course $2',
	'ep-online-add-close-button' => 'Close',
	'ep-online-add-retry' => 'Retry',
	'ep-online-addittion-failed' => 'Something went wrong - could not add the Online Ambassador to the course.',
	'ep-online-add-title' => 'Add an Online Ambassador to the course',
	'ep-online-add-self-title' => 'Become an {{GENDER:$1|Online Ambassador}} for this course',
	'ep-online-add-button' => 'Add Online Ambassador',
	'ep-online-add-self-button' => 'Become {{GENDER:$1|Online Ambassador}}',
	'ep-online-add-text' => 'You are adding an Online Ambassador for course $1. Enter the username of the Online Ambassador and a brief description why this person is being added.',
	'ep-online-add-self-text' => 'You are adding yourself as {{GENDER:$1|Online Ambassador}} for course $1. Please add a brief description why you are doing so.',
	'ep-online-add-cancel-button' => 'Cancel',
	'ep-online-summary-input' => 'Summary:',
	'ep-online-name-input' => 'User name:',

	// ep.enlist campus
	'ep-campus-remove-title' => 'Remove Campus Ambassador from course',
	'ep-campus-remove-button' => 'Remove Campus Ambassador',
	'ep-campus-removing' => 'Removing...',
	'ep-campus-removal-success' => 'This Campus Ambassador has been successfully removed from this course.',
	'ep-campus-close-button' => 'Close',
	'ep-campus-remove-retry' => 'Retry',
	'ep-campus-remove-failed' => 'Something went wrong - could not remove the Campus Ambassador from the course.',
	'ep-campus-cancel-button' => 'Cancel',
	'ep-campus-remove-text' => 'You are about to remove $2 (Username: $1) as {{GENDER:$1|Campus Ambassador}} from course $3. Please enter a brief summary with the reason for this removal.',
	'ep-campus-adding' => 'Adding...',
	'ep-campus-addittion-success' => '$1 has been successfully added as {{GENDER:$1|Campus Ambassador}} for course $2!',
	'ep-campus-addittion-self-success' => 'You have been successfully added as {{GENDER:$1|Campus Ambassador}} for course $2!',
	'ep-campus-addittion-null' => '$1 has already been added as {{GENDER:$1|Campus Ambassador}} to course $2',
	'ep-campus-addittion-invalid-user' => 'There is no user with name $1, so no one has been added to course $2',
	'ep-campus-add-close-button' => 'Close',
	'ep-campus-add-retry' => 'Retry',
	'ep-campus-addittion-failed' => 'Something went wrong - could not add the Campus Ambassador to the course.',
	'ep-campus-add-title' => 'Add a Campus Ambassador to the course',
	'ep-campus-add-self-title' => 'Become an {{GENDER:$1|Campus Ambassador}} for this course',
	'ep-campus-add-button' => 'Add Campus Ambassador',
	'ep-campus-add-self-button' => 'Become {{GENDER:$1|Campus Ambassador}}',
	'ep-campus-add-text' => 'You are adding a Campus Ambassador for course $1. Enter the username of the Campus Ambassador and a brief description why this person is being added.',
	'ep-campus-add-self-text' => 'You are adding yourself as {{GENDER:$1|Campus Ambassador}} for course $1. Please add a brief description why you are doing so.',
	'ep-campus-add-cancel-button' => 'Cancel',
	'ep-campus-summary-input' => 'Summary:',
	'ep-campus-name-input' => 'User name:',

	// EPInstrucor
	'ep-instructor-remove' => 'remove as instructor',

	// EPCA
	'ep-campus-remove' => 'remove as Campus Ambassador',

	// EPOA
	'ep-online-remove' => 'remove as Online Ambassador',

	// API enlist
	'ep-enlist-invalid-user-args' => 'You need to either provide the username or the userid parameter',
	'ep-enlist-invalid-user' => 'The provided user id or name is not valid and can therefore not be associated as instrucor or ambassador with the specified course',
	'ep-enlist-invalid-course' => 'There is no course with the provided ID',

	// Special:OnlineAmbassadorProfile
	'onlineambassadorprofile' => 'Online Ambassador profile',
	'onlineambassadorprofile-legend' => 'My Online Ambassador profile',
	'onlineambassadorprofile-text' => 'Your Online Ambassador profile is what students get to see when they browse available ambassadors.',
	'epoa-profile-invalid-photo' => 'The photo must be located on {{PLURAL:$2|this website: $1|one of these websites: $1}}',
	'epoa-profile-bio' => 'Short bio',
	'epoa-profile-photo' => 'Profile photo',
	'epoa-profile-photo-help' => 'A picture of you that will be shown next to your bio. Enter the name of an image on Wikimedia Commons and a preview will appear. You can type the first few letters of the image name and then select your image from the suggestion list. If you do not have a picture of you on commons yet, [$1 go upload one]!',
	'epoa-profile-saved' => 'Your profile has been saved',
	'epoa-profile-invalid-bio' => 'Your bio needs to be at least contain $1 {{PLURAL:$1|character|characters}}.',
	'epoa-visible' => 'Publicly list me as Online Ambassador',

	// Special:CampusAmbassadorProfile
	'campusambassadorprofile' => 'Campus Ambassador profile',
	'campusambassadorprofile-legend' => 'My Campus Ambassador profile',
	'campusambassadorprofile-text' => 'Your Campus Ambassador profile is what students get to see when they browse available ambassadors.',
	'epca-profile-invalid-photo' => 'The photo must be located on {{PLURAL:$2|this website: $1|one of these websites: $1}}',
	'epca-profile-bio' => 'Short bio',
	'epca-profile-photo' => 'Profile photo',
	'epca-profile-photo-help' => 'A picture of you that will be shown next to your bio. Enter the name of an image on Wikimedia Commons and a preview will appear. You can type the first few letters of the image name and then select your image from the suggestion list. If you do not have a picture of you on commons yet, [$1 go upload one]!',
	'epca-profile-saved' => 'Your profile has been saved',
	'epca-profile-invalid-bio' => 'Your bio needs to be at least contain $1 {{PLURAL:$1|character|characters}}.',
	'epca-visible' => 'Publicly list me as Campus Ambassador',

	// Special:StudentActivity
	'ep-studentactivity-noresults' => 'There are no students that where active in the last 24 hours :(

You can find a full list of students on [[Special:Students|the student list]].',
	'ep-studentactivity-count' => 'There {{PLURAL:$1|is|are}} currently $1 {{PLURAL:$1|student|students}} that {{PLURAL:$1|was|where}} active in the last 24 hours.',

	// Cached special page, back compat for MW < 1.20
	'cachedspecial-viewing-cached-ttl' => 'You are viewing a cached version of this page, which can be up to $1 old.',
	'cachedspecial-viewing-cached-ts' => 'You are viewing a cached version of this page, which might not be completely actual.',
	'cachedspecial-refresh-now' => 'View latest.',

	// Durations, back compat for MW < 1.20
	'duration-seconds' => '$1 {{PLURAL:$1|second|seconds}}',
	'duration-minutes' => '$1 {{PLURAL:$1|minute|minutes}}',
	'duration-hours' => '$1 {{PLURAL:$1|hour|hours}}',
	'duration-days' => '$1 {{PLURAL:$1|day|days}}',
	'duration-weeks' => '$1 {{PLURAL:$1|week|weeks}}',
	'duration-years' => '$1 {{PLURAL:$1|year|years}}',
	'duration-centuries' => '$1 {{PLURAL:$1|century|centuries}}',
);

/** Message documentation (Message documentation)
 * @author Jeroen De Dauw
 *
 * Please leave the doc headers intact, else it becomes very hard to
 * get a decent overview of what is and what is not (yet) documented!
 */
$messages['qqq'] = array(
	'educationprogram-desc' => 'Extension description for on Special:Version',

	// Misc
	'ep-item-summary' => 'Table column header',
	'ep-toplink' => 'Text of a link the the top menu (next to "My preferences")',
	'ep-org-course-delete-comment' => "Success message. $1 is an institution name, $2 is a user provided comment",
	'ep-org-course-delete' => "Success message. $1 is an institution name",
	'ep-form-summary' => 'Summary input label',
	'ep-form-minor' => 'Minor edit checkbox label',
	'ep-move-error' => 'Error message you get when you try to move stuff in or out of an EP namespace',
	'ep-student-view-profile' => 'Text of links to student profiles, typically used in tool link lists, next to stuff such as "talk" and "contributions"',

	// Tabs
	'ep-tab-view' => 'Tab label',
	'ep-tab-edit' => 'Tab label',
	'ep-tab-create' => 'Tab label',
	'ep-tab-history' => 'Tab label',
	'ep-tab-enroll' => 'Tab label',
	'ep-tab-disenroll' => 'Tab label',
	'ep-tab-delete' => 'Tab label',

	// Tooltips
	'tooltip-ep-form-save' => 'Tooltip text',
	'tooltip-ep-edit-institution' => 'Tooltip text',
	'tooltip-ep-edit-course' => 'Tooltip text',
	'tooltip-ep-summary' => 'Tooltip text',
	'tooltip-ep-minor' => 'Tooltip text',

	// Access keys
	'accesskey-ep-form-save' => 'Access key, do not translate',
	'accesskey-ep-edit-institution' => 'Access key, do not translate',
	'accesskey-ep-edit-course' => 'Access key, do not translate',
	'accesskey-ep-summary' => 'Access key, do not translate',
	'accesskey-ep-minor' => 'Access key, do not translate',

	// Navigation links
	'ep-nav-orgs' => 'Text of link to institution list',
	'ep-nav-courses' => 'Text of link to courses list',
	'ep-nav-mycourses' => 'Text of link to Special:MyCourses',
	'ep-nav-students' => 'Text of link to students list',
	'ep-nav-mentors' => 'Text of link to mentors list',
	'ep-nav-cas' => 'Text of link to Campus Ambassadors list',
	'ep-nav-oas' => 'Text of link to Online Ambassadors list',
	'ep-nav-oaprofile' => 'Text of link to a users Online Ambassadors profile',
	'ep-nav-caprofile' => 'Text of link to a users Campus Ambassadors profile',

	// Logging
	// log-name = {{Name of the $1 log group}}
	'log-name-institution' => '{{log-name|institution}}',
	'log-name-course' => '{{log-name|course}}',
	'log-name-student' => '{{log-name|student}}',
	'log-name-online' => '{{log-name|online}}',
	'log-name-campus' => '{{log-name|campus}}',
	'log-name-instructor' => '{{log-name|instructor}}',
	'log-name-eparticle' => '{{log-name|eparticle}}',

	// log-header = {{Header of the $1 log groups page}}
	'log-header-institution' => '{{log-header|institution}}',
	'log-header-course' => '{{log-header|course}}',
	'log-header-instructor' => '{{log-header|instructor}}',
	'log-header-campus' => '{{log-header|campus}}',
	'log-header-online' => '{{log-header|online}}',
	'log-header-student' => '{{log-header|student}}',

	// log-description = {{Description of the $1 log group}}
	'log-description-institution' => '{{log-description|institution}}',
	'log-description-course' => '{{log-description|course}}',
	'log-description-instructor' => '{{log-description|instructor}}',
	'log-description-online' => '{{log-description|online}}',
	'log-description-campus' => '{{log-description|campus}}',
	'log-description-student' => '{{log-description|student}}',

	'logentry-institution-add' => 'Log entry. $1 is the performing user, $3 is the name of the added institution',
	'logentry-institution-remove' => 'Log entry. $1 is the performing user, $3 is the name of the removed institution',
	'logentry-institution-update' => 'Log entry. $1 is the performing user, $3 is the name of the updated institution',
	'logentry-institution-undelete' => 'Log entry. $1 is the performing user, $3 is the name of the undeleted institution',

	'logentry-course-add' => 'Log entry. $1 is the performing user, $3 is the name of the added course',
	'logentry-course-remove' => 'Log entry. $1 is the performing user, $3 is the name of the removed course',
	'logentry-course-update' => 'Log entry. $1 is the performing user, $3 is the name of the updated course',
	'logentry-course-undelete' => 'Log entry. $1 is the performing user, $3 is the name of the undeleted course',

	'logentry-instructor-add' => 'Log entry. $1 is the performing user (link), $2 is the name of this user, $3 is the name of the course, $4 is the amount of added people, $5 is a list of added people',
	'logentry-instructor-remove' => 'Log entry. $1 is the performing user (link), $2 is the name of this user, $3 is the name of the course, $4 is the amount of added people, $5 is a list of removed people',
	'logentry-instructor-selfadd' => 'Log entry. $1 is the performing user (link), $2 is the name of this user, $3 is the name of the course',
	'logentry-instructor-selfremove' => 'Log entry. $1 is the performing user (link), $2 is the name of this user, $3 is the name of the course',

	'logentry-online-add' => 'Log entry. $1 is the performing user (link), $2 is the name of this user, $3 is the name of the course, $4 is the amount of added people, $5 is a list of added people',
	'logentry-online-remove' => 'Log entry. $1 is the performing user (link), $2 is the name of this user, $3 is the name of the course, $4 is the amount of added people, $5 is a list of removed people',
	'logentry-online-selfadd' => 'Log entry. $1 is the performing user (link), $2 is the name of this user, $3 is the name of the course',
	'logentry-online-selfremove' => 'Log entry. $1 is the performing user (link), $2 is the name of this user, $3 is the name of the course',
	'logentry-online-profilesave' => 'Log entry. User updated own ambassador profile. $1 is the performing user (link), $2 is the name of this user',

	'logentry-campus-add' => 'Log entry. $1 is the performing user (link), $2 is the name of this user, $3 is the name of the course, $4 is the amount of added people, $5 is a list of added people',
	'logentry-campus-remove' => 'Log entry. $1 is the performing user (link), $2 is the name of this user, $3 is the name of the course, $4 is the amount of added people, $5 is a list of removed people',
	'logentry-campus-selfadd' => 'Log entry. $1 is the performing user (link), $2 is the name of this user, $3 is the name of the course',
	'logentry-campus-selfremove' => 'Log entry. $1 is the performing user (link), $2 is the name of this user, $3 is the name of the course',
	'logentry-campus-profilesave' => 'Log entry. User updated own ambassador profile. $1 is the performing user (link), $2 is the name of this user',

	'logentry-student-add' => 'Log entry. $1 is the performing user (link), $2 is the name of this user, $3 is the course in which the user enrolled',
	'logentry-student-remove' => 'Log entry. $1 is the performing user (link), $2 is the name of this user, $3 is the name of the course, $4 is the amount of added people, $5 is a list of removed people',
	'logentry-student-selfadd' => 'Log entry. $1 is the performing user (link), $2 is the name of this user, $3 is the course in which the user enrolled',
	'logentry-student-selfremove' => 'Log entry. $1 is the performing user (link), $2 is the name of this user, $3 is the course in which the user disenrolled',

	'logentry-eparticle-selfadd' => 'Log entry. $1 is the performing user (link), $2 is the name of this user, $3 is the article, $4 is the course',
	'logentry-eparticle-selfremove' => 'Log entry. $1 is the performing user (link), $2 is the name of this user, $3 is the article, $4 is the course',
	'logentry-eparticle-add' => 'Log entry. $1 is the performing user (link), $2 is the name of this user, $3 is the article, $4 is the course, $5 is the user that own the article (link), $6 is the name of this user',
	'logentry-eparticle-remove' => 'Log entry. $1 is the performing user (link), $2 is the name of this user, $3 is the article, $4 is the course, $5 is the user that own the article (link), $6 is the name of this user',
	'logentry-eparticle-review' => 'Log entry. $1 is the performing user (link), $2 is the name of this user, $3 is the article, $4 is the course, $5 is the user that own the article (link), $6 is the name of this user',
	'logentry-eparticle-unreview' => 'Log entry. $1 is the performing user (link), $2 is the name of this user, $3 is the article, $4 is the course, $5 is the user that own the article (link), $6 is the name of this user',

	// Preferences
	'prefs-education' => 'Preferences tab label',
	'ep-prefs-showtoplink' => 'Preference checkbox label',
	'ep-prefs-bulkdelorgs' => 'Preference checkbox label',
	'ep-prefs-bulkdelcourses' => 'Preference checkbox label',

	// Rights
	'right-ep-org' => '{{doc-right|ep-org}}',
	'right-ep-course' => '{{doc-right|ep-course}}',
	'right-ep-token' => '{{doc-right|ep-token}}',
	'right-ep-remstudent' => '{{doc-right|ep-remstudent}}',
	'right-ep-enroll' => '{{doc-right|ep-enroll}}',
	'right-ep-online' => '{{doc-right|ep-online}}',
	'right-ep-campus' => '{{doc-right|ep-campus}}',
	'right-ep-instructor' => '{{doc-right|ep-instructor}}',
	'right-ep-beonline' => '{{doc-right|ep-beonline}}',
	'right-ep-becampus' => '{{doc-right|ep-becampus}}',
	'right-ep-beinstructor' => '{{doc-right|ep-beinstructor}}',
	'right-ep-bereviewer' => '{{doc-right|ep-bereviewer}}',
	'right-ep-remreviewer' => '{{doc-right|ep-remreviewer}}',
	'right-ep-bulkdelorgs' => '{{doc-right|ep-bulkdelorgs}}',
	'right-ep-bulkdelcourses' => '{{doc-right|ep-bulkdelcourses}}',

	// Actions
	'action-ep-org' => '{{doc-action|ep-org}}',
	'action-ep-course' => '{{doc-action|ep-course}}',
	'action-ep-token' => '{{doc-action|ep-token}}',
	'action-ep-remstudent' => '{{doc-action|ep-remstudent}}',
	'action-ep-enroll' => '{{doc-action|ep-enroll}}',
	'action-ep-online' => '{{doc-action|ep-online}}',
	'action-ep-campus' => '{{doc-action|ep-campus}}',
	'action-ep-instructor' => '{{doc-action|ep-instructor}}',
	'action-ep-beonline' => '{{doc-action|ep-beonline}}',
	'action-ep-becampus' => '{{doc-action|ep-becampus}}',
	'action-ep-beinstructor' => '{{doc-action|ep-beinstructor}}',
	'action-ep-bereviewer' => '{{doc-action|ep-bereviewer}}',
	'action-ep-remreviewer' => '{{doc-action|ep-remreviewer}}',
	'action-ep-bulkdelorgs' => '{{doc-action|ep-bulkdelorgs}}',
	'action-ep-bulkdelcourses' => '{{doc-action|ep-bulkdelcourses}}',

	// Special pages
	'specialpages-group-education' => 'Special pages group, h2',
	'special-mycourses' => '{{doc-special|mycourses}}',
	'special-institutions' => '{{doc-special|institutions}}',
	'special-student' => '{{doc-special|student}}',
	'special-students' => '{{doc-special|students}}',
	'special-courses' => '{{doc-special|courses}}',
	'special-educationprogram' => '{{doc-special|educationprogram}}',
	'special-enroll' => '{{doc-special|enroll}}',
	'special-onlineambassadors' => '{{doc-special|onlineambassadors}}',
	'special-campusambassadors' => '{{doc-special|campusambassadors}}',
	'special-onlineambassador' => '{{doc-special|onlineambassador}}',
	'special-campusambassador' => '{{doc-special|campusambassador}}',
	'special-disenroll' => '{{doc-special|disenroll}}',
	'special-studentactivity' => '{{doc-special|studentactivity}}',

);
