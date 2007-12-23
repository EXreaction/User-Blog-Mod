# User Blogs Mod Database Schema

BEGIN TRANSACTION;

# Table: 'phpbb_blogs'
CREATE TABLE phpbb_blogs (
	blog_id INTEGER PRIMARY KEY NOT NULL ,
	user_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	user_ip varchar(40) NOT NULL DEFAULT '',
	blog_subject text(65535) NOT NULL DEFAULT '',
	blog_text mediumtext(16777215) NOT NULL DEFAULT '',
	blog_checksum varchar(32) NOT NULL DEFAULT '',
	blog_time INTEGER UNSIGNED NOT NULL DEFAULT '0',
	blog_approved INTEGER UNSIGNED NOT NULL DEFAULT '1',
	blog_reported INTEGER UNSIGNED NOT NULL DEFAULT '0',
	enable_bbcode INTEGER UNSIGNED NOT NULL DEFAULT '1',
	enable_smilies INTEGER UNSIGNED NOT NULL DEFAULT '1',
	enable_magic_url INTEGER UNSIGNED NOT NULL DEFAULT '1',
	bbcode_bitfield varchar(255) NOT NULL DEFAULT '',
	bbcode_uid varchar(8) NOT NULL DEFAULT '',
	blog_edit_time INTEGER UNSIGNED NOT NULL DEFAULT '0',
	blog_edit_reason text(65535) NOT NULL DEFAULT '',
	blog_edit_user INTEGER UNSIGNED NOT NULL DEFAULT '0',
	blog_edit_count INTEGER UNSIGNED NOT NULL DEFAULT '0',
	blog_edit_locked INTEGER UNSIGNED NOT NULL DEFAULT '0',
	blog_deleted INTEGER UNSIGNED NOT NULL DEFAULT '0',
	blog_deleted_time INTEGER UNSIGNED NOT NULL DEFAULT '0',
	blog_read_count INTEGER UNSIGNED NOT NULL DEFAULT '1',
	blog_reply_count INTEGER UNSIGNED NOT NULL DEFAULT '0',
	blog_real_reply_count INTEGER UNSIGNED NOT NULL DEFAULT '0',
	perm_guest tinyint(1) NOT NULL DEFAULT '1',
	perm_registered tinyint(1) NOT NULL DEFAULT '2',
	perm_foe tinyint(1) NOT NULL DEFAULT '0',
	perm_friend tinyint(1) NOT NULL DEFAULT '2'
);

CREATE INDEX phpbb_blogs_user_id ON phpbb_blogs (user_id);
CREATE INDEX phpbb_blogs_user_ip ON phpbb_blogs (user_ip);
CREATE INDEX phpbb_blogs_blog_approved ON phpbb_blogs (blog_approved);
CREATE INDEX phpbb_blogs_blog_deleted ON phpbb_blogs (blog_deleted);
CREATE INDEX phpbb_blogs_perm_guest ON phpbb_blogs (perm_guest);
CREATE INDEX phpbb_blogs_perm_registered ON phpbb_blogs (perm_registered);
CREATE INDEX phpbb_blogs_perm_foe ON phpbb_blogs (perm_foe);
CREATE INDEX phpbb_blogs_perm_friend ON phpbb_blogs (perm_friend);

# Table: 'phpbb_blogs_reply'
CREATE TABLE phpbb_blogs_reply (
	reply_id INTEGER PRIMARY KEY NOT NULL ,
	blog_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	user_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	user_ip varchar(40) NOT NULL DEFAULT '',
	reply_subject text(65535) NOT NULL DEFAULT '',
	reply_text mediumtext(16777215) NOT NULL DEFAULT '',
	reply_checksum varchar(32) NOT NULL DEFAULT '',
	reply_time INTEGER UNSIGNED NOT NULL DEFAULT '0',
	reply_approved INTEGER UNSIGNED NOT NULL DEFAULT '1',
	reply_reported INTEGER UNSIGNED NOT NULL DEFAULT '0',
	enable_bbcode INTEGER UNSIGNED NOT NULL DEFAULT '1',
	enable_smilies INTEGER UNSIGNED NOT NULL DEFAULT '1',
	enable_magic_url INTEGER UNSIGNED NOT NULL DEFAULT '1',
	bbcode_bitfield varchar(255) NOT NULL DEFAULT '',
	bbcode_uid varchar(8) NOT NULL DEFAULT '',
	reply_edit_time INTEGER UNSIGNED NOT NULL DEFAULT '0',
	reply_edit_reason text(65535) NOT NULL DEFAULT '',
	reply_edit_user INTEGER UNSIGNED NOT NULL DEFAULT '0',
	reply_edit_count INTEGER UNSIGNED NOT NULL DEFAULT '0',
	reply_edit_locked INTEGER UNSIGNED NOT NULL DEFAULT '0',
	reply_deleted INTEGER UNSIGNED NOT NULL DEFAULT '0',
	reply_deleted_time INTEGER UNSIGNED NOT NULL DEFAULT '0'
);

