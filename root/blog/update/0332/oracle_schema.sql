/*
User Blogs Mod Database Schema
*/


/*
	Table: 'phpbb_blogs_categories'
*/
CREATE TABLE phpbb_blogs_categories (
	category_id number(8) NOT NULL,
	parent_id number(8) DEFAULT '0' NOT NULL,
	left_id number(8) DEFAULT '0' NOT NULL,
	right_id number(8) DEFAULT '0' NOT NULL,
	category_name varchar2(765) DEFAULT '' ,
	category_description clob DEFAULT '' ,
	category_description_bitfield varchar2(255) DEFAULT '' ,
	category_description_uid varchar2(8) DEFAULT '' ,
	category_description_options number(11) DEFAULT '7' NOT NULL,
	rules clob DEFAULT '' ,
	rules_bitfield varchar2(255) DEFAULT '' ,
	rules_uid varchar2(8) DEFAULT '' ,
	rules_options number(11) DEFAULT '7' NOT NULL,
	blog_count number(8) DEFAULT '0' NOT NULL,
	CONSTRAINT pk_phpbb_blogs_categories PRIMARY KEY (category_id)
)
/

CREATE INDEX phpbb_blogs_categories_left_right_id ON phpbb_blogs_categories (left_id, right_id)
/

CREATE SEQUENCE phpbb_blogs_categories_seq
/

CREATE OR REPLACE TRIGGER t_phpbb_blogs_categories
BEFORE INSERT ON phpbb_blogs_categories
FOR EACH ROW WHEN (
	new.category_id IS NULL OR new.category_id = 0
)
BEGIN
	SELECT phpbb_blogs_categories_seq.nextval
	INTO :new.category_id
	FROM dual;
END;
/


/*
	Table: 'phpbb_blogs_in_categories'
*/
CREATE TABLE phpbb_blogs_in_categories (
	blog_id number(8) DEFAULT '0' NOT NULL,
	category_id number(8) DEFAULT '0' NOT NULL,
	CONSTRAINT pk_phpbb_blogs_in_categories PRIMARY KEY (blog_id, category_id)
)
/


