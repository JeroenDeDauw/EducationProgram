-- MySQL version of the database schema for the Education Program extension.
-- Licence: GNU GPL v3+
-- Author: Jeroen De Dauw < jeroendedauw@gmail.com >

-- Organizations, ie universities
CREATE TABLE IF NOT EXISTS /*_*/ep_orgs (
  org_id                     INT unsigned        NOT NULL auto_increment PRIMARY KEY,

  org_name                   VARCHAR(255)        NOT NULL, -- Name of the organization
  org_city                   VARCHAR(255)        NOT NULL, -- Name of the city where the org is located
  org_country                VARCHAR(255)        NOT NULL, -- Name of the country where the org is located

  -- Summary fields - cahing data or computations on data stored elswhere
  org_active                 TINYINT unsigned    NOT NULL, -- If the org has any active courses
  org_course_count           SMALLINT unsigned   NOT NULL, -- Amount of courses
  org_instructor_count       SMALLINT unsigned   NOT NULL, -- Amount of instructors
  org_oa_count               INT unsigned        NOT NULL, -- Amount of online ambassadors
  org_ca_count               INT unsigned        NOT NULL, -- Amount of campus ambassadors
  org_student_count          INT unsigned        NOT NULL, -- Amount of students
  org_courses                BLOB                NOT NULL -- The ids of the courses (linking ep_courses.course_id)
) /*$wgDBTableOptions*/;

CREATE UNIQUE INDEX /*i*/ep_org_name ON /*_*/ep_orgs (org_name);
CREATE INDEX /*i*/ep_org_city ON /*_*/ep_orgs (org_city);
CREATE INDEX /*i*/ep_org_country ON /*_*/ep_orgs (org_country);
CREATE INDEX /*i*/ep_org_active ON /*_*/ep_orgs (org_active);
CREATE INDEX /*i*/ep_org_course_count ON /*_*/ep_orgs (org_course_count);
CREATE INDEX /*i*/ep_org_oa_count ON /*_*/ep_orgs (org_oa_count);
CREATE INDEX /*i*/ep_org_ca_count ON /*_*/ep_orgs (org_ca_count);
CREATE INDEX /*i*/ep_org_student_count ON /*_*/ep_orgs (org_student_count);
CREATE INDEX /*i*/ep_org_instructor_count ON /*_*/ep_orgs (org_instructor_count);



-- Courses.
CREATE TABLE IF NOT EXISTS /*_*/ep_courses (
  course_id                  INT unsigned        NOT NULL auto_increment PRIMARY KEY,

  course_org_id              INT unsigned        NOT NULL, -- Foreign key on ep_orgs.org_id. Helper field, not strictly needed.
  course_name                VARCHAR(255)        NOT NULL, -- Title of the course. ie "Master in Angry Birds (2012 q1)"
  course_mc                  VARCHAR(255)        NOT NULL, -- Name of the course. ie "Master in Angry Birds"
  course_start               varbinary(14)       NOT NULL, -- Start time of the course
  course_end                 varbinary(14)       NOT NULL, -- End time of the course
  course_description         TEXT                NOT NULL, -- Description of the course
  course_students            BLOB                NOT NULL, --  List of associated students (linking user.user_id)
  course_online_ambs         BLOB                NOT NULL, -- List of associated online ambassadors (linking user.user_id)
  course_campus_ambs         BLOB                NOT NULL, -- List of associated campus ambassadors (linking user.user_id)
  course_instructors         BLOB                NOT NULL, -- List of associated instructors (linking user.user_id)
  course_token               VARCHAR(255)        NOT NULL, -- Token needed to enroll
  course_field               VARCHAR(255)        NOT NULL, -- Field of study
  course_level               VARCHAR(255)        NOT NULL, -- Study level
  course_term                VARCHAR(255)        NOT NULL, -- Academic term
  course_lang                VARCHAR(10)         NOT NULL, -- Language (code)

  -- Summary fields - cahing data or computations on data stored elswhere
  course_instructor_count    TINYINT unsigned    NOT NULL, -- Amount of instructors
  course_oa_count            SMALLINT unsigned   NOT NULL, -- Amount of online ambassadors
  course_ca_count            SMALLINT unsigned   NOT NULL, -- Amount of campus ambassadors
  course_student_count       SMALLINT unsigned   NOT NULL -- Amount of students
) /*$wgDBTableOptions*/;

