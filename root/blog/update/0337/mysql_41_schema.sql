# Table: 'phpbb_blogs_poll_options'
CREATE TABLE phpbb_blogs_poll_options (
	poll_option_id tinyint(4) DEFAULT '0' NOT NULL,
	blog_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	poll_option_text text NOT NULL,
	poll_option_total mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	KEY poll_opt_id (poll_option_id),
	KEY blog_id (blog_id)
) CHARACTER SET `utf8` COLLATE `utf8_bin`;


# Table: 'phpbb_blogs_poll_votes'
CREATE TABLE phpbb_blogs_poll_votes (
	blog_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	poll_option_id tinyint(4) DEFAULT '0' NOT NULL,
	vote_user_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	vote_user_ip varchar(40) DEFAULT '' NOT NULL,
	KEY blog_id (blog_id),
	KEY vote_user_id (vote_user_id),
	KEY vote_user_ip (vote_user_ip)
) CHARACTER SET `utf8` COLLATE `utf8_bin`;


