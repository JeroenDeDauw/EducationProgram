-- for SQLite compatibility, the index can not have the
-- same name as a table
DROP INDEX /*i*/ep_users_per_course ON /*_*/ep_users_per_course;
CREATE UNIQUE INDEX /*i*/ep_upc_user_courseid_role ON /*_*/ep_users_per_course (upc_user_id, upc_course_id, upc_role);