CREATE INDEX /*i*/ep_course_org_id ON /*_*/ep_courses (course_org_id);
CREATE INDEX /*i*/ep_course_name ON /*_*/ep_courses (course_name);
CREATE INDEX /*i*/ep_course_mc ON /*_*/ep_courses (course_mc);
CREATE INDEX /*i*/ep_course_start ON /*_*/ep_courses (course_start);
CREATE INDEX /*i*/ep_course_end ON /*_*/ep_courses (course_end);
CREATE INDEX /*i*/ep_course_token ON /*_*/ep_courses (course_token);
CREATE INDEX /*i*/ep_course_field ON /*_*/ep_courses (course_field);
CREATE INDEX /*i*/ep_course_level ON /*_*/ep_courses (course_level);
CREATE INDEX /*i*/ep_course_term ON /*_*/ep_courses (course_term);
CREATE INDEX /*i*/ep_course_lang ON /*_*/ep_courses (course_lang);
CREATE INDEX /*i*/ep_course_student_count ON /*_*/ep_courses (course_student_count);
CREATE INDEX /*i*/ep_course_oa_count ON /*_*/ep_courses (course_oa_count);
CREATE INDEX /*i*/ep_course_ca_count ON /*_*/ep_courses (course_ca_count);
CREATE INDEX /*i*/ep_course_instructor_count ON /*_*/ep_courses (course_instructor_count);



-- Articles students are working on.
CREATE TABLE IF NOT EXISTS /*_*/ep_articles (
  article_id                 INT unsigned        NOT NULL auto_increment PRIMARY KEY,

  article_user_id            INT unsigned        NOT NULL, -- Foreign key on user.user_id
  article_course_id          INT unsigned        NOT NULL, -- Foreign key on ep_courses.course_id
  article_page_id            INT unsigned        NOT NULL, -- Foreign key on page.page_id
  article_page_title         varchar(255) binary NOT NULL, -- Full title of the page, to allow for associating non-existing pages

  article_reviewers          BLOB                NOT NULL -- List of reviewers for this article (linking user.user_id)
) /*$wgDBTableOptions*/;

CREATE INDEX /*i*/ep_articles_user_id ON /*_*/ep_articles (article_user_id);
CREATE INDEX /*i*/ep_articles_course_id ON /*_*/ep_articles (article_course_id);
CREATE INDEX /*i*/ep_articles_page_id ON /*_*/ep_articles (article_page_id);
CREATE INDEX /*i*/ep_articles_page_title ON /*_*/ep_articles (article_page_title);
CREATE UNIQUE INDEX /*i*/ep_articles_course_page ON /*_*/ep_articles (article_course_id, article_user_id, article_page_title);



-- Students. In essence this is an extension to the user table.
CREATE TABLE IF NOT EXISTS /*_*/ep_students (
  student_id                 INT unsigned        NOT NULL auto_increment PRIMARY KEY,

  student_user_id            INT unsigned        NOT NULL, -- Foreign key on user.user_id
  student_first_enroll       varbinary(14)       NOT NULL, -- Time of first enrollment

  student_last_active        varbinary(14)       NOT NULL, -- Time of last activity
  student_active_enroll      TINYINT unsigned    NOT NULL -- If the student is enrolled in any active courses
) /*$wgDBTableOptions*/;

CREATE UNIQUE INDEX /*i*/ep_students_user_id ON /*_*/ep_students (student_user_id);
CREATE INDEX /*i*/ep_students_first_enroll ON /*_*/ep_students (student_first_enroll);
CREATE INDEX /*i*/ep_students_last_active ON /*_*/ep_students (student_last_active);
CREATE INDEX /*i*/ep_students_active_enroll ON /*_*/ep_students (student_active_enroll);



-- Links the students with their courses.
CREATE TABLE IF NOT EXISTS /*_*/ep_users_per_course (
  upc_user_id                INT unsigned        NOT NULL, -- Foreign key on ep_user.user_id
  upc_course_id              INT unsigned        NOT NULL, -- Foreign key on ep_courses.course_id
  upc_role                   TINYINT unsigned    NOT NULL -- The role the user has for the course
) /*$wgDBTableOptions*/;

