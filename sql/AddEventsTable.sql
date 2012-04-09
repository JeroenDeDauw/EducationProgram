-- MySQL patch for the Education Program extension.
-- Licence: GNU GPL v2+
-- Author: Jeroen De Dauw < jeroendedauw@gmail.com >

-- Education timeline events.
CREATE TABLE IF NOT EXISTS /*_*/ep_events (
  event_id                   INT unsigned        NOT NULL auto_increment PRIMARY KEY,
  event_course_id            INT unsigned        NOT NULL, -- Foreign key on ep_courses.course_id
  event_user_id              INT unsigned        NOT NULL, -- The user creating the event. Foreign key on user.user_id
  event_time                 varbinary(14)       NOT NULL, -- Time the event took place
  event_type                 VARCHAR(25)         NOT NULL, -- Type of the event
  event_info                 BLOB                NOT NULL -- Event info, can be different fields depending on event type
) /*$wgDBTableOptions*/;

CREATE INDEX /*i*/ep_events_course_id ON /*_*/ep_events (event_course_id);
CREATE INDEX /*i*/ep_events_user_id ON /*_*/ep_events (event_user_id);
CREATE INDEX /*i*/ep_events_time ON /*_*/ep_events (event_time);
CREATE INDEX /*i*/ep_events_type ON /*_*/ep_events (event_type);