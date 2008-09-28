BEGIN TRANSACTION;

# Table: 'phpbb_blogs_poll_options'
CREATE TABLE phpbb_blogs_poll_options (
	poll_option_id tinyint(4) NOT NULL DEFAULT '0',
	blog_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	poll_option_text text(65535) NOT NULL DEFAULT '',
	poll_option_total INTEGER UNSIGNED NOT NULL DEFAULT '0'
);

CREATE INDEX phpbb_blogs_poll_options_poll_opt_id ON phpbb_blogs_poll_options (poll_option_id);
CREATE INDEX phpbb_blogs_poll_options_blog_id ON phpbb_blogs_poll_options (blog_id);

# Table: 'phpbb_blogs_poll_votes'
CREATE TABLE phpbb_blogs_poll_votes (
	blog_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	poll_option_id tinyint(4) NOT NULL DEFAULT '0',
	vote_user_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	vote_user_ip varchar(40) NOT NULL DEFAULT ''
);

CREATE INDEX phpbb_blogs_poll_votes_blog_id ON phpbb_blogs_poll_votes (blog_id);
CREATE INDEX phpbb_blogs_poll_votes_vote_user_id ON phpbb_blogs_poll_votes (vote_user_id);
CREATE INDEX phpbb_blogs_poll_votes_vote_user_ip ON phpbb_blogs_poll_votes (vote_user_ip);


COMMIT;