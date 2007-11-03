/*
User Blogs Mod Database Schema
*/


/*
	Table: 'phpbb_blogs'
*/
CREATE TABLE phpbb_blogs (
	blog_id number(8) NOT NULL,
	user_id number(8) DEFAULT '0' NOT NULL,
	user_ip varchar2(40) DEFAULT '' ,
	blog_subject varchar2(300) DEFAULT '' ,
	blog_text clob DEFAULT '' ,
	blog_checksum varchar2(32) DEFAULT '' ,
	blog_time number(11) DEFAULT '0' NOT NULL,
	blog_approved number(1) DEFAULT '1' NOT NULL,
	blog_reported number(1) DEFAULT '0' NOT NULL,
	enable_bbcode number(1) DEFAULT '1' NOT NULL,
	enable_smilies number(1) DEFAULT '1' NOT NULL,
	enable_magic_url number(1) DEFAULT '1' NOT NULL,
	bbcode_bitfield varchar2(255) DEFAULT '' ,
	bbcode_uid varchar2(8) DEFAULT '' ,
	blog_edit_time number(11) DEFAULT '0' NOT NULL,
	blog_edit_reason varchar2(765) DEFAULT '' ,
	blog_edit_user number(8) DEFAULT '0' NOT NULL,
	blog_edit_count number(4) DEFAULT '0' NOT NULL,
	blog_edit_locked number(1) DEFAULT '0' NOT NULL,
	blog_deleted number(8) DEFAULT '0' NOT NULL,
	blog_deleted_time number(11) DEFAULT '0' NOT NULL,
	blog_read_count number(8) DEFAULT '0' NOT NULL,
	blog_reply_count number(8) DEFAULT '0' NOT NULL,
	blog_real_reply_count number(8) DEFAULT '0' NOT NULL,
	perm_guest number(1) DEFAULT '1' NOT NULL,
	perm_registered number(1) DEFAULT '2' NOT NULL,
	perm_foe number(1) DEFAULT '0' NOT NULL,
	perm_friend number(1) DEFAULT '2' NOT NULL,
	CONSTRAINT pk_phpbb_blogs PRIMARY KEY (blog_id)
)
/

CREATE INDEX phpbb_blogs_user_id ON phpbb_blogs (user_id)
/
CREATE INDEX phpbb_blogs_user_ip ON phpbb_blogs (user_ip)
/
CREATE INDEX phpbb_blogs_blog_approved ON phpbb_blogs (blog_approved)
/
CREATE INDEX phpbb_blogs_blog_deleted ON phpbb_blogs (blog_deleted)
/
CREATE INDEX phpbb_blogs_perm_guest ON phpbb_blogs (perm_guest)
/
CREATE INDEX phpbb_blogs_perm_registered ON phpbb_blogs (perm_registered)
/
CREATE INDEX phpbb_blogs_perm_foe ON phpbb_blogs (perm_foe)
/
CREATE INDEX phpbb_blogs_perm_friend ON phpbb_blogs (perm_friend)
/

CREATE SEQUENCE phpbb_blogs_seq
/

CREATE OR REPLACE TRIGGER t_phpbb_blogs
BEFORE INSERT ON phpbb_blogs
FOR EACH ROW WHEN (
	new.blog_id IS NULL OR new.blog_id = 0
)
BEGIN
	SELECT phpbb_blogs_seq.nextval
	INTO :new.blog_id
	FROM dual;
END;
/


/*
	Table: 'phpbb_blogs_reply'
*/
CREATE TABLE phpbb_blogs_reply (
	reply_id number(8) NOT NULL,
	blog_id number(8) DEFAULT '0' NOT NULL,
	user_id number(8) DEFAULT '0' NOT NULL,
	user_ip varchar2(40) DEFAULT '' ,
	reply_subject varchar2(300) DEFAULT '' ,
	reply_text clob DEFAULT '' ,
	reply_checksum varchar2(32) DEFAULT '' ,
	reply_time number(11) DEFAULT '0' NOT NULL,
	reply_approved number(1) DEFAULT '1' NOT NULL,
	reply_reported number(1) DEFAULT '0' NOT NULL,
	enable_bbcode number(1) DEFAULT '1' NOT NULL,
	enable_smilies number(1) DEFAULT '1' NOT NULL,
	enable_magic_url number(1) DEFAULT '1' NOT NULL,
	bbcode_bitfield varchar2(255) DEFAULT '' ,
	bbcode_uid varchar2(8) DEFAULT '' ,
	reply_edit_time number(11) DEFAULT '0' NOT NULL,
	reply_edit_reason varchar2(765) DEFAULT '' ,
	reply_edit_user number(8) DEFAULT '0' NOT NULL,
	reply_edit_count number(8) DEFAULT '0' NOT NULL,
	reply_edit_locked number(1) DEFAULT '0' NOT NULL,
	reply_deleted number(8) DEFAULT '0' NOT NULL,
	reply_deleted_time number(11) DEFAULT '0' NOT NULL,
	CONSTRAINT pk_phpbb_blogs_reply PRIMARY KEY (reply_id)
)
/

CREATE INDEX phpbb_blogs_reply_user_id ON phpbb_blogs_reply (user_id)
/
CREATE INDEX phpbb_blogs_reply_user_ip ON phpbb_blogs_reply (user_ip)
/
CREATE INDEX phpbb_blogs_reply_reply_approved ON phpbb_blogs_reply (reply_approved)
/
CREATE INDEX phpbb_blogs_reply_reply_deleted ON phpbb_blogs_reply (reply_deleted)
/

CREATE SEQUENCE phpbb_blogs_reply_seq
/

CREATE OR REPLACE TRIGGER t_phpbb_blogs_reply
BEFORE INSERT ON phpbb_blogs_reply
FOR EACH ROW WHEN (
	new.reply_id IS NULL OR new.reply_id = 0
)
BEGIN
	SELECT phpbb_blogs_reply_seq.nextval
	INTO :new.reply_id
	FROM dual;
END;
/


/*
	Table: 'phpbb_blogs_subscription'
*/
CREATE TABLE phpbb_blogs_subscription (
	sub_user_id number(8) DEFAULT '0' NOT NULL,
	sub_type number(1) DEFAULT '1' NOT NULL,
	blog_id number(8) DEFAULT '0' NOT NULL,
	user_id number(8) DEFAULT '0' NOT NULL,
	CONSTRAINT pk_phpbb_blogs_subscription PRIMARY KEY (sub_user_id, sub_type, blog_id, user_id)
)
/


/*
	Table: 'phpbb_blogs_plugins'
*/
CREATE TABLE phpbb_blogs_plugins (
	plugin_id number(8) NOT NULL,
	plugin_name varchar2(300) DEFAULT '' ,
	plugin_enabled number(1) DEFAULT '0' NOT NULL,
	plugin_version varchar2(300) DEFAULT '' ,
	CONSTRAINT pk_phpbb_blogs_plugins PRIMARY KEY (plugin_id)
)
/

CREATE INDEX phpbb_blogs_plugins_plugin_name ON phpbb_blogs_plugins (plugin_name)
/
CREATE INDEX phpbb_blogs_plugins_plugin_enabled ON phpbb_blogs_plugins (plugin_enabled)
/

CREATE SEQUENCE phpbb_blogs_plugins_seq
/

CREATE OR REPLACE TRIGGER t_phpbb_blogs_plugins
BEFORE INSERT ON phpbb_blogs_plugins
FOR EACH ROW WHEN (
	new.plugin_id IS NULL OR new.plugin_id = 0
)
BEGIN
	SELECT phpbb_blogs_plugins_seq.nextval
	INTO :new.plugin_id
	FROM dual;
END;
/


/*
	Table: 'phpbb_blogs_users'
*/
CREATE TABLE phpbb_blogs_users (
	user_id number(8) DEFAULT '0' NOT NULL,
	perm_guest number(1) DEFAULT '1' NOT NULL,
	perm_registered number(1) DEFAULT '2' NOT NULL,
	perm_foe number(1) DEFAULT '0' NOT NULL,
	perm_friend number(1) DEFAULT '2' NOT NULL,
	title varchar2(300) DEFAULT '' ,
	description clob DEFAULT '' ,
	description_bbcode_bitfield varchar2(255) DEFAULT '' ,
	description_bbcode_uid varchar2(8) DEFAULT '' ,
	instant_redirect number(1) DEFAULT '1' NOT NULL,
	CONSTRAINT pk_phpbb_blogs_users PRIMARY KEY (user_id)
)
/


