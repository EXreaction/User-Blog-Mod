/*
User Blogs Mod Database Schema
*/

BEGIN;


/*
	Table: 'phpbb_blogs'
*/
CREATE SEQUENCE phpbb_blogs_seq;

CREATE TABLE phpbb_blogs (
	blog_id INT4 DEFAULT nextval('phpbb_blogs_seq'),
	user_id INT4 DEFAULT '0' NOT NULL CHECK (user_id >= 0),
	user_ip varchar(40) DEFAULT '' NOT NULL,
	blog_subject varchar(100) DEFAULT '' NOT NULL,
	blog_text TEXT DEFAULT '' NOT NULL,
	blog_checksum varchar(32) DEFAULT '' NOT NULL,
	blog_time INT4 DEFAULT '0' NOT NULL CHECK (blog_time >= 0),
	blog_approved INT2 DEFAULT '1' NOT NULL CHECK (blog_approved >= 0),
	blog_reported INT2 DEFAULT '0' NOT NULL CHECK (blog_reported >= 0),
	enable_bbcode INT2 DEFAULT '1' NOT NULL CHECK (enable_bbcode >= 0),
	enable_smilies INT2 DEFAULT '1' NOT NULL CHECK (enable_smilies >= 0),
	enable_magic_url INT2 DEFAULT '1' NOT NULL CHECK (enable_magic_url >= 0),
	bbcode_bitfield varchar(255) DEFAULT '' NOT NULL,
	bbcode_uid varchar(8) DEFAULT '' NOT NULL,
	blog_edit_time INT4 DEFAULT '0' NOT NULL CHECK (blog_edit_time >= 0),
	blog_edit_reason varchar(255) DEFAULT '' NOT NULL,
	blog_edit_user INT4 DEFAULT '0' NOT NULL CHECK (blog_edit_user >= 0),
	blog_edit_count INT2 DEFAULT '0' NOT NULL CHECK (blog_edit_count >= 0),
	blog_edit_locked INT2 DEFAULT '0' NOT NULL CHECK (blog_edit_locked >= 0),
	blog_deleted INT4 DEFAULT '0' NOT NULL CHECK (blog_deleted >= 0),
	blog_deleted_time INT4 DEFAULT '0' NOT NULL CHECK (blog_deleted_time >= 0),
	blog_read_count INT4 DEFAULT '0' NOT NULL CHECK (blog_read_count >= 0),
	blog_reply_count INT4 DEFAULT '0' NOT NULL CHECK (blog_reply_count >= 0),
	blog_real_reply_count INT4 DEFAULT '0' NOT NULL CHECK (blog_real_reply_count >= 0),
	perm_guest INT2 DEFAULT '1' NOT NULL,
	perm_registered INT2 DEFAULT '2' NOT NULL,
	perm_foe INT2 DEFAULT '0' NOT NULL,
	perm_friend INT2 DEFAULT '2' NOT NULL,
	PRIMARY KEY (blog_id)
);

CREATE INDEX phpbb_blogs_user_id ON phpbb_blogs (user_id);
CREATE INDEX phpbb_blogs_user_ip ON phpbb_blogs (user_ip);
CREATE INDEX phpbb_blogs_blog_approved ON phpbb_blogs (blog_approved);
CREATE INDEX phpbb_blogs_blog_deleted ON phpbb_blogs (blog_deleted);
CREATE INDEX phpbb_blogs_perm_guest ON phpbb_blogs (perm_guest);
CREATE INDEX phpbb_blogs_perm_registered ON phpbb_blogs (perm_registered);
CREATE INDEX phpbb_blogs_perm_foe ON phpbb_blogs (perm_foe);
CREATE INDEX phpbb_blogs_perm_friend ON phpbb_blogs (perm_friend);

/*
	Table: 'phpbb_blogs_reply'
*/
CREATE SEQUENCE phpbb_blogs_reply_seq;

