-- MySQL patch for the Education Program extension.
-- Licence: GNU GPL v3+
-- Author: Jeroen De Dauw < jeroendedauw@gmail.com >

ALTER TABLE /*_*/ep_orgs ADD COLUMN org_courses BLOB NOT NULL;

UPDATE /*_*/ep_orgs SET org_courses = 'a:0:{}'; -- Serialized empty array