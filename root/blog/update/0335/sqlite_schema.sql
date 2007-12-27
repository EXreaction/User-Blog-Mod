# User Blogs Mod Database Schema

BEGIN TRANSACTION;

# Table: 'phpbb_blogs_ratings'
CREATE TABLE phpbb_blogs_ratings (
	blog_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	user_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	rating INTEGER UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (blog_id, user_id)
);



COMMIT;