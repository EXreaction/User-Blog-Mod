/*
User Blogs Mod Database Schema
*/

BEGIN;


/*
	Table: 'phpbb_blogs_ratings'
*/
CREATE TABLE phpbb_blogs_ratings (
	blog_id INT4 DEFAULT '0' NOT NULL CHECK (blog_id >= 0),
	user_id INT4 DEFAULT '0' NOT NULL CHECK (user_id >= 0),
	rating INT4 DEFAULT '0' NOT NULL CHECK (rating >= 0),
	PRIMARY KEY (blog_id, user_id)
);



COMMIT;