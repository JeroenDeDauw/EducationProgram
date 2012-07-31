-- Patch for the ep_courses and ep_orgs tables.
-- Licence: GNU GPL v2+
-- Author: Jeroen De Dauw < jeroendedauw@gmail.com >


ALTER TABLE /*_*/ep_courses
	ADD COLUMN course_touched varbinary(14) NOT NULL;

ALTER TABLE /*_*/ep_orgs
	ADD COLUMN org_touched varbinary(14) NOT NULL;