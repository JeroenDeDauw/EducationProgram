-- SQL for the Education Program extension.
-- Adds additional fields.
-- Licence: GNU GPL v3+
-- Author: Jeroen De Dauw < jeroendedauw@gmail.com >

ALTER TABLE /*_*/ep_courses ADD COLUMN course_name VARCHAR(255) NOT NULL;
CREATE INDEX /*i*/ep_course_name ON /*_*/ep_courses (course_name);

ALTER TABLE /*_*/ep_courses ADD COLUMN course_timeline TEXT NOT NULL;
ALTER TABLE /*_*/ep_mcs ADD COLUMN mc_timeline TEXT NOT NULL;

-- Articles students are working on.
CREATE TABLE IF NOT EXISTS /*_*/ep_articles (
  article_id                 INT unsigned        NOT NULL auto_increment PRIMARY KEY,

  article_user_id            INT unsigned        NOT NULL, -- Foreign key on user.user_id
  article_course_id          INT unsigned        NOT NULL, -- Foreign key on ep_courses.course_id
  article_page_id            INT unsigned        NOT NULL, -- Foreign key on page.page_id

  article_reviewers          BLOB                NOT NULL -- List of reviewers for this article (linking user.user_id)
) /*$wgDBTableOptions*/;

CREATE INDEX /*i*/ep_articles_user_id ON /*_*/ep_articles (article_user_id);
CREATE INDEX /*i*/ep_articles_course_id ON /*_*/ep_articles (article_course_id);
CREATE INDEX /*i*/ep_articles_page_id ON /*_*/ep_articles (article_page_id);
CREATE UNIQUE INDEX /*i*/ep_articles_course_page ON /*_*/ep_articles (article_course_id, article_page_id);

ALTER TABLE /*_*/ep_cas ADD COLUMN ca_bio TEXT NOT NULL;
ALTER TABLE /*_*/ep_cas ADD COLUMN ca_photo VARCHAR(255) NOT NULL;

ALTER TABLE /*_*/ep_oas ADD COLUMN oa_bio TEXT NOT NULL;
ALTER TABLE /*_*/ep_oas ADD COLUMN oa_photo VARCHAR(255) NOT NULL;