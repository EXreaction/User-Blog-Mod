/*

 $Id: postgres_schema.sql 485 2008-08-15 23:33:57Z exreaction@gmail.com $

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
	blog_subject varchar(255) DEFAULT '' NOT NULL,
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
	blog_read_count INT4 DEFAULT '1' NOT NULL CHECK (blog_read_count >= 0),
	blog_reply_count INT4 DEFAULT '0' NOT NULL CHECK (blog_reply_count >= 0),
	blog_real_reply_count INT4 DEFAULT '0' NOT NULL CHECK (blog_real_reply_count >= 0),
	blog_attachment INT2 DEFAULT '0' NOT NULL CHECK (blog_attachment >= 0),
	perm_guest INT2 DEFAULT '1' NOT NULL,
	perm_registered INT2 DEFAULT '2' NOT NULL,
	perm_foe INT2 DEFAULT '0' NOT NULL,
	perm_friend INT2 DEFAULT '2' NOT NULL,
	rating decimal(6,2) DEFAULT '0' NOT NULL,
	num_ratings INT4 DEFAULT '0' NOT NULL CHECK (num_ratings >= 0),
	poll_title varchar(255) DEFAULT '' NOT NULL,
	poll_start INT4 DEFAULT '0' NOT NULL CHECK (poll_start >= 0),
	poll_length INT4 DEFAULT '0' NOT NULL CHECK (poll_length >= 0),
	poll_max_options INT2 DEFAULT '1' NOT NULL,
	poll_last_vote INT4 DEFAULT '0' NOT NULL CHECK (poll_last_vote >= 0),
	poll_vote_change INT2 DEFAULT '0' NOT NULL CHECK (poll_vote_change >= 0),
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
CREATE INDEX phpbb_blogs_rating ON phpbb_blogs (rating);

/*
	Table: 'phpbb_blogs_attachment'
*/
CREATE SEQUENCE phpbb_blogs_attachment_seq;

CREATE TABLE phpbb_blogs_attachment (
	attach_id INT4 DEFAULT nextval('phpbb_blogs_attachment_seq'),
	blog_id INT4 DEFAULT '0' NOT NULL CHECK (blog_id >= 0),
	reply_id INT4 DEFAULT '0' NOT NULL CHECK (reply_id >= 0),
	poster_id INT4 DEFAULT '0' NOT NULL CHECK (poster_id >= 0),
	is_orphan INT2 DEFAULT '1' NOT NULL CHECK (is_orphan >= 0),
	physical_filename varchar(255) DEFAULT '' NOT NULL,
	real_filename varchar(255) DEFAULT '' NOT NULL,
	download_count INT4 DEFAULT '0' NOT NULL CHECK (download_count >= 0),
	attach_comment varchar(4000) DEFAULT '' NOT NULL,
	extension varchar(100) DEFAULT '' NOT NULL,
	mimetype varchar(100) DEFAULT '' NOT NULL,
	filesize INT4 DEFAULT '0' NOT NULL CHECK (filesize >= 0),
	filetime INT4 DEFAULT '0' NOT NULL CHECK (filetime >= 0),
	thumbnail INT2 DEFAULT '0' NOT NULL CHECK (thumbnail >= 0),
	PRIMARY KEY (attach_id)
);

CREATE INDEX phpbb_blogs_attachment_blog_id ON phpbb_blogs_attachment (blog_id);
CREATE INDEX phpbb_blogs_attachment_reply_id ON phpbb_blogs_attachment (reply_id);
CREATE INDEX phpbb_blogs_attachment_filetime ON phpbb_blogs_attachment (filetime);
CREATE INDEX phpbb_blogs_attachment_poster_id ON phpbb_blogs_attachment (poster_id);
CREATE INDEX phpbb_blogs_attachment_is_orphan ON phpbb_blogs_attachment (is_orphan);

/*
	Table: 'phpbb_blogs_categories'
*/
CREATE SEQUENCE phpbb_blogs_categories_seq;

