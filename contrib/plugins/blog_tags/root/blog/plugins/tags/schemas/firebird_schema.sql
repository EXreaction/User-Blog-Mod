#
# $Id: $
#


# Table: 'phpbb_blogs_tags'
CREATE TABLE phpbb_blogs_tags (
	tag_id INTEGER NOT NULL,
	tag_name BLOB SUB_TYPE TEXT CHARACTER SET UTF8 DEFAULT '' NOT NULL,
	tag_count INTEGER DEFAULT 0 NOT NULL
);;

ALTER TABLE phpbb_blogs_tags ADD PRIMARY KEY (tag_id);;

CREATE INDEX phpbb_blogs_tags_tag_count ON phpbb_blogs_tags(tag_count);;

CREATE GENERATOR phpbb_blogs_tags_gen;;
SET GENERATOR phpbb_blogs_tags_gen TO 0;;

CREATE TRIGGER t_phpbb_blogs_tags FOR phpbb_blogs_tags
BEFORE INSERT
AS
BEGIN
	NEW.tag_id = GEN_ID(phpbb_blogs_tags_gen, 1);
END;;


