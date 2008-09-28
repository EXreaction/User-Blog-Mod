/*
	Table: 'phpbb_blogs_poll_options'
*/
CREATE TABLE phpbb_blogs_poll_options (
	poll_option_id number(4) DEFAULT '0' NOT NULL,
	blog_id number(8) DEFAULT '0' NOT NULL,
	poll_option_text clob DEFAULT '' ,
	poll_option_total number(8) DEFAULT '0' NOT NULL
)
/

CREATE INDEX phpbb_blogs_poll_options_poll_opt_id ON phpbb_blogs_poll_options (poll_option_id)
/
CREATE INDEX phpbb_blogs_poll_options_blog_id ON phpbb_blogs_poll_options (blog_id)
/

/*
	Table: 'phpbb_blogs_poll_votes'
*/
CREATE TABLE phpbb_blogs_poll_votes (
	blog_id number(8) DEFAULT '0' NOT NULL,
	poll_option_id number(4) DEFAULT '0' NOT NULL,
	vote_user_id number(8) DEFAULT '0' NOT NULL,
	vote_user_ip varchar2(40) DEFAULT '' 
)
/

CREATE INDEX phpbb_blogs_poll_votes_blog_id ON phpbb_blogs_poll_votes (blog_id)
/
CREATE INDEX phpbb_blogs_poll_votes_vote_user_id ON phpbb_blogs_poll_votes (vote_user_id)
/
CREATE INDEX phpbb_blogs_poll_votes_vote_user_ip ON phpbb_blogs_poll_votes (vote_user_ip)
/