CREATE TABLE phpbb_blogs_categories (
	category_id INT4 DEFAULT nextval('phpbb_blogs_categories_seq'),
	parent_id INT4 DEFAULT '0' NOT NULL CHECK (parent_id >= 0),
	left_id INT4 DEFAULT '0' NOT NULL CHECK (left_id >= 0),
	right_id INT4 DEFAULT '0' NOT NULL CHECK (right_id >= 0),
	category_name varchar(255) DEFAULT '' NOT NULL,
	category_description TEXT DEFAULT '' NOT NULL,
	category_description_bitfield varchar(255) DEFAULT '' NOT NULL,
	category_description_uid varchar(8) DEFAULT '' NOT NULL,
	category_description_options INT4 DEFAULT '7' NOT NULL CHECK (category_description_options >= 0),
	rules TEXT DEFAULT '' NOT NULL,
	rules_bitfield varchar(255) DEFAULT '' NOT NULL,
	rules_uid varchar(8) DEFAULT '' NOT NULL,
	rules_options INT4 DEFAULT '7' NOT NULL CHECK (rules_options >= 0),
	blog_count INT4 DEFAULT '0' NOT NULL CHECK (blog_count >= 0),
	PRIMARY KEY (category_id)
);

CREATE INDEX phpbb_blogs_categories_left_right_id ON phpbb_blogs_categories (left_id, right_id);

/*
	Table: 'phpbb_blogs_in_categories'
*/
CREATE TABLE phpbb_blogs_in_categories (
	blog_id INT4 DEFAULT '0' NOT NULL CHECK (blog_id >= 0),
	category_id INT4 DEFAULT '0' NOT NULL CHECK (category_id >= 0),
	PRIMARY KEY (blog_id, category_id)
);


/*
	Table: 'phpbb_blogs_plugins'
*/
CREATE SEQUENCE phpbb_blogs_plugins_seq;

CREATE TABLE phpbb_blogs_plugins (
	plugin_id INT4 DEFAULT nextval('phpbb_blogs_plugins_seq'),
	plugin_name varchar(255) DEFAULT '' NOT NULL,
	plugin_enabled INT2 DEFAULT '0' NOT NULL CHECK (plugin_enabled >= 0),
	plugin_version varchar(100) DEFAULT '' NOT NULL,
	PRIMARY KEY (plugin_id)
);

CREATE INDEX phpbb_blogs_plugins_plugin_name ON phpbb_blogs_plugins (plugin_name);
CREATE INDEX phpbb_blogs_plugins_plugin_enabled ON phpbb_blogs_plugins (plugin_enabled);

/*
	Table: 'phpbb_blogs_poll_options'
*/
CREATE TABLE phpbb_blogs_poll_options (
	poll_option_id INT2 DEFAULT '0' NOT NULL,
	blog_id INT4 DEFAULT '0' NOT NULL CHECK (blog_id >= 0),
	poll_option_text varchar(4000) DEFAULT '' NOT NULL,
	poll_option_total INT4 DEFAULT '0' NOT NULL CHECK (poll_option_total >= 0)
);

CREATE INDEX phpbb_blogs_poll_options_poll_opt_id ON phpbb_blogs_poll_options (poll_option_id);
CREATE INDEX phpbb_blogs_poll_options_blog_id ON phpbb_blogs_poll_options (blog_id);

/*
	Table: 'phpbb_blogs_poll_votes'
*/
CREATE TABLE phpbb_blogs_poll_votes (
	blog_id INT4 DEFAULT '0' NOT NULL CHECK (blog_id >= 0),
	poll_option_id INT2 DEFAULT '0' NOT NULL,
	vote_user_id INT4 DEFAULT '0' NOT NULL CHECK (vote_user_id >= 0),
	vote_user_ip varchar(40) DEFAULT '' NOT NULL
);

CREATE INDEX phpbb_blogs_poll_votes_blog_id ON phpbb_blogs_poll_votes (blog_id);
CREATE INDEX phpbb_blogs_poll_votes_vote_user_id ON phpbb_blogs_poll_votes (vote_user_id);
CREATE INDEX phpbb_blogs_poll_votes_vote_user_ip ON phpbb_blogs_poll_votes (vote_user_ip);

/*
	Table: 'phpbb_blogs_ratings'
*/
CREATE TABLE phpbb_blogs_ratings (
	blog_id INT4 DEFAULT '0' NOT NULL CHECK (blog_id >= 0),
	user_id INT4 DEFAULT '0' NOT NULL CHECK (user_id >= 0),
	rating INT4 DEFAULT '0' NOT NULL CHECK (rating >= 0),
	PRIMARY KEY (blog_id, user_id)
);


/*
	Table: 'phpbb_blogs_reply'
*/
CREATE SEQUENCE phpbb_blogs_reply_seq;

CREATE TABLE phpbb_blogs_reply (
	reply_id INT4 DEFAULT nextval('phpbb_blogs_reply_seq'),
	blog_id INT4 DEFAULT '0' NOT NULL CHECK (blog_id >= 0),
	user_id INT4 DEFAULT '0' NOT NULL CHECK (user_id >= 0),
	user_ip varchar(40) DEFAULT '' NOT NULL,
	reply_subject varchar(255) DEFAULT '' NOT NULL,
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
	reply_attachment INT2 DEFAULT '0' NOT NULL CHECK (reply_attachment >= 0),
	PRIMARY KEY (reply_id)
);

CREATE INDEX phpbb_blogs_reply_blog_id ON phpbb_blogs_reply (blog_id);
CREATE INDEX phpbb_blogs_reply_user_id ON phpbb_blogs_reply (user_id);
CREATE INDEX phpbb_blogs_reply_user_ip ON phpbb_blogs_reply (user_ip);
CREATE INDEX phpbb_blogs_reply_reply_approved ON phpbb_blogs_reply (reply_approved);
CREATE INDEX phpbb_blogs_reply_reply_deleted ON phpbb_blogs_reply (reply_deleted);

/*
	Table: 'phpbb_blogs_subscription'
*/
CREATE TABLE phpbb_blogs_subscription (
	sub_user_id INT4 DEFAULT '0' NOT NULL CHECK (sub_user_id >= 0),
	sub_type INT4 DEFAULT '0' NOT NULL CHECK (sub_type >= 0),
	blog_id INT4 DEFAULT '0' NOT NULL CHECK (blog_id >= 0),
	user_id INT4 DEFAULT '0' NOT NULL CHECK (user_id >= 0),
	PRIMARY KEY (sub_user_id, sub_type, blog_id, user_id)
);


/*
	Table: 'phpbb_blogs_users'
*/
CREATE TABLE phpbb_blogs_users (
	user_id INT4 DEFAULT '0' NOT NULL CHECK (user_id >= 0),
	perm_guest INT2 DEFAULT '1' NOT NULL,
	perm_registered INT2 DEFAULT '2' NOT NULL,
	perm_foe INT2 DEFAULT '0' NOT NULL,
	perm_friend INT2 DEFAULT '2' NOT NULL,
	title varchar(255) DEFAULT '' NOT NULL,
	description TEXT DEFAULT '' NOT NULL,
	description_bbcode_bitfield varchar(255) DEFAULT '' NOT NULL,
	description_bbcode_uid varchar(8) DEFAULT '' NOT NULL,
	instant_redirect INT2 DEFAULT '1' NOT NULL CHECK (instant_redirect >= 0),
	blog_subscription_default INT4 DEFAULT '0' NOT NULL CHECK (blog_subscription_default >= 0),
	blog_style varchar(255) DEFAULT '' NOT NULL,
	blog_css TEXT DEFAULT '' NOT NULL,
	PRIMARY KEY (user_id)
);


/*
	Table: 'phpbb_blog_search_wordlist'
*/
CREATE SEQUENCE phpbb_blog_search_wordlist_seq;

CREATE TABLE phpbb_blog_search_wordlist (
	word_id INT4 DEFAULT nextval('phpbb_blog_search_wordlist_seq'),
	word_text varchar(255) DEFAULT '' NOT NULL,
	word_common INT2 DEFAULT '0' NOT NULL CHECK (word_common >= 0),
	word_count INT4 DEFAULT '0' NOT NULL CHECK (word_count >= 0),
	PRIMARY KEY (word_id)
);

CREATE UNIQUE INDEX phpbb_blog_search_wordlist_word_text ON phpbb_blog_search_wordlist (word_text);
CREATE INDEX phpbb_blog_search_wordlist_word_count ON phpbb_blog_search_wordlist (word_count);

/*
	Table: 'phpbb_blog_search_wordmatch'
*/
CREATE TABLE phpbb_blog_search_wordmatch (
	blog_id INT4 DEFAULT '0' NOT NULL CHECK (blog_id >= 0),
	reply_id INT4 DEFAULT '0' NOT NULL CHECK (reply_id >= 0),
	word_id INT4 DEFAULT '0' NOT NULL CHECK (word_id >= 0),
	title_match INT2 DEFAULT '0' NOT NULL CHECK (title_match >= 0)
);

CREATE UNIQUE INDEX phpbb_blog_search_wordmatch_unique_match ON phpbb_blog_search_wordmatch (blog_id, reply_id, word_id, title_match);
CREATE INDEX phpbb_blog_search_wordmatch_word_id ON phpbb_blog_search_wordmatch (word_id);
CREATE INDEX phpbb_blog_search_wordmatch_blog_id ON phpbb_blog_search_wordmatch (blog_id);
CREATE INDEX phpbb_blog_search_wordmatch_reply_id ON phpbb_blog_search_wordmatch (reply_id);


COMMIT;