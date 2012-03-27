-- MySQL patch for the Education Program extension.
-- Licence: GNU GPL v2+
-- Author: Jeroen De Dauw < jeroendedauw@gmail.com >

ALTER TABLE /*_*/ep_cas ADD COLUMN ca_visible TINYINT unsigned NOT NULL;
ALTER TABLE /*_*/ep_oas ADD COLUMN oa_visible TINYINT unsigned NOT NULL;

CREATE INDEX /*i*/ep_cas_visible ON /*_*/ep_cas (ca_visible);
CREATE INDEX /*i*/ep_oas_visible ON /*_*/ep_oas (oa_visible);