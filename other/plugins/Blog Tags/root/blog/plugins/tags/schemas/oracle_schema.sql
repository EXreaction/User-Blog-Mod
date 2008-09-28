/*

 $Id: $

*/


/*
	Table: 'phpbb_blogs_tags'
*/
CREATE TABLE phpbb_blogs_tags (
	tag_id number(8) NOT NULL,
	tag_name clob DEFAULT '' ,
	tag_count number(8) DEFAULT '0' NOT NULL,
	CONSTRAINT pk_phpbb_blogs_tags PRIMARY KEY (tag_id)
)
/

CREATE INDEX pbt_tag_count ON phpbb_blogs_tags (tag_count)
/

CREATE SEQUENCE phpbb_blogs_tags_seq
/

CREATE OR REPLACE TRIGGER t_phpbb_blogs_tags
BEFORE INSERT ON phpbb_blogs_tags
FOR EACH ROW WHEN (
	new.tag_id IS NULL OR new.tag_id = 0
)
BEGIN
	SELECT phpbb_blogs_tags_seq.nextval
	INTO :new.tag_id
	FROM dual;
END;
/