CREATE TABLE phpbb_blogs_reply (
	reply_id INT4 DEFAULT nextval('phpbb_blogs_reply_seq'),
	blog_id INT4 DEFAULT '0' NOT NULL CHECK (blog_id >= 0),
	user_id INT4 DEFAULT '0' NOT NULL CHECK (user_id >= 0),
	user_ip varchar(40) DEFAULT '' NOT NULL,
	reply_subject varchar(100) DEFAULT '' NOT NULL,
	reply_text TEXT DEFAULT '' NOT NULL,
	reply_checksum varchar(32) DEFAULT '' NOT NULL,
	reply_time INT4 DEFAULT '0' NOT NULL CHECK (reply_time >= 0),
	reply_approved INT2 DEFAULT '1' NOT NULL CHECK (reply_approved >= 0),
	reply_reported INT2 DEFAULT '0' NOT NULL CHECK (reply_reported >= 0),
	enable_bbcode INT2 DEFAULT '1' NOT NULL CHECK (enable_bbcode >= 0),
	enable_smilies INT2 DEFAULT '1' NOT NULL CHECK (enable_smilies >= 0),
	enable_magic_url INT2 DEFAULT '1' NOT NULL CHECK (enable_magic_url >= 0),
	bbcode_bitfield varchar(255) DEFAULT '' NOT NULL,
	bbcode_uid varchar(8) DEFAULT '' NOT NULL,
	reply_edit_time INT4 DEFAULT '0' NOT NULL CHECK (reply_edit_time >= 0),
	reply_edit_reason varchar(255) DEFAULT '' NOT NULL,
	reply_edit_user INT4 DEFAULT '0' NOT NULL CHECK (reply_edit_user >= 0),
	reply_edit_count INT4 DEFAULT '0' NOT NULL CHECK (reply_edit_count >= 0),
	reply_edit_locked INT2 DEFAULT '0' NOT NULL CHECK (reply_edit_locked >= 0),
	reply_deleted INT4 DEFAULT '0' NOT NULL CHECK (reply_deleted >= 0),
	reply_deleted_time INT4 DEFAULT '0' NOT NULL CHECK (reply_deleted_time >= 0),
	PRIMARY KEY (reply_id)
);

CREATE INDEX phpbb_blogs_reply_user_id ON phpbb_blogs_reply (user_id);
CREATE INDEX phpbb_blogs_reply_user_ip ON phpbb_blogs_reply (user_ip);
CREATE INDEX phpbb_blogs_reply_reply_approved ON phpbb_blogs_reply (reply_approved);
CREATE INDEX phpbb_blogs_reply_reply_deleted ON phpbb_blogs_reply (reply_deleted);

/*
	Table: 'phpbb_blogs_subscription'
*/
CREATE TABLE phpbb_blogs_subscription (
	sub_user_id INT4 DEFAULT '0' NOT NULL CHECK (sub_user_id >= 0),
	sub_type INT2 DEFAULT '1' NOT NULL,
	blog_id INT4 DEFAULT '0' NOT NULL CHECK (blog_id >= 0),
	user_id INT4 DEFAULT '0' NOT NULL CHECK (user_id >= 0),
	PRIMARY KEY (sub_user_id, sub_type, blog_id, user_id)
);


/*
	Table: 'phpbb_blogs_plugins'
*/
CREATE SEQUENCE phpbb_blogs_plugins_seq;

CREATE TABLE phpbb_blogs_plugins (
	plugin_id INT4 DEFAULT nextval('phpbb_blogs_plugins_seq'),
	plugin_name varchar(100) DEFAULT '' NOT NULL,
	plugin_enabled INT2 DEFAULT '0' NOT NULL CHECK (plugin_enabled >= 0),
	plugin_version varchar(100) DEFAULT '' NOT NULL,
	PRIMARY KEY (plugin_id)
);

CREATE INDEX phpbb_blogs_plugins_plugin_name ON phpbb_blogs_plugins (plugin_name);
CREATE INDEX phpbb_blogs_plugins_plugin_enabled ON phpbb_blogs_plugins (plugin_enabled);

/*
	Table: 'phpbb_blogs_users'
*/
CREATE TABLE phpbb_blogs_users (
	user_id INT4 DEFAULT '0' NOT NULL CHECK (user_id >= 0),
	perm_guest INT2 DEFAULT '1' NOT NULL,
	perm_registered INT2 DEFAULT '2' NOT NULL,
	perm_foe INT2 DEFAULT '0' NOT NULL,
	perm_friend INT2 DEFAULT '2' NOT NULL,
	title varchar(100) DEFAULT '' NOT NULL,
	description TEXT DEFAULT '' NOT NULL,
	description_bbcode_bitfield varchar(255) DEFAULT '' NOT NULL,
	description_bbcode_uid varchar(8) DEFAULT '' NOT NULL,
	instant_redirect INT2 DEFAULT '1' NOT NULL CHECK (instant_redirect >= 0),
	PRIMARY KEY (user_id)
);



COMMIT;