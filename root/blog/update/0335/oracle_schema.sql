/*
User Blogs Mod Database Schema
*/


/*
	Table: 'phpbb_blogs_ratings'
*/
CREATE TABLE phpbb_blogs_ratings (
	blog_id number(8) DEFAULT '0' NOT NULL,
	user_id number(8) DEFAULT '0' NOT NULL,
	rating number(8) DEFAULT '0' NOT NULL,
	CONSTRAINT pk_phpbb_blogs_ratings PRIMARY KEY (blog_id, user_id)
)
/


