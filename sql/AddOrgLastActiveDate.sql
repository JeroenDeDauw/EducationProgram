-- Patch for theep_orgs table.
-- Licence: GNU GPL v2+
-- Author: Andrew Green < agreen@wikimedia.org >

ALTER TABLE /*_*/ep_orgs
	ADD COLUMN org_last_active_date varbinary(14) NOT NULL DEFAULT '19700101000000'; -- Projected end date of last course