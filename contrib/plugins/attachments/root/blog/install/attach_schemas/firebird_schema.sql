# User Blogs Mod Database Schema


# Table: 'phpbb_blogs_attachment'
CREATE TABLE phpbb_blogs_attachment (
	attach_id INTEGER NOT NULL,
	blog_id INTEGER DEFAULT 0 NOT NULL,
	reply_id INTEGER DEFAULT 0 NOT NULL,
	poster_id INTEGER DEFAULT 0 NOT NULL,
	is_orphan INTEGER DEFAULT 1 NOT NULL,
	physical_filename VARCHAR(255) CHARACTER SET NONE DEFAULT '' NOT NULL,
	real_filename VARCHAR(255) CHARACTER SET NONE DEFAULT '' NOT NULL,
	download_count INTEGER DEFAULT 0 NOT NULL,
	attach_comment BLOB SUB_TYPE TEXT CHARACTER SET UTF8 DEFAULT '' NOT NULL,
	extension VARCHAR(100) CHARACTER SET NONE DEFAULT '' NOT NULL,
	mimetype VARCHAR(100) CHARACTER SET NONE DEFAULT '' NOT NULL,
	filesize INTEGER DEFAULT 0 NOT NULL,
	filetime INTEGER DEFAULT 0 NOT NULL,
	thumbnail INTEGER DEFAULT 0 NOT NULL
);;

ALTER TABLE phpbb_blogs_attachment ADD PRIMARY KEY (attach_id);;

CREATE INDEX phpbb_blogs_attachment_blog_id ON phpbb_blogs_attachment(blog_id);;
CREATE INDEX phpbb_blogs_attachment_reply_id ON phpbb_blogs_attachment(reply_id);;
CREATE INDEX phpbb_blogs_attachment_filetime ON phpbb_blogs_attachment(filetime);;
CREATE INDEX phpbb_blogs_attachment_poster_id ON phpbb_blogs_attachment(poster_id);;
CREATE INDEX phpbb_blogs_attachment_is_orphan ON phpbb_blogs_attachment(is_orphan);;

CREATE GENERATOR phpbb_blogs_attachment_gen;;
SET GENERATOR phpbb_blogs_attachment_gen TO 0;;

CREATE TRIGGER t_phpbb_blogs_attachment FOR phpbb_blogs_attachment
BEFORE INSERT
AS
BEGIN
	NEW.attach_id = GEN_ID(phpbb_blogs_attachment_gen, 1);
END;;


