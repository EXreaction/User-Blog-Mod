# User Blogs Mod Database Schema


# Table: 'phpbb_blogs_categories'
CREATE TABLE phpbb_blogs_categories (
	category_id INTEGER NOT NULL,
	parent_id INTEGER DEFAULT 0 NOT NULL,
	left_id INTEGER DEFAULT 0 NOT NULL,
	right_id INTEGER DEFAULT 0 NOT NULL,
	category_name VARCHAR(255) CHARACTER SET UTF8 DEFAULT '' NOT NULL COLLATE UNICODE,
	category_description BLOB SUB_TYPE TEXT CHARACTER SET UTF8 DEFAULT '' NOT NULL,
	category_description_bitfield VARCHAR(255) CHARACTER SET NONE DEFAULT '' NOT NULL,
	category_description_uid VARCHAR(8) CHARACTER SET NONE DEFAULT '' NOT NULL,
	category_description_options INTEGER DEFAULT 7 NOT NULL,
	rules BLOB SUB_TYPE TEXT CHARACTER SET UTF8 DEFAULT '' NOT NULL,
	rules_bitfield VARCHAR(255) CHARACTER SET NONE DEFAULT '' NOT NULL,
	rules_uid VARCHAR(8) CHARACTER SET NONE DEFAULT '' NOT NULL,
	rules_options INTEGER DEFAULT 7 NOT NULL,
	blog_count INTEGER DEFAULT 0 NOT NULL
);;

ALTER TABLE phpbb_blogs_categories ADD PRIMARY KEY (category_id);;

CREATE INDEX phpbb_blogs_categories_left_right_id ON phpbb_blogs_categories(left_id, right_id);;

CREATE GENERATOR phpbb_blogs_categories_gen;;
SET GENERATOR phpbb_blogs_categories_gen TO 0;;

CREATE TRIGGER t_phpbb_blogs_categories FOR phpbb_blogs_categories
BEFORE INSERT
AS
BEGIN
	NEW.category_id = GEN_ID(phpbb_blogs_categories_gen, 1);
END;;


# Table: 'phpbb_blogs_in_categories'
CREATE TABLE phpbb_blogs_in_categories (
	blog_id INTEGER DEFAULT 0 NOT NULL,
	category_id INTEGER DEFAULT 0 NOT NULL
);;

ALTER TABLE phpbb_blogs_in_categories ADD PRIMARY KEY (blog_id, category_id);;


