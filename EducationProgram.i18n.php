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

	// Tooltips
	'tooltip-ep-form-save' => 'Save',
	'tooltip-ep-edit-institution' => 'Edit this institution',
	'tooltip-ep-edit-course' => 'Edit this course',
	'tooltip-ep-edit-mc' => 'Edit this master course',

	// Access keys
	'accesskey-ep-form-save' => 's', # do not translate or duplicate this message to other languages
	'accesskey-ep-edit-institution' => 'e', # do not translate or duplicate this message to other languages
	'accesskey-ep-edit-course' => 'e', # do not translate or duplicate this message to other languages
	'accesskey-ep-edit-mc' => 'e', # do not translate or duplicate this message to other languages

	// Navigation links
	'ep-nav-orgs' => 'Institution list',
	'ep-nav-courses' => 'Courses list',
	'ep-nav-mcs' => 'Master courses list',
	'ep-nav-mycourses' => 'My courses',
	'ep-nav-students' => 'Student list',
	'ep-nav-mentors' => 'Ambassador list',

	// Logging
	'log-name-institution' => 'Institution log',
	'log-name-course' => 'Course log',
	'log-name-mc' => 'Master course log',
	'log-name-student' => 'Student log',
	'log-name-ambassador' => 'Ambassador log',
	'log-name-instructor' => 'Instructor log',

	'log-header-institution' => 'These events track the changes that are made to institutions.',
	'log-header-course' => 'These events track the changes that are made to courses.',
	'log-header-mc' => 'These events track the changes that are made to master courses.',

	'logentry-institution-add' => '$1 created institution $3',
	'logentry-institution-remove' => '$1 removed institution $3',
	'logentry-institution-update' => '$1 updated institution $3',

	'logentry-course-add' => '$1 created course $3',
	'logentry-course-remove' => '$1 removed course $3',
	'logentry-course-update' => '$1 updated course $3',

	'logentry-mc-add' => '$1 created master course $3',
	'logentry-mc-remove' => '$1 removed master course $3',
	'logentry-mc-update' => '$1 updated master course $3',

	'logentry-instructor-add' => '$1 {{GENDER:$2|added}} {{PLURAL:$4|instructor|instructors}} $5 to course $3',
	'logentry-instructor-remove' => '$1 {{GENDER:$2|removed}} {{PLURAL:$4|instructor|instructors}} $5 from course $3',
	'logentry-instructor-selfadd' => '$1 {{GENDER:$2|added himself|added herself}} as instructor to course $3',
	'logentry-instructor-selfremove' => '$1 {{GENDER:$2|removed himself|removed herself}} as instructor from course $3',

	'logentry-ambassador-add' => '$1 added {{PLURAL:$4|ambassador|ambassadors}} $5 to course $3',
	'logentry-ambassador-remove' => '$1 removed {{PLURAL:$4|ambassador|ambassadors}} $5 from course $3',
	'logentry-ambassador-selfadd' => '$1 added {{GENDER:$2|himself|herself}} as ambassador to course $3',
	'logentry-ambassador-selfremove' => '$1 removed {{GENDER:$2|himself|herself}} as ambassador from course $3',

	'logentry-student-enroll' => '$1 enrolled in course $3',
	'logentry-student-remove' => '$1 removed $4 as student from course $3',
	'logentry-student-selfremove' => '$1 removed {{GENDER:$2|his|her}} enrollment from course $3',

	// Preferences
	'prefs-education' => 'Education',
	'ep-prefs-showtoplink' => 'Show a link to [[Special:MyCourses|your courses]] at the top of every page.',

	// Rights
	'right-ep-org' => 'Manage Education Program institutions',
	'right-ep-course' => 'Manage Education Program courses',
	'right-ep-mcs' => 'Manage Education Program master courses',
	'right-ep-token' => 'See Education Program enrollment tokens',
	'right-ep-remstudent' => 'Remove students from courses',
	'right-ep-enroll' => 'Enroll in Education Program courses',
	'right-ep-online' => 'Add or remove online ambassadors to courses',
	'right-ep-campus' => 'Add or remove campus ambassadors to courses',
	'right-ep-instructor' => 'Add or remove instructors to master courses',

	// Actions
	'action-ep-org' => 'manage institutions',
	'action-ep-course' => 'manage courses',
	'action-ep-mc' => 'manage master courses',
	'action-ep-token' => 'see enrollment tokens',
	'action-ep-remstudent' => 'remove students from courses',
	'action-ep-enroll' => 'enroll in courses',
	'action-ep-online' => 'add or remove online ambassadors to courses',
	'action-ep-campus' => 'add or remove campus ambassadors to courses',
	'action-ep-instructor' => 'add or remove instructors to master courses',

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
	'special-institution' => 'Institution',
	'special-institutions' => 'Institutions',
	'special-student' => 'Student',
	'special-students' => 'Students',
	'special-course' => 'Course',
	'special-courses' => 'Courses',
	'special-educationprogram' => 'Education Program',
	'special-editinstitution-add' => 'Add institution',
	'special-editinstitution-edit' => 'Edit institution',
	'special-mastercourses' => 'Master courses',
	'special-mastercourse' => 'Master course',
	'special-editmc-add' => 'Add master course',
	'special-editmc-edit' => 'Edit master course',
	'special-editcourse-add' => 'Add course',
	'special-editcourse-edit' => 'Edit course',
	'special-enroll' => 'Enroll',
	'special-onlineambassadors' => 'Online ambassadors',
	'special-campusambassadors' => 'Campus ambassadors',
	'special-onlineambassador' => 'Online ambassador',
	'special-campusambassador' => 'Campus ambassador',

	// Course statuses
	'ep-course-status-passed' => 'Passed',
	'ep-course-status-current' => 'Current',
	'ep-course-status-planned' => 'Planned',

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
	'ep-courses-namedoc' => 'Enter the name for the new course (which should be unique) and hit the button.',
	'ep-courses-newname' => 'Course name:',
	'ep-courses-add' => 'Add course',
	'ep-courses-addorgfirst' => 'You need to [[Special:Institutions|add an institution]] before you can create any courses.',
	'ep-courses-noorgs' => 'You are not a mentor of any institutions yet, so cannot add any courses.',
	'ep-courses-neworg' => 'Institution',

	// Special:Courses
	'ep-courses-nosuchcourse' => 'There is no course with id "$1". Existing courses are listed below.',
	'ep-courses-noresults' => 'There are no courses to list.',
	'ep-courses-addnew' => 'Add a new course',
	'ep-courses-namedoc' => 'Enter the master course the course belongs to and the year in which it is active.',
	'ep-courses-newyear' => 'Course year:',
	'ep-courses-newcourse' => 'Course master course:',
	'ep-courses-add' => 'Add course',
	'ep-courses-nocourses' => 'There are no master courses yet. You need to [[Special:MasterCourses|add a master course]] before you can create any courses.',
	'ep-courses-addcoursefirst' => 'The institutions you are a mentor for do not have any master courses associated with them. You need to [[Special:MasterCourses|add a master course]] before you can create any courses.',

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

	// Org pager
	'eporgpager-header-name' => 'Name',
	'eporgpager-header-city' => 'City',
	'eporgpager-header-country' => 'Country',
	'eporgpager-filter-country' => 'Country',
	'eporgpager-header-courses' => 'Courses',
	'eporgpager-header-students' => 'Students',
	'eporgpager-header-mcs' => 'Master courses',
	'eporgpager-header-active' => 'Active',
	'eporgpager-filter-active' => 'Active courses',
	'eporgpager-yes' => 'Yes',
	'eporgpager-no' => 'No',

	// Master course pager
	'epmcpager-header-name' => 'Name',
	'epmcpager-header-org-id' => 'Institution',
	'epmcpager-filter-org-id' => 'Institution',
	'epmcpager-header-students' => 'Students',
	'epmcpager-header-active' => 'Active',
	'epmcpager-filter-active' => 'Active courses',
	'epmcpager-yes' => 'Yes',
	'epmcpager-no' => 'No',

	// Course pager
	'epcoursepager-header-id' => 'Id',
	'epcoursepager-header-course-id' => 'Master course',
	'epcoursepager-header-year' => 'Year',
	'epcoursepager-header-start' => 'Start',
	'epcoursepager-header-end' => 'End',
	'epcoursepager-filter-course-id' => 'Master course',
	'epcoursepager-filter-year' => 'Year',
	'epcoursepager-filter-org-id' => 'Institution',
	'epcoursepager-header-status' => 'Status',
	'epcoursepager-filter-status' => 'Status',
	'epcoursepager-header-students' => 'Students',

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

	// Special:EditInstitution
	'editinstitution-text' => 'Enter the institution details below and click submit to save your changes.',
	'educationprogram-org-edit-name' => 'Institution name',
	'editinstitution-add-legend' => 'Add institution',
	'editinstitution-edit-legend' => 'Edit institution',
	'educationprogram-org-edit-city' => 'City',
	'educationprogram-org-edit-country' => 'Country',
	'educationprogram-org-submit' => 'Submit',

	// Special:EditCourse
	'editcourse-add-legend' => 'Add course',
	'editcourse-edit-legend' => 'Edit course',
	'ep-course-edit-name' => 'Name',
	'ep-course-edit-org' => 'Institution',
	'ep-course-edit-description' => 'Description',

	'ep-course-invalid-name' => 'This name is to short. It needs to be at least contain $1 {{PLURAL:$1|character|characters}}.',
	'ep-course-invalid-description' => 'This description is to short. It needs to be at least contain $1 {{PLURAL:$1|character|characters}}.',
	'ep-course-invalid-org' => 'This institution does not exist',

	// Special:EditCourse
	'editcourse-add-legend' => 'Add course',
	'editcourse-edit-legend' => 'Edit course',
	'ep-course-edit-year' => 'Year',
	'ep-course-edit-mastercourse' => 'Master course',
	'ep-course-edit-start' => 'Start date',
	'ep-course-edit-end' => 'End date',
	'ep-course-edit-token' => 'Enrollment token',
	'ep-course-edit-description' => 'Description',

	'ep-course-invalid-mastercourse' => 'This master course does not exist.',
	'ep-course-invalid-token' => 'The token needs to be at least contain $1 {{PLURAL:$1|character|characters}}.',
	'ep-course-invalid-description' => 'The description needs to be at least contain $1 {{PLURAL:$1|character|characters}}.',

	// ep.pager
	'ep-pager-confirm-delete' => 'Are you sure you want to delete this item?',
	'ep-pager-delete-fail' => 'Could not delete this item.',
	'ep-pager-confirm-delete-selected' => 'Are you sure you want to delete the selected {{PLURAL:$1|item|items}}?',
	'ep-pager-delete-selected-fail' => 'Could not delete the selected {{PLURAL:$1|item|items}}.',

	// Special:Institution
	'ep-institution-none' => 'There is no institution with name "$1". See [[Special:Institution|here]] for a list of institutions.',
	'ep-institution-create' => 'There is no institution with name "$1" yet, but you can create it.',
	'ep-institution-title' => 'Institution: $1',
	'ep-institution-courses' => 'Courses',
	'specialinstitution-summary-name' => 'Name',
	'specialinstitution-summary-city' => 'City',
	'specialinstitution-summary-country' => 'Country',
	'specialinstitution-summary-status' => 'Status',
	'specialinstitution-summary-courses' => 'Course count',
	'specialinstitution-summary-terms' => 'Term count',
	'specialinstitution-summary-students' => 'Student count',
	'ep-institution-nav-edit' => 'Edit this institution',
	'ep-institution-add-course' => 'Add a course',
	'ep-institution-inactive' => 'Inactive',
	'ep-institution-active' => 'Active',

	// Special:Course
	'ep-course-title' => 'Course: $1',
	'ep-course-terms' => 'Terms',
	'ep-course-none' => 'There is no course with name "$1". See [[Special:Courses|here]] for a list of courses.',
	'ep-course-create' => 'There is no course with name "$1" yet, but you can create it.',
	'specialcourse-summary-name' => 'Name',
	'specialcourse-summary-org' => 'Institution',
	'specialcourse-summary-students' => 'Student count',
	'specialcourse-summary-status' => 'Status',
	'ep-course-description' => 'Description',
	'ep-course-nav-edit' => 'Edit this course',
	'ep-course-add-term' => 'Add a term',
	'ep-course-inactive' => 'Inactive',
	'ep-course-active' => 'Active',
	'specialcourse-summary-terms' => 'Term count',
	'specialcourse-summary-instructors' => 'Instructors',
	'ep-course-no-instructors' => 'There are no instructors for this course.',
	'ep-course-become-instructor' => 'Become instructor',
	'ep-course-add-instructor' => 'Add an instructor',

	// Special:Course
	'ep-course-title' => 'Course: $1',
	'ep-course-students' => 'Students',
	'ep-course-none' => 'There is no course with id "$1". See [[Special:Courses|here]] for a list of courses.',
	'ep-course-create' => 'There is no course with id "$1", but you can create a new one.',
	'specialcourse-summary-org' => 'Institution',
	'specialcourse-summary-mastercourse' => 'Master course',
	'specialcourse-summary-year' => 'Year',
	'specialcourse-summary-start' => 'Start',
	'specialcourse-summary-end' => 'End',
	'ep-course-description' => 'description',
	'specialcourse-summary-token' => 'Enrollment token',
	'ep-course-nav-edit' => 'Edit this course',

	// Special:Ambassador
	'ep-ambassador-does-not-exist' => 'There is no ambassador with name "$1". See [[Special:Ambassadors|here]] for a list of ambassadors.',
	'ep-ambassador-title' => 'Ambassador: $1',

	// Special:Student
	'ep-student-none' => 'There is no student with id "$1". See [[Special:Students|here]] for a list of students.',
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
    'ep-mycourses-not-a-student' => 'You are not enrolled in any [[Special:Courses|courses]].',

	// ep.instructor
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
	'ep-instructor-summary-input' => 'Summary',
	'ep-instructor-name-input' => 'User name',

	// EPInstrucor
	'ep-instructor-remove' => 'remove from course',

	// API addinstructor
	'ep-addinstructor-invalid-user-args' => 'You need to either provide the username or the userid parameter',
	'ep-addinstructor-invalid-user' => 'The provided user id or name is not valid and can therefore not be associated as instrucor with the specified course',
	'ep-addinstructor-invalid-course' => 'There is no course with the provided ID',
);

/** Message documentation (Message documentation)
 * @author Jeroen De Dauw
 */
$messages['qqq'] = array(
	'right-' => '{{doc-right|}}',

	'action-' => '{{doc-action|}}',

	'specialpages-group-education' => 'Special pages group, h2',
	'special-mycourses' => '{{doc-special|mycourses}}',
	'special-institution' => '{{doc-special|institution}}',
	'special-institutions' => '{{doc-special|institutions}}',
	'special-student' => '{{doc-special|student}}',
	'special-students' => '{{doc-special|students}}',
	'special-course' => '{{doc-special|course}}',
	'special-courses' => '{{doc-special|courses}}',
	'special-educationprogram' => '{{doc-special|educationprogram}}',

	'ep-institutions-nosuchinstitution' => 'Error message stating there is no institution with name $1',
);