CREATE UNIQUE INDEX /*i*/ep_users_per_course ON /*_*/ep_users_per_course (upc_user_id, upc_course_id, upc_role);
CREATE INDEX /*i*/ep_upc_course_id ON /*_*/ep_users_per_course (upc_course_id);
CREATE INDEX /*i*/ep_upc_role ON /*_*/ep_users_per_course (upc_role);



-- Instructors. In essence this is an extension to the user table.
CREATE TABLE IF NOT EXISTS /*_*/ep_instructors (
  instructor_id              INT unsigned        NOT NULL auto_increment PRIMARY KEY,
  instructor_user_id         INT unsigned        NOT NULL -- Foreign key on user.user_id
) /*$wgDBTableOptions*/;

CREATE UNIQUE INDEX /*i*/ep_instructors_user_id ON /*_*/ep_instructors (instructor_user_id);



-- Campus ambassadors. In essence this is an extension to the user table.
CREATE TABLE IF NOT EXISTS /*_*/ep_cas (
  ca_id                      INT unsigned        NOT NULL auto_increment PRIMARY KEY,
  ca_user_id                 INT unsigned        NOT NULL, -- Foreign key on user.user_id

  ca_visible                 TINYINT unsigned    NOT NULL, -- If the profile should be public
  ca_bio                     TEXT                NOT NULL, -- Bio of the ambassador
  ca_photo                   VARCHAR(255)        NOT NULL -- Name of a photo of the ambassador on commons
) /*$wgDBTableOptions*/;

CREATE UNIQUE INDEX /*i*/ep_cas_user_id ON /*_*/ep_cas (ca_user_id);
CREATE INDEX /*i*/ep_cas_visible ON /*_*/ep_cas (ca_visible);



-- Online ambassadors. In essence this is an extension to the user table.
CREATE TABLE IF NOT EXISTS /*_*/ep_oas (
  oa_id                      INT unsigned        NOT NULL auto_increment PRIMARY KEY,
  oa_user_id                 INT unsigned        NOT NULL, -- Foreign key on user.user_id

  oa_visible                 TINYINT unsigned    NOT NULL, -- If the profile should be public
  oa_bio                     TEXT                NOT NULL, -- Bio of the ambassador
  oa_photo                   VARCHAR(255)        NOT NULL -- Name of a photo of the ambassador on commons
) /*$wgDBTableOptions*/;

CREATE UNIQUE INDEX /*i*/ep_oas_user_id ON /*_*/ep_oas (oa_user_id);
CREATE INDEX /*i*/ep_oas_visible ON /*_*/ep_oas (oa_visible);



-- Revision table, holding blobs of various types of objects, such as orgs or students.
-- This is somewhat based on the (core) revision table and is meant to serve
-- as a prototype for a more general system to store this kind of data in a visioned fashion.
CREATE TABLE IF NOT EXISTS /*_*/ep_revisions (
  rev_id                     INT unsigned        NOT NULL auto_increment PRIMARY KEY,
  rev_object_id              INT unsigned        NOT NULL,
  rev_object_identifier      VARCHAR(255)        NULL,
  rev_type                   varbinary(32)       NOT NULL,
  rev_comment                TINYBLOB            NOT NULL,
  rev_user_id                INT unsigned        NOT NULL default 0,
  rev_user_text              varbinary(255)      NOT NULL,
  rev_time                   varbinary(14)       NOT NULL,
  rev_minor_edit             TINYINT unsigned    NOT NULL default 0,
  rev_deleted                TINYINT unsigned    NOT NULL default 0,
  rev_data                   BLOB                NOT NULL
) /*$wgDBTableOptions*/;

CREATE INDEX /*i*/ep_revision_object_id ON /*_*/ep_revisions (rev_object_id);
CREATE INDEX /*i*/ep_revision_type ON /*_*/ep_revisions (rev_type);
CREATE INDEX /*i*/ep_revision_user_id ON /*_*/ep_revisions (rev_user_id);
CREATE INDEX /*i*/ep_revision_user_text ON /*_*/ep_revisions (rev_user_text);
CREATE INDEX /*i*/ep_revision_time ON /*_*/ep_revisions (rev_time);
CREATE INDEX /*i*/ep_revision_minor_edit ON /*_*/ep_revisions (rev_minor_edit);
CREATE INDEX /*i*/ep_revision_deleted ON /*_*/ep_revisions (rev_deleted);
CREATE INDEX /*i*/ep_revision_object_identifier ON /*_*/ep_revisions (rev_object_identifier);
