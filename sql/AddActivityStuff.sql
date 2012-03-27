-- MySQL patch for the Education Program extension.
-- Licence: GNU GPL v2+
-- Author: Jeroen De Dauw < jeroendedauw@gmail.com >

ALTER TABLE /*_*/ep_users_per_course ADD COLUMN upc_time varbinary(14) NOT NULL;

CREATE INDEX /*i*/ep_upc_time ON /*_*/ep_users_per_course (upc_time);

ALTER TABLE /*_*/ep_students ADD COLUMN student_last_enroll varbinary(14) NOT NULL;
ALTER TABLE /*_*/ep_students ADD COLUMN student_last_course INT unsigned NOT NULL;
ALTER TABLE /*_*/ep_students ADD COLUMN student_first_course INT unsigned NOT NULL;

CREATE INDEX /*i*/ep_students_first_course ON /*_*/ep_students (student_first_course);
CREATE INDEX /*i*/ep_students_last_enroll ON /*_*/ep_students (student_last_enroll);
CREATE INDEX /*i*/ep_students_last_course ON /*_*/ep_students (student_last_course);

UPDATE /*_*/ep_users_per_course SET upc_time = '20120315224638';
UPDATE /*_*/ep_students SET student_last_enroll = '20120315224638';
UPDATE /*_*/ep_students SET student_last_active = '20120315224638';

