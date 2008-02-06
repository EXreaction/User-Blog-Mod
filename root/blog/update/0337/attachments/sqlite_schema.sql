BEGIN TRANSACTION;

# Table: 'phpbb_blogs_attachment'
CREATE TABLE phpbb_blogs_attachment (
	attach_id INTEGER PRIMARY KEY NOT NULL ,
	blog_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	reply_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	poster_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	is_orphan INTEGER UNSIGNED NOT NULL DEFAULT '1',
	physical_filename varchar(255) NOT NULL DEFAULT '',
	real_filename varchar(255) NOT NULL DEFAULT '',
	download_count INTEGER UNSIGNED NOT NULL DEFAULT '0',
	attach_comment text(65535) NOT NULL DEFAULT '',
	extension varchar(100) NOT NULL DEFAULT '',
	mimetype varchar(100) NOT NULL DEFAULT '',
	filesize INTEGER UNSIGNED NOT NULL DEFAULT '0',
	filetime INTEGER UNSIGNED NOT NULL DEFAULT '0',
	thumbnail INTEGER UNSIGNED NOT NULL DEFAULT '0'
);

CREATE INDEX phpbb_blogs_attachment_blog_id ON phpbb_blogs_attachment (blog_id);
CREATE INDEX phpbb_blogs_attachment_reply_id ON phpbb_blogs_attachment (reply_id);
CREATE INDEX phpbb_blogs_attachment_filetime ON phpbb_blogs_attachment (filetime);
CREATE INDEX phpbb_blogs_attachment_poster_id ON phpbb_blogs_attachment (poster_id);
CREATE INDEX phpbb_blogs_attachment_is_orphan ON phpbb_blogs_attachment (is_orphan);


COMMIT;