# User Blogs Mod Database Schema

# Table: 'phpbb_blogs'
CREATE TABLE phpbb_blogs (
	blog_id mediumint(8) UNSIGNED NOT NULL auto_increment,
	user_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	user_ip varbinary(40) DEFAULT '' NOT NULL,
	blog_subject blob NOT NULL,
	blog_text mediumblob NOT NULL,
	blog_checksum varbinary(32) DEFAULT '' NOT NULL,
	blog_time int(11) UNSIGNED DEFAULT '0' NOT NULL,
	blog_approved tinyint(1) UNSIGNED DEFAULT '1' NOT NULL,
	blog_reported tinyint(1) UNSIGNED DEFAULT '0' NOT NULL,
	enable_bbcode tinyint(1) UNSIGNED DEFAULT '1' NOT NULL,
	enable_smilies tinyint(1) UNSIGNED DEFAULT '1' NOT NULL,
	enable_magic_url tinyint(1) UNSIGNED DEFAULT '1' NOT NULL,
	bbcode_bitfield varbinary(255) DEFAULT '' NOT NULL,
	bbcode_uid varbinary(8) DEFAULT '' NOT NULL,
	blog_edit_time int(11) UNSIGNED DEFAULT '0' NOT NULL,
	blog_edit_reason blob NOT NULL,
	blog_edit_user mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	blog_edit_count smallint(4) UNSIGNED DEFAULT '0' NOT NULL,
	blog_edit_locked tinyint(1) UNSIGNED DEFAULT '0' NOT NULL,
	blog_deleted mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	blog_deleted_time int(11) UNSIGNED DEFAULT '0' NOT NULL,
	blog_read_count mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	blog_reply_count mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	blog_real_reply_count mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	perm_guest tinyint(1) DEFAULT '1' NOT NULL,
	perm_registered tinyint(1) DEFAULT '2' NOT NULL,
	perm_foe tinyint(1) DEFAULT '0' NOT NULL,
	perm_friend tinyint(1) DEFAULT '2' NOT NULL,
	PRIMARY KEY (blog_id),
	KEY user_id (user_id),
	KEY user_ip (user_ip),
	KEY blog_approved (blog_approved),
	KEY blog_deleted (blog_deleted),
	KEY perm_guest (perm_guest),
	KEY perm_registered (perm_registered),
	KEY perm_foe (perm_foe),
	KEY perm_friend (perm_friend)
);


# Table: 'phpbb_blogs_reply'
CREATE TABLE phpbb_blogs_reply (
	reply_id mediumint(8) UNSIGNED NOT NULL auto_increment,
	blog_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	user_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	user_ip varbinary(40) DEFAULT '' NOT NULL,
	reply_subject blob NOT NULL,
	reply_text mediumblob NOT NULL,
	reply_checksum varbinary(32) DEFAULT '' NOT NULL,
	reply_time int(11) UNSIGNED DEFAULT '0' NOT NULL,
	reply_approved tinyint(1) UNSIGNED DEFAULT '1' NOT NULL,
	reply_reported tinyint(1) UNSIGNED DEFAULT '0' NOT NULL,
	enable_bbcode tinyint(1) UNSIGNED DEFAULT '1' NOT NULL,
	enable_smilies tinyint(1) UNSIGNED DEFAULT '1' NOT NULL,
	enable_magic_url tinyint(1) UNSIGNED DEFAULT '1' NOT NULL,
	bbcode_bitfield varbinary(255) DEFAULT '' NOT NULL,
	bbcode_uid varbinary(8) DEFAULT '' NOT NULL,
	reply_edit_time int(11) UNSIGNED DEFAULT '0' NOT NULL,
	reply_edit_reason blob NOT NULL,
	reply_edit_user mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	reply_edit_count mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	reply_edit_locked tinyint(1) UNSIGNED DEFAULT '0' NOT NULL,
	reply_deleted mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	reply_deleted_time int(11) UNSIGNED DEFAULT '0' NOT NULL,
	PRIMARY KEY (reply_id),
	KEY user_id (user_id),
	KEY user_ip (user_ip),
	KEY reply_approved (reply_approved),
	KEY reply_deleted (reply_deleted)
);


# Table: 'phpbb_blogs_subscription'
CREATE TABLE phpbb_blogs_subscription (
	sub_user_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	sub_type tinyint(1) DEFAULT '1' NOT NULL,
	blog_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	user_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	PRIMARY KEY (sub_user_id, sub_type, blog_id, user_id)
);


# Table: 'phpbb_blogs_plugins'
CREATE TABLE phpbb_blogs_plugins (
	plugin_id mediumint(8) UNSIGNED NOT NULL auto_increment,
	plugin_name blob NOT NULL,
	plugin_enabled tinyint(1) UNSIGNED DEFAULT '0' NOT NULL,
	plugin_version blob NOT NULL,
	PRIMARY KEY (plugin_id),
	KEY plugin_name (plugin_name(255)),
	KEY plugin_enabled (plugin_enabled)
);


# Table: 'phpbb_blogs_users'
CREATE TABLE phpbb_blogs_users (
	user_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	perm_guest tinyint(1) DEFAULT '1' NOT NULL,
	perm_registered tinyint(1) DEFAULT '2' NOT NULL,
	perm_foe tinyint(1) DEFAULT '0' NOT NULL,
	perm_friend tinyint(1) DEFAULT '2' NOT NULL,
	title blob NOT NULL,
	description mediumblob NOT NULL,
	description_bbcode_bitfield varbinary(255) DEFAULT '' NOT NULL,
	description_bbcode_uid varbinary(8) DEFAULT '' NOT NULL,
	instant_redirect tinyint(1) UNSIGNED DEFAULT '1' NOT NULL,
	PRIMARY KEY (user_id)
);

