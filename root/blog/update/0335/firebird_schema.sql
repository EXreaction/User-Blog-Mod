# User Blogs Mod Database Schema


# Table: 'phpbb_blogs_ratings'
CREATE TABLE phpbb_blogs_ratings (
	blog_id INTEGER DEFAULT 0 NOT NULL,
	user_id INTEGER DEFAULT 0 NOT NULL,
	rating INTEGER DEFAULT 0 NOT NULL
);;

ALTER TABLE phpbb_blogs_ratings ADD PRIMARY KEY (blog_id, user_id);;


