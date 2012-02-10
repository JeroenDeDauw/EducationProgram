-- MySQL for the Education Program extension.
-- Licence: GNU GPL v3+
-- Author: Jeroen De Dauw < jeroendedauw@gmail.com >

DROP TABLE IF EXISTS /*_*/ep_cas_per_course;
DROP TABLE IF EXISTS /*_*/ep_oas_per_course;
DROP TABLE IF EXISTS /*_*/ep_students_per_course;

-- Links the students with their courses.
CREATE TABLE IF NOT EXISTS /*_*/ep_users_per_course (
  upc_user_id                INT unsigned        NOT NULL, -- Foreign key on ep_user.user_id
  upc_course_id              INT unsigned        NOT NULL, -- Foreign key on ep_courses.course_id
  upc_role                   TINYINT unsigned    NOT NULL -- The role the user has for the course
) /*$wgDBTableOptions*/;

CREATE UNIQUE INDEX /*i*/ep_users_per_course ON /*_*/ep_users_per_course (upc_user_id, upc_course_id, upc_role);
CREATE INDEX /*i*/ep_upc_course_id ON /*_*/ep_users_per_course (upc_course_id);
CREATE INDEX /*i*/ep_upc_role ON /*_*/ep_users_per_course (upc_role);