/*

 $Id: $

*/

BEGIN;


/*
	Table: 'phpbb_blogs_tags'
*/
CREATE SEQUENCE phpbb_blogs_tags_seq;

CREATE TABLE phpbb_blogs_tags (
	tag_id INT4 DEFAULT nextval('phpbb_blogs_tags_seq'),
	tag_name TEXT DEFAULT '' NOT NULL,
	tag_count INT4 DEFAULT '0' NOT NULL CHECK (tag_count >= 0),
	PRIMARY KEY (tag_id)
);

CREATE INDEX phpbb_blogs_tags_tag_count ON phpbb_blogs_tags (tag_count);


COMMIT;