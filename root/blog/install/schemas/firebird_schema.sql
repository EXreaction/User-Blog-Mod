# User Blogs Mod Database Schema


# Table: 'phpbb_blogs'
CREATE TABLE phpbb_blogs (
	blog_id INTEGER NOT NULL,
	user_id INTEGER DEFAULT 0 NOT NULL,
	user_ip VARCHAR(40) CHARACTER SET NONE DEFAULT '' NOT NULL,
	blog_subject VARCHAR(100) CHARACTER SET UTF8 DEFAULT '' NOT NULL COLLATE UNICODE,
	blog_text BLOB SUB_TYPE TEXT CHARACTER SET UTF8 DEFAULT '' NOT NULL,
	blog_checksum VARCHAR(32) CHARACTER SET NONE DEFAULT '' NOT NULL,
	blog_time INTEGER DEFAULT 0 NOT NULL,
	blog_approved INTEGER DEFAULT 1 NOT NULL,
	blog_reported INTEGER DEFAULT 0 NOT NULL,
	enable_bbcode INTEGER DEFAULT 1 NOT NULL,
	enable_smilies INTEGER DEFAULT 1 NOT NULL,
	enable_magic_url INTEGER DEFAULT 1 NOT NULL,
	bbcode_bitfield VARCHAR(255) CHARACTER SET NONE DEFAULT '' NOT NULL,
	bbcode_uid VARCHAR(8) CHARACTER SET NONE DEFAULT '' NOT NULL,
	blog_edit_time INTEGER DEFAULT 0 NOT NULL,
	blog_edit_reason VARCHAR(255) CHARACTER SET UTF8 DEFAULT '' NOT NULL COLLATE UNICODE,
	blog_edit_user INTEGER DEFAULT 0 NOT NULL,
	blog_edit_count INTEGER DEFAULT 0 NOT NULL,
	blog_edit_locked INTEGER DEFAULT 0 NOT NULL,
	blog_deleted INTEGER DEFAULT 0 NOT NULL,
	blog_deleted_time INTEGER DEFAULT 0 NOT NULL,
	blog_read_count INTEGER DEFAULT 0 NOT NULL,
	blog_reply_count INTEGER DEFAULT 0 NOT NULL,
	blog_real_reply_count INTEGER DEFAULT 0 NOT NULL,
	perm_guest INTEGER DEFAULT 1 NOT NULL,
	perm_registered INTEGER DEFAULT 2 NOT NULL,
	perm_foe INTEGER DEFAULT 0 NOT NULL,
	perm_friend INTEGER DEFAULT 2 NOT NULL
);;

ALTER TABLE phpbb_blogs ADD PRIMARY KEY (blog_id);;

CREATE INDEX phpbb_blogs_user_id ON phpbb_blogs(user_id);;
CREATE INDEX phpbb_blogs_user_ip ON phpbb_blogs(user_ip);;
CREATE INDEX phpbb_blogs_blog_approved ON phpbb_blogs(blog_approved);;
CREATE INDEX phpbb_blogs_blog_deleted ON phpbb_blogs(blog_deleted);;
CREATE INDEX phpbb_blogs_perm_guest ON phpbb_blogs(perm_guest);;
CREATE INDEX phpbb_blogs_perm_registered ON phpbb_blogs(perm_registered);;
CREATE INDEX phpbb_blogs_perm_foe ON phpbb_blogs(perm_foe);;
CREATE INDEX phpbb_blogs_perm_friend ON phpbb_blogs(perm_friend);;

CREATE GENERATOR phpbb_blogs_gen;;
SET GENERATOR phpbb_blogs_gen TO 0;;

CREATE TRIGGER t_phpbb_blogs FOR phpbb_blogs
BEFORE INSERT
AS
BEGIN
	NEW.blog_id = GEN_ID(phpbb_blogs_gen, 1);
END;;


# Table: 'phpbb_blogs_reply'
CREATE TABLE phpbb_blogs_reply (
	reply_id INTEGER NOT NULL,
	blog_id INTEGER DEFAULT 0 NOT NULL,
	user_id INTEGER DEFAULT 0 NOT NULL,
	user_ip VARCHAR(40) CHARACTER SET NONE DEFAULT '' NOT NULL,
	reply_subject VARCHAR(100) CHARACTER SET UTF8 DEFAULT '' NOT NULL COLLATE UNICODE,
	reply_text BLOB SUB_TYPE TEXT CHARACTER SET UTF8 DEFAULT '' NOT NULL,
	reply_checksum VARCHAR(32) CHARACTER SET NONE DEFAULT '' NOT NULL,
	reply_time INTEGER DEFAULT 0 NOT NULL,
	reply_approved INTEGER DEFAULT 1 NOT NULL,
	reply_reported INTEGER DEFAULT 0 NOT NULL,
	enable_bbcode INTEGER DEFAULT 1 NOT NULL,
	enable_smilies INTEGER DEFAULT 1 NOT NULL,
	enable_magic_url INTEGER DEFAULT 1 NOT NULL,
	bbcode_bitfield VARCHAR(255) CHARACTER SET NONE DEFAULT '' NOT NULL,
	bbcode_uid VARCHAR(8) CHARACTER SET NONE DEFAULT '' NOT NULL,
	reply_edit_time INTEGER DEFAULT 0 NOT NULL,
	reply_edit_reason VARCHAR(255) CHARACTER SET UTF8 DEFAULT '' NOT NULL COLLATE UNICODE,
	reply_edit_user INTEGER DEFAULT 0 NOT NULL,
	reply_edit_count INTEGER DEFAULT 0 NOT NULL,
	reply_edit_locked INTEGER DEFAULT 0 NOT NULL,
	reply_deleted INTEGER DEFAULT 0 NOT NULL,
	reply_deleted_time INTEGER DEFAULT 0 NOT NULL
);;

ALTER TABLE phpbb_blogs_reply ADD PRIMARY KEY (reply_id);;

CREATE INDEX phpbb_blogs_reply_user_id ON phpbb_blogs_reply(user_id);;
CREATE INDEX phpbb_blogs_reply_user_ip ON phpbb_blogs_reply(user_ip);;
CREATE INDEX phpbb_blogs_reply_reply_approved ON phpbb_blogs_reply(reply_approved);;
CREATE INDEX phpbb_blogs_reply_reply_deleted ON phpbb_blogs_reply(reply_deleted);;

CREATE GENERATOR phpbb_blogs_reply_gen;;
SET GENERATOR phpbb_blogs_reply_gen TO 0;;

CREATE TRIGGER t_phpbb_blogs_reply FOR phpbb_blogs_reply
BEFORE INSERT
AS
BEGIN
	NEW.reply_id = GEN_ID(phpbb_blogs_reply_gen, 1);
END;;


# Table: 'phpbb_blogs_subscription'
CREATE TABLE phpbb_blogs_subscription (
	sub_user_id INTEGER DEFAULT 0 NOT NULL,
	sub_type INTEGER DEFAULT 1 NOT NULL,
	blog_id INTEGER DEFAULT 0 NOT NULL,
	user_id INTEGER DEFAULT 0 NOT NULL
);;

ALTER TABLE phpbb_blogs_subscription ADD PRIMARY KEY (sub_user_id, sub_type, blog_id, user_id);;


# Table: 'phpbb_blogs_plugins'
CREATE TABLE phpbb_blogs_plugins (
	plugin_id INTEGER NOT NULL,
	plugin_name VARCHAR(100) CHARACTER SET UTF8 DEFAULT '' NOT NULL COLLATE UNICODE,
	plugin_enabled INTEGER DEFAULT 0 NOT NULL,
	plugin_version VARCHAR(100) CHARACTER SET UTF8 DEFAULT '' NOT NULL COLLATE UNICODE
);;

ALTER TABLE phpbb_blogs_plugins ADD PRIMARY KEY (plugin_id);;

CREATE INDEX phpbb_blogs_plugins_plugin_name ON phpbb_blogs_plugins(plugin_name);;
CREATE INDEX phpbb_blogs_plugins_plugin_enabled ON phpbb_blogs_plugins(plugin_enabled);;

CREATE GENERATOR phpbb_blogs_plugins_gen;;
SET GENERATOR phpbb_blogs_plugins_gen TO 0;;

CREATE TRIGGER t_phpbb_blogs_plugins FOR phpbb_blogs_plugins
BEFORE INSERT
AS
BEGIN
	NEW.plugin_id = GEN_ID(phpbb_blogs_plugins_gen, 1);
END;;


# Table: 'phpbb_blogs_users'
CREATE TABLE phpbb_blogs_users (
	user_id INTEGER DEFAULT 0 NOT NULL,
	perm_guest INTEGER DEFAULT 1 NOT NULL,
	perm_registered INTEGER DEFAULT 2 NOT NULL,
	perm_foe INTEGER DEFAULT 0 NOT NULL,
	perm_friend INTEGER DEFAULT 2 NOT NULL,
	title VARCHAR(100) CHARACTER SET UTF8 DEFAULT '' NOT NULL COLLATE UNICODE,
	description BLOB SUB_TYPE TEXT CHARACTER SET UTF8 DEFAULT '' NOT NULL,
	description_bbcode_bitfield VARCHAR(255) CHARACTER SET NONE DEFAULT '' NOT NULL,
	description_bbcode_uid VARCHAR(8) CHARACTER SET NONE DEFAULT '' NOT NULL,
	instant_redirect INTEGER DEFAULT 1 NOT NULL
);;

ALTER TABLE phpbb_blogs_users ADD PRIMARY KEY (user_id);;


