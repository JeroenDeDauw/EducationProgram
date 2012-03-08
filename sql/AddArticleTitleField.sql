-- MySQL patch for the Education Program extension.
-- Licence: GNU GPL v3+
-- Author: Jeroen De Dauw < jeroendedauw@gmail.com >

ALTER TABLE /*_*/ep_articles ADD COLUMN article_page_title varchar(255) binary NOT NULL;

CREATE INDEX /*i*/ep_articles_page_title ON /*_*/ep_articles (article_page_title);

DROP INDEX /*i*/ep_articles_course_page ON /*_*/ep_articles;
CREATE UNIQUE INDEX /*i*/ep_articles_course_page ON /*_*/ep_articles (article_course_id, article_user_id, article_page_title);