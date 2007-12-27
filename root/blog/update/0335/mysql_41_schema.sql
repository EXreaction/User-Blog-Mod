# User Blogs Mod Database Schema

# Table: 'phpbb_blogs_ratings'
CREATE TABLE phpbb_blogs_ratings (
	blog_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	user_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	rating mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	PRIMARY KEY (blog_id, user_id)
) CHARACTER SET `utf8` COLLATE `utf8_bin`;


