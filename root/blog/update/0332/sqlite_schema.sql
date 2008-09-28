# User Blogs Mod Database Schema

BEGIN TRANSACTION;

# Table: 'phpbb_blogs_categories'
CREATE TABLE phpbb_blogs_categories (
	category_id INTEGER PRIMARY KEY NOT NULL ,
	parent_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	left_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	right_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	category_name text(65535) NOT NULL DEFAULT '',
	category_description mediumtext(16777215) NOT NULL DEFAULT '',
	category_description_bitfield varchar(255) NOT NULL DEFAULT '',
	category_description_uid varchar(8) NOT NULL DEFAULT '',
	category_description_options INTEGER UNSIGNED NOT NULL DEFAULT '7',
	rules mediumtext(16777215) NOT NULL DEFAULT '',
	rules_bitfield varchar(255) NOT NULL DEFAULT '',
	rules_uid varchar(8) NOT NULL DEFAULT '',
	rules_options INTEGER UNSIGNED NOT NULL DEFAULT '7',
	blog_count INTEGER UNSIGNED NOT NULL DEFAULT '0'
);

CREATE INDEX phpbb_blogs_categories_left_right_id ON phpbb_blogs_categories (left_id, right_id);

# Table: 'phpbb_blogs_in_categories'
CREATE TABLE phpbb_blogs_in_categories (
	blog_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	category_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (blog_id, category_id)
);



COMMIT;