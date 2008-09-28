#
# $Id: mysql_40_schema.sql 485 2008-08-15 23:33:57Z exreaction@gmail.com $
#

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
	blog_read_count mediumint(8) UNSIGNED DEFAULT '1' NOT NULL,
	blog_reply_count mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	blog_real_reply_count mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	blog_attachment tinyint(1) UNSIGNED DEFAULT '0' NOT NULL,
	perm_guest tinyint(1) DEFAULT '1' NOT NULL,
	perm_registered tinyint(1) DEFAULT '2' NOT NULL,
	perm_foe tinyint(1) DEFAULT '0' NOT NULL,
	perm_friend tinyint(1) DEFAULT '2' NOT NULL,
	rating decimal(6,2) DEFAULT '0' NOT NULL,
	num_ratings mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	poll_title blob NOT NULL,
	poll_start int(11) UNSIGNED DEFAULT '0' NOT NULL,
	poll_length int(11) UNSIGNED DEFAULT '0' NOT NULL,
	poll_max_options tinyint(4) DEFAULT '1' NOT NULL,
	poll_last_vote int(11) UNSIGNED DEFAULT '0' NOT NULL,
	poll_vote_change tinyint(1) UNSIGNED DEFAULT '0' NOT NULL,
	PRIMARY KEY (blog_id),
	KEY user_id (user_id),
	KEY user_ip (user_ip),
	KEY blog_approved (blog_approved),
	KEY blog_deleted (blog_deleted),
	KEY perm_guest (perm_guest),
	KEY perm_registered (perm_registered),
	KEY perm_foe (perm_foe),
	KEY perm_friend (perm_friend),
	KEY rating (rating)
);


# Table: 'phpbb_blogs_attachment'
CREATE TABLE phpbb_blogs_attachment (
	attach_id mediumint(8) UNSIGNED NOT NULL auto_increment,
	blog_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	reply_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	poster_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	is_orphan tinyint(1) UNSIGNED DEFAULT '1' NOT NULL,
	physical_filename varbinary(255) DEFAULT '' NOT NULL,
	real_filename varbinary(255) DEFAULT '' NOT NULL,
	download_count mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	attach_comment blob NOT NULL,
	extension varbinary(100) DEFAULT '' NOT NULL,
	mimetype varbinary(100) DEFAULT '' NOT NULL,
	filesize int(20) UNSIGNED DEFAULT '0' NOT NULL,
	filetime int(11) UNSIGNED DEFAULT '0' NOT NULL,
	thumbnail tinyint(1) UNSIGNED DEFAULT '0' NOT NULL,
	PRIMARY KEY (attach_id),
	KEY blog_id (blog_id),
	KEY reply_id (reply_id),
	KEY filetime (filetime),
	KEY poster_id (poster_id),
	KEY is_orphan (is_orphan)
);


# Table: 'phpbb_blogs_categories'
CREATE TABLE phpbb_blogs_categories (
	category_id mediumint(8) UNSIGNED NOT NULL auto_increment,
	parent_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	left_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	right_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	category_name blob NOT NULL,
	category_description mediumblob NOT NULL,
	category_description_bitfield varbinary(255) DEFAULT '' NOT NULL,
	category_description_uid varbinary(8) DEFAULT '' NOT NULL,
	category_description_options int(11) UNSIGNED DEFAULT '7' NOT NULL,
	rules mediumblob NOT NULL,
	rules_bitfield varbinary(255) DEFAULT '' NOT NULL,
	rules_uid varbinary(8) DEFAULT '' NOT NULL,
	rules_options int(11) UNSIGNED DEFAULT '7' NOT NULL,
	blog_count mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	PRIMARY KEY (category_id),
	KEY left_right_id (left_id, right_id)
);


# Table: 'phpbb_blogs_in_categories'
CREATE TABLE phpbb_blogs_in_categories (
	blog_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	category_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	PRIMARY KEY (blog_id, category_id)
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


# Table: 'phpbb_blogs_poll_options'
CREATE TABLE phpbb_blogs_poll_options (
	poll_option_id tinyint(4) DEFAULT '0' NOT NULL,
	blog_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	poll_option_text blob NOT NULL,
	poll_option_total mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	KEY poll_opt_id (poll_option_id),
	KEY blog_id (blog_id)
);


# Table: 'phpbb_blogs_poll_votes'
CREATE TABLE phpbb_blogs_poll_votes (
	blog_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	poll_option_id tinyint(4) DEFAULT '0' NOT NULL,
	vote_user_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	vote_user_ip varbinary(40) DEFAULT '' NOT NULL,
	KEY blog_id (blog_id),
	KEY vote_user_id (vote_user_id),
	KEY vote_user_ip (vote_user_ip)
);


# Table: 'phpbb_blogs_ratings'
CREATE TABLE phpbb_blogs_ratings (
	blog_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	user_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	rating mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	PRIMARY KEY (blog_id, user_id)
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
	reply_attachment tinyint(1) UNSIGNED DEFAULT '0' NOT NULL,
	PRIMARY KEY (reply_id),
	KEY blog_id (blog_id),
	KEY user_id (user_id),
	KEY user_ip (user_ip),
	KEY reply_approved (reply_approved),
	KEY reply_deleted (reply_deleted)
);


# Table: 'phpbb_blogs_subscription'
CREATE TABLE phpbb_blogs_subscription (
	sub_user_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	sub_type int(11) UNSIGNED DEFAULT '0' NOT NULL,
	blog_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	user_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	PRIMARY KEY (sub_user_id, sub_type, blog_id, user_id)
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
	blog_subscription_default int(11) UNSIGNED DEFAULT '0' NOT NULL,
	blog_style blob NOT NULL,
	blog_css mediumblob NOT NULL,
	PRIMARY KEY (user_id)
);


# Table: 'phpbb_blog_search_wordlist'
CREATE TABLE phpbb_blog_search_wordlist (
	word_id mediumint(8) UNSIGNED NOT NULL auto_increment,
	word_text blob NOT NULL,
	word_common tinyint(1) UNSIGNED DEFAULT '0' NOT NULL,
	word_count mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	PRIMARY KEY (word_id),
	UNIQUE word_text (word_text(255)),
	KEY word_count (word_count)
);


# Table: 'phpbb_blog_search_wordmatch'
CREATE TABLE phpbb_blog_search_wordmatch (
	blog_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	reply_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	word_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	title_match tinyint(1) UNSIGNED DEFAULT '0' NOT NULL,
	UNIQUE unique_match (blog_id, reply_id, word_id, title_match),
	KEY word_id (word_id),
	KEY blog_id (blog_id),
	KEY reply_id (reply_id)
);


