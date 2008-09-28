/*
User Blogs Mod Database Schema
*/

BEGIN;


/*
	Table: 'phpbb_blogs_categories'
*/
CREATE SEQUENCE phpbb_blogs_categories_seq;

CREATE TABLE phpbb_blogs_categories (
	category_id INT4 DEFAULT nextval('phpbb_blogs_categories_seq'),
	parent_id INT4 DEFAULT '0' NOT NULL CHECK (parent_id >= 0),
	left_id INT4 DEFAULT '0' NOT NULL CHECK (left_id >= 0),
	right_id INT4 DEFAULT '0' NOT NULL CHECK (right_id >= 0),
	category_name varchar(255) DEFAULT '' NOT NULL,
	category_description TEXT DEFAULT '' NOT NULL,
	category_description_bitfield varchar(255) DEFAULT '' NOT NULL,
	category_description_uid varchar(8) DEFAULT '' NOT NULL,
	category_description_options INT4 DEFAULT '7' NOT NULL CHECK (category_description_options >= 0),
	rules TEXT DEFAULT '' NOT NULL,
	rules_bitfield varchar(255) DEFAULT '' NOT NULL,
	rules_uid varchar(8) DEFAULT '' NOT NULL,
	rules_options INT4 DEFAULT '7' NOT NULL CHECK (rules_options >= 0),
	blog_count INT4 DEFAULT '0' NOT NULL CHECK (blog_count >= 0),
	PRIMARY KEY (category_id)
);

CREATE INDEX phpbb_blogs_categories_left_right_id ON phpbb_blogs_categories (left_id, right_id);

/*
	Table: 'phpbb_blogs_in_categories'
*/
CREATE TABLE phpbb_blogs_in_categories (
	blog_id INT4 DEFAULT '0' NOT NULL CHECK (blog_id >= 0),
	category_id INT4 DEFAULT '0' NOT NULL CHECK (category_id >= 0),
	PRIMARY KEY (blog_id, category_id)
);



COMMIT;