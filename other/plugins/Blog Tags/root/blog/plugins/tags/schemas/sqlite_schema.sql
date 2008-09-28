#
# $Id: $
#

BEGIN TRANSACTION;

# Table: 'phpbb_blogs_tags'
CREATE TABLE phpbb_blogs_tags (
	tag_id INTEGER PRIMARY KEY NOT NULL ,
	tag_name mediumtext(16777215) NOT NULL DEFAULT '',
	tag_count INTEGER UNSIGNED NOT NULL DEFAULT '0'
);

CREATE INDEX phpbb_blogs_tags_tag_count ON phpbb_blogs_tags (tag_count);


COMMIT;