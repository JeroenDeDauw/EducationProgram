-- Patch for the ep_courses table.
-- Licence: GNU GPL v2+
-- Author: Jeroen De Dauw < jeroendedauw@gmail.com >


ALTER TABLE /*_*/ep_courses
	DROP INDEX ep_course_name,
	DROP INDEX ep_course_mc,
	CHANGE course_name course_title VARCHAR(255) NOT NULL,
	CHANGE course_mc course_name VARCHAR(255) NOT NULL;

CREATE INDEX /*i*/ep_course_title ON /*_*/ep_courses (course_title);
CREATE INDEX /*i*/ep_course_name ON /*_*/ep_courses (course_name);