-- SQL for the Education Program extension.
-- Add object id field to revision table.
-- Licence: GNU GPL v3+
-- Author: Jeroen De Dauw < jeroendedauw@gmail.com >

ALTER TABLE /*_*/ep_revisions ADD COLUMN rev_object_id INT unsigned NOT NULL;
CREATE INDEX /*i*/ep_revisions_object_id ON /*_*/ep_revisions (rev_object_id);