CREATE INDEX phpbb_blogs_reply_blog_id ON phpbb_blogs_reply (blog_id);
CREATE INDEX phpbb_blogs_reply_user_id ON phpbb_blogs_reply (user_id);
CREATE INDEX phpbb_blogs_reply_user_ip ON phpbb_blogs_reply (user_ip);
CREATE INDEX phpbb_blogs_reply_reply_approved ON phpbb_blogs_reply (reply_approved);
CREATE INDEX phpbb_blogs_reply_reply_deleted ON phpbb_blogs_reply (reply_deleted);

# Table: 'phpbb_blogs_subscription'
CREATE TABLE phpbb_blogs_subscription (
	sub_user_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	sub_type tinyint(1) NOT NULL DEFAULT '1',
	blog_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	user_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (sub_user_id, sub_type, blog_id, user_id)
);


# Table: 'phpbb_blogs_plugins'
CREATE TABLE phpbb_blogs_plugins (
	plugin_id INTEGER PRIMARY KEY NOT NULL ,
	plugin_name text(65535) NOT NULL DEFAULT '',
	plugin_enabled INTEGER UNSIGNED NOT NULL DEFAULT '0',
	plugin_version text(65535) NOT NULL DEFAULT ''
);

CREATE INDEX phpbb_blogs_plugins_plugin_name ON phpbb_blogs_plugins (plugin_name);
CREATE INDEX phpbb_blogs_plugins_plugin_enabled ON phpbb_blogs_plugins (plugin_enabled);

# Table: 'phpbb_blogs_users'
CREATE TABLE phpbb_blogs_users (
	user_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	perm_guest tinyint(1) NOT NULL DEFAULT '1',
	perm_registered tinyint(1) NOT NULL DEFAULT '2',
	perm_foe tinyint(1) NOT NULL DEFAULT '0',
	perm_friend tinyint(1) NOT NULL DEFAULT '2',
	title text(65535) NOT NULL DEFAULT '',
	description mediumtext(16777215) NOT NULL DEFAULT '',
	description_bbcode_bitfield varchar(255) NOT NULL DEFAULT '',
	description_bbcode_uid varchar(8) NOT NULL DEFAULT '',
	instant_redirect INTEGER UNSIGNED NOT NULL DEFAULT '1',
	PRIMARY KEY (user_id)
);


# Table: 'phpbb_blog_search_wordlist'
CREATE TABLE phpbb_blog_search_wordlist (
	word_id INTEGER PRIMARY KEY NOT NULL ,
	word_text varchar(255) NOT NULL DEFAULT '',
	word_common INTEGER UNSIGNED NOT NULL DEFAULT '0',
	word_count INTEGER UNSIGNED NOT NULL DEFAULT '0'
);

CREATE UNIQUE INDEX phpbb_blog_search_wordlist_wrd_txt ON phpbb_blog_search_wordlist (word_text);
CREATE INDEX phpbb_blog_search_wordlist_wrd_cnt ON phpbb_blog_search_wordlist (word_count);

# Table: 'phpbb_blog_search_wordmatch'
CREATE TABLE phpbb_blog_search_wordmatch (
	blog_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	reply_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	word_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	title_match INTEGER UNSIGNED NOT NULL DEFAULT '0'
);

CREATE UNIQUE INDEX phpbb_blog_search_wordmatch_unq_mtch ON phpbb_blog_search_wordmatch (blog_id, reply_id, word_id, title_match);
CREATE INDEX phpbb_blog_search_wordmatch_word_id ON phpbb_blog_search_wordmatch (word_id);
CREATE INDEX phpbb_blog_search_wordmatch_blog_id ON phpbb_blog_search_wordmatch (blog_id);
CREATE INDEX phpbb_blog_search_wordmatch_reply_id ON phpbb_blog_search_wordmatch (reply_id);

# Table: 'phpbb_blogs_categories'
CREATE TABLE phpbb_blogs_categories (
	category_id INTEGER PRIMARY KEY NOT NULL ,
	parent_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	left_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	right_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	category_name text(65535) NOT NULL DEFAULT '',
	category_description mediumtext(16777215) NOT NULL DEFAULT '',
	category_description_bitfield varchar(255) NOT NULL DEFAULT '',
	category_description_uid varchar(8) NOT NULL DEFAULT '',
	category_description_options INTEGER UNSIGNED NOT NULL DEFAULT '7',
	rules mediumtext(16777215) NOT NULL DEFAULT '',
	rules_bitfield varchar(255) NOT NULL DEFAULT '',
	rules_uid varchar(8) NOT NULL DEFAULT '',
	rules_options INTEGER UNSIGNED NOT NULL DEFAULT '7',
	blog_count INTEGER UNSIGNED NOT NULL DEFAULT '0'
);

CREATE INDEX phpbb_blogs_categories_left_right_id ON phpbb_blogs_categories (left_id, right_id);

# Table: 'phpbb_blogs_in_categories'
CREATE TABLE phpbb_blogs_in_categories (
	blog_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	category_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (blog_id, category_id)
);



COMMIT;