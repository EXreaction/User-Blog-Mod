BEGIN;

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


COMMIT;